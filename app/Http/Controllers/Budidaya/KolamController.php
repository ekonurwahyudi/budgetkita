<?php

namespace App\Http\Controllers\Budidaya;

use App\Http\Controllers\Controller;
use App\Exports\KolamParameterExport;
use App\Imports\KolamParameterImport;
use App\Models\Kolam;
use App\Models\KolamParameter;
use App\Models\PemberianPakan;
use App\Models\Persediaan;
use App\Models\RiwayatPersediaan;
use App\Models\Siklus;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class KolamController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'siklus_id'   => 'required|uuid|exists:sikluses,id',
            'blok_id'     => 'required|uuid|exists:bloks,id',
            'nama_kolam'  => 'required|string|max:255',
            'tgl_berdiri' => 'nullable|date',
            'total_tebar' => 'nullable|integer|min:0',
            'status'      => 'required|in:aktif,selesai,batal',
            'user_ids'    => 'nullable|array',
            'user_ids.*'  => 'uuid|exists:users,id',
        ]);

        $kolam = Kolam::create($request->only('siklus_id', 'blok_id', 'nama_kolam', 'tgl_berdiri', 'total_tebar', 'status'));

        if ($request->filled('user_ids')) {
            $kolam->users()->sync($request->user_ids);
        }

        return redirect()->back()->with('success', 'Kolam berhasil ditambahkan.');
    }

    public function update(Request $request, Kolam $kolam)
    {
        $request->validate([
            'nama_kolam'  => 'required|string|max:255',
            'tgl_berdiri' => 'nullable|date',
            'total_tebar' => 'nullable|integer|min:0',
            'status'      => 'required|in:aktif,selesai,batal',
            'user_ids'    => 'nullable|array',
            'user_ids.*'  => 'uuid|exists:users,id',
        ]);

        $kolam->update($request->only('nama_kolam', 'tgl_berdiri', 'total_tebar', 'status'));
        $kolam->users()->sync($request->input('user_ids', []));

        return redirect()->back()->with('success', 'Kolam berhasil diperbarui.');
    }

    public function destroy(Kolam $kolam)
    {
        $kolam->delete();
        return redirect()->back()->with('success', 'Kolam berhasil dihapus.');
    }

    public function show(Kolam $kolam)
    {
        $kolam->load(['siklus.blok.tambak', 'users', 'parameters.user', 'pakan.itemPersediaan.kategoriPersediaan']);

        $dates = collect();
        $start = $kolam->tgl_berdiri ? $kolam->tgl_berdiri->copy() : Carbon::today();
        $end = Carbon::today();
        while ($start->lte($end)) {
            $dates->push($start->copy()->format('Y-m-d'));
            $start->addDay();
        }
        $dates = $dates->reverse();

        $parametersByKeyed = $kolam->parameters->keyBy(fn($p) => $p->tgl_parameter?->format('Y-m-d'));

        $pakanGroups = $kolam->pakan
            ->filter(fn($p) => $p->tgl_pakan)
            ->sortByDesc(fn($p) => $p->tgl_pakan->format('Y-m-d'))
            ->groupBy(fn($p) => $p->tgl_pakan->format('Y-m-d'))
            ->map(fn($group) => [
                'date'      => $group->first()->tgl_pakan->format('Y-m-d'),
                'is_today'  => $group->first()->tgl_pakan->format('Y-m-d') === now()->format('Y-m-d'),
                'is_puasa'  => $group->where('puasa', true)->count() > 0,
                'total_jumlah' => $group->where('puasa', false)->sum('jumlah_pakan'),
                'unit'      => $group->where('puasa', false)->first()?->unit ?? 'kg',
                'items'     => $group->values(),
                'by_time'   => [
                    '06:00' => $group->filter(fn($i) => $i->tgl_pakan?->format('H') === '06')->values(),
                    '10:00' => $group->filter(fn($i) => $i->tgl_pakan?->format('H') === '10')->values(),
                    '14:00' => $group->filter(fn($i) => $i->tgl_pakan?->format('H') === '14')->values(),
                    '18:00' => $group->filter(fn($i) => $i->tgl_pakan?->format('H') === '18')->values(),
                ],
                'jenis_pakan' => $group->where('puasa', false)->map(fn($i) => $i->itemPersediaan?->deskripsi ?? $i->itemPersediaan?->kode_item_persediaan ?? '')->unique()->filter()->join(', '),
            ])
            ->values();

        $cumulativeByDate = [];
        $running = 0;
        $datesAsc = $kolam->pakan
            ->filter(fn($p) => $p->tgl_pakan)
            ->sortBy(fn($p) => $p->tgl_pakan->format('Y-m-d'))
            ->groupBy(fn($p) => $p->tgl_pakan->format('Y-m-d'));
        foreach ($datesAsc as $date => $dayPakans) {
            $running += $dayPakans->where('puasa', false)->sum('jumlah_pakan');
            $cumulativeByDate[$date] = $running;
        }

        $perPage = (int) request('per_page', 10);
        $page = (int) request('pakan_page', 1);
        $pagedPakan = new \Illuminate\Pagination\LengthAwarePaginator(
            $pakanGroups->forPage($page, $perPage),
            $pakanGroups->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => array_merge(request()->query(), ['pakan_page' => $page]), 'pageName' => 'pakan_page']
        );

        $pakanByJenis = $kolam->pakan
            ->filter(fn($p) => $p->tgl_pakan && !$p->puasa && $p->itemPersediaan)
            ->groupBy(fn($p) => $p->item_persediaan_id)
            ->map(function ($group) {
                $item = $group->first()->itemPersediaan;
                $persediaan = \App\Models\Persediaan::where('item_persediaan_id', $group->first()->item_persediaan_id)->first();
                return [
                    'id'    => $group->first()->item_persediaan_id,
                    'persediaan_id' => $persediaan ? $persediaan->id : null,
                    'name'  => $item?->deskripsi ?? $item?->kode_item_persediaan ?? 'Lainnya',
                    'total' => $group->sum('jumlah_pakan'),
                    'unit'  => $group->first()->unit ?? 'kg',
                    'sisa'  => $persediaan ? $persediaan->qty : 0,
                    'sisa_unit' => $persediaan ? $persediaan->unit : 'kg',
                ];
            })
            ->values();

        $pakanHistory = $kolam->pakan
            ->filter(fn($p) => $p->tgl_pakan && !$p->puasa)
            ->sortBy(fn($p) => $p->tgl_pakan->format('Y-m-d'))
            ->groupBy(fn($p) => $p->tgl_pakan->format('Y-m-d'))
            ->map(fn($group) => [
                'date'  => $group->first()->tgl_pakan->format('d/m'),
                'total' => $group->sum('jumlah_pakan'),
            ])
            ->values()
            ->take(14);

        return view('budidaya.kolam.show', compact('kolam', 'dates', 'parametersByKeyed', 'pakanGroups', 'cumulativeByDate', 'pagedPakan', 'pakanByJenis', 'pakanHistory'));
    }

    // Parameter CRUD
    public function storeParameter(Request $request, Kolam $kolam)
    {
        $request->validate([
            'tgl_parameter'    => 'required|date',
            'ph_pagi'          => 'nullable|numeric',
            'ph_sore'          => 'nullable|numeric',
            'do_pagi'          => 'nullable|numeric',
            'do_sore'          => 'nullable|numeric',
            'suhu_pagi'        => 'nullable|numeric',
            'suhu_sore'        => 'nullable|numeric',
            'kecerahan_pagi'   => 'nullable|numeric',
            'kecerahan_sore'   => 'nullable|numeric',
            'salinitas'        => 'nullable|numeric',
            'tinggi_air'       => 'nullable|numeric',
            'warna_air'        => 'nullable|string|max:100',
            'alk'              => 'nullable|numeric',
            'ca'               => 'nullable|numeric',
            'mg'               => 'nullable|numeric',
            'mbw'              => 'nullable|numeric',
            'masa'             => 'nullable|numeric',
            'sr'               => 'nullable|numeric',
            'pcr'              => 'nullable|numeric',
            'perlakuan_harian' => 'nullable|string',
            'status'           => 'required|in:normal,perhatian,kritis',
        ]);

        $input = $request->only([
            'tgl_parameter', 'ph_pagi', 'ph_sore', 'do_pagi', 'do_sore',
            'suhu_pagi', 'suhu_sore', 'kecerahan_pagi', 'kecerahan_sore',
            'salinitas', 'tinggi_air', 'warna_air', 'alk', 'ca', 'mg',
            'mbw', 'masa', 'sr', 'pcr', 'perlakuan_harian', 'status',
        ]);
        $input['kolam_id'] = $kolam->id;
        $input['user_id']  = auth()->id();

        $parameter = KolamParameter::create($input);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'parameter' => $parameter->load('user')]);
        }

        return redirect()->back()->with('success', 'Parameter harian berhasil dicatat.');
    }

    public function destroyParameter(KolamParameter $parameter)
    {
        $parameter->delete();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Parameter berhasil dihapus.');
    }

    public function updateParameter(Request $request, KolamParameter $parameter)
    {
        $request->validate([
            'ph_pagi'          => 'nullable|numeric',
            'ph_sore'          => 'nullable|numeric',
            'do_pagi'          => 'nullable|numeric',
            'do_sore'          => 'nullable|numeric',
            'suhu_pagi'        => 'nullable|numeric',
            'suhu_sore'        => 'nullable|numeric',
            'kecerahan_pagi'   => 'nullable|numeric',
            'kecerahan_sore'   => 'nullable|numeric',
            'salinitas'        => 'nullable|numeric',
            'tinggi_air'       => 'nullable|numeric',
            'warna_air'        => 'nullable|string|max:100',
            'alk'              => 'nullable|numeric',
            'ca'               => 'nullable|numeric',
            'mg'               => 'nullable|numeric',
            'mbw'              => 'nullable|numeric',
            'masa'             => 'nullable|numeric',
            'sr'               => 'nullable|numeric',
            'pcr'              => 'nullable|numeric',
            'perlakuan_harian' => 'nullable|string',
            'status'           => 'nullable|in:normal,perhatian,kritis',
        ]);

        $input = $request->only([
            'ph_pagi', 'ph_sore', 'do_pagi', 'do_sore',
            'suhu_pagi', 'suhu_sore', 'kecerahan_pagi', 'kecerahan_sore',
            'salinitas', 'tinggi_air', 'warna_air', 'alk', 'ca', 'mg',
            'mbw', 'masa', 'sr', 'pcr', 'perlakuan_harian', 'status',
        ]);
        $input = array_filter($input, fn($v) => $v !== null && $v !== '');

        $parameter->update($input);
        return response()->json(['success' => true, 'parameter' => $parameter->fresh()]);
    }

    public function exportParameter(Kolam $kolam)
    {
        $kolam->load('parameters.user');
        $filename = 'parameter_' . str_replace(' ', '_', $kolam->nama_kolam) . '_' . now()->format('Ymd') . '.xlsx';
        return Excel::download(new KolamParameterExport($kolam), $filename);
    }

    public function importParameter(Request $request, Kolam $kolam)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            Excel::import(new KolamParameterImport($kolam), $request->file('file'));
            return redirect()->back()->with('success', 'Parameter berhasil diimport.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }

    public function bySiklus($siklusId)
    {
        $user = auth()->user();
        $hasTambak = $user->tambaks()->exists();

        $query = \App\Models\Kolam::where('siklus_id', $siklusId)
            ->where('status', 'aktif');
        if ($hasTambak) {
            $query->whereHas('users', fn($q) => $q->where('users.id', $user->id));
        }
        $kolams = $query->get(['id', 'nama_kolam']);

        return response()->json($kolams);
    }

    // Pakan CRUD
    public function storePakan(Request $request, Kolam $kolam)
    {
        $request->validate([
            'tgl_pakan'           => 'required|date',
            'item_persediaan_id'  => 'required|uuid|exists:item_persediaans,id',
            'jumlah_pakan'        => 'required|numeric|min:0.01',
            'unit'                => 'required|in:kg,gram,ml,liter',
        ]);

        $persediaan = Persediaan::where('item_persediaan_id', $request->item_persediaan_id)->first();
        if (!$persediaan) {
            return response()->json(['success' => false, 'message' => 'Item persediaan tidak ditemukan.'], 404);
        }

        $qtyBase = match($request->unit) {
            'gram'  => (float) $request->jumlah_pakan / 1000,
            'ml'    => (float) $request->jumlah_pakan / 1000,
            default => (float) $request->jumlah_pakan,
        };

        if ($persediaan->qty < $qtyBase) {
            return response()->json(['success' => false, 'message' => 'Stok tidak mencukupi. Sisa: ' . number_format($persediaan->qty, 2) . ' ' . $persediaan->unit], 422);
        }

        $pakan = PemberianPakan::create([
            'blok_id'             => $kolam->siklus->blok_id,
            'siklus_id'           => $kolam->siklus_id,
            'kolam_id'            => $kolam->id,
            'tgl_pakan'           => $request->tgl_pakan,
            'jumlah_pakan'        => $request->jumlah_pakan,
            'unit'                => $request->unit,
            'item_persediaan_id'  => $request->item_persediaan_id,
        ]);

        $persediaan->decrement('qty', $qtyBase);
        $persediaan->total_harga = ($persediaan->qty - $qtyBase) * $persediaan->harga_per_unit;
        $persediaan->save();

        RiwayatPersediaan::create([
            'persediaan_id' => $persediaan->id,
            'jenis'         => 'pengeluaran',
            'qty_masuk'     => 0,
            'qty_keluar'    => $qtyBase,
            'blok_id'       => $kolam->siklus->blok_id,
            'siklus_id'     => $kolam->siklus_id,
            'harga_per_unit'=> $persediaan->harga_per_unit,
            'harga_total'   => $qtyBase * $persediaan->harga_per_unit,
            'catatan'       => 'Pemberian pakan kolam ' . $kolam->nama_kolam,
        ]);

        $pakan->load('itemPersediaan.kategoriPersediaan');

        return response()->json(['success' => true, 'pakan' => $pakan]);
    }

    public function destroyPakan(PemberianPakan $pakan)
    {
        $persediaan = Persediaan::where('item_persediaan_id', $pakan->item_persediaan_id)->first();
        if ($persediaan) {
            $qtyBase = match($pakan->unit ?? 'kg') {
                'gram'  => (float) $pakan->jumlah_pakan / 1000,
                'ml'    => (float) $pakan->jumlah_pakan / 1000,
                default => (float) $pakan->jumlah_pakan,
            };
            $persediaan->increment('qty', $qtyBase);
            $persediaan->total_harga = $persediaan->qty * $persediaan->harga_per_unit;
            $persediaan->save();
        }

        $pakan->delete();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Pemberian pakan berhasil dihapus.');
    }
}
