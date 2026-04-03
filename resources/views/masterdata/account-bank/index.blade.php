@extends('layouts.app')

@section('title', 'Account Bank')
@section('page-title', 'Account Bank')
@section('page-description', 'Kelola data account bank')

@section('content')
<div class="grid w-full space-y-5">
    <div class="kt-card">
        <div class="kt-card-header min-h-16">
            <input type="text" placeholder="Cari..." class="kt-input" style="width:200px" data-kt-datatable-search="#kt_datatable" />
            @can('account-bank.create')
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
                            <th scope="col" data-kt-datatable-column="kode">
                                <span class="kt-table-col"><span class="kt-table-col-label">Kode</span><span class="kt-table-col-sort"></span></span>
                            </th>
                            <th scope="col" data-kt-datatable-column="nama_bank">
                                <span class="kt-table-col"><span class="kt-table-col-label">Nama Bank</span><span class="kt-table-col-sort"></span></span>
                            </th>
                            <th scope="col" data-kt-datatable-column="nama_pemilik">
                                <span class="kt-table-col"><span class="kt-table-col-label">Nama Pemilik</span><span class="kt-table-col-sort"></span></span>
                            </th>
                            <th scope="col" data-kt-datatable-column="nomor_rekening">
                                <span class="kt-table-col"><span class="kt-table-col-label">No. Rekening</span><span class="kt-table-col-sort"></span></span>
                            </th>
                            <th scope="col" data-kt-datatable-column="saldo">
                                <span class="kt-table-col"><span class="kt-table-col-label">Saldo</span><span class="kt-table-col-sort"></span></span>
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
                            <td>{{ $item->kode_account }}</td>
                            <td>{{ $item->nama_bank }}</td>
                            <td>{{ $item->nama_pemilik ?? '-' }}</td>
                            <td>{{ $item->nomor_rekening ?? '-' }}</td>
                            <td>Rp {{ number_format($item->saldo, 0, ',', '.') }}</td>
                            <td>
                                <span class="kt-badge kt-badge-sm {{ $item->status === 'aktif' ? 'kt-badge-success' : 'kt-badge-destructive' }}">
                                    {{ $item->status === 'aktif' ? 'Aktif' : 'Non Aktif' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <span class="inline-flex gap-2.5">
                                    @can('account-bank.edit')
                                    <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline" onclick="openEditModal('{{ $item->id }}')"><i class="ki-filled ki-pencil"></i></button>
                                    @endcan
                                    @can('account-bank.delete')
                                    <form method="POST" action="{{ route('account-bank.destroy', $item) }}" onsubmit="return confirm('Yakin hapus?')">
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
<div class="kt-modal" data-kt-modal="true" id="formModal">
    <div class="kt-modal-content max-w-[600px] top-5 lg:top-[10%]">
        <div class="kt-modal-header">
            <h3 class="kt-modal-title" id="modalTitle">Tambah Account Bank</h3>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost" data-kt-modal-dismiss="true"><i class="ki-filled ki-cross"></i></button>
        </div>
        <form id="dataForm" method="POST">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">
            <div class="kt-modal-body flex flex-col gap-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Kode Account <span class="text-danger">*</span></label>
                        <input type="text" name="kode_account" id="kode_account" class="kt-input" required>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Nama Bank <span class="text-danger">*</span></label>
                        <select name="nama_bank" id="nama_bank" class="kt-select" required>
                            <option value="">-- Pilih Bank --</option>
                            @foreach($banks as $b)
                            <option value="{{ $b }}">{{ $b }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Nama Pemilik</label>
                        <input type="text" name="nama_pemilik" id="nama_pemilik" class="kt-input">
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Nomor Rekening</label>
                        <input type="text" name="nomor_rekening" id="nomor_rekening" class="kt-input">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Saldo <span class="text-danger">*</span></label>
                        <input type="number" name="saldo" id="saldo" class="kt-input" step="0.01" min="0" required>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Status <span class="text-danger">*</span></label>
                        <select name="status" id="status" class="kt-select" required>
                            <option value="aktif">Aktif</option>
                            <option value="nonaktif">Non Aktif</option>
                        </select>
                    </div>
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
function openCreateModal() {
    document.getElementById('modalTitle').textContent = 'Tambah Account Bank';
    document.getElementById('dataForm').action = "{{ route('account-bank.store') }}";
    document.getElementById('formMethod').value = 'POST';
    ['kode_account','nama_bank','nama_pemilik','nomor_rekening'].forEach(f => document.getElementById(f).value = '');
    document.getElementById('saldo').value = '0';
    document.getElementById('status').value = 'aktif';
    KTModal.getInstance(document.querySelector('#formModal')).show();
}
function openEditModal(id) {
    fetch(`/masterdata/account-bank/${id}/edit`)
        .then(r => r.json())
        .then(data => {
            document.getElementById('modalTitle').textContent = 'Edit Account Bank';
            document.getElementById('dataForm').action = `/masterdata/account-bank/${id}`;
            document.getElementById('formMethod').value = 'PUT';
            document.getElementById('kode_account').value = data.kode_account;
            document.getElementById('nama_bank').value = data.nama_bank;
            document.getElementById('nama_pemilik').value = data.nama_pemilik || '';
            document.getElementById('nomor_rekening').value = data.nomor_rekening || '';
            document.getElementById('saldo').value = data.saldo;
            document.getElementById('status').value = data.status || 'aktif';
            KTModal.getInstance(document.querySelector('#formModal')).show();
        });
}
</script>
@endpush