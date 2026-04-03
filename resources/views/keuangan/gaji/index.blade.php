@extends('layouts.app')

@section('title', 'Gaji Karyawan')
@section('page-title', 'Gaji Karyawan')
@section('page-description', 'Kelola gaji karyawan')

@section('content')
<div class="grid w-full space-y-5">
    <div class="kt-card">
        <div class="kt-card-header min-h-16">
            <form method="GET" class="flex items-center gap-2">
                <input type="text" name="search" placeholder="Cari..." class="kt-input" style="width:200px" data-kt-datatable-search="#kt_datatable" value="{{ request('search') }}" />
            </form>
            @can('gaji-karyawan.create')
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
                            <th data-kt-datatable-column="karyawan"><span class="kt-table-col"><span class="kt-table-col-label">Karyawan</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="jabatan"><span class="kt-table-col"><span class="kt-table-col-label">Jabatan</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="gaji_pokok"><span class="kt-table-col"><span class="kt-table-col-label">Gaji Pokok</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="thp"><span class="kt-table-col"><span class="kt-table-col-label">THP</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="status"><span class="kt-table-col"><span class="kt-table-col-label">Status</span><span class="kt-table-col-sort"></span></span></th>
                            <th class="w-28" data-kt-datatable-column="aksi"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $i => $item)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td class="text-mono">{{ $item->nomor_transaksi }}</td>
                            <td>{{ $item->user->name ?? '-' }}</td>
                            <td>{{ $item->user->jabatan ?? '-' }}</td>
                            <td class="text-mono">Rp {{ number_format($item->gaji_pokok, 0, ',', '.') }}</td>
                            <td class="text-mono">Rp {{ number_format($item->thp, 0, ',', '.') }}</td>
                            <td>
                                @if($item->status === 'selesai')
                                    <span class="kt-badge kt-badge-sm kt-badge-success">Selesai</span>
                                @elseif($item->status === 'cancel')
                                    <span class="kt-badge kt-badge-sm kt-badge-destructive">Cancel</span>
                                @elseif($item->status === 'proses')
                                    <span class="kt-badge kt-badge-sm kt-badge-primary">Proses</span>
                                @elseif($item->status === 'pending')
                                    <span class="kt-badge kt-badge-sm kt-badge-warning">Pending</span>
                                @else
                                    <span class="kt-badge kt-badge-sm kt-badge-outline">Awaiting</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <span class="inline-flex gap-2.5">
                                    @if($item->status === 'awaiting_approval' && auth()->user()->hasRole('Owner'))
                                    <form method="POST" action="{{ route('gaji.approve', $item) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline text-success" title="Approve"><i class="ki-filled ki-check"></i></button>
                                    </form>
                                    <form method="POST" action="{{ route('gaji.reject', $item) }}" class="inline" onsubmit="return confirm('Yakin reject?')">
                                        @csrf
                                        <button type="submit" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline text-danger" title="Reject"><i class="ki-filled ki-cross"></i></button>
                                    </form>
                                    @endif
                                    @can('gaji-karyawan.edit')
                                    @if(in_array($item->status, ['awaiting_approval','pending']))
                                    <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline" onclick="openEditModal('{{ $item->id }}')"><i class="ki-filled ki-pencil"></i></button>
                                    @endif
                                    @endcan
                                    @can('gaji-karyawan.delete')
                                    @if($item->status === 'awaiting_approval')
                                    <form method="POST" action="{{ route('gaji.destroy', $item) }}" onsubmit="return confirm('Yakin hapus?')">
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

<!-- Modal Create/Edit -->
<div class="kt-modal" data-kt-modal="true" id="formModal">
    <div class="kt-modal-content max-w-[650px] top-5 lg:top-[5%]">
        <div class="kt-modal-header">
            <h3 class="kt-modal-title" id="modalTitle">Tambah Gaji Karyawan</h3>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost" data-kt-modal-dismiss="true"><i class="ki-filled ki-cross"></i></button>
        </div>
        <form id="dataForm" method="POST">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">
            <div class="kt-modal-body flex flex-col gap-4" style="max-height:75vh;overflow-y:auto;">
                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium text-foreground">Karyawan <span class="text-danger">*</span></label>
                    <select name="user_id" id="user_id" class="kt-select" required>
                        <option value="">-- Pilih Karyawan --</option>
                        @foreach($karyawans as $karyawan)
                        <option value="{{ $karyawan->id }}">{{ $karyawan->name }} - {{ $karyawan->jabatan }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Gaji Pokok <span class="text-danger">*</span></label>
                        <div class="kt-input-group">
                            <span class="kt-input-addon">Rp.</span>
                            <input class="kt-input" type="number" name="gaji_pokok" id="gaji_pokok" step="0.01" min="0" placeholder="0" required oninput="calcTHP()"/>
                        </div>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Upah Lembur</label>
                        <div class="kt-input-group">
                            <span class="kt-input-addon">Rp.</span>
                            <input class="kt-input" type="number" name="upah_lembur" id="upah_lembur" step="0.01" min="0" placeholder="0" oninput="calcTHP()"/>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Bonus</label>
                        <div class="kt-input-group">
                            <span class="kt-input-addon">Rp.</span>
                            <input class="kt-input" type="number" name="bonus" id="bonus" step="0.01" min="0" placeholder="0" oninput="calcTHP()"/>
                        </div>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Pajak</label>
                        <div class="kt-input-group">
                            <span class="kt-input-addon">Rp.</span>
                            <input class="kt-input" type="number" name="pajak" id="pajak" step="0.01" min="0" placeholder="0" oninput="calcTHP()"/>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">BPJS</label>
                        <div class="kt-input-group">
                            <span class="kt-input-addon">Rp.</span>
                            <input class="kt-input" type="number" name="bpjs" id="bpjs" step="0.01" min="0" placeholder="0" oninput="calcTHP()"/>
                        </div>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Potongan</label>
                        <div class="kt-input-group">
                            <span class="kt-input-addon">Rp.</span>
                            <input class="kt-input" type="number" name="potongan" id="potongan" step="0.01" min="0" placeholder="0" oninput="calcTHP()"/>
                        </div>
                    </div>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium text-foreground">THP (Take Home Pay)</label>
                    <div class="kt-input-group">
                        <span class="kt-input-addon">Rp.</span>
                        <input class="kt-input" type="number" name="thp" id="thp" step="0.01" placeholder="0" readonly/>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Jenis Pembayaran <span class="text-danger">*</span></label>
                        <select name="jenis_pembayaran" id="jenis_pembayaran" class="kt-select" required onchange="toggleAccountBank()">
                            <option value="cash">Cash</option>
                            <option value="bank">Bank</option>
                        </select>
                    </div>
                    <div class="flex flex-col gap-1" id="accountBankField" style="display:none;">
                        <label class="text-sm font-medium text-foreground">Account Bank</label>
                        <select name="account_bank_id" id="account_bank_id" class="kt-select">
                            <option value="">-- Pilih Bank --</option>
                            @foreach($accountBanks as $bank)
                            <option value="{{ $bank->id }}">{{ $bank->nama_bank }} - {{ $bank->nama_pemilik }}</option>
                            @endforeach
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
var formFields = ['user_id','gaji_pokok','upah_lembur','bonus','pajak','bpjs','potongan','thp','jenis_pembayaran','account_bank_id'];

function calcTHP() {
    var gaji_pokok = parseFloat(document.getElementById('gaji_pokok').value) || 0;
    var upah_lembur = parseFloat(document.getElementById('upah_lembur').value) || 0;
    var bonus = parseFloat(document.getElementById('bonus').value) || 0;
    var pajak = parseFloat(document.getElementById('pajak').value) || 0;
    var bpjs = parseFloat(document.getElementById('bpjs').value) || 0;
    var potongan = parseFloat(document.getElementById('potongan').value) || 0;
    document.getElementById('thp').value = (gaji_pokok + upah_lembur + bonus - pajak - bpjs - potongan).toFixed(2);
}

function toggleAccountBank() {
    document.getElementById('accountBankField').style.display = document.getElementById('jenis_pembayaran').value === 'bank' ? '' : 'none';
}

function openCreateModal() {
    document.getElementById('modalTitle').textContent = 'Tambah Gaji Karyawan';
    document.getElementById('dataForm').action = "{{ route('gaji.store') }}";
    document.getElementById('formMethod').value = 'POST';
    formFields.forEach(f => { var el = document.getElementById(f); if(el) el.value = ''; });
    document.getElementById('jenis_pembayaran').value = 'cash';
    document.getElementById('thp').value = '0';
    toggleAccountBank();
    KTModal.getInstance(document.querySelector('#formModal')).show();
}

function openEditModal(id) {
    fetch('/keuangan/gaji/' + id + '/edit')
        .then(r => r.json())
        .then(data => {
            document.getElementById('modalTitle').textContent = 'Edit Gaji Karyawan';
            document.getElementById('dataForm').action = '/keuangan/gaji/' + id;
            document.getElementById('formMethod').value = 'PUT';
            formFields.forEach(f => {
                var el = document.getElementById(f);
                if (el) el.value = data[f] ?? '';
            });
            calcTHP();
            toggleAccountBank();
            KTModal.getInstance(document.querySelector('#formModal')).show();
        });
}
</script>
@endpush
