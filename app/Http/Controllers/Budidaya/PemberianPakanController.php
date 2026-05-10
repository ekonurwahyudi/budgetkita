<?php

namespace App\Http\Controllers\Budidaya;

use App\Http\Controllers\Controller;
use App\Models\KategoriPersediaan;
use App\Models\PemberianPakan;
use App\Models\Persediaan;
use App\Models\RiwayatPersediaan;
use App\Models\Tambak;
use Illuminate\Http\Request;

class PemberianPakanController extends Controller
{
    // Konversi ke unit dasar (kg untuk berat, liter untuk volume)
    private function toBaseUnit(float $qty, string $unit): float
    {
        return match($unit) {
            'gram' => $qty / 1000,
            'ml'   => $qty / 1000,
            default => $qty, // kg, liter sudah base
        };
    }

    public function index()
    {
        $user = auth()->user();
        $hasTambak = $user->tambaks()->exists();
        $query = PemberianPakan::with(['blok.tambak', 'siklus', 'kolam', 'itemPersediaan.kategoriPersediaan']);
        if ($hasTambak) {
            $tambakIds = $user->tambaks()->pluck('tambaks.id');
            $query->whereHas('blok', fn ($q) => $q->whereIn('tambak_id', $tambakIds));
        }
        $data = $query->latest()->get()
            ->filter(fn($p) =>
                !$p->itemPersediaan?->kategoriPersediaan ||
                stripos($p->itemPersediaan->kategoriPersediaan->deskripsi, 'pakan') !== false
            )->values();

        $groups = $data->groupBy(fn($p) =>
            ($p->kolam_id ?? 'null') . '|' . $p->tgl_pakan?->format('Y-m-d H:i') ?? 'null'
        )->map(fn($group) => [
            'kolam_id'   => $group->first()->kolam_id,
            'kolam'      => $group->first()->kolam?->nama_kolam ?? '-',
            'blok'       => $group->first()->blok?->nama_blok ?? '',
            'siklus'     => $group->first()->siklus?->nama_siklus ?? '',
            'tambak'     => $group->first()->blok?->tambak?->nama_tambak ?? '',
            'tgl_pakan'  => $group->first()->tgl_pakan,
            'is_puasa'   => $group->where('puasa', true)->count() > 0,
            'total_jumlah' => $group->where('puasa', false)->sum('jumlah_pakan'),
            'unit'       => $group->where('puasa', false)->first()?->unit ?? 'kg',
            'items'      => $group->values(),
        ])->values();

        $perPage = (int) request('per_page', 10);
        $page = (int) request('page', 1);
        $paged = new \Illuminate\Pagination\LengthAwarePaginator(
            $groups->forPage($page, $perPage),
            $groups->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('budidaya.pemberian-pakan.index', compact('data', 'paged'));
    }

    public function create()
    {
        $user = auth()->user();
        $hasTambak = $user->tambaks()->exists();
        $tambaks = $hasTambak
            ? Tambak::whereIn('id', $user->tambaks()->pluck('tambaks.id'))->orderBy('nama_tambak')->get()
            : Tambak::orderBy('nama_tambak')->get();

        $autoTambak = $tambaks->count() === 1 ? $tambaks->first() : null;

        $kategoriPakan = KategoriPersediaan::where('deskripsi', 'ilike', '%pakan%')->pluck('id');
        $kategoriPersediaans = KategoriPersediaan::whereIn('id', $kategoriPakan)->orderBy('deskripsi')->get();

        $itemPakans = Persediaan::with('itemPersediaan.kategoriPersediaan')
            ->where('qty', '>', 0)->get()
            ->filter(fn($p) => $kategoriPakan->contains($p->itemPersediaan?->kategori_persediaan_id))
            ->map(fn($p) => [
                'id'          => $p->item_persediaan_id,
                'kategori_id' => $p->itemPersediaan?->kategori_persediaan_id,
                'nama'        => ($p->itemPersediaan?->kode_item_persediaan ?? '') . ' - ' . ($p->itemPersediaan?->deskripsi ?? '-'),
                'stok'        => $p->qty,
                'unit'        => $p->unit,
            ]);

        $bloks = collect();
        $sikluses = collect();
        $kolams = collect();

        if ($autoTambak) {
            $bloks = \App\Models\Blok::where('tambak_id', $autoTambak->id)->orderBy('nama_blok')->get(['id', 'nama_blok', 'tambak_id']);
            if ($bloks->count() === 1) {
                $sikluses = \App\Models\Siklus::where('blok_id', $bloks->first()->id)->where('status', '!=', 'selesai')->orderBy('nama_siklus')->get(['id', 'nama_siklus', 'blok_id']);
                if ($sikluses->count() === 1) {
                    $kolamQuery = \App\Models\Kolam::where('siklus_id', $sikluses->first()->id)
                        ->where('status', '!=', 'selesai');
                    if ($hasTambak) {
                        $kolamQuery->whereHas('users', fn($q) => $q->where('users.id', $user->id));
                    }
                    $kolams = $kolamQuery->orderBy('nama_kolam')->get(['id', 'nama_kolam', 'siklus_id']);
                }
            }
        }

        return view('budidaya.pemberian-pakan.form', [
            'pemberianPakan'      => null,
            'tambaks'             => $tambaks,
            'autoTambak'          => $autoTambak,
            'bloks'               => $bloks,
            'sikluses'            => $sikluses,
            'kolams'              => $kolams,
            'kategoriPersediaans' => $kategoriPersediaans,
            'itemPakans'          => $itemPakans,
        ]);
    }

    public function store(Request $request)
    {
        $puasa = $request->boolean('puasa');

        if ($puasa) {
            $request->validate([
                'blok_id'    => 'required|uuid|exists:bloks,id',
                'siklus_id'  => 'required|uuid|exists:sikluses,id',
                'kolam_id'   => 'nullable|uuid|exists:kolams,id',
                'tgl_pakan'  => 'required|date',
                'puasa'      => 'nullable|boolean',
            ]);
        } else {
            $request->validate([
                'blok_id'                => 'required|uuid|exists:bloks,id',
                'siklus_id'              => 'required|uuid|exists:sikluses,id',
                'kolam_id'               => 'nullable|uuid|exists:kolams,id',
                'tgl_pakan'              => 'required|date',
                'puasa'                  => 'nullable|boolean',
                'items'                  => 'required|array|min:1',
                'items.*.item_persediaan_id' => 'required|uuid|exists:item_persediaans,id',
                'items.*.jumlah_pakan'   => 'required|numeric|min:0.01',
                'items.*.unit'           => 'required|in:kg,gram,ml,liter',
            ]);
        }

        $puasa = $request->boolean('puasa');

        if ($puasa) {
            PemberianPakan::create([
                'blok_id'             => $request->blok_id,
                'siklus_id'           => $request->siklus_id,
                'kolam_id'            => $request->kolam_id ?: null,
                'tgl_pakan'           => $request->tgl_pakan,
                'jumlah_pakan'        => 0,
                'unit'                => 'kg',
                'puasa'               => true,
                'item_persediaan_id'  => null,
            ]);
            return redirect()->route('pemberian-pakan.index')->with('success', 'Puasa berhasil dicatat.');
        }

        foreach ($request->input('items', []) as $item) {
            $persediaan = Persediaan::where('item_persediaan_id', $item['item_persediaan_id'])->first();
            if (!$persediaan) {
                return redirect()->back()->withInput()->with('error', 'Item persediaan tidak ditemukan.');
            }

            $qtyBase = $this->toBaseUnit((float) $item['jumlah_pakan'], $item['unit']);

            if ($persediaan->qty < $qtyBase) {
                return redirect()->back()->withInput()->with('error', 'Stok tidak mencukupi untuk ' . ($persediaan->itemPersediaan?->deskripsi ?? '-') . '. Stok tersedia: ' . number_format($persediaan->qty, 2) . ' ' . $persediaan->unit);
            }

            $pemberianPakan = PemberianPakan::create([
                'blok_id'             => $request->blok_id,
                'siklus_id'           => $request->siklus_id,
                'kolam_id'            => $request->kolam_id,
                'tgl_pakan'           => $request->tgl_pakan,
                'jumlah_pakan'        => $item['jumlah_pakan'],
                'unit'                => $item['unit'],
                'puasa'               => false,
                'item_persediaan_id'  => $item['item_persediaan_id'],
            ]);

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
                'catatan'       => 'Pemberian ' . $item['jumlah_pakan'] . ' ' . $item['unit'] . ' - ' . ($pemberianPakan->itemPersediaan?->deskripsi ?? ''),
            ]);
        }

        return redirect()->route('pemberian-pakan.index')->with('success', 'Pemberian pakan berhasil dicatat.');
    }

    public function show(PemberianPakan $pemberianPakan)
    {
        $pemberianPakan->load(['blok.tambak', 'siklus', 'kolam', 'itemPersediaan.kategoriPersediaan']);

        $groupItems = PemberianPakan::with(['itemPersediaan.kategoriPersediaan'])
            ->where('kolam_id', $pemberianPakan->kolam_id)
            ->where('tgl_pakan', $pemberianPakan->tgl_pakan)
            ->where('blok_id', $pemberianPakan->blok_id)
            ->where('siklus_id', $pemberianPakan->siklus_id)
            ->orderBy('puasa', 'desc')
            ->get();

        return view('budidaya.pemberian-pakan.show', compact('pemberianPakan', 'groupItems'));
    }

    public function destroy(PemberianPakan $pemberianPakan)
    {
        $persediaan = Persediaan::where('item_persediaan_id', $pemberianPakan->item_persediaan_id)->first();
        if ($persediaan) {
            $qtyBase = $this->toBaseUnit((float) $pemberianPakan->jumlah_pakan, $pemberianPakan->unit ?? 'kg');
            $persediaan->increment('qty', $qtyBase);
            $persediaan->total_harga = $persediaan->qty * $persediaan->harga_per_unit;
            $persediaan->save();
        }

        $pemberianPakan->delete();
        return redirect()->route('pemberian-pakan.index')->with('success', 'Data berhasil dihapus.');
    }
}
