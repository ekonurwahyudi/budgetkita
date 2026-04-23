<?php

namespace App\Http\Controllers\Budidaya;

use App\Http\Controllers\Controller;
use App\Models\KategoriPersediaan;
use App\Models\PemberianPakan;
use App\Models\Persediaan;
use App\Models\RiwayatPersediaan;
use App\Models\Tambak;
use Illuminate\Http\Request;

class PemberianKimiaController extends Controller
{
    private function toBaseUnit(float $qty, string $unit): float
    {
        return match($unit) {
            'gram' => $qty / 1000,
            'ml'   => $qty / 1000,
            default => $qty,
        };
    }

    public function index()
    {
        $tambakIds = auth()->user()->tambaks()->pluck('tambaks.id');
        $data = PemberianPakan::with(['blok.tambak', 'siklus', 'itemPersediaan.kategoriPersediaan'])
            ->whereHas('blok', fn ($q) => $q->whereIn('tambak_id', $tambakIds))
            ->latest()->get()
            ->filter(fn($p) =>
                $p->itemPersediaan?->kategoriPersediaan &&
                stripos($p->itemPersediaan->kategoriPersediaan->deskripsi, 'pakan') === false
            )->values();
        return view('budidaya.pemberian-kimia.index', compact('data'));
    }

    public function create()
    {
        $tambakIds = auth()->user()->tambaks()->pluck('tambaks.id');
        $tambaks = Tambak::whereIn('id', $tambakIds)->orderBy('nama_tambak')->get();

        // Kategori selain pakan
        $kategoriPakan = KategoriPersediaan::where('deskripsi', 'ilike', '%pakan%')->pluck('id');
        $kategoriPersediaans = KategoriPersediaan::whereNotIn('id', $kategoriPakan)->orderBy('deskripsi')->get();

        $itemPakans = Persediaan::with('itemPersediaan.kategoriPersediaan')
            ->where('qty', '>', 0)->get()
            ->filter(fn($p) => !$kategoriPakan->contains($p->itemPersediaan?->kategori_persediaan_id))
            ->map(fn($p) => [
                'id'          => $p->item_persediaan_id,
                'kategori_id' => $p->itemPersediaan?->kategori_persediaan_id,
                'nama'        => ($p->itemPersediaan?->kode_item_persediaan ?? '') . ' - ' . ($p->itemPersediaan?->deskripsi ?? '-'),
                'stok'        => $p->qty,
                'unit'        => $p->unit,
            ]);

        return view('budidaya.pemberian-kimia.form', [
            'pemberianPakan'      => null,
            'tambaks'             => $tambaks,
            'kategoriPersediaans' => $kategoriPersediaans,
            'itemPakans'          => $itemPakans,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'blok_id'             => 'required|uuid|exists:bloks,id',
            'siklus_id'           => 'required|uuid|exists:sikluses,id',
            'tgl_pakan'           => 'required|date',
            'jumlah_pakan'        => 'required|numeric|min:0.01',
            'unit'                => 'required|in:kg,gram,ml,liter',
            'item_persediaan_id'  => 'required|uuid|exists:item_persediaans,id',
        ]);

        $persediaan = Persediaan::where('item_persediaan_id', $request->item_persediaan_id)->first();
        if (!$persediaan) {
            return redirect()->back()->withInput()->with('error', 'Item persediaan tidak ditemukan.');
        }

        $qtyBase = $this->toBaseUnit((float) $request->jumlah_pakan, $request->unit);
        if ($persediaan->qty < $qtyBase) {
            return redirect()->back()->withInput()->with('error', 'Stok tidak mencukupi. Stok: ' . number_format($persediaan->qty, 2) . ' ' . $persediaan->unit);
        }

        $record = PemberianPakan::create($request->only([
            'blok_id', 'siklus_id', 'tgl_pakan', 'jumlah_pakan', 'unit', 'item_persediaan_id',
        ]));

        $persediaan->decrement('qty', $qtyBase);
        $persediaan->total_harga = $persediaan->qty * $persediaan->harga_per_unit;
        $persediaan->save();

        RiwayatPersediaan::create([
            'persediaan_id' => $persediaan->id,
            'jenis'         => 'pengeluaran',
            'qty_masuk'     => 0,
            'qty_keluar'    => $qtyBase,
            'blok_id'       => $request->blok_id,
            'siklus_id'     => $request->siklus_id,
            'harga_per_unit'=> $persediaan->harga_per_unit,
            'harga_total'   => $qtyBase * $persediaan->harga_per_unit,
            'catatan'       => 'Pemberian ' . $request->jumlah_pakan . ' ' . $request->unit . ' - ' . ($record->itemPersediaan?->deskripsi ?? ''),
        ]);

        return redirect()->route('pemberian-kimia.index')->with('success', 'Pemberian kimia/antibiotik berhasil dicatat.');
    }

    public function show(PemberianPakan $pemberianKimia)
    {
        $pemberianKimia->load(['blok.tambak', 'siklus', 'itemPersediaan.kategoriPersediaan']);
        return view('budidaya.pemberian-kimia.show', ['pemberianPakan' => $pemberianKimia]);
    }

    public function destroy(PemberianPakan $pemberianKimia)
    {
        $persediaan = Persediaan::where('item_persediaan_id', $pemberianKimia->item_persediaan_id)->first();
        if ($persediaan) {
            $qtyBase = $this->toBaseUnit((float) $pemberianKimia->jumlah_pakan, $pemberianKimia->unit ?? 'kg');
            $persediaan->increment('qty', $qtyBase);
            $persediaan->total_harga = $persediaan->qty * $persediaan->harga_per_unit;
            $persediaan->save();
        }
        $pemberianKimia->delete();
        return redirect()->route('pemberian-kimia.index')->with('success', 'Data berhasil dihapus.');
    }
}
