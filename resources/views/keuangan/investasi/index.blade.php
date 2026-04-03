@extends('layouts.app')

@section('title', 'Investasi')
@section('page-title', 'Investasi')
@section('page-description', 'Kelola data investasi')

@section('content')
<div class="grid w-full space-y-5">
    <div class="kt-card">
        <div class="kt-card-header min-h-16">
            <input type="text" placeholder="Cari..." class="kt-input" style="width:200px" data-kt-datatable-search="#kt_datatable" />
            @can('investasi.create')
            <button type="button" class="kt-btn kt-btn-outline" onclick="openCreateModal()">
                <i class="ki-filled ki-plus-squared"></i> Tambah
            </button>
            @endcan
        </div>
        <div id="kt_datatable" class="kt-card-table" data-kt-datatable="true" data-kt-datatable-page-size="10" data-kt-datatable-state-save="true">
            <div class="kt-table-wrapper kt-scrollable">
                <table class="kt-table" data-kt-datatable-table="true">
                    <thead>
                        <tr>
                            <th class="w-12" data-kt-datatable-column="no"><span class="kt-table-col"><span class="kt-table-col-label">No</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="nomor"><span class="kt-table-col"><span class="kt-table-col-label">No. Transaksi</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="deskripsi"><span class="kt-table-col"><span class="kt-table-col-label">Deskripsi</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="kategori"><span class="kt-table-col"><span class="kt-table-col-label">Kategori</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="nominal"><span class="kt-table-col"><span class="kt-table-col-label">Nominal</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="status"><span class="kt-table-col"><span class="kt-table-col-label">Status</span><span class="kt-table-col-sort"></span></span></th>
                            <th class="w-28" data-kt-datatable-column="aksi"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $i => $item)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td class="text-mono">{{ $item->nomor_transaksi }}</td>
                            <td>{{ Str::limit($item->deskripsi, 40) }}</td>
                            <td>{{ $item->kategoriInvestasi?->nama ?? '-' }}</td>
                            <td class="text-mono">Rp {{ number_format($item->nominal, 0, ',', '.') }}</td>
                            <td>
                                @if($item->status === 'selesai')<span class="kt-badge kt-badge-sm kt-badge-success">Selesai</span>
                                @elseif($item->status === 'cancel')<span class="kt-badge kt-badge-sm kt-badge-destructive">Cancel</span>
                                @elseif($item->status === 'proses')<span class="kt-badge kt-badge-sm kt-badge-primary">Proses</span>
                                @elseif($item->status === 'pending')<span class="kt-badge kt-badge-sm kt-badge-warning">Pending</span>
                                @else<span class="kt-badge kt-badge-sm kt-badge-outline">Awaiting</span>@endif
                            </td>
                            <td class="text-end">
                                <span class="inline-flex gap-2.5">
                                    @if($item->status === 'awaiting_approval' && auth()->user()->hasRole('Owner'))
                                    <form method="POST" action="{{ route('investasi.approve', $item) }}" class="inline">@csrf<button type="submit" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline text-success" title="Approve"><i class="ki-filled ki-check"></i></button></form>
                                    <form method="POST" action="{{ route('investasi.reject', $item) }}" class="inline" onsubmit="return confirm('Yakin reject?')">@csrf<button type="submit" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline text-danger" title="Reject"><i class="ki-filled ki-cross"></i></button></form>
                                    @endif
                                    @can('investasi.edit')@if(in_array($item->status, ['awaiting_approval','pending']))
                                    <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline" onclick="openEditModal('{{ $item->id }}')"><i class="ki-filled ki-pencil"></i></button>
                                    @endif @endcan
                                    @can('investasi.delete')@if($item->status === 'awaiting_approval')
                                    <form method="POST" action="{{ route('investasi.destroy', $item) }}" onsubmit="return confirm('Yakin hapus?')">@csrf @method('DELETE')<button type="submit" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline text-danger"><i class="ki-filled ki-trash"></i></button></form>
                                    @endif @endcan
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
    <div class="kt-modal-content max-w-[550px] top-5 lg:top-[10%]">
        <div class="kt-modal-header">
            <h3 class="kt-modal-title" id="modalTitle">Tambah Investasi</h3>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost" data-kt-modal-dismiss="true"><i class="ki-filled ki-cross"></i></button>
        </div>
        <form id="dataForm" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">
            <div class="kt-modal-body flex flex-col gap-4" style="max-height:75vh;overflow-y:auto;">
                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium text-foreground">Deskripsi <span class="text-danger">*</span></label>
                    <textarea name="deskripsi" id="deskripsi" class="kt-input" rows="2" required></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Nominal <span class="text-danger">*</span></label>
                        <div class="kt-input-group"><span class="kt-input-addon">Rp.</span><input class="kt-input" type="number" name="nominal" id="nominal" step="0.01" min="0" placeholder="0" required/></div>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Kategori <span class="text-danger">*</span></label>
                        <select name="kategori_investasi_id" id="kategori_investasi_id" class="kt-select" required>
                            <option value="">-- Pilih --</option>
                            @foreach($kategoriInvestasis as $kat)<option value="{{ $kat->id }}">{{ $kat->nama }}</option>@endforeach
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Jenis Pembayaran <span class="text-danger">*</span></label>
                        <select name="jenis_pembayaran" id="jenis_pembayaran" class="kt-select" required onchange="toggleBank()">
                            <option value="cash">Cash</option><option value="bank">Bank</option>
                        </select>
                    </div>
                    <div class="flex flex-col gap-1" id="bankField" style="display:none;">
                        <label class="text-sm font-medium text-foreground">Account Bank</label>
                        <select name="account_bank_id" id="account_bank_id" class="kt-select">
                            <option value="">-- Pilih --</option>
                            @foreach($accountBanks as $bank)<option value="{{ $bank->id }}">{{ $bank->nama_bank }} - {{ $bank->nama_pemilik }}</option>@endforeach
                        </select>
                    </div>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium text-foreground">Eviden</label>
                    <input type="file" name="eviden" id="eviden" class="kt-input" accept=".jpg,.jpeg,.png,.gif,.bmp,.webp,.pdf,.xlsx,.xls">
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium text-foreground">Catatan</label>
                    <textarea name="catatan" id="catatan" class="kt-input" rows="2"></textarea>
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
function toggleBank() { document.getElementById('bankField').style.display = document.getElementById('jenis_pembayaran').value === 'bank' ? '' : 'none'; }
function openCreateModal() {
    document.getElementById('modalTitle').textContent = 'Tambah Investasi';
    document.getElementById('dataForm').action = "{{ route('investasi.store') }}";
    document.getElementById('formMethod').value = 'POST';
    ['deskripsi','nominal','kategori_investasi_id','jenis_pembayaran','account_bank_id','catatan'].forEach(f => { var el = document.getElementById(f); if(el) el.value = ''; });
    document.getElementById('jenis_pembayaran').value = 'cash'; toggleBank();
    KTModal.getInstance(document.querySelector('#formModal')).show();
}
function openEditModal(id) {
    fetch('/keuangan/investasi/' + id + '/edit').then(r => r.json()).then(data => {
        document.getElementById('modalTitle').textContent = 'Edit Investasi';
        document.getElementById('dataForm').action = '/keuangan/investasi/' + id;
        document.getElementById('formMethod').value = 'PUT';
        ['deskripsi','nominal','kategori_investasi_id','jenis_pembayaran','account_bank_id','catatan'].forEach(f => { var el = document.getElementById(f); if(el) el.value = data[f] ?? ''; });
        toggleBank();
        KTModal.getInstance(document.querySelector('#formModal')).show();
    });
}
</script>
@endpush
