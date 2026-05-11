<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $pages = [
            'dashboard' => ['view'],
            'karyawan' => ['view', 'create', 'edit', 'delete'],
            'kategori-transaksi' => ['view', 'create', 'edit', 'delete'],
            'item-transaksi' => ['view', 'create', 'edit', 'delete'],
            'sumber-dana' => ['view', 'create', 'edit', 'delete'],
            'kategori-persediaan' => ['view', 'create', 'edit', 'delete'],
            'item-persediaan' => ['view', 'create', 'edit', 'delete'],
            'kategori-investasi' => ['view', 'create', 'edit', 'delete'],
            'kategori-hutang-piutang' => ['view', 'create', 'edit', 'delete'],
            'kategori-aset' => ['view', 'create', 'edit', 'delete'],
            'account-bank' => ['view', 'create', 'edit', 'delete'],
            'transaksi-keuangan' => ['view', 'create', 'edit', 'delete', 'approve'],
            'gaji-karyawan' => ['view', 'create', 'edit', 'delete', 'approve'],
            'investasi' => ['view', 'create', 'edit', 'delete', 'approve'],
            'hutang-piutang' => ['view', 'create', 'edit', 'delete', 'approve'],
            'sharing-revenue' => ['view', 'create', 'edit', 'delete', 'approve'],
            'laporan-keuangan' => ['view'],
            'persediaan' => ['view', 'create', 'edit', 'delete'],
            'pembelian-persediaan' => ['view', 'create', 'edit', 'delete', 'approve'],
            'pembelian-aset' => ['view', 'create', 'edit', 'delete', 'approve'],
            'tambak' => ['view', 'create', 'edit', 'delete'],
            'blok' => ['view', 'create', 'edit', 'delete'],
            'siklus' => ['view', 'create', 'edit', 'delete'],
            'panen' => ['view', 'create', 'edit', 'delete', 'approve'],
            'kolam' => ['view', 'create', 'edit', 'delete'],
            'pemberian-pakan' => ['view', 'create', 'edit', 'delete'],
            'roles' => ['view', 'create', 'edit', 'delete'],
        ];

        $modules = ['masterdata', 'keuangan', 'operasional', 'budidaya'];

        // Build list of valid permission names
        $validPermissions = [];
        foreach ($pages as $page => $actions) {
            foreach ($actions as $action) {
                $validPermissions[] = "{$page}.{$action}";
            }
        }
        foreach ($modules as $mod) {
            $validPermissions[] = "{$mod}.view";
        }

        // Create or update permissions — also remove orphaned ones
        foreach ($validPermissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // Delete orphaned permissions that are not in our list
        Permission::whereNotIn('name', $validPermissions)->delete();

        // Owner: full access
        $owner = Role::firstOrCreate(['name' => 'Owner']);
        $owner->syncPermissions(Permission::all());

        // Manager: all except approve & roles management
        $manager = Role::firstOrCreate(['name' => 'Manager']);
        $manager->syncPermissions(
            Permission::where('name', 'not like', '%.approve')
                ->where('name', 'not like', 'roles.%')
                ->get()
        );

        // Finance: dashboard + masterdata view + keuangan + operasional
        $finance = Role::firstOrCreate(['name' => 'Finance']);
        $finance->syncPermissions(
            Permission::where(function ($q) {
                $q->where('name', 'dashboard.view')
                  ->orWhere('name', 'masterdata.view')
                  ->orWhere('name', 'like', 'kategori-transaksi.view')
                  ->orWhere('name', 'like', 'item-transaksi.view')
                  ->orWhere('name', 'like', 'sumber-dana.view')
                  ->orWhere('name', 'like', 'account-bank.%')
                  ->orWhere('name', 'like', 'transaksi-keuangan.%')
                  ->orWhere('name', 'like', 'gaji-karyawan.%')
                  ->orWhere('name', 'like', 'investasi.%')
                  ->orWhere('name', 'like', 'hutang-piutang.%')
                  ->orWhere('name', 'like', 'sharing-revenue.%')
                  ->orWhere('name', 'like', 'keuangan.view')
                  ->orWhere('name', 'like', 'laporan-keuangan.view')
                  ->orWhere('name', 'like', 'operasional.view');
            })->where('name', 'not like', '%.approve')->get()
        );
    }
}
