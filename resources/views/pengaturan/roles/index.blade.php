@extends('layouts.app')

@section('title', 'Role & Permission')
@section('page-title', 'Role & Permission')
@section('page-description', 'Kelola role dan permission pengguna')

@section('content')
<div class="grid w-full space-y-5">
    <div class="kt-card">
        <div class="kt-card-header min-h-16">
            <input type="text" placeholder="Cari..." class="kt-input" style="width:200px" data-kt-datatable-search="#kt_datatable" />
            @can('roles.create')
            <button type="button" class="kt-btn kt-btn-outline" onclick="openCreateModal()">
                <i class="ki-filled ki-plus-squared"></i> Tambah Role
            </button>
            @endcan
        </div>
        <div id="kt_datatable" class="kt-card-table" data-kt-datatable="true" data-kt-datatable-page-size="10">
            <div class="kt-table-wrapper kt-scrollable">
                <table class="kt-table" data-kt-datatable-table="true">
                    <thead>
                        <tr>
                            <th scope="col" class="w-16" data-kt-datatable-column="no">
                                <span class="kt-table-col"><span class="kt-table-col-label">No</span><span class="kt-table-col-sort"></span></span>
                            </th>
                            <th scope="col" data-kt-datatable-column="name">
                                <span class="kt-table-col"><span class="kt-table-col-label">Nama Role</span><span class="kt-table-col-sort"></span></span>
                            </th>
                            <th scope="col" data-kt-datatable-column="permissions">
                                <span class="kt-table-col"><span class="kt-table-col-label">Jumlah Permission</span><span class="kt-table-col-sort"></span></span>
                            </th>
                            <th scope="col" data-kt-datatable-column="users">
                                <span class="kt-table-col"><span class="kt-table-col-label">Users</span><span class="kt-table-col-sort"></span></span>
                            </th>
                            <th scope="col" class="w-24" data-kt-datatable-column="aksi"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($roles as $i => $role)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td><span class="font-medium">{{ $role->name }}</span></td>
                            <td>{{ $role->permissions->count() }} permission</td>
                            <td>{{ $role->users_count }} user</td>
                            <td class="text-end">
                                <span class="inline-flex gap-2.5">
                                    @can('roles.edit')
                                    <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline" onclick="openEditModal({{ $role->id }})"><i class="ki-filled ki-pencil"></i></button>
                                    @endcan
                                    @can('roles.delete')
                                    @if($role->name !== 'Owner')
                                    <form method="POST" action="{{ route('roles.destroy', $role) }}" onsubmit="return confirm('Yakin hapus role ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline text-danger"><i class="ki-filled ki-trash"></i></button>
                                    </form>
                                    @endif
                                    @endcan
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="kt-datatable-toolbar">
                <div class="kt-datatable-length">Show <select class="kt-select kt-select-sm w-16" name="perpage" data-kt-datatable-size="true"></select> per page</div>
                <div class="kt-datatable-info"><span data-kt-datatable-info="true"></span><div class="kt-datatable-pagination" data-kt-datatable-pagination="true"></div></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="kt-modal" data-kt-modal="true" id="formModal">
    <div class="kt-modal-content max-w-[750px] top-5 lg:top-[5%]">
        <div class="kt-modal-header">
            <h3 class="kt-modal-title" id="modalTitle">Tambah Role</h3>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost" data-kt-modal-dismiss="true"><i class="ki-filled ki-cross"></i></button>
        </div>
        <form id="dataForm" method="POST">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">
            <div class="kt-modal-body flex flex-col gap-5" style="max-height:70vh;overflow-y:auto">
                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium text-foreground">Nama Role <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="roleName" class="kt-input" required>
                </div>

                @foreach($structured as $groupName => $pages)
                <div class="flex flex-col gap-3">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-semibold text-foreground">{{ $groupName }}</span>
                        <div class="flex-1 border-t border-border"></div>
                    </div>
                    @foreach($pages as $pg)
                    <div class="border border-border rounded-lg px-4 py-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-foreground">{{ $pg['label'] }}</span>
                            <div class="flex items-center gap-4">
                                @foreach($pg['actions'] as $act)
                                <label class="flex items-center gap-1.5 cursor-pointer">
                                    <input type="checkbox" name="permissions[]" value="{{ $act['name'] }}" class="kt-checkbox perm-check perm-{{ $pg['page'] }}">
                                    <span class="text-xs text-secondary-foreground capitalize">{{ $act['action'] }}</span>
                                </label>
                                @endforeach
                                <span class="border-l border-border h-4"></span>
                                <label class="flex items-center gap-1.5 cursor-pointer">
                                    <input type="checkbox" class="kt-checkbox" onchange="togglePage(this, '{{ $pg['page'] }}')">
                                    <span class="text-xs text-muted-foreground">Semua</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endforeach
            </div>
            <div class="kt-modal-footer justify-end">
                <button type="button" class="kt-btn kt-btn-outline" data-kt-modal-dismiss="true">Batal</button>
                <button type="submit" class="kt-btn kt-btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function togglePage(el, page) {
    document.querySelectorAll('.perm-' + page).forEach(function(cb) { cb.checked = el.checked; });
}

function uncheckAll() {
    document.querySelectorAll('.perm-check').forEach(function(cb) { cb.checked = false; });
    document.querySelectorAll('.kt-checkbox').forEach(function(cb) { if (!cb.classList.contains('perm-check')) cb.checked = false; });
}

function checkPermissions(perms) {
    uncheckAll();
    perms.forEach(function(p) {
        var cb = document.querySelector('input[value="' + p + '"]');
        if (cb) cb.checked = true;
    });
}

function openCreateModal() {
    document.getElementById('modalTitle').textContent = 'Tambah Role';
    document.getElementById('dataForm').action = "{{ route('roles.store') }}";
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('roleName').value = '';
    uncheckAll();
    KTModal.getInstance(document.querySelector('#formModal')).show();
}

function openEditModal(id) {
    fetch('/pengaturan/roles/' + id + '/edit')
        .then(function(r) { return r.json(); })
        .then(function(data) {
            document.getElementById('modalTitle').textContent = 'Edit Role';
            document.getElementById('dataForm').action = '/pengaturan/roles/' + id;
            document.getElementById('formMethod').value = 'PUT';
            document.getElementById('roleName').value = data.name;
            checkPermissions(data.permissions);
            KTModal.getInstance(document.querySelector('#formModal')).show();
        });
}
</script>
@endpush