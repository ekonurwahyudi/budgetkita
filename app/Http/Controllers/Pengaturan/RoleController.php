<?php

namespace App\Http\Controllers\Pengaturan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    private function getPageGroups(): array
    {
        return [
            'Dashboard' => ['dashboard'],
            'Master Data' => [
                'karyawan', 'kategori-transaksi', 'item-transaksi',
                'sumber-dana', 'kategori-persediaan', 'item-persediaan',
                'kategori-investasi', 'kategori-hutang-piutang',
                'kategori-aset', 'account-bank',
            ],
            'Keuangan' => [
                'transaksi-keuangan', 'gaji-karyawan',
                'investasi', 'hutang-piutang',
            ],
            'Operasional' => [
                'persediaan', 'pembelian-persediaan', 'pembelian-aset',
            ],
            'Budidaya' => [
                'tambak', 'blok', 'siklus', 'panen', 'pemberian-pakan',
            ],
            'Pengaturan' => ['roles'],
        ];
    }

    private function getPageLabels(): array
    {
        return [
            'dashboard' => 'Dashboard',
            'karyawan' => 'Data Karyawan',
            'kategori-transaksi' => 'Kategori Transaksi',
            'item-transaksi' => 'Item Transaksi',
            'sumber-dana' => 'Sumber Dana',
            'kategori-persediaan' => 'Kategori Persediaan',
            'item-persediaan' => 'Item Persediaan',
            'kategori-investasi' => 'Kategori Investasi',
            'kategori-hutang-piutang' => 'Kategori Hutang/Piutang',
            'kategori-aset' => 'Kategori Aset',
            'account-bank' => 'Account Bank',
            'transaksi-keuangan' => 'Transaksi Keuangan',
            'gaji-karyawan' => 'Gaji Karyawan',
            'investasi' => 'Investasi',
            'hutang-piutang' => 'Hutang/Piutang',
            'persediaan' => 'Persediaan',
            'pembelian-persediaan' => 'Pembelian Persediaan',
            'pembelian-aset' => 'Pembelian Aset',
            'tambak' => 'Daftar Tambak',
            'blok' => 'Daftar Blok/Kolam',
            'siklus' => 'Daftar Siklus',
            'panen' => 'Panen',
            'pemberian-pakan' => 'Pemberian Pakan',
            'roles' => 'Role & Permission',
        ];
    }

    public function index()
    {
        $roles = Role::with('permissions')->withCount('users')->orderBy('name')->get();
        $permissions = Permission::orderBy('name')->get();
        $pageGroups = $this->getPageGroups();
        $pageLabels = $this->getPageLabels();

        // Build structured data: group > page > actions
        $structured = [];
        foreach ($pageGroups as $groupName => $pages) {
            foreach ($pages as $page) {
                $perms = $permissions->filter(fn($p) => str_starts_with($p->name, $page . '.'));
                if ($perms->isEmpty()) continue;
                $actions = [];
                foreach ($perms as $p) {
                    $action = str_replace($page . '.', '', $p->name);
                    $actions[] = ['name' => $p->name, 'action' => $action];
                }
                $structured[$groupName][] = [
                    'page' => $page,
                    'label' => $pageLabels[$page] ?? ucfirst($page),
                    'actions' => $actions,
                ];
            }
        }

        return view('pengaturan.roles.index', compact('roles', 'structured'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);
        $role = Role::create(['name' => $request->name]);
        $role->syncPermissions($request->permissions ?? []);
        return redirect()->back()->with('success', 'Role berhasil ditambahkan.');
    }

    public function edit(Role $role)
    {
        return response()->json([
            'id' => $role->id,
            'name' => $role->name,
            'permissions' => $role->permissions->pluck('name')->toArray(),
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);
        $role->update(['name' => $request->name]);
        $role->syncPermissions($request->permissions ?? []);
        return redirect()->back()->with('success', 'Role berhasil diperbarui.');
    }

    public function destroy(Role $role)
    {
        if ($role->name === 'Owner') {
            return redirect()->back()->with('error', 'Role Owner tidak bisa dihapus.');
        }
        $role->delete();
        return redirect()->back()->with('success', 'Role berhasil dihapus.');
    }
}