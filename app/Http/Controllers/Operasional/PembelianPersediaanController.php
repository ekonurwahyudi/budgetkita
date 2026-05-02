<?php

namespace App\Http\Controllers\Operasional;

use App\Http\Controllers\Controller;
use App\Models\AccountBank;
use App\Models\ItemPersediaan;
use App\Models\PembelianPersediaan;
use App\Models\PembelianPersediaanItem;
use App\Services\ApprovalService;
use App\Services\AutoNumberService;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PembelianPersediaanController extends Controller
{
    public function index()
    {
        $hasTambak = auth()->user()->tambaks()->exists();
        $data = $hasTambak
            ? PembelianPersediaan::with('items')->latest()->get()
            : collect();
        return view('operasional.pembelian-persediaan.index', compact('data'));
    }

    public function create()
    {
        $kategoriPersediaans = \App\Models\KategoriPersediaan::orderBy('deskripsi')->get();
        $itemPersediaans = ItemPersediaan::with('kategoriPersediaan')->orderBy('deskripsi')->get();
        $accountBanks = AccountBank::where('status', 'aktif')->orderBy('nama_bank')->get();
        return view('operasional.pembelian-persediaan.form', compact('kategoriPersediaans', 'itemPersediaans', 'accountBanks') + ['pembelianPersediaan' => null]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'tgl_pembelian'              => 'required|date',
            'jenis_pembayaran'           => 'required|in:cash,bank',
            'account_bank_id'            => 'nullable|required_if:jenis_pembayaran,bank|uuid|exists:account_banks,id',
            'catatan'                    => 'nullable|string',
            'eviden.*'                   => 'nullable|file|max:5120|mimes:jpg,jpeg,png,gif,bmp,webp,pdf,xlsx,xls',
            'items'                      => 'required|array|min:1',
            'items.*.item_persediaan_id' => 'required|uuid|exists:item_persediaans,id',
            'items.*.qty'                => 'required|numeric|min:0.01',
            'items.*.satuan'             => 'required|string',
            'items.*.harga_satuan'       => 'required|numeric|min:0',
        ]);

        $input = $request->only(['tgl_pembelian', 'jenis_pembayaran', 'account_bank_id', 'catatan']);
        $input['nomor_transaksi'] = app(AutoNumberService::class)->generate('INVB');

        if ($request->hasFile('eviden')) {
            $paths = [];
            foreach ($request->file('eviden') as $file) {
                $paths[] = app(FileUploadService::class)->upload($file);
            }
            $input['eviden'] = $paths;
        }

        $pembelian = PembelianPersediaan::create($input);

        foreach ($request->input('items', []) as $item) {
            $hargaTotal = (float) $item['qty'] * (float) $item['harga_satuan'];
            $pembelian->items()->create([
                'item_persediaan_id' => $item['item_persediaan_id'],
                'qty'                => $item['qty'],
                'satuan'             => $item['satuan'],
                'harga_satuan'       => $item['harga_satuan'],
                'harga_total'        => $hargaTotal,
            ]);
        }

        return redirect()->route('pembelian-persediaan.index')->with('success', 'Pembelian persediaan berhasil ditambahkan.');
    }

    public function show(PembelianPersediaan $pembelianPersediaan)
    {
        $pembelianPersediaan->load(['items.itemPersediaan', 'accountBank']);
        return view('operasional.pembelian-persediaan.show', compact('pembelianPersediaan'));
    }

    public function edit(PembelianPersediaan $pembelianPersediaan)
    {
        $pembelianPersediaan->load('items.itemPersediaan');
        $kategoriPersediaans = \App\Models\KategoriPersediaan::orderBy('deskripsi')->get();
        $itemPersediaans = ItemPersediaan::with('kategoriPersediaan')->orderBy('deskripsi')->get();
        $accountBanks = AccountBank::where('status', 'aktif')->orderBy('nama_bank')->get();
        return view('operasional.pembelian-persediaan.form', compact('pembelianPersediaan', 'kategoriPersediaans', 'itemPersediaans', 'accountBanks'));
    }

    public function update(Request $request, PembelianPersediaan $pembelianPersediaan)
    {
        $request->validate([
            'tgl_pembelian'              => 'required|date',
            'jenis_pembayaran'           => 'required|in:cash,bank',
            'account_bank_id'            => 'nullable|required_if:jenis_pembayaran,bank|uuid|exists:account_banks,id',
            'catatan'                    => 'nullable|string',
            'eviden.*'                   => 'nullable|file|max:5120|mimes:jpg,jpeg,png,gif,bmp,webp,pdf,xlsx,xls',
            'items'                      => 'required|array|min:1',
            'items.*.item_persediaan_id' => 'required|uuid|exists:item_persediaans,id',
            'items.*.qty'                => 'required|numeric|min:0.01',
            'items.*.satuan'             => 'required|string',
            'items.*.harga_satuan'       => 'required|numeric|min:0',
        ]);

        $input = $request->only(['tgl_pembelian', 'jenis_pembayaran', 'account_bank_id', 'catatan']);

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
            $nominalLama = $pembelianPersediaan->items->sum('harga_total');

            // Reverse saldo lama jika sudah selesai via bank (pembelian selalu keluar)
            if ($pembelianPersediaan->status === 'selesai' && $pembelianPersediaan->jenis_pembayaran === 'bank' && $pembelianPersediaan->account_bank_id) {
                $bankLama = AccountBank::find($pembelianPersediaan->account_bank_id);
                if ($bankLama) {
                    $bankLama->increment('saldo', $nominalLama);
                }
            }

            $pembelianPersediaan->update($input);

            // Sync items
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

            // Apply saldo baru jika masih selesai via bank
            $pembelianPersediaan->refresh();
            $pembelianPersediaan->load('items');
            $nominalBaru = $pembelianPersediaan->items->sum('harga_total');

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
            $nominalLama = $pembelianPersediaan->items->sum('harga_total');

            // Reverse saldo jika sudah selesai via bank (pembelian selalu keluar)
            if ($pembelianPersediaan->status === 'selesai' && $pembelianPersediaan->jenis_pembayaran === 'bank' && $pembelianPersediaan->account_bank_id) {
                $bank = AccountBank::find($pembelianPersediaan->account_bank_id);
                if ($bank) {
                    $bank->increment('saldo', $nominalLama);
                }
            }

            $pembelianPersediaan->items()->delete();
            $pembelianPersediaan->delete();
        });

        return redirect()->back()->with('success', 'Pembelian persediaan berhasil dihapus.');
    }

    public function approve(PembelianPersediaan $pembelianPersediaan)
    {
        $pembelianPersediaan->load('items');
        app(ApprovalService::class)->approve($pembelianPersediaan);
        return redirect()->back()->with('success', 'Pembelian persediaan berhasil di-approve.');
    }

    public function reject(PembelianPersediaan $pembelianPersediaan)
    {
        app(ApprovalService::class)->reject($pembelianPersediaan);
        return redirect()->back()->with('success', 'Pembelian persediaan berhasil di-reject.');
    }
}
