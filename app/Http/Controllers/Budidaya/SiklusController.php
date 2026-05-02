<?php

namespace App\Http\Controllers\Budidaya;

use App\Http\Controllers\Controller;
use App\Models\Blok;
use App\Models\PemberianPakan;
use App\Models\Siklus;
use App\Models\Tambak;
use App\Models\TransaksiKeuangan;
use Illuminate\Http\Request;

class SiklusController extends Controller
{
    public function index(Request $request)
    {
        $tambakIds = auth()->user()->tambaks()->pluck('tambaks.id');
        $query = Siklus::with('blok.tambak')
            ->whereHas('blok', fn ($q) => $q->whereIn('tambak_id', $tambakIds));
        if ($request->filled('blok_id')) {
            $query->where('blok_id', $request->blok_id);
        }
        if ($request->filled('tambak_id')) {
            $query->whereHas('blok', fn ($q) => $q->where('tambak_id', $request->tambak_id));
        }
        $data = $query->latest()->get();
        $tambaks = Tambak::whereIn('id', $tambakIds)->orderBy('nama_tambak')->get();
        $bloks = Blok::with('tambak')->whereIn('tambak_id', $tambakIds)->orderBy('nama_blok')->get();
        return view('budidaya.siklus.index', compact('data', 'tambaks', 'bloks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'blok_id' => 'required|uuid|exists:bloks,id',
            'nama_siklus' => 'required|string|max:255',
            'tgl_siklus' => 'required|date',
            'lama_persiapan' => 'nullable|integer|min:0',
            'tgl_tebar' => 'required|date',
            'total_tebar' => 'required|integer|min:0',
            'spesies_udang' => 'nullable|string|max:255',
            'umur_awal' => 'required|integer|min:0',
            'kecerahan' => 'nullable|numeric', 'suhu' => 'nullable|numeric',
            'do_level' => 'nullable|numeric', 'salinitas' => 'nullable|numeric',
            'ph_pagi' => 'nullable|numeric', 'ph_sore' => 'nullable|numeric',
            'fcr' => 'nullable|numeric', 'adg' => 'nullable|numeric',
            'sr' => 'nullable|numeric', 'mbw' => 'nullable|numeric', 'size' => 'nullable|numeric',
            'status' => 'required|in:aktif,selesai,gagal',
            'harga_pakan' => 'nullable|numeric|min:0',
        ]);

        // Check only 1 active siklus per blok
        if ($request->status === 'aktif') {
            $existing = Siklus::where('blok_id', $request->blok_id)->where('status', 'aktif')->exists();
            if ($existing) {
                return redirect()->back()->with('error', 'Blok ini sudah memiliki siklus aktif. Selesaikan siklus aktif terlebih dahulu.');
            }
        }

        $input = $request->only([
            'blok_id', 'nama_siklus', 'tgl_siklus', 'lama_persiapan', 'tgl_tebar',
            'total_tebar', 'spesies_udang', 'umur_awal', 'kecerahan', 'suhu',
            'do_level', 'salinitas', 'ph_pagi', 'ph_sore', 'fcr', 'adg', 'sr', 'mbw', 'size', 'status', 'harga_pakan',
        ]);
        // Auto-compute selisih_ph
        if ($request->filled('ph_pagi') && $request->filled('ph_sore')) {
            $input['selisih_ph'] = $request->ph_sore - $request->ph_pagi;
        }
        Siklus::create($input);
        return redirect()->back()->with('success', 'Siklus berhasil ditambahkan.');
    }

    public function show(Siklus $siklus)
    {
        $siklus->load(['blok.tambak', 'panens.accountBank']);
        $transaksis = TransaksiKeuangan::with(['itemTransaksi', 'kategoriTransaksi', 'sumberDana'])
            ->where('siklus_id', $siklus->id)
            ->latest('tgl_kwitansi')
            ->get();

        $semuaPemberian = PemberianPakan::with('itemPersediaan.kategoriPersediaan')
            ->where('siklus_id', $siklus->id)
            ->latest('tgl_pakan')
            ->get();

        $pemberianPakans = $semuaPemberian->filter(fn($p) =>
            !$p->itemPersediaan?->kategoriPersediaan ||
            stripos($p->itemPersediaan->kategoriPersediaan->deskripsi, 'pakan') !== false
        )->values();

        $pemberianKimia = $semuaPemberian->filter(fn($p) =>
            $p->itemPersediaan?->kategoriPersediaan &&
            stripos($p->itemPersediaan->kategoriPersediaan->deskripsi, 'pakan') === false
        )->values();

        $kolams = \App\Models\Kolam::with(['users'])
            ->where('siklus_id', $siklus->id)
            ->when(!auth()->user()->hasRole('Owner'), function ($q) {
                $q->whereHas('users', fn ($q2) => $q2->where('users.id', auth()->id()));
            })
            ->latest()->get();

        // Load latest parameter per kolam manually (avoid UUID + MAX issue)
        $kolamIds = $kolams->pluck('id');
        $latestParams = \App\Models\KolamParameter::whereIn('kolam_id', $kolamIds)
            ->selectRaw('DISTINCT ON (kolam_id) *')
            ->orderBy('kolam_id')
            ->orderByDesc('created_at')
            ->get()
            ->keyBy('kolam_id');

        $kolams->each(fn($k) => $k->setRelation('latestParameter', $latestParams->get($k->id)));

        $users = \App\Models\User::where('status', 'aktif')->orderBy('nama')->get();
        $accountBanks = \App\Models\AccountBank::where('status', 'aktif')->orderBy('nama_bank')->get();
        return view('budidaya.siklus.show', compact('siklus', 'transaksis', 'pemberianPakans', 'pemberianKimia', 'accountBanks', 'kolams', 'users'));
    }

    public function edit(Siklus $siklus)
    {
        $data = $siklus->toArray();
        $data['tgl_siklus'] = $siklus->tgl_siklus?->format('Y-m-d');
        $data['tgl_tebar'] = $siklus->tgl_tebar?->format('Y-m-d');
        return response()->json($data);
    }

    public function update(Request $request, Siklus $siklus)
    {
        $request->validate([
            'blok_id' => 'required|uuid|exists:bloks,id',
            'nama_siklus' => 'required|string|max:255',
            'tgl_siklus' => 'required|date_format:Y-m-d,m/d/Y,d/m/Y',
            'lama_persiapan' => 'required|integer|min:0',
            'tgl_tebar' => 'required|date_format:Y-m-d,m/d/Y,d/m/Y',
            'total_tebar' => 'required|integer|min:0',
            'spesies_udang' => 'required|string|max:255',
            'umur_awal' => 'required|integer|min:0',
            'kecerahan' => 'nullable|numeric', 'suhu' => 'nullable|numeric',
            'do_level' => 'nullable|numeric', 'salinitas' => 'nullable|numeric',
            'ph_pagi' => 'nullable|numeric', 'ph_sore' => 'nullable|numeric',
            'fcr' => 'nullable|numeric', 'adg' => 'nullable|numeric',
            'sr' => 'nullable|numeric', 'mbw' => 'nullable|numeric', 'size' => 'nullable|numeric',
            'status' => 'required|in:aktif,selesai,gagal',
            'harga_pakan' => 'nullable|numeric|min:0',
        ]);

        if ($request->status === 'aktif' && $siklus->status !== 'aktif') {
            $existing = Siklus::where('blok_id', $request->blok_id)->where('status', 'aktif')->where('id', '!=', $siklus->id)->exists();
            if ($existing) {
                return redirect()->back()->with('error', 'Blok ini sudah memiliki siklus aktif.');
            }
        }

        $input = $request->only([
            'blok_id', 'nama_siklus', 'tgl_siklus', 'lama_persiapan', 'tgl_tebar',
            'total_tebar', 'spesies_udang', 'umur_awal', 'kecerahan', 'suhu',
            'do_level', 'salinitas', 'ph_pagi', 'ph_sore', 'fcr', 'adg', 'sr', 'mbw', 'size', 'status', 'harga_pakan',
        ]);
        if ($request->filled('ph_pagi') && $request->filled('ph_sore')) {
            $input['selisih_ph'] = $request->ph_sore - $request->ph_pagi;
        }
        $siklus->update($input);
        return redirect()->back()->with('success', 'Siklus berhasil diperbarui.');
    }

    public function destroy(Siklus $siklus)
    {
        $siklus->delete();
        return redirect()->back()->with('success', 'Siklus berhasil dihapus.');
    }

    public function byBlok(Blok $blok)
    {
        return response()->json($blok->sikluses()->where('status', 'aktif')->orderBy('nama_siklus')->get());
    }
}
