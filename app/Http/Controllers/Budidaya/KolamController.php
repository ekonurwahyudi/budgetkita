<?php

namespace App\Http\Controllers\Budidaya;

use App\Http\Controllers\Controller;
use App\Exports\KolamParameterExport;
use App\Imports\KolamParameterImport;
use App\Models\Kolam;
use App\Models\KolamParameter;
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
        $kolam->load(['siklus.blok.tambak', 'users', 'parameters.user']);

        $dates = collect();
        $start = $kolam->tgl_berdiri ? $kolam->tgl_berdiri->copy() : Carbon::today();
        $end = Carbon::today();
        while ($start->lte($end)) {
            $dates->push($start->copy()->format('Y-m-d'));
            $start->addDay();
        }
        $dates = $dates->reverse();

        $parametersByKeyed = $kolam->parameters->keyBy(fn($p) => $p->tgl_parameter?->format('Y-m-d'));

        return view('budidaya.kolam.show', compact('kolam', 'dates', 'parametersByKeyed'));
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
}
