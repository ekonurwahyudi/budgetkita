<?php

namespace App\Imports;

use App\Models\AccountBank;
use App\Models\Blok;
use App\Models\ItemTransaksi;
use App\Models\KategoriTransaksi;
use App\Models\Siklus;
use App\Models\SumberDana;
use App\Models\Tambak;
use App\Models\TransaksiKeuangan;
use App\Services\AutoNumberService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToCollection;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class TransaksiKeuanganImport implements ToCollection
{
    private int $imported = 0;

    public function collection(Collection $rows): void
    {
        $headers = $this->headers($rows->first() ?? collect());
        $errors = [];
        $tambakIds = Auth::user()->tambaks()->pluck('tambaks.id')->all();

        DB::transaction(function () use ($rows, $headers, $tambakIds, &$errors) {
            foreach ($rows as $index => $row) {
                if ($index === 0 || $this->isEmptyRow($row)) {
                    continue;
                }

                $line = $index + 1;
                $data = $this->rowData($row, $headers);

                try {
                    $item = $this->item($data['item_transaksi'] ?? null);
                    $kategori = $this->kategori($data['kategori'] ?? null, $item);
                    $tambak = $this->tambak($data['tambak'] ?? null, $tambakIds);
                    $blok = $this->blok($data['blok'] ?? null, $tambak);
                    $siklus = $this->siklus($data['siklus'] ?? null, $blok);
                    $sumberDana = $this->sumberDana($data['sumber_dana'] ?? null);
                    $jenisPembayaran = $this->jenisPembayaran($data['jenis_pembayaran'] ?? null);
                    $accountBank = $this->accountBank($data['account_bank'] ?? null, $jenisPembayaran);

                    TransaksiKeuangan::create([
                        'nomor_transaksi' => app(AutoNumberService::class)->generate('INVT'),
                        'jenis_transaksi' => $this->jenisTransaksi($data['jenis'] ?? null),
                        'tgl_kwitansi' => $this->date($data['tanggal'] ?? null),
                        'aktivitas' => $this->requiredText($data['aktivitas'] ?? null, 'Aktivitas'),
                        'nominal' => $this->nominal($data['nominal'] ?? null),
                        'item_transaksi_id' => $item->id,
                        'kategori_transaksi_id' => $kategori->id,
                        'tambak_id' => $tambak->id,
                        'blok_id' => $blok?->id,
                        'siklus_id' => $siklus?->id,
                        'sumber_dana_id' => $sumberDana->id,
                        'jenis_pembayaran' => $jenisPembayaran,
                        'account_bank_id' => $accountBank?->id,
                        'catatan' => $this->nullableText($data['catatan'] ?? null),
                        'status' => 'awaiting_approval',
                        'created_by' => Auth::id(),
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
            if ($key) {
                $data[$key] = $value;
            }
        }

        return $data;
    }

    private function key($value): string
    {
        $key = strtolower(trim((string) $value));
        $key = str_replace(['.', '/', '-', ' '], '_', $key);

        return match ($key) {
            'jenis_transaksi' => 'jenis',
            'tgl_kwitansi', 'tanggal_kwitansi', 'tgl' => 'tanggal',
            'aktivitas_kegiatan', 'kegiatan' => 'aktivitas',
            'item', 'item_transaksi' => 'item_transaksi',
            'kategori_transaksi' => 'kategori',
            'jenis_bayar', 'pembayaran' => 'jenis_pembayaran',
            'bank', 'account', 'akun_bank', 'account_bank' => 'account_bank',
            default => $key,
        };
    }

    private function isEmptyRow(Collection $row): bool
    {
        return $row->filter(fn ($value) => trim((string) $value) !== '')->isEmpty();
    }

    private function jenisTransaksi($value): string
    {
        $value = strtolower(str_replace([' ', '-'], '_', trim((string) $value)));

        return match ($value) {
            'uang_masuk', 'masuk' => 'uang_masuk',
            'uang_keluar', 'keluar' => 'uang_keluar',
            'cash_card' => 'cash_card',
            default => throw new \InvalidArgumentException('Jenis transaksi harus Uang Masuk, Uang Keluar, atau Cash Card.'),
        };
    }

    private function jenisPembayaran($value): string
    {
        $value = strtolower(trim((string) $value));

        return match ($value) {
            'cash', 'tunai' => 'cash',
            'bank', 'transfer' => 'bank',
            default => throw new \InvalidArgumentException('Jenis pembayaran harus Cash atau Bank.'),
        };
    }

    private function date($value): string
    {
        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value)->format('Y-m-d');
        }

        if (is_numeric($value)) {
            return Carbon::instance(ExcelDate::excelToDateTimeObject((float) $value))->format('Y-m-d');
        }

        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y'] as $format) {
            try {
                return Carbon::createFromFormat($format, trim((string) $value))->format('Y-m-d');
            } catch (\Throwable) {
            }
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            throw new \InvalidArgumentException('Tanggal tidak valid.');
        }
    }

    private function nominal($value): float
    {
        // Angka asli dari cell numerik Excel → langsung pakai.
        if (is_int($value) || is_float($value)) {
            if ($value < 0) {
                throw new \InvalidArgumentException('Nominal harus angka dan minimal 0.');
            }

            return (float) $value;
        }

        $s = preg_replace('/[^0-9.,]/', '', (string) $value);

        // Deteksi pola RIBUAN DULU (sebelum is_numeric), karena "946.500" & "946,500"
        // valid sebagai 946.5 padahal maksudnya 946500.
        if (preg_match('/^\d{1,3}(\.\d{3})+$/', $s)) {
            // Ribuan ID: 946.500 / 1.950.000
            $s = str_replace('.', '', $s);
        } elseif (preg_match('/^\d{1,3}(,\d{3})+$/', $s)) {
            // Ribuan US: 946,500 / 1,950,000
            $s = str_replace(',', '', $s);
        } elseif (!is_numeric($s)) {
            $hasDot = str_contains($s, '.');
            $hasComma = str_contains($s, ',');
            if ($hasDot && $hasComma) {
                // Separator TERAKHIR = desimal, yang lain = ribuan.
                $s = (strrpos($s, ',') > strrpos($s, '.'))
                    ? str_replace(',', '.', str_replace('.', '', $s))   // ID: 1.234,56
                    : str_replace(',', '', $s);                          // US: 1,234.56
            } elseif ($hasComma) {
                // Sisa koma diikuti 1-2 digit => desimal (ID).
                $s = str_replace(',', '.', $s);
            }
        }

        if (!is_numeric($s) || (float) $s < 0) {
            throw new \InvalidArgumentException('Nominal harus angka dan minimal 0.');
        }

        return (float) $s;
    }

    private function requiredText($value, string $label): string
    {
        $text = trim((string) $value);
        if ($text === '') {
            throw new \InvalidArgumentException("{$label} wajib diisi.");
        }

        return $text;
    }

    private function nullableText($value): ?string
    {
        $text = trim((string) $value);
        return $text === '' ? null : $text;
    }

    private function kategori($value, ?ItemTransaksi $item): KategoriTransaksi
    {
        // Kategori diturunkan dari item (lebih spesifik). Kolom kategori hanya fallback.
        if ($item?->kategoriTransaksi) {
            return $item->kategoriTransaksi;
        }

        $value = $this->requiredText($value, 'Kategori');

        return KategoriTransaksi::where('kode_kategori', $value)
            ->orWhere('deskripsi', $value)
            ->firstOr(fn () => throw new \InvalidArgumentException("Kategori '{$value}' tidak ditemukan."));
    }

    private function item($value): ItemTransaksi
    {
        $value = $this->requiredText($value, 'Item transaksi');

        return ItemTransaksi::where('kode_item', $value)
            ->orWhere('deskripsi', $value)
            ->firstOr(fn () => throw new \InvalidArgumentException("Item transaksi '{$value}' tidak ditemukan. Periksa kode item pada referensi."));
    }

    private function tambak($value, array $tambakIds): Tambak
    {
        $value = $this->requiredText($value, 'Tambak');

        return Tambak::whereIn('id', $tambakIds)
            ->where('nama_tambak', $value)
            ->firstOr(fn () => throw new \InvalidArgumentException("Tambak '{$value}' tidak ditemukan atau tidak bisa diakses."));
    }

    private function blok($value, Tambak $tambak): ?Blok
    {
        $value = $this->nullableText($value);
        if (!$value) {
            return null;
        }

        return Blok::where('tambak_id', $tambak->id)
            ->where('nama_blok', $value)
            ->firstOr(fn () => throw new \InvalidArgumentException("Blok '{$value}' tidak ditemukan pada tambak tersebut."));
    }

    private function siklus($value, ?Blok $blok): ?Siklus
    {
        $value = $this->nullableText($value);
        if (!$value) {
            return null;
        }

        if (!$blok) {
            throw new \InvalidArgumentException('Blok wajib diisi jika Siklus diisi.');
        }

        return Siklus::where('blok_id', $blok->id)
            ->where('nama_siklus', $value)
            ->firstOr(fn () => throw new \InvalidArgumentException("Siklus '{$value}' tidak ditemukan pada blok tersebut."));
    }

    private function sumberDana($value): SumberDana
    {
        $value = $this->requiredText($value, 'Sumber dana');

        return SumberDana::where('kode_sumber_dana', $value)
            ->orWhere('deskripsi', $value)
            ->firstOr(fn () => throw new \InvalidArgumentException("Sumber dana '{$value}' tidak ditemukan."));
    }

    private function accountBank($value, string $jenisPembayaran): ?AccountBank
    {
        $value = $this->nullableText($value);
        if ($jenisPembayaran === 'cash') {
            return null;
        }

        if (!$value) {
            throw new \InvalidArgumentException('Account bank wajib diisi untuk pembayaran Bank.');
        }

        return AccountBank::where('status', 'aktif')
            ->where(fn ($q) => $q->where('kode_account', $value)->orWhere('nama_bank', $value)->orWhere('nomor_rekening', $value))
            ->firstOr(fn () => throw new \InvalidArgumentException("Account bank '{$value}' tidak ditemukan atau tidak aktif."));
    }
}
