@extends('layouts.app')

@section('title', 'Data Karyawan')
@section('page-title', 'Data Karyawan')
@section('page-description', 'Kelola data karyawan')

@section('content')
<div class="grid w-full space-y-5">
    <div class="kt-card">
        <div class="kt-card-header min-h-16">
            <input type="text" placeholder="Cari..." class="kt-input" style="width:200px" data-kt-datatable-search="#kt_datatable" />
            @can('karyawan.create')
            <button type="button" class="kt-btn kt-btn-outline" onclick="openCreateModal()">
                <i class="ki-filled ki-plus-squared"></i> Tambah
            </button>
            @endcan
        </div>
        <div id="kt_datatable" class="kt-card-table" data-kt-datatable="true" data-kt-datatable-page-size="10">
            <div class="kt-table-wrapper kt-scrollable">
                <table class="kt-table" data-kt-datatable-table="true">
                    <thead>
                        <tr>
                            <th scope="col" class="w-12" data-kt-datatable-column="no">
                                <span class="kt-table-col"><span class="kt-table-col-label">No</span><span class="kt-table-col-sort"></span></span>
                            </th>
                            <th scope="col" data-kt-datatable-column="nama">
                                <span class="kt-table-col"><span class="kt-table-col-label">Nama</span><span class="kt-table-col-sort"></span></span>
                            </th>
                            <th scope="col" data-kt-datatable-column="jabatan">
                                <span class="kt-table-col"><span class="kt-table-col-label">Jabatan</span><span class="kt-table-col-sort"></span></span>
                            </th>
                            <th scope="col" data-kt-datatable-column="role">
                                <span class="kt-table-col"><span class="kt-table-col-label">Role</span><span class="kt-table-col-sort"></span></span>
                            </th>
                            <th scope="col" data-kt-datatable-column="no_hp">
                                <span class="kt-table-col"><span class="kt-table-col-label">No HP</span><span class="kt-table-col-sort"></span></span>
                            </th>
                            <th scope="col" data-kt-datatable-column="email">
                                <span class="kt-table-col"><span class="kt-table-col-label">Email</span><span class="kt-table-col-sort"></span></span>
                            </th>
                            <th scope="col" data-kt-datatable-column="mulai_bekerja">
                                <span class="kt-table-col"><span class="kt-table-col-label">Masa Kerja</span><span class="kt-table-col-sort"></span></span>
                            </th>
                            <th scope="col" data-kt-datatable-column="status">
                                <span class="kt-table-col"><span class="kt-table-col-label">Status</span><span class="kt-table-col-sort"></span></span>
                            </th>
                            <th scope="col" class="w-24" data-kt-datatable-column="aksi"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $i => $item)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $item->nama }}</td>
                            <td>{{ $item->jabatan }}</td>
                            <td>{{ $item->roles->first()?->name ?? '-' }}</td>
                            <td>{{ $item->no_hp }}</td>
                            <td>{{ $item->email }}</td>
                            <td>
                                @if($item->mulai_bekerja)
                                    @php
                                        $diff = $item->mulai_bekerja->diff(now());
                                        $parts = [];
                                        if ($diff->y) $parts[] = $diff->y . ' thn';
                                        if ($diff->m) $parts[] = $diff->m . ' bln';
                                        if (empty($parts)) $parts[] = $diff->d . ' hari';
                                    @endphp
                                    {{ implode(' ', $parts) }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($item->status === 'aktif')
                                    <span class="kt-badge kt-badge-success">Aktif</span>
                                @else
                                    <span class="kt-badge kt-badge-destructive">Block</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <span class="inline-flex gap-2.5">
                                    @can('karyawan.edit')
                                    <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline" onclick="openEditModal('{{ $item->id }}')"><i class="ki-filled ki-pencil"></i></button>
                                    @endcan
                                    @can('karyawan.delete')
                                    <form method="POST" action="{{ route('karyawan.destroy', $item) }}" onsubmit="return confirm('Yakin hapus?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline text-danger"><i class="ki-filled ki-trash"></i></button>
                                    </form>
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
<div class="kt-modal" data-kt-modal="true" data-kt-modal-backdrop-static="true" id="formModal">
    <div class="kt-modal-content max-w-[600px] top-5 lg:top-[5%]">
        <div class="kt-modal-header">
            <h3 class="kt-modal-title" id="modalTitle">Tambah Karyawan</h3>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost" data-kt-modal-dismiss="true"><i class="ki-filled ki-cross"></i></button>
        </div>
        <form id="dataForm" method="POST">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">
            <div class="kt-modal-body flex flex-col gap-4" style="max-height:70vh;overflow-y:auto">
                <!-- Error container -->
                <div id="formErrors" class="hidden rounded-lg p-3 flex items-start gap-2" style="background:#fef2f2;border:1px solid #fca5a5;">
                    <i class="ki-filled ki-information-2 text-base mt-0.5 shrink-0" style="color:#dc2626;"></i>
                    <ul id="formErrorList" class="list-none flex flex-col gap-1"></ul>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="nama" id="nama" class="kt-input" required>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Jabatan <span class="text-danger">*</span></label>
                        <input type="text" name="jabatan" id="jabatan" class="kt-input" required>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">No HP <span class="text-danger">*</span></label>
                        <input type="text" name="no_hp" id="no_hp" class="kt-input" required>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="email" class="kt-input" required>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Tempat Lahir</label>
                        <input type="text" name="tempat_lahir" id="tempat_lahir" class="kt-input">
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Tanggal Lahir</label>
                        <div class="kt-input">
                            <i class="ki-outline ki-calendar"></i>
                            <input class="grow" name="tgl_lahir" id="tgl_lahir" data-kt-date-picker="true" data-kt-date-picker-input-mode="true" placeholder="Pilih tanggal" readonly type="text"/>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Nomor Rekening</label>
                        <input type="text" name="nomor_rekening" id="nomor_rekening" class="kt-input">
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Bank</label>
                        <select name="bank" id="bank" class="kt-select">
                            <option value="">-- Pilih Bank --</option>
                            @foreach($banks as $b)
                            <option value="{{ $b }}">{{ $b }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Mulai Bekerja</label>
                        <div class="kt-input">
                            <i class="ki-outline ki-calendar"></i>
                            <input class="grow" name="mulai_bekerja" id="mulai_bekerja" data-kt-date-picker="true" data-kt-date-picker-input-mode="true" placeholder="Pilih tanggal" readonly type="text"/>
                        </div>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Role <span class="text-danger">*</span></label>
                        <select name="role" id="role" class="kt-select" required>
                            <option value="">-- Pilih Role --</option>
                            @foreach($roles as $r)
                            <option value="{{ $r->name }}">{{ $r->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium text-foreground">Status <span class="text-danger">*</span></label>
                    <select name="status" id="status" class="kt-select" required>
                        <option value="aktif">Aktif</option>
                        <option value="block">Block</option>
                    </select>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium text-foreground">Password <span class="text-danger" id="pwdRequired">*</span></label>
                    <input type="password" name="password" id="password" class="kt-input">
                    <span class="text-xs text-muted-foreground" id="pwdHint" style="display:none;">Kosongkan jika tidak ingin mengubah password</span>
                </div>
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
var fields = ['nama','jabatan','no_hp','email','tempat_lahir','tgl_lahir','nomor_rekening','bank','mulai_bekerja','role','status','password'];

function showErrors(errors) {
    var container = document.getElementById('formErrors');
    var list = document.getElementById('formErrorList');
    list.innerHTML = '';
    Object.values(errors).forEach(function(msgs) {
        msgs.forEach(function(msg) {
            var li = document.createElement('li');
            li.className = 'text-sm font-medium';
            li.style.color = '#dc2626';
            li.textContent = msg;
            list.appendChild(li);
        });
    });
    container.classList.remove('hidden');
}

function hideErrors() {
    document.getElementById('formErrors').classList.add('hidden');
    document.getElementById('formErrorList').innerHTML = '';
}

function openCreateModal() {
    document.getElementById('modalTitle').textContent = 'Tambah Karyawan';
    document.getElementById('dataForm').dataset.action = "{{ route('karyawan.store') }}";
    document.getElementById('formMethod').value = 'POST';
    fields.forEach(function(f) { var el = document.getElementById(f); if(el) el.value = ''; });
    document.getElementById('status').value = 'aktif';
    document.getElementById('password').required = true;
    document.getElementById('pwdRequired').style.display = '';
    document.getElementById('pwdHint').style.display = 'none';
    hideErrors();
    KTModal.getInstance(document.querySelector('#formModal')).show();
}

function openEditModal(id) {
    fetch('/masterdata/karyawan/' + id + '/edit')
        .then(function(r) { return r.json(); })
        .then(function(data) {
            document.getElementById('modalTitle').textContent = 'Edit Karyawan';
            document.getElementById('dataForm').dataset.action = '/masterdata/karyawan/' + id;
            document.getElementById('formMethod').value = 'PUT';
            document.getElementById('nama').value = data.nama || '';
            document.getElementById('jabatan').value = data.jabatan || '';
            document.getElementById('no_hp').value = data.no_hp || '';
            document.getElementById('email').value = data.email || '';
            document.getElementById('tempat_lahir').value = data.tempat_lahir || '';
            document.getElementById('tgl_lahir').value = data.tgl_lahir ? data.tgl_lahir.substring(0,10) : '';
            document.getElementById('nomor_rekening').value = data.nomor_rekening || '';
            document.getElementById('bank').value = data.bank || '';
            document.getElementById('mulai_bekerja').value = data.mulai_bekerja ? data.mulai_bekerja.substring(0,10) : '';
            document.getElementById('role').value = data.role || '';
            document.getElementById('status').value = data.status || 'aktif';
            document.getElementById('password').value = '';
            document.getElementById('password').required = false;
            document.getElementById('pwdRequired').style.display = 'none';
            document.getElementById('pwdHint').style.display = '';
            hideErrors();
            KTModal.getInstance(document.querySelector('#formModal')).show();
        });
}

document.getElementById('dataForm').addEventListener('submit', function(e) {
    e.preventDefault();
    hideErrors();

    var form = this;
    var action = form.dataset.action;
    var method = document.getElementById('formMethod').value;
    var formData = new FormData(form);

    fetch(action, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'X-HTTP-Method-Override': method,
        },
        body: formData,
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            KTModal.getInstance(document.querySelector('#formModal')).hide();
            window.location.reload();
        } else if (data.errors) {
            showErrors(data.errors);
        }
    })
    .catch(function() {
        window.location.reload();
    });
});
</script>
@endpush