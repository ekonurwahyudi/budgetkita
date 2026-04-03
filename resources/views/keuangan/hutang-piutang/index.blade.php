@extends('layouts.app')

@section('title', 'Hutang/Piutang')
@section('page-title', 'Hutang/Piutang')
@section('page-description', 'Kelola data hutang dan piutang')

@section('content')
<div class="grid w-full space-y-5">
    <div class="kt-card">
        <div class="kt-card-header min-h-16">
            <input type="text" placeholder="Cari..." class="kt-input" style="width:200px" data-kt-datatable-search="#kt_datatable" />
            @can('hutang-piutang.create')
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
                            <th data-kt-datatable-column="jenis"><span class="kt-table-col"><span class="kt-table-col-label">Jenis</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="aktivitas"><span class="kt-table-col"><span class="kt-table-col-label">Aktivitas</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="nominal"><span class="kt-table-col"><span class="kt-table-col-label">Nominal</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="jatuh_tempo"><span class="kt-table-col"><span class="kt-table-col-label">Jatuh Tempo</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="sisa"><span class="kt-table-col"><span class="kt-table-col-label">Sisa</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="status"><span class="kt-table-col"><span class="kt-table-col-label">Status</span><span class="kt-table-col-sort"></span></span></th>
                            <th class="w-28" data-kt-datatable-column="aksi"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $i => $item)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td class="text-mono">{{ $item->nomor_transaksi }}</td>
                            <td>
                                @if($item->jenis === 'hutang')<span class="kt-badge kt-badge-sm kt-badge-destructive kt-badge-outline">Hutang</span>
                                @else<span class="kt-badge kt-badge-sm kt-badge-success kt-badge-outline">Piutang</span>@endif
                            </td>
                            <td>{{ Str::limit($item->aktivitas, 30) }}</td>
                            <td class="text-mono">Rp {{ number_format($item->nominal, 0, ',', '.') }}</td>
                            <td>{{ $item->jatuh_tempo?->format('d/m/Y') ?? '-' }}</td>
                            <td class="text-mono">Rp {{ number_format($item->sisa_pembayaran, 0, ',', '.') }}</td>
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
                                    <form method="POST" action="{{ route('hutang-piutang.approve', $item) }}" class="inline">@csrf<button type="submit" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline text-success" title="Approve"><i class="ki-filled ki-check"></i></button></form>
                                    <form method="POST" action="{{ route('hutang-piutang.reject', $item) }}" class="inline" onsubmit="return confirm('Yakin reject?')">@csrf<button type="submit" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline text-danger" title="Reject"><i class="ki-filled ki-cross"></i></button></form>
                                    @endif
                                    @can('hutang-piutang.edit')@if(in_array($item->status, ['awaiting_approval','pending']))
                                    <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline" onclick="openEditModal('{{ $item->id }}')"><i class="ki-filled ki-pencil"></i></button>
                                    @endif @endcan
                                    @can('hutang-piutang.delete')@if($item->status === 'awaiting_approval')
                                    <form method="POST" action="{{ route('hutang-piutang.destroy', $item) }}" onsubmit="return confirm('Yakin hapus?')">@csrf @method('DELETE')<button type="submit" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline text-danger"><i class="ki-filled ki-trash"></i></button></form>
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
    <div class="kt-modal-content max-w-[600px] top-5 lg:top-[10%]">
        <div class="kt-modal-header">
            <h3 class="kt-modal-title" id="modalTitle">Tambah Hutang/Piutang</h3>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost" data-kt-modal-dismiss="true"><i class="ki-filled ki-cross"></i></button>
        </div>
        <form id="dataForm" method="POST">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">
            <div class="kt-modal-body flex flex-col gap-4" style="max-height:75vh;overflow-y:auto;">
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Jenis <span class="text-danger">*</span></label>
                        <select name="jenis" id="jenis" class="kt-select" required>
                            <option value="hutang">Hutang</option><option value="piutang">Piutang</option>
                        </select>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Kategori <span class="text-danger">*</span></label>
                        <select name="kategori_hutang_piutang_id" id="kategori_hutang_piutang_id" class="kt-select" required>
                            <option value="">-- Pilih --</option>
                            @foreach($kategoriHutangPiutangs as $kat)<option value="{{ $kat->id }}">{{ $kat->nama }}</option>@endforeach
                        </select>
                    </div>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium text-foreground">Aktivitas/Kegiatan <span class="text-danger">*</span></label>
                    <textarea name="aktivitas" id="aktivitas" class="kt-input" rows="2" required></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Nominal <span class="text-danger">*</span></label>
                        <div class="kt-input-group"><span class="kt-input-addon">Rp.</span><input class="kt-input" type="number" name="nominal" id="nominal" step="0.01" min="0" placeholder="0" required oninput="calcSisa()"/></div>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Jatuh Tempo <span class="text-danger">*</span></label>
                        <div class="kt-input"><i class="ki-outline ki-calendar"></i><input class="grow" name="jatuh_tempo" id="jatuh_tempo" data-kt-date-picker="true" data-kt-date-picker-input-mode="true" placeholder="Pilih tanggal" readonly type="text" required/></div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Nominal Bayar</label>
                        <div class="kt-input-group"><span class="kt-input-addon">Rp.</span><input class="kt-input" type="number" name="nominal_bayar" id="nominal_bayar" step="0.01" min="0" placeholder="0" oninput="calcSisa()"/></div>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Sisa Pembayaran</label>
                        <div class="kt-input-group"><span class="kt-input-addon">Rp.</span><input class="kt-input" type="number" id="sisa_display" readonly style="background:var(--muted);"/></div>
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
function calcSisa() {
    var nom = parseFloat(document.getElementById('nominal').value) || 0;
    var bayar = parseFloat(document.getElementById('nominal_bayar').value) || 0;
    document.getElementById('sisa_display').value = (nom - bayar).toFixed(2);
}
function toggleBank() { document.getElementById('bankField').style.display = document.getElementById('jenis_pembayaran').value === 'bank' ? '' : 'none'; }
function openCreateModal() {
    document.getElementById('modalTitle').textContent = 'Tambah Hutang/Piutang';
    document.getElementById('dataForm').action = "{{ route('hutang-piutang.store') }}";
    document.getElementById('formMethod').value = 'POST';
    ['jenis','aktivitas','kategori_hutang_piutang_id','nominal','jatuh_tempo','nominal_bayar','jenis_pembayaran','account_bank_id','catatan'].forEach(f => { var el = document.getElementById(f); if(el) el.value = ''; });
    document.getElementById('jenis').value = 'hutang';
    document.getElementById('jenis_pembayaran').value = 'cash';
    document.getElementById('sisa_display').value = '';
    toggleBank();
    KTModal.getInstance(document.querySelector('#formModal')).show();
}
function openEditModal(id) {
    fetch('/keuangan/hutang-piutang/' + id + '/edit').then(r => r.json()).then(data => {
        document.getElementById('modalTitle').textContent = 'Edit Hutang/Piutang';
        document.getElementById('dataForm').action = '/keuangan/hutang-piutang/' + id;
        document.getElementById('formMethod').value = 'PUT';
        ['jenis','aktivitas','kategori_hutang_piutang_id','nominal','nominal_bayar','jenis_pembayaran','account_bank_id','catatan'].forEach(f => { var el = document.getElementById(f); if(el) el.value = data[f] ?? ''; });
        calcSisa(); toggleBank();
        KTModal.getInstance(document.querySelector('#formModal')).show();
        setTimeout(function() {
            var el = document.getElementById('jatuh_tempo');
            if (el && el._flatpickr) el._flatpickr.setDate(data.jatuh_tempo, true);
            else if (el) el.value = data.jatuh_tempo || '';
        }, 100);
    });
}
</script>
@endpush
