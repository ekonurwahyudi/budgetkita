<?php

namespace App\Http\Controllers\Masterdata;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class KaryawanController extends Controller
{
    private array $bankList = [
        'BCA', 'BNI', 'BRI', 'Bank Mandiri', 'BSI', 'CIMB Niaga',
        'Bank Danamon', 'Bank Permata', 'OCBC NISP', 'Bank Mega',
        'Bank BTN', 'Bank Muamalat', 'Bank Sinarmas', 'Bank Bukopin',
        'Bank BTPN', 'Bank Jago', 'Bank DBS Indonesia', 'Bank UOB Indonesia',
        'Bank Maybank Indonesia', 'Bank Panin', 'Bank BJB', 'Bank Jatim',
        'Bank Jateng', 'Bank DKI', 'Bank DIY', 'Bank Nagari',
        'Bank Sumut', 'Bank Riau Kepri', 'Bank Kalbar', 'Bank Kaltimtara',
    ];

    public function index()
    {
        $data = User::with('roles')->latest()->get();
        $roles = Role::orderBy('name')->get();
        $banks = $this->bankList;
        return view('masterdata.karyawan.index', compact('data', 'roles', 'banks'));
    }

    public function store(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'jabatan' => 'required|string|max:255',
            'no_hp' => 'required|string|max:20',
            'email' => 'required|email|unique:users,email',
            'tempat_lahir' => 'nullable|string|max:255',
            'tgl_lahir' => 'nullable|date',
            'nomor_rekening' => 'nullable|string|max:50',
            'bank' => 'nullable|string|max:100',
            'mulai_bekerja' => 'nullable|date',
            'role' => 'required|string|exists:roles,name',
            'status' => 'required|in:aktif,block',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'nama' => $request->nama,
            'jabatan' => $request->jabatan,
            'no_hp' => $request->no_hp,
            'email' => $request->email,
            'tempat_lahir' => $request->tempat_lahir,
            'tgl_lahir' => $request->tgl_lahir,
            'nomor_rekening' => $request->nomor_rekening,
            'bank' => $request->bank,
            'mulai_bekerja' => $request->mulai_bekerja,
            'password' => Hash::make($request->password),
            'status' => $request->status,
        ]);
        $user->assignRole($request->role);

        return response()->json(['success' => true, 'message' => 'Karyawan berhasil ditambahkan.']);
    }

    public function edit(User $karyawan)
    {
        $karyawan->load('roles');
        $karyawan->role = $karyawan->roles->first()?->name;
        return response()->json($karyawan);
    }

    public function update(Request $request, User $karyawan)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'jabatan' => 'required|string|max:255',
            'no_hp' => 'required|string|max:20',
            'email' => ['required', 'email', \Illuminate\Validation\Rule::unique('users', 'email')->ignore($karyawan->id)],
            'tempat_lahir' => 'nullable|string|max:255',
            'tgl_lahir' => 'nullable|date',
            'nomor_rekening' => 'nullable|string|max:50',
            'bank' => 'nullable|string|max:100',
            'mulai_bekerja' => 'nullable|date',
            'role' => 'required|string|exists:roles,name',
            'status' => 'required|in:aktif,block',
            'password' => 'nullable|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $karyawan->update([
            'nama' => $request->nama,
            'jabatan' => $request->jabatan,
            'no_hp' => $request->no_hp,
            'email' => $request->email,
            'tempat_lahir' => $request->tempat_lahir,
            'tgl_lahir' => $request->tgl_lahir,
            'nomor_rekening' => $request->nomor_rekening,
            'bank' => $request->bank,
            'mulai_bekerja' => $request->mulai_bekerja,
            'status' => $request->status,
        ]);

        if ($request->filled('password')) {
            $karyawan->update(['password' => Hash::make($request->password)]);
        }

        $karyawan->syncRoles([$request->role]);

        return response()->json(['success' => true, 'message' => 'Karyawan berhasil diperbarui.']);
    }

    public function destroy(User $karyawan)
    {
        $karyawan->delete();
        return redirect()->back()->with('success', 'Karyawan berhasil dihapus.');
    }
}