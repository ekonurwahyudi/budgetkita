<?php

namespace App\Http\Controllers\Operasional;

use App\Http\Controllers\Controller;
use App\Models\AccountBank;
use App\Models\Blok;
use App\Models\HutangPiutang;
use App\Models\ItemPersediaan;
use App\Models\KategoriHutangPiutang;
use App\Models\KategoriPersediaan;
use App\Models\PembelianPersediaan;
use App\Models\PembelianPersediaanReturn;
use App\Models\Persediaan;
use App\Models\RiwayatPersediaan;
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

class PembelianPersediaanController extends Controller
{
    public function index()
    {
        $hasTambak = auth()->user()->tambaks()->exists();
        $data = $hasTambak
            ? PembelianPersediaan::with(['items', 'blok', 'siklus'])->latest()->get()
            : collect();
        return view('operasional.pembelian-persediaan.index', compact('data'));
    }

    public function create()
    {
        return view('operasional.pembelian-persediaan.form', $this->formData() + ['pembelianPersediaan' => null]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'tgl_pembelian'              => 'required|date',
            'tambak_id'                   => 'required|uuid|exists:tambaks,id',
            'blok_id'                     => 'nullable|uuid|exists:bloks,id',
            'siklus_id'                   => 'nullable|uuid|exists:sikluses,id',
            'status_pembayaran'           => 'required|in:lunas,hutang,sebagian',
            'nominal_dibayar'             => 'nullable|numeric|min:0',
            'konfirmasi_hutang'           => 'nullable|accepted_if:status_pembayaran,sebagian',
            'jenis_pembayaran'           => 'required|in:cash,bank',
            'account_bank_id'            => 'nullable|required_if:status_pembayaran,lunas,sebagian|uuid|exists:account_banks,id',
            'catatan'                    => 'nullable|string',
            'eviden.*'                   => 'nullable|file|max:5120|mimes:jpg,jpeg,png,gif,bmp,webp,pdf,xlsx,xls',
            'items'                      => 'required|array|min:1',
            'items.*.item_persediaan_id' => 'required|uuid|exists:item_persediaans,id',
            'items.*.qty'                => 'required|numeric|min:0.01',
            'items.*.satuan'             => 'required|string',
            'items.*.harga_satuan'       => 'required|numeric|min:0',
        ]);

        $total = $this->calculateItemsTotal($request);
        $input = $request->only([
            'tgl_pembelian', 'tambak_id', 'blok_id', 'siklus_id',
            'status_pembayaran', 'jenis_pembayaran', 'account_bank_id', 'catatan',
        ]);
        $input['nominal_dibayar'] = $this->calculatePaidAmount($request, $total);
        $input['nomor_transaksi'] = app(AutoNumberService::class)->generate('INVB');
        $input['created_by'] = auth()->id();
        if ($input['status_pembayaran'] === 'hutang') {
            $input['account_bank_id'] = null;
        }

        if ($request->hasFile('eviden')) {
            $paths = [];
            foreach ($request->file('eviden') as $file) {
                $paths[] = app(FileUploadService::class)->upload($file);
            }
            $input['eviden'] = $paths;
        }

        $pembelian = DB::transaction(function () use ($request, $input) {
            $pembelian = PembelianPersediaan::create($input);
            $this->syncItems($pembelian, $request);
            $this->syncHutangForPersediaan($pembelian->refresh()->load('items'));
            return $pembelian;
        });

        app(NotifikasiService::class)->kirimApprovalRequest(
            'Pembelian Persediaan Menunggu Approval',
            'Pembelian persediaan No. ' . $pembelian->nomor_transaksi . ' memerlukan persetujuan.',
            route('pembelian-persediaan.show', $pembelian->id)
        );

        return redirect()->route('pembelian-persediaan.index')->with('success', 'Pembelian persediaan berhasil ditambahkan.');
    }

    public function show(PembelianPersediaan $pembelianPersediaan)
    {
        $pembelianPersediaan->load([
            'items.itemPersediaan',
            'items.returns',
            'returns.item.itemPersediaan',
            'returns.persediaan',
            'tambak',
            'blok',
            'siklus',
            'accountBank',
            'hutangPiutang',
        ]);
        return view('operasional.pembelian-persediaan.show', compact('pembelianPersediaan'));
    }

    public function edit(PembelianPersediaan $pembelianPersediaan)
    {
        $pembelianPersediaan->load('items.itemPersediaan');
        return view('operasional.pembelian-persediaan.form', $this->formData($pembelianPersediaan) + compact('pembelianPersediaan'));
    }

    public function update(Request $request, PembelianPersediaan $pembelianPersediaan)
    {
        $request->validate([
            'tgl_pembelian'              => 'required|date',
            'tambak_id'                   => 'required|uuid|exists:tambaks,id',
            'blok_id'                     => 'nullable|uuid|exists:bloks,id',
            'siklus_id'                   => 'nullable|uuid|exists:sikluses,id',
            'status_pembayaran'           => 'required|in:lunas,hutang,sebagian',
            'nominal_dibayar'             => 'nullable|numeric|min:0',
            'konfirmasi_hutang'           => 'nullable|accepted_if:status_pembayaran,sebagian',
            'jenis_pembayaran'           => 'required|in:cash,bank',
            'account_bank_id'            => 'nullable|required_if:status_pembayaran,lunas,sebagian|uuid|exists:account_banks,id',
            'catatan'                    => 'nullable|string',
            'eviden.*'                   => 'nullable|file|max:5120|mimes:jpg,jpeg,png,gif,bmp,webp,pdf,xlsx,xls',
            'items'                      => 'required|array|min:1',
            'items.*.item_persediaan_id' => 'required|uuid|exists:item_persediaans,id',
            'items.*.qty'                => 'required|numeric|min:0.01',
            'items.*.satuan'             => 'required|string',
            'items.*.harga_satuan'       => 'required|numeric|min:0',
        ]);

        $total = $this->calculateItemsTotal($request);
        $input = $request->only([
            'tgl_pembelian', 'tambak_id', 'blok_id', 'siklus_id',
            'status_pembayaran', 'jenis_pembayaran', 'account_bank_id', 'catatan',
        ]);
        $input['nominal_dibayar'] = $this->calculatePaidAmount($request, $total);
        if ($input['status_pembayaran'] === 'hutang') {
            $input['account_bank_id'] = null;
        }

        if ($request->hasFile('eviden')) {
            $existing = $pembelianPersediaan->eviden ?? [];
            foreach ($request->file('eviden') as $file) {
                $existing[] = app(FileUploadService::class)->upload($file);
            }
            $input['eviden'] = $existing;
        }
        if ($request->filled('hapus_eviden')) {
            $existing = $pembelianPersediaan->eviden ?? [];
            $input['eviden'] = array_values(array_filter($existing, fn($p) => !in_array($p, $request->input('hapus_eviden', []))));
        }

        DB::transaction(function () use ($request, $pembelianPersediaan, $input) {
            // Hitung nominal lama dari items sebelum dihapus
            $nominalLama = $this->getPersediaanPaidAmount($pembelianPersediaan);

            // Reverse saldo lama jika sudah selesai via bank (pembelian selalu keluar)
            if ($pembelianPersediaan->status === 'selesai' && $pembelianPersediaan->jenis_pembayaran === 'bank' && $pembelianPersediaan->account_bank_id) {
                $bankLama = AccountBank::find($pembelianPersediaan->account_bank_id);
                if ($bankLama) {
                    $bankLama->increment('saldo', $nominalLama);
                }
            }

            $pembelianPersediaan->update($input);

            // Sync items
            $this->syncItems($pembelianPersediaan, $request);
            $this->syncHutangForPersediaan($pembelianPersediaan->refresh()->load('items'));

            // Apply saldo baru jika masih selesai via bank
            $pembelianPersediaan->refresh();
            $pembelianPersediaan->load('items');
            $nominalBaru = $this->getPersediaanPaidAmount($pembelianPersediaan);

            if ($pembelianPersediaan->status === 'selesai' && $pembelianPersediaan->jenis_pembayaran === 'bank' && $pembelianPersediaan->account_bank_id) {
                $bankBaru = AccountBank::find($pembelianPersediaan->account_bank_id);
                if ($bankBaru) {
                    $bankBaru->decrement('saldo', $nominalBaru);
                }
            }
        });

        return redirect()->route('pembelian-persediaan.index')->with('success', 'Pembelian persediaan berhasil diperbarui.');
    }

    public function destroy(PembelianPersediaan $pembelianPersediaan)
    {
        DB::transaction(function () use ($pembelianPersediaan) {
            $pembelianPersediaan->load('items');
            $nominalLama = $this->getPersediaanPaidAmount($pembelianPersediaan);

            // Reverse saldo jika sudah selesai via bank (pembelian selalu keluar)
            if ($pembelianPersediaan->status === 'selesai' && $pembelianPersediaan->jenis_pembayaran === 'bank' && $pembelianPersediaan->account_bank_id) {
                $bank = AccountBank::find($pembelianPersediaan->account_bank_id);
                if ($bank) {
                    $bank->increment('saldo', $nominalLama);
                }
            }

            $this->removeLinkedHutang($pembelianPersediaan);
            $pembelianPersediaan->items()->delete();
            $pembelianPersediaan->delete();
        });

        return redirect()->back()->with('success', 'Pembelian persediaan berhasil dihapus.');
    }

    public function approve(PembelianPersediaan $pembelianPersediaan)
    {
        DB::transaction(function () use ($pembelianPersediaan) {
            $pembelianPersediaan->load('items');
            $pembelianPersediaan->update(['reject_reason' => null]);
            app(ApprovalService::class)->approve($pembelianPersediaan);

            $pembelianPersediaan->refresh();
            if ($pembelianPersediaan->hutang_piutang_id) {
                HutangPiutang::whereKey($pembelianPersediaan->hutang_piutang_id)->update([
                    'status' => 'selesai',
                    'reject_reason' => null,
                ]);
            }
        });
        app(NotifikasiService::class)->kirimKeRole('Owner',
            'Pembelian Persediaan Disetujui',
            'Pembelian persediaan No. ' . $pembelianPersediaan->nomor_transaksi . ' telah disetujui.',
            'info',
            route('pembelian-persediaan.show', $pembelianPersediaan->id)
        );
        return redirect()->back()->with('success', 'Pembelian persediaan berhasil di-approve.');
    }

    public function reject(Request $request, PembelianPersediaan $pembelianPersediaan)
    {
        $validated = $request->validate([
            'alasan_reject' => 'required|string|min:5|max:1000',
        ], [
            'alasan_reject.required' => 'Alasan reject wajib diisi.',
            'alasan_reject.min' => 'Alasan reject minimal 5 karakter.',
        ]);

        $pembelianPersediaan->update(['reject_reason' => $validated['alasan_reject']]);
        app(ApprovalService::class)->reject($pembelianPersediaan);
        if ($pembelianPersediaan->hutang_piutang_id) {
            HutangPiutang::whereKey($pembelianPersediaan->hutang_piutang_id)->update([
                'status' => 'cancel',
                'reject_reason' => 'Pembelian persediaan ditolak: ' . $validated['alasan_reject'],
            ]);
        }

        if ($pembelianPersediaan->created_by) {
            app(NotifikasiService::class)->kirim(
                $pembelianPersediaan->created_by,
                'Pembelian Persediaan Ditolak',
                'Pembelian persediaan No. ' . $pembelianPersediaan->nomor_transaksi . ' ditolak. Alasan: ' . $validated['alasan_reject'],
                'warning',
                route('pembelian-persediaan.show', $pembelianPersediaan->id)
            );
        }

        return redirect()->back()->with('success', 'Pembelian persediaan berhasil di-reject.');
    }

    public function returnStock(Request $request, PembelianPersediaan $pembelianPersediaan)
    {
        if ($pembelianPersediaan->status !== 'selesai') {
            return redirect()->back()->with('error', 'Stok hanya bisa dikembalikan setelah pembelian selesai.');
        }

        $validated = $request->validate([
            'returns' => 'required|array',
            'returns.*' => 'nullable|numeric|min:0',
            'catatan_pengembalian' => 'nullable|string|max:1000',
        ]);

        $pembelianPersediaan->load(['items.returns', 'hutangPiutang']);
        $qtyReturns = collect($validated['returns'])
            ->map(fn ($qty) => (float) $qty)
            ->filter(fn ($qty) => $qty > 0);

        if ($qtyReturns->isEmpty()) {
            return redirect()->back()->with('error', 'Isi minimal satu qty pengembalian.');
        }

        try {
            DB::transaction(function () use ($pembelianPersediaan, $qtyReturns, $validated) {
                $netBefore = $this->getPersediaanTotal($pembelianPersediaan);
                $paidBefore = (float) $pembelianPersediaan->nominal_dibayar;
                $remainingDebt = max(0, $netBefore - $paidBefore);
                $returnTotal = 0;
                $refundTotal = 0;

                foreach ($qtyReturns as $itemId => $qty) {
                    $item = $pembelianPersediaan->items->firstWhere('id', $itemId);
                    if (!$item) {
                        throw new \RuntimeException('Item pengembalian tidak valid.');
                    }

                    $returnedQty = (float) $item->returns->sum('qty');
                    $remainingQty = max(0, (float) $item->qty - $returnedQty);
                    if ($qty > $remainingQty) {
                        throw new \RuntimeException('Qty pengembalian melebihi sisa item ' . ($item->itemPersediaan?->deskripsi ?? '-'));
                    }

                    $persediaan = Persediaan::where('item_persediaan_id', $item->item_persediaan_id)->lockForUpdate()->first();
                    if (!$persediaan || (float) $persediaan->qty < $qty) {
                        throw new \RuntimeException('Stok tidak cukup untuk ' . ($item->itemPersediaan?->deskripsi ?? '-'));
                    }

                    $hargaTotal = round($qty * (float) $item->harga_satuan, 2);
                    $potongHutang = min($hargaTotal, $remainingDebt);
                    $refund = $hargaTotal - $potongHutang;
                    $remainingDebt -= $potongHutang;
                    $returnTotal += $hargaTotal;
                    $refundTotal += $refund;

                    $persediaan->qty = max(0, (float) $persediaan->qty - $qty);
                    $persediaan->total_harga = round((float) $persediaan->qty * (float) $persediaan->harga_per_unit, 2);
                    $persediaan->save();

                    PembelianPersediaanReturn::create([
                        'pembelian_persediaan_id' => $pembelianPersediaan->id,
                        'pembelian_persediaan_item_id' => $item->id,
                        'persediaan_id' => $persediaan->id,
                        'qty' => $qty,
                        'harga_satuan' => $item->harga_satuan,
                        'harga_total' => $hargaTotal,
                        'nominal_potong_hutang' => $potongHutang,
                        'nominal_refund' => $refund,
                        'catatan' => $validated['catatan_pengembalian'] ?? null,
                        'created_by' => auth()->id(),
                    ]);

                    RiwayatPersediaan::create([
                        'persediaan_id' => $persediaan->id,
                        'jenis' => 'pengeluaran',
                        'qty_masuk' => 0,
                        'qty_keluar' => $qty,
                        'blok_id' => $pembelianPersediaan->blok_id,
                        'siklus_id' => $pembelianPersediaan->siklus_id,
                        'harga_per_unit' => $item->harga_satuan,
                        'harga_total' => $hargaTotal,
                        'catatan' => 'Pengembalian stok dari pembelian ' . $pembelianPersediaan->nomor_transaksi,
                    ]);
                }

                $newPaid = max(0, $paidBefore - $refundTotal);
                $netAfter = max(0, $netBefore - $returnTotal);

                $pembelianPersediaan->nominal_dibayar = min($newPaid, $netAfter);
                $pembelianPersediaan->status_pembayaran = match (true) {
                    $netAfter <= 0 => 'lunas',
                    $pembelianPersediaan->nominal_dibayar <= 0 => 'hutang',
                    $pembelianPersediaan->nominal_dibayar < $netAfter => 'sebagian',
                    default => 'lunas',
                };
                if ($pembelianPersediaan->status_pembayaran === 'hutang') {
                    $pembelianPersediaan->account_bank_id = null;
                }
                $pembelianPersediaan->save();

                if ($refundTotal > 0 && $pembelianPersediaan->account_bank_id) {
                    AccountBank::whereKey($pembelianPersediaan->account_bank_id)->increment('saldo', $refundTotal);
                }

                $this->syncHutangForPersediaan($pembelianPersediaan->refresh()->load(['items', 'returns']));
            });
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', 'Stok berhasil dikembalikan.');
    }

    private function formData(?PembelianPersediaan $pembelianPersediaan = null): array
    {
        $hasTambak = auth()->user()->tambaks()->exists();
        $tambakIds = $hasTambak ? auth()->user()->tambaks()->pluck('tambaks.id') : Tambak::pluck('id');
        $selectedTambakId = old('tambak_id', $pembelianPersediaan?->tambak_id ?? ActiveTambak::id() ?? $tambakIds->first());
        $selectedBlokId = old('blok_id', $pembelianPersediaan?->blok_id);
        $selectedSiklusId = old('siklus_id', $pembelianPersediaan?->siklus_id);

        return [
            'kategoriPersediaans' => KategoriPersediaan::orderBy('deskripsi')->get(),
            'itemPersediaans' => ItemPersediaan::with('kategoriPersediaan')->orderBy('deskripsi')->get(),
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

    private function syncItems(PembelianPersediaan $pembelianPersediaan, Request $request): void
    {
        $pembelianPersediaan->items()->delete();
        foreach ($request->input('items', []) as $item) {
            $hargaTotal = (float) $item['qty'] * (float) $item['harga_satuan'];
            $pembelianPersediaan->items()->create([
                'item_persediaan_id' => $item['item_persediaan_id'],
                'qty'                => $item['qty'],
                'satuan'             => $item['satuan'],
                'harga_satuan'       => $item['harga_satuan'],
                'harga_total'        => $hargaTotal,
            ]);
        }
    }

    private function calculateItemsTotal(Request $request): float
    {
        return collect($request->input('items', []))->sum(
            fn ($item) => (float) ($item['qty'] ?? 0) * (float) ($item['harga_satuan'] ?? 0)
        );
    }

    private function calculatePaidAmount(Request $request, float $total): float
    {
        return match ($request->input('status_pembayaran')) {
            'hutang' => 0,
            'sebagian' => min((float) $request->input('nominal_dibayar', 0), $total),
            default => $total,
        };
    }

    private function getPersediaanTotal(PembelianPersediaan $pembelianPersediaan): float
    {
        if (!$pembelianPersediaan->relationLoaded('items')) {
            $pembelianPersediaan->load('items');
        }
        if (!$pembelianPersediaan->relationLoaded('returns')) {
            $pembelianPersediaan->load('returns');
        }

        return max(0, (float) $pembelianPersediaan->items->sum('harga_total') - (float) $pembelianPersediaan->returns->sum('harga_total'));
    }

    private function getPersediaanPaidAmount(PembelianPersediaan $pembelianPersediaan): float
    {
        $total = $this->getPersediaanTotal($pembelianPersediaan);

        return match ($pembelianPersediaan->status_pembayaran) {
            'hutang' => 0,
            'sebagian' => (float) ($pembelianPersediaan->nominal_dibayar ?? 0),
            default => $total,
        };
    }

    private function syncHutangForPersediaan(PembelianPersediaan $pembelianPersediaan): void
    {
        if (!in_array($pembelianPersediaan->status_pembayaran, ['hutang', 'sebagian'], true)) {
            $this->removeLinkedHutang($pembelianPersediaan);
            return;
        }

        $total = $this->getPersediaanTotal($pembelianPersediaan);
        $dibayar = (float) $pembelianPersediaan->nominal_dibayar;
        $sisa = max(0, $total - $dibayar);

        if ($sisa <= 0) {
            $this->removeLinkedHutang($pembelianPersediaan);
            return;
        }

        $kategori = KategoriHutangPiutang::firstOrCreate(
            ['kode_hutang_piutang' => 'PERSEDIAAN'],
            ['deskripsi' => 'Hutang Pembelian Persediaan']
        );

        $payload = [
            'jenis' => 'hutang',
            'nama_pemberi_hutang' => null,
            'aktivitas' => 'Pembelian persediaan ' . $pembelianPersediaan->nomor_transaksi,
            'kategori_hutang_piutang_id' => $kategori->id,
            'nominal' => $total,
            'total_bayar' => $total,
            'jatuh_tempo' => Carbon::parse($pembelianPersediaan->tgl_pembelian)->addDays(30)->toDateString(),
            'nominal_bayar' => $dibayar,
            'sisa_pembayaran' => $sisa,
            'jenis_pembayaran' => 'cash',
            'account_bank_id' => null,
            'catatan' => trim(($pembelianPersediaan->catatan ?? '') . "\n\nOtomatis dari pembelian persediaan " . $pembelianPersediaan->nomor_transaksi),
            'status' => $pembelianPersediaan->status === 'selesai' ? 'selesai' : 'awaiting_approval',
            'created_by' => $pembelianPersediaan->created_by,
            'created_at' => Carbon::parse($pembelianPersediaan->tgl_pembelian),
        ];

        if ($pembelianPersediaan->hutang_piutang_id) {
            $hutang = HutangPiutang::find($pembelianPersediaan->hutang_piutang_id);
            if ($hutang) {
                $hutang->update($payload);
                return;
            }
        }

        $payload['nomor_transaksi'] = app(AutoNumberService::class)->generate('INVH');
        $hutang = HutangPiutang::create($payload);
        $pembelianPersediaan->updateQuietly(['hutang_piutang_id' => $hutang->id]);
    }

    private function removeLinkedHutang(PembelianPersediaan $pembelianPersediaan): void
    {
        if (!$pembelianPersediaan->hutang_piutang_id) {
            return;
        }

        $hutang = HutangPiutang::with('payments')->find($pembelianPersediaan->hutang_piutang_id);
        if ($hutang) {
            if ($hutang->payments->isEmpty()) {
                $hutang->delete();
            } else {
                $hutang->update(['status' => 'cancel']);
            }
        }

        $pembelianPersediaan->updateQuietly(['hutang_piutang_id' => null]);
    }
}
