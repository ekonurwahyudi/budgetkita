<?php

namespace App\Imports;

use App\Models\Blok;
use App\Models\KategoriAset;
use App\Models\PembelianAset;
use App\Models\Siklus;
use App\Models\Tambak;
use App\Services\AutoNumberService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToCollection;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class PembelianAsetImport implements ToCollection
{
    private int $imported = 0;
    private bool $processed = false;

    public function collection(Collection $rows): void
    {
        if ($this->processed) return;
        $this->processed = true;

        $headers = $this->headers($rows->first() ?? collect());
        $tambakIds = Auth::user()->tambaks()->pluck('tambaks.id')->all();
        $errors = [];

        DB::transaction(function () use ($rows, $headers, $tambakIds, &$errors) {
            foreach ($rows as $index => $row) {
                if ($index === 0 || $this->isEmptyRow($row)) continue;

                $line = $index + 1;
                $data = $this->rowData($row, $headers);

                try {
                    $namaAset = $this->requiredText($data['nama_aset'] ?? null, 'Nama aset');
                    $tanggal = $this->date($data['tanggal_pembelian'] ?? null);
                    $qty = (int) ($this->number($data['qty'] ?? 1) ?: 1);
                    $hargaSatuan = $this->number($data['harga_satuan'] ?? null);
                    $nominal = round($qty * $hargaSatuan, 2);
                    $statusPembayaran = $this->statusPembayaran($data['status_pembayaran'] ?? 'lunas');
                    $tambak = $this->tambak($data['tambak'] ?? null, $tambakIds);
                    $blok = $this->blok($data['blok'] ?? null, $tambak);
                    $siklus = $this->siklus($data['siklus'] ?? null, $blok);
                    $metode = $this->metodeDepresiasi($data['metode_depresiasi'] ?? null);

                    PembelianAset::create([
                        'tambak_id' => $tambak->id,
                        'blok_id' => $blok?->id,
                        'siklus_id' => $siklus?->id,
                        'nomor_transaksi' => app(AutoNumberService::class)->generate('INVA'),
                        'nama_aset' => $namaAset,
                        'kategori_aset_id' => $this->kategori($data['kategori_aset'] ?? null)->id,
                        'tgl_pembelian' => $tanggal,
                        'qty' => max(1, $qty),
                        'qty_tersedia' => max(1, $qty),
                        'qty_rusak' => 0,
                        'harga_satuan' => $hargaSatuan,
                        'nominal_pembelian' => $nominal,
                        'umur_manfaat' => $metode === 'tanpa' ? 0 : (int) ($this->number($data['umur_manfaat'] ?? 5) ?: 5),
                        'nilai_residu' => 0,
                        'metode_depresiasi' => $metode,
                        'persen_depresiasi' => null,
                        'jenis_pembayaran' => 'cash',
                        'account_bank_id' => null,
                        'status_pembayaran' => $statusPembayaran,
                        'nominal_dibayar' => $statusPembayaran === 'lunas' ? $nominal : 0,
                        'status' => 'awaiting_approval',
                        'created_by' => Auth::id(),
                        'eviden' => $this->urlArray($data['eviden_url'] ?? null),
                        'foto_aset' => $this->urlArray($data['foto_aset_url'] ?? null),
                    ]);

                    $this->imported++;
                } catch (\Throwable $e) {
                    $errors[] = "Baris {$line}: {$e->getMessage()}";
                }
            }

            if ($errors) {
                throw ValidationException::withMessages(['file' => $errors]);
            }
        });
    }

    public function imported(): int
    {
        return $this->imported;
    }

    private function headers(Collection $row): array
    {
        return $row->mapWithKeys(fn ($value, $index) => [$index => $this->key($value)])->all();
    }

    private function rowData(Collection $row, array $headers): array
    {
        $data = [];
        foreach ($row as $index => $value) {
            $key = $headers[$index] ?? null;
            if ($key) $data[$key] = $value;
        }
        return $data;
    }

    private function key($value): string
    {
        $key = strtolower(trim((string) $value));
        $key = str_replace(['.', '/', '-', ' '], '_', $key);

        return match ($key) {
            'tanggal', 'tgl_pembelian', 'tanggal_beli' => 'tanggal_pembelian',
            'kategori', 'kategori_aset' => 'kategori_aset',
            'harga', 'harga_unit', 'harga_satuan' => 'harga_satuan',
            'status_pembayaran', 'status_bayar', 'pembayaran' => 'status_pembayaran',
            'depresiasi', 'metode' => 'metode_depresiasi',
            'umur', 'umur_manfaat' => 'umur_manfaat',
            'eviden', 'url_eviden', 'eviden_url' => 'eviden_url',
            'foto', 'foto_aset', 'url_foto_aset', 'foto_aset_url' => 'foto_aset_url',
            default => $key,
        };
    }

    private function isEmptyRow(Collection $row): bool
    {
        return $row->filter(fn ($value) => trim((string) $value) !== '')->isEmpty();
    }

    private function requiredText($value, string $label): string
    {
        $text = trim((string) $value);
        if ($text === '') throw new \InvalidArgumentException("{$label} wajib diisi.");
        return $text;
    }

    private function nullableText($value): ?string
    {
        $text = trim((string) $value);
        return $text === '' ? null : $text;
    }

    private function date($value): string
    {
        if ($value instanceof \DateTimeInterface) return Carbon::instance($value)->format('Y-m-d');
        if (is_numeric($value)) return Carbon::instance(ExcelDate::excelToDateTimeObject((float) $value))->format('Y-m-d');

        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y'] as $format) {
            try {
                return Carbon::createFromFormat($format, trim((string) $value))->format('Y-m-d');
            } catch (\Throwable) {
            }
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            throw new \InvalidArgumentException('Tanggal pembelian tidak valid.');
        }
    }

    private function number($value): float
    {
        if (is_int($value) || is_float($value)) return max(0, (float) $value);

        $s = preg_replace('/[^0-9.,]/', '', (string) $value);
        if (preg_match('/^\d{1,3}(\.\d{3})+$/', $s)) {
            $s = str_replace('.', '', $s);
        } elseif (preg_match('/^\d{1,3}(,\d{3})+$/', $s)) {
            $s = str_replace(',', '', $s);
        } elseif (str_contains($s, ',') && !str_contains($s, '.')) {
            $s = str_replace(',', '.', $s);
        } elseif (str_contains($s, ',') && str_contains($s, '.')) {
            $s = str_replace(',', '.', str_replace('.', '', $s));
        }

        if (!is_numeric($s) || (float) $s < 0) {
            throw new \InvalidArgumentException('Angka harus valid dan minimal 0.');
        }

        return (float) $s;
    }

    private function kategori($value): KategoriAset
    {
        $value = $this->nullableText($value);
        if (!$value) {
            return KategoriAset::firstOr(fn () => throw new \InvalidArgumentException('Kategori aset belum ada. Isi master kategori aset dulu.'));
        }

        return KategoriAset::where('kode_aset', $value)
            ->orWhere('deskripsi', $value)
            ->firstOr(fn () => throw new \InvalidArgumentException("Kategori aset '{$value}' tidak ditemukan."));
    }

    private function tambak($value, array $tambakIds): Tambak
    {
        $value = $this->nullableText($value);
        if (!$value) {
            return Tambak::whereIn('id', $tambakIds)->firstOr(fn () => throw new \InvalidArgumentException('Tambak wajib diisi atau user belum punya akses tambak.'));
        }

        return Tambak::whereIn('id', $tambakIds)
            ->where('nama_tambak', $value)
            ->firstOr(fn () => throw new \InvalidArgumentException("Tambak '{$value}' tidak ditemukan atau tidak bisa diakses."));
    }

    private function blok($value, Tambak $tambak): ?Blok
    {
        $value = $this->nullableText($value);
        if (!$value) return null;

        return Blok::where('tambak_id', $tambak->id)
            ->where('nama_blok', $value)
            ->firstOr(fn () => throw new \InvalidArgumentException("Blok '{$value}' tidak ditemukan pada tambak tersebut."));
    }

    private function siklus($value, ?Blok $blok): ?Siklus
    {
        $value = $this->nullableText($value);
        if (!$value) return null;
        if (!$blok) throw new \InvalidArgumentException('Blok wajib diisi jika Siklus diisi.');

        return Siklus::where('blok_id', $blok->id)
            ->where('nama_siklus', $value)
            ->firstOr(fn () => throw new \InvalidArgumentException("Siklus '{$value}' tidak ditemukan pada blok tersebut."));
    }

    private function metodeDepresiasi($value): string
    {
        $value = strtolower(str_replace([' ', '-'], '_', trim((string) $value)));
        return match ($value) {
            '', 'tanpa', 'tanpa_depresiasi' => 'tanpa',
            'garis_lurus', 'lurus' => 'garis_lurus',
            'persen', 'persentase' => 'persen',
            default => throw new \InvalidArgumentException('Metode depresiasi harus Tanpa Depresiasi, Garis Lurus, atau Persen.'),
        };
    }

    private function statusPembayaran($value): string
    {
        $value = strtolower(trim((string) $value));
        return match ($value) {
            '', 'lunas' => 'lunas',
            'hutang' => 'hutang',
            default => throw new \InvalidArgumentException('Status pembayaran import aset hanya Lunas atau Hutang.'),
        };
    }

    private function urlArray($value): ?array
    {
        $text = $this->nullableText($value);
        return $text ? array_values(array_filter(array_map('trim', explode(',', $text)))) : null;
    }
}
