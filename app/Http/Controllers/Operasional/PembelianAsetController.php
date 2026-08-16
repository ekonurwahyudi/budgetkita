<?php

namespace App\Http\Controllers\Operasional;

use App\Http\Controllers\Controller;
use App\Imports\PembelianAsetImport;
use App\Models\AccountBank;
use App\Models\Blok;
use App\Models\HutangPiutang;
use App\Models\KategoriAset;
use App\Models\KategoriHutangPiutang;
use App\Models\PembelianAset;
use App\Models\PenjualanAset;
use App\Models\Siklus;
use App\Models\Tambak;
use App\Services\ApprovalService;
use App\Services\AutoNumberService;
use App\Services\FileUploadService;
use App\Services\NotifikasiService;
use App\Support\ActiveTambak;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class PembelianAsetController extends Controller
{
    public function index()
    {
        $hasTambak = auth()->user()->tambaks()->exists();
        $tambakIds = $hasTambak ? auth()->user()->tambaks()->pluck('tambaks.id') : Tambak::pluck('id');
        $data = $hasTambak
            ? PembelianAset::with(['kategoriAset', 'accountBank', 'hutangPiutang', 'penjualanAsets', 'blok', 'siklus'])->latest()->get()
            : collect();
        $kategoriAsets = KategoriAset::orderBy('deskripsi')->get();
        $tambaks = Tambak::whereIn('id', $tambakIds)->orderBy('nama_tambak')->get();
        $bloks = Blok::with('tambak')->whereIn('tambak_id', $tambakIds)->orderBy('nama_blok')->get();
        $sikluses = Siklus::with('blok')->whereHas('blok', fn ($q) => $q->whereIn('tambak_id', $tambakIds))->orderBy('nama_siklus')->get();

        return view('operasional.pembelian-aset.index', compact('data', 'kategoriAsets', 'tambaks', 'bloks', 'sikluses'));
    }

    public function create()
    {
        return view('operasional.pembelian-aset.form', $this->formData() + ['pembelianAset' => null]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_aset'          => 'required|string|max:255',
            'kategori_aset_id'   => 'required|uuid|exists:kategori_asets,id',
            'tambak_id'           => 'required|uuid|exists:tambaks,id',
            'blok_id'             => 'nullable|uuid|exists:bloks,id',
            'siklus_id'           => 'nullable|uuid|exists:sikluses,id',
            'tgl_pembelian'      => 'required|date',
            'qty'                 => 'required|integer|min:1',
            'harga_satuan'        => 'required|numeric|min:0',
            'nominal_pembelian'   => 'required|numeric|min:0',
            'umur_manfaat'       => 'nullable|integer|min:0',
            'nilai_residu'       => 'nullable|numeric|min:0',
            'metode_depresiasi'  => 'required|in:garis_lurus,persen,tanpa',
            'persen_depresiasi'  => 'nullable|numeric|min:0|max:100',
            'status_pembayaran'  => 'required|in:lunas,hutang,sebagian',
            'nominal_dibayar'    => 'nullable|numeric|min:0',
            'konfirmasi_hutang'  => 'nullable|accepted_if:status_pembayaran,sebagian',
            'jenis_pembayaran'   => 'required|in:cash,bank',
            'account_bank_id'    => 'nullable|required_if:status_pembayaran,lunas,sebagian|uuid|exists:account_banks,id',
            'catatan'            => 'nullable|string',
            'eviden.*'           => 'nullable|file|max:5120|mimes:jpg,jpeg,png,pdf',
            'foto_aset.*'        => 'nullable|file|max:5120|mimes:jpg,jpeg,png',
        ]);

        $input = $request->only([
            'nama_aset', 'kategori_aset_id', 'tambak_id', 'blok_id', 'siklus_id', 'tgl_pembelian',
            'qty', 'harga_satuan', 'metode_depresiasi',
            'status_pembayaran', 'jenis_pembayaran', 'account_bank_id', 'catatan',
        ]);

        $input['nominal_pembelian'] = $this->calculateTotal($request);
        $input['nominal_dibayar'] = $this->calculatePaidAmount($request, $input['nominal_pembelian']);
        $input['umur_manfaat'] = $request->input('umur_manfaat') ?: 0;
        $input['nilai_residu'] = $request->input('nilai_residu') ?: 0;
        $input['qty_tersedia'] = (int) $input['qty'];
        $input['qty_rusak'] = 0;
        $input['nomor_transaksi'] = app(AutoNumberService::class)->generate('INVA');
        $input['created_by'] = auth()->id();
        if ($input['status_pembayaran'] === 'hutang') {
            $input['account_bank_id'] = null;
        }

        if ($input['metode_depresiasi'] === 'persen') {
            $input['persen_depresiasi'] = $request->input('persen_depresiasi') ?: 0;
        } elseif ($input['metode_depresiasi'] === 'tanpa') {
            $input['persen_depresiasi'] = null;
            $input['umur_manfaat'] = 0;
            $input['nilai_residu'] = 0;
        } else {
            $input['persen_depresiasi'] = null;
        }

        if ($request->hasFile('eviden')) {
            $paths = [];
            foreach ($request->file('eviden') as $file) {
                $paths[] = app(FileUploadService::class)->upload($file);
            }
            $input['eviden'] = $paths;
        }

        if ($request->hasFile('foto_aset')) {
            $fotos = [];
            foreach ($request->file('foto_aset') as $file) {
                $fotos[] = app(FileUploadService::class)->upload($file);
            }
            $input['foto_aset'] = $fotos;
        }

        $aset = DB::transaction(function () use ($input) {
            $aset = PembelianAset::create($input);
            $this->syncHutangForAset($aset);
            return $aset;
        });

        app(NotifikasiService::class)->kirimApprovalRequest(
            'Pembelian Aset Menunggu Approval',
            'Pembelian aset "' . $input['nama_aset'] . '" sebesar Rp ' . number_format($input['nominal_pembelian'], 0, ',', '.') . ' memerlukan persetujuan.',
            route('pembelian-aset.show', $aset->id)
        );

        return redirect()->route('pembelian-aset.index')->with('success', 'Pembelian aset berhasil ditambahkan.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $import = new PembelianAsetImport();
        Excel::import($import, $request->file('file'));

        if ($import->imported() > 0) {
            app(NotifikasiService::class)->kirimApprovalRequest(
                'Import Pembelian Aset Menunggu Approval',
                $import->imported() . ' pembelian aset hasil import Excel memerlukan persetujuan.',
                route('pembelian-aset.index')
            );
        }

        return redirect()->route('pembelian-aset.index')->with('success', $import->imported() . ' pembelian aset berhasil diimport dengan status awaiting.');
    }

    public function show(PembelianAset $pembelianAset)
    {
        $pembelianAset->load([
            'kategoriAset',
            'tambak',
            'blok',
            'siklus',
            'accountBank',
            'hutangPiutang',
            'penjualanAsets' => fn ($q) => $q->latest('tgl_penjualan')->latest('created_at'),
            'penjualanAsets.accountBank',
            'penjualanAsets.piutang',
        ]);
        $accountBanks = AccountBank::where('status', 'aktif')->orderBy('nama_bank')->get();
        return view('operasional.pembelian-aset.show', compact('pembelianAset', 'accountBanks'));
    }

    public function edit(PembelianAset $pembelianAset)
    {
        return view('operasional.pembelian-aset.form', $this->formData($pembelianAset) + compact('pembelianAset'));
    }

    public function update(Request $request, PembelianAset $pembelianAset)
    {
        $request->validate([
            'nama_aset'          => 'required|string|max:255',
            'kategori_aset_id'   => 'required|uuid|exists:kategori_asets,id',
            'tambak_id'           => 'required|uuid|exists:tambaks,id',
            'blok_id'             => 'nullable|uuid|exists:bloks,id',
            'siklus_id'           => 'nullable|uuid|exists:sikluses,id',
            'tgl_pembelian'      => 'required|date',
            'qty'                 => 'required|integer|min:1',
            'harga_satuan'        => 'required|numeric|min:0',
            'nominal_pembelian'   => 'required|numeric|min:0',
            'umur_manfaat'       => 'nullable|integer|min:0',
            'nilai_residu'       => 'nullable|numeric|min:0',
            'metode_depresiasi'  => 'required|in:garis_lurus,persen,tanpa',
            'persen_depresiasi'  => 'nullable|numeric|min:0|max:100',
            'status_pembayaran'  => 'required|in:lunas,hutang,sebagian',
            'nominal_dibayar'    => 'nullable|numeric|min:0',
            'konfirmasi_hutang'  => 'nullable|accepted_if:status_pembayaran,sebagian',
            'jenis_pembayaran'   => 'required|in:cash,bank',
            'account_bank_id'    => 'nullable|required_if:status_pembayaran,lunas,sebagian|uuid|exists:account_banks,id',
            'catatan'            => 'nullable|string',
            'eviden.*'           => 'nullable|file|max:5120|mimes:jpg,jpeg,png,pdf',
            'foto_aset.*'        => 'nullable|file|max:5120|mimes:jpg,jpeg,png',
        ]);

        $input = $request->only([
            'nama_aset', 'kategori_aset_id', 'tambak_id', 'blok_id', 'siklus_id', 'tgl_pembelian',
            'qty', 'harga_satuan', 'metode_depresiasi',
            'status_pembayaran', 'jenis_pembayaran', 'account_bank_id', 'catatan',
        ]);

        $input['nominal_pembelian'] = $this->calculateTotal($request);
        $input['nominal_dibayar'] = $this->calculatePaidAmount($request, $input['nominal_pembelian']);
        $input['umur_manfaat'] = $request->input('umur_manfaat') ?: 0;
        $input['nilai_residu'] = $request->input('nilai_residu') ?: 0;
        $qtyDelta = (int) $input['qty'] - (int) $pembelianAset->qty;
        $input['qty_tersedia'] = max(0, (int) $pembelianAset->qty_tersedia + $qtyDelta);
        if ($input['status_pembayaran'] === 'hutang') {
            $input['account_bank_id'] = null;
        }

        if ($input['metode_depresiasi'] === 'persen') {
            $input['persen_depresiasi'] = $request->input('persen_depresiasi') ?: 0;
        } elseif ($input['metode_depresiasi'] === 'tanpa') {
            $input['persen_depresiasi'] = null;
            $input['umur_manfaat'] = 0;
            $input['nilai_residu'] = 0;
        } else {
            $input['persen_depresiasi'] = null;
        }

        if ($request->hasFile('eviden')) {
            $existing = $pembelianAset->eviden ?? [];
            foreach ($request->file('eviden') as $file) {
                $existing[] = app(FileUploadService::class)->upload($file);
            }
            $input['eviden'] = $existing;
        }
        if ($request->filled('hapus_eviden')) {
            $existing = $pembelianAset->eviden ?? [];
            $input['eviden'] = array_values(array_filter($existing, fn($p) => !in_array($p, $request->input('hapus_eviden', []))));
        }

        if ($request->hasFile('foto_aset')) {
            $existing = $pembelianAset->foto_aset ?? [];
            foreach ($request->file('foto_aset') as $file) {
                $existing[] = app(FileUploadService::class)->upload($file);
            }
            $input['foto_aset'] = $existing;
        }
        if ($request->filled('hapus_foto')) {
            $existing = $pembelianAset->foto_aset ?? [];
            $input['foto_aset'] = array_values(array_filter($existing, fn($p) => !in_array($p, $request->input('hapus_foto', []))));
        }

        DB::transaction(function () use ($pembelianAset, $input) {
            // Reverse saldo lama jika sudah selesai via bank (pembelian selalu keluar)
            if ($pembelianAset->status === 'selesai' && $pembelianAset->jenis_pembayaran === 'bank' && $pembelianAset->account_bank_id) {
                $bankLama = AccountBank::find($pembelianAset->account_bank_id);
                if ($bankLama) {
                    $bankLama->increment('saldo', $this->getAsetPaidAmount($pembelianAset));
                }
            }

            $pembelianAset->update($input);
            $this->syncHutangForAset($pembelianAset->refresh());

            // Apply saldo baru jika masih selesai via bank
            $pembelianAset->refresh();
            if ($pembelianAset->status === 'selesai' && $pembelianAset->jenis_pembayaran === 'bank' && $pembelianAset->account_bank_id) {
                $bankBaru = AccountBank::find($pembelianAset->account_bank_id);
                if ($bankBaru) {
                    $bankBaru->decrement('saldo', $this->getAsetPaidAmount($pembelianAset));
                }
            }
        });

        return redirect()->route('pembelian-aset.index')->with('success', 'Pembelian aset berhasil diperbarui.');
    }

    public function destroy(PembelianAset $pembelianAset)
    {
        DB::transaction(function () use ($pembelianAset) {
            // Reverse saldo jika sudah selesai via bank (pembelian selalu keluar)
            if ($pembelianAset->status === 'selesai' && $pembelianAset->jenis_pembayaran === 'bank' && $pembelianAset->account_bank_id) {
                $bank = AccountBank::find($pembelianAset->account_bank_id);
                if ($bank) {
                    $bank->increment('saldo', $this->getAsetPaidAmount($pembelianAset));
                }
            }

            $this->removeLinkedHutang($pembelianAset);
            $pembelianAset->delete();
        });

        return redirect()->back()->with('success', 'Pembelian aset berhasil dihapus.');
    }

    public function approve(PembelianAset $pembelianAset)
    {
        DB::transaction(function () use ($pembelianAset) {
            $pembelianAset->update(['reject_reason' => null]);
            app(ApprovalService::class)->approve($pembelianAset);

            $pembelianAset->refresh();
            if ($pembelianAset->hutang_piutang_id) {
                HutangPiutang::whereKey($pembelianAset->hutang_piutang_id)->update([
                    'status' => 'selesai',
                    'reject_reason' => null,
                ]);
            }
        });

        app(NotifikasiService::class)->kirimKeRole('Owner',
            'Pembelian Aset Disetujui',
            'Pembelian aset "' . $pembelianAset->nama_aset . '" telah disetujui.',
            'info',
            route('pembelian-aset.show', $pembelianAset->id)
        );
        return redirect()->back()->with('success', 'Pembelian aset berhasil di-approve.');
    }

    public function reject(Request $request, PembelianAset $pembelianAset)
    {
        $validated = $request->validate([
            'alasan_reject' => 'required|string|min:5|max:1000',
        ], [
            'alasan_reject.required' => 'Alasan reject wajib diisi.',
            'alasan_reject.min' => 'Alasan reject minimal 5 karakter.',
        ]);

        $pembelianAset->update(['reject_reason' => $validated['alasan_reject']]);
        app(ApprovalService::class)->reject($pembelianAset);
        if ($pembelianAset->hutang_piutang_id) {
            HutangPiutang::whereKey($pembelianAset->hutang_piutang_id)->update([
                'status' => 'cancel',
                'reject_reason' => 'Pembelian aset ditolak: ' . $validated['alasan_reject'],
            ]);
        }

        if ($pembelianAset->created_by) {
            app(NotifikasiService::class)->kirim(
                $pembelianAset->created_by,
                'Pembelian Aset Ditolak',
                'Pembelian aset "' . $pembelianAset->nama_aset . '" ditolak. Alasan: ' . $validated['alasan_reject'],
                'warning',
                route('pembelian-aset.show', $pembelianAset->id)
            );
        }

        return redirect()->back()->with('success', 'Pembelian aset berhasil di-reject.');
    }

    public function updateKondisi(Request $request, PembelianAset $pembelianAset)
    {
        $validated = $request->validate([
            'qty_rusak' => 'required|integer|min:0',
        ]);

        $tersisa = (int) $pembelianAset->qty_tersedia + (int) $pembelianAset->qty_rusak;
        if ($validated['qty_rusak'] > $tersisa) {
            return redirect()->back()->with('error', 'Qty rusak tidak boleh lebih besar dari sisa aset.');
        }

        $pembelianAset->update([
            'qty_rusak' => $validated['qty_rusak'],
            'qty_tersedia' => $tersisa - $validated['qty_rusak'],
        ]);

        return redirect()->back()->with('success', 'Kondisi aset berhasil diperbarui.');
    }

    public function jual(Request $request, PembelianAset $pembelianAset)
    {
        $validated = $request->validate([
            'tgl_penjualan' => 'required|date',
            'qty_jual' => 'required|integer|min:1',
            'kondisi' => 'required|in:baik,rusak',
            'harga_satuan_jual' => 'required|numeric|min:0',
            'pembeli' => 'nullable|string|max:255',
            'status_pembayaran_jual' => 'required|in:lunas,piutang,sebagian',
            'nominal_dibayar_jual' => 'nullable|numeric|min:0',
            'account_bank_id_jual' => 'nullable|required_if:status_pembayaran_jual,lunas,sebagian|uuid|exists:account_banks,id',
            'catatan_jual' => 'nullable|string',
        ]);

        $stokTersedia = $validated['kondisi'] === 'rusak'
            ? (int) $pembelianAset->qty_rusak
            : (int) $pembelianAset->qty_tersedia;

        if ($validated['qty_jual'] > $stokTersedia) {
            return redirect()->back()->with('error', 'Qty jual melebihi stok kondisi ' . $validated['kondisi'] . '.');
        }

        $total = round($validated['qty_jual'] * (float) $validated['harga_satuan_jual'], 2);
        $dibayar = match ($validated['status_pembayaran_jual']) {
            'piutang' => 0,
            'sebagian' => min((float) ($validated['nominal_dibayar_jual'] ?? 0), $total),
            default => $total,
        };

        DB::transaction(function () use ($validated, $pembelianAset, $total, $dibayar) {
            if ($validated['kondisi'] === 'rusak') {
                $pembelianAset->decrement('qty_rusak', $validated['qty_jual']);
            } else {
                $pembelianAset->decrement('qty_tersedia', $validated['qty_jual']);
            }

            if ($dibayar > 0 && !empty($validated['account_bank_id_jual'])) {
                AccountBank::whereKey($validated['account_bank_id_jual'])->increment('saldo', $dibayar);
            }

            $penjualan = PenjualanAset::create([
                'pembelian_aset_id' => $pembelianAset->id,
                'nomor_transaksi' => app(AutoNumberService::class)->generate('JLA'),
                'tgl_penjualan' => $validated['tgl_penjualan'],
                'qty' => $validated['qty_jual'],
                'kondisi' => $validated['kondisi'],
                'harga_satuan' => $validated['harga_satuan_jual'],
                'total_penjualan' => $total,
                'pembeli' => $validated['pembeli'] ?? null,
                'status_pembayaran' => $validated['status_pembayaran_jual'],
                'nominal_dibayar' => $dibayar,
                'account_bank_id' => $validated['account_bank_id_jual'] ?? null,
                'catatan' => $validated['catatan_jual'] ?? null,
                'created_by' => auth()->id(),
            ]);

            $this->syncPiutangForPenjualan($penjualan);
        });

        return redirect()->back()->with('success', 'Penjualan aset berhasil dicatat.');
    }

    private function calculateTotal(Request $request): float
    {
        return round(((int) $request->input('qty', 0)) * ((float) $request->input('harga_satuan', 0)), 2);
    }

    private function formData(?PembelianAset $pembelianAset = null): array
    {
        $hasTambak = auth()->user()->tambaks()->exists();
        $tambakIds = $hasTambak ? auth()->user()->tambaks()->pluck('tambaks.id') : Tambak::pluck('id');
        $selectedTambakId = old('tambak_id', $pembelianAset?->tambak_id ?? ActiveTambak::id() ?? $tambakIds->first());
        $selectedBlokId = old('blok_id', $pembelianAset?->blok_id);
        $selectedSiklusId = old('siklus_id', $pembelianAset?->siklus_id);

        return [
            'kategoriAsets' => KategoriAset::orderBy('deskripsi')->get(),
            'accountBanks' => AccountBank::where('status', 'aktif')->orderBy('nama_bank')->get(),
            'tambaks' => Tambak::whereIn('id', $tambakIds)->orderBy('nama_tambak')->get(),
            'selectedTambakId' => $selectedTambakId,
            'selectedBlokId' => $selectedBlokId,
            'selectedSiklusId' => $selectedSiklusId,
            'bloks' => $selectedTambakId
                ? Blok::where('tambak_id', $selectedTambakId)->orderBy('nama_blok')->get()
                : collect(),
            'sikluses' => $selectedBlokId
                ? Siklus::where('blok_id', $selectedBlokId)->where('status', '!=', 'selesai')->orderBy('nama_siklus')->get()
                : collect(),
        ];
    }

    private function calculatePaidAmount(Request $request, float $total): float
    {
        return match ($request->input('status_pembayaran')) {
            'hutang' => 0,
            'sebagian' => min((float) $request->input('nominal_dibayar', 0), $total),
            default => $total,
        };
    }

    private function getAsetPaidAmount(PembelianAset $aset): float
    {
        return match ($aset->status_pembayaran) {
            'hutang' => 0,
            'sebagian' => (float) ($aset->nominal_dibayar ?? 0),
            default => (float) $aset->nominal_pembelian,
        };
    }

    private function syncHutangForAset(PembelianAset $aset): void
    {
        if (!in_array($aset->status_pembayaran, ['hutang', 'sebagian'], true)) {
            $this->removeLinkedHutang($aset);
            return;
        }

        $total = (float) $aset->nominal_pembelian;
        $dibayar = (float) $aset->nominal_dibayar;
        $sisa = max(0, $total - $dibayar);

        if ($sisa <= 0) {
            $this->removeLinkedHutang($aset);
            return;
        }

        $kategori = KategoriHutangPiutang::firstOrCreate(
            ['kode_hutang_piutang' => 'ASET'],
            ['deskripsi' => 'Hutang Pembelian Aset']
        );

        $payload = [
            'jenis' => 'hutang',
            'nama_pemberi_hutang' => null,
            'aktivitas' => 'Pembelian aset ' . $aset->nama_aset . ' (' . $aset->nomor_transaksi . ')',
            'kategori_hutang_piutang_id' => $kategori->id,
            'nominal' => $total,
            'total_bayar' => $total,
            'jatuh_tempo' => Carbon::parse($aset->tgl_pembelian)->addDays(30)->toDateString(),
            'nominal_bayar' => $dibayar,
            'sisa_pembayaran' => $sisa,
            'jenis_pembayaran' => 'cash',
            'account_bank_id' => null,
            'catatan' => trim(($aset->catatan ?? '') . "\n\nOtomatis dari pembelian aset " . $aset->nomor_transaksi),
            'status' => $aset->status === 'selesai' ? 'selesai' : 'awaiting_approval',
            'created_by' => $aset->created_by,
            'created_at' => Carbon::parse($aset->tgl_pembelian),
        ];

        if ($aset->hutang_piutang_id) {
            $hutang = HutangPiutang::find($aset->hutang_piutang_id);
            if ($hutang) {
                $hutang->update($payload);
                return;
            }
        }

        $payload['nomor_transaksi'] = app(AutoNumberService::class)->generate('INVH');
        $hutang = HutangPiutang::create($payload);
        $aset->updateQuietly(['hutang_piutang_id' => $hutang->id]);
    }

    private function removeLinkedHutang(PembelianAset $aset): void
    {
        if (!$aset->hutang_piutang_id) {
            return;
        }

        $hutang = HutangPiutang::with('payments')->find($aset->hutang_piutang_id);
        if ($hutang) {
            if ($hutang->payments->isEmpty()) {
                $hutang->delete();
            } else {
                $hutang->update(['status' => 'cancel']);
            }
        }

        $aset->updateQuietly(['hutang_piutang_id' => null]);
    }

    private function syncPiutangForPenjualan(PenjualanAset $penjualan): void
    {
        if (!in_array($penjualan->status_pembayaran, ['piutang', 'sebagian'], true)) {
            return;
        }

        $total = (float) $penjualan->total_penjualan;
        $dibayar = (float) $penjualan->nominal_dibayar;
        $sisa = max(0, $total - $dibayar);

        if ($sisa <= 0) {
            $penjualan->updateQuietly(['status_pembayaran' => 'lunas', 'piutang_id' => null]);
            return;
        }

        $kategori = KategoriHutangPiutang::firstOrCreate(
            ['kode_hutang_piutang' => 'JUALASET'],
            ['deskripsi' => 'Piutang Penjualan Aset']
        );

        $payload = [
            'jenis' => 'piutang',
            'nama_pemberi_hutang' => $penjualan->pembeli,
            'aktivitas' => 'Penjualan aset ' . $penjualan->pembelianAset->nama_aset . ' (' . $penjualan->nomor_transaksi . ')',
            'kategori_hutang_piutang_id' => $kategori->id,
            'nominal' => $sisa,
            'total_bayar' => $sisa,
            'jatuh_tempo' => Carbon::parse($penjualan->tgl_penjualan)->addDays(30)->toDateString(),
            'nominal_bayar' => 0,
            'sisa_pembayaran' => $sisa,
            'jenis_pembayaran' => 'cash',
            'account_bank_id' => null,
            'catatan' => trim(($penjualan->catatan ?? '') . "\n\nOtomatis dari penjualan aset " . $penjualan->nomor_transaksi),
            'status' => 'selesai',
            'created_by' => $penjualan->created_by,
            'created_at' => Carbon::parse($penjualan->tgl_penjualan),
        ];

        $payload['nomor_transaksi'] = app(AutoNumberService::class)->generate('INVP');
        $piutang = HutangPiutang::create($payload);
        $penjualan->updateQuietly(['piutang_id' => $piutang->id]);
    }
}
