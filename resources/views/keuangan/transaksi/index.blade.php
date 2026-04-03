@extends('layouts.app')

@section('title', 'Transaksi Keuangan')
@section('page-title', 'Transaksi Keuangan')
@section('page-description', 'Kelola transaksi keuangan')

@section('content')
<div class="grid w-full space-y-5">
    <div class="kt-card">
        <div class="kt-card-header min-h-16">
            <form method="GET" class="flex items-center gap-2">
                <input type="text" name="search" placeholder="Cari..." class="kt-input" style="width:200px" data-kt-datatable-search="#kt_datatable" value="{{ request('search') }}" />
                <select name="status" class="kt-select sm:w-40" onchange="this.form.submit()">
                    <option value="">-- Status --</option>
                    <option value="awaiting_approval" {{ request('status')=='awaiting_approval'?'selected':'' }}>Awaiting</option>
                    <option value="proses" {{ request('status')=='proses'?'selected':'' }}>Proses</option>
                    <option value="selesai" {{ request('status')=='selesai'?'selected':'' }}>Selesai</option>
                    <option value="cancel" {{ request('status')=='cancel'?'selected':'' }}>Cancel</option>
                    <option value="pending" {{ request('status')=='pending'?'selected':'' }}>Pending</option>
                </select>
                <select name="jenis_transaksi" class="kt-select sm:w-40" onchange="this.form.submit()">
                    <option value="">-- Jenis --</option>
                    <option value="uang_masuk" {{ request('jenis_transaksi')=='uang_masuk'?'selected':'' }}>Uang Masuk</option>
                    <option value="uang_keluar" {{ request('jenis_transaksi')=='uang_keluar'?'selected':'' }}>Uang Keluar</option>
                    <!-- <option value="cash_card" {{ request('jenis_transaksi')=='cash_card'?'selected':'' }}>Cash Card</option> -->
                </select>
            </form>
            @can('transaksi-keuangan.create')
            <button type="button" class="kt-btn kt-btn kt-btn-primary" onclick="openCreateModal()">
                <i class="ki-filled ki-plus-squared"></i> Tambah Transaksi
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
                            <th data-kt-datatable-column="tgl"><span class="kt-table-col"><span class="kt-table-col-label">Tanggal</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="aktivitas"><span class="kt-table-col"><span class="kt-table-col-label">Aktivitas/Kegiatan</span><span class="kt-table-col-sort"></span></span></th>
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
                            <td>
                                @if($item->jenis_transaksi === 'uang_masuk')
                                    <span class="kt-badge kt-badge-sm kt-badge-success kt-badge-outline">Uang Masuk</span>
                                @elseif($item->jenis_transaksi === 'uang_keluar')
                                    <span class="kt-badge kt-badge-sm kt-badge-destructive kt-badge-outline">Uang Keluar</span>
                                <!-- @else
                                    <span class="kt-badge kt-badge-sm kt-badge-warning kt-badge-outline">Cash Card</span> -->
                                @endif
                            </td>
                            <td>{{ $item->tgl_kwitansi?->format('d/m/Y') ?? '-' }}</td>
                            <td>{{ Str::limit($item->aktivitas, 40) }}</td>
                            <td class="text-mono">Rp {{ number_format($item->nominal, 0, ',', '.') }}</td>
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
                                    <form method="POST" action="{{ route('transaksi.approve', $item) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline text-success" title="Approve"><i class="ki-filled ki-check"></i></button>
                                    </form>
                                    <form method="POST" action="{{ route('transaksi.reject', $item) }}" class="inline" onsubmit="return confirm('Yakin reject?')">
                                        @csrf
                                        <button type="submit" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline text-danger" title="Reject"><i class="ki-filled ki-cross"></i></button>
                                    </form>
                                    @endif
                                    @can('transaksi-keuangan.edit')
                                    @if(in_array($item->status, ['awaiting_approval','pending']))
                                    <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline" onclick="openEditModal('{{ $item->id }}')"><i class="ki-filled ki-pencil"></i></button>
                                    @endif
                                    @endcan
                                    @can('transaksi-keuangan.delete')
                                    @if($item->status === 'awaiting_approval')
                                    <form method="POST" action="{{ route('transaksi.destroy', $item) }}" onsubmit="return confirm('Yakin hapus?')">
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
    <div class="kt-modal-content max-w-[500px] top-5 lg:top-[10%]">
        <div class="kt-modal-header">
            <h3 class="kt-modal-title" id="modalTitle">Tambah Transaksi</h3>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost" data-kt-modal-dismiss="true"><i class="ki-filled ki-cross"></i></button>
        </div>
        <form id="dataForm" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">
            <div class="kt-modal-body flex flex-col gap-4" style="max-height:75vh;overflow-y:auto;">
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Jenis Transaksi <span class="text-danger">*</span></label>
                        <select name="jenis_transaksi" id="jenis_transaksi" class="kt-select" required>
                            <option value="uang_masuk">Uang Masuk</option>
                            <option value="uang_keluar">Uang Keluar</option>
                            <!-- <option value="cash_card">Cash Card</option> -->
                        </select>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Tanggal Kwitansi <span class="text-danger">*</span></label>
                        <div class="kt-input">
                            <i class="ki-outline ki-calendar"></i>
                            <input class="grow" name="tgl_kwitansi" id="tgl_kwitansi" data-kt-date-picker="true" data-kt-date-picker-input-mode="true" placeholder="Pilih tanggal" readonly type="text" required/>
                        </div>
                    </div>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium text-foreground">Aktivitas/Kegiatan <span class="text-danger">*</span></label>
                    <textarea name="aktivitas" id="aktivitas" class="kt-input" rows="2" style="height: 60px;" required></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Kategori Transaksi <span class="text-danger">*</span></label>
                        <select name="kategori_transaksi_id" id="kategori_transaksi_id" class="kt-select" required onchange="loadItemsByKategori()">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($kategoriTransaksis as $kat)
                            <option value="{{ $kat->id }}">{{ $kat->kode_kategori }} - {{ $kat->deskripsi }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Item Transaksi <span class="text-danger">*</span></label>
                        <select name="item_transaksi_id" id="item_transaksi_id" class="kt-select" required>
                            <option value="">-- Pilih Item --</option>
                            @foreach($itemTransaksis as $it)
                            <option value="{{ $it->id }}" data-kategori="{{ $it->kategori_transaksi_id }}">{{ $it->kode_item }} - {{ $it->deskripsi }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium text-foreground">Nominal <span class="text-danger">*</span></label>
                    <div class="kt-input-group">
                        <span class="kt-input-addon">Rp.</span>
                        <input class="kt-input" type="number" name="nominal" id="nominal" step="0.01" min="0" placeholder="0" required/>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Tambak <span class="text-danger">*</span></label>
                        <select name="tambak_id" id="tambak_id" class="kt-select" required onchange="loadBlokByTambak()">
                            <option value="">-- Pilih --</option>
                            @foreach($tambaks as $t)
                            <option value="{{ $t->id }}">{{ $t->nama_tambak }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Blok</label>
                        <select name="blok_id" id="blok_id" class="kt-select" onchange="loadSiklusByBlok()">
                            <option value="">-- Pilih --</option>
                        </select>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Siklus</label>
                        <select name="siklus_id" id="siklus_id" class="kt-select">
                            <option value="">-- Pilih --</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Sumber Dana <span class="text-danger">*</span></label>
                        <select name="sumber_dana_id" id="sumber_dana_id" class="kt-select" required>
                            <option value="">-- Pilih --</option>
                            @foreach($sumberDanas as $sd)
                            <option value="{{ $sd->id }}">{{ $sd->deskripsi }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Pembayaran <span class="text-danger">*</span></label>
                        <select name="pembayaran_combo" id="pembayaran_combo" class="kt-select" required onchange="onPembayaranChange()">
                            @foreach($accountBanks as $bank)
                            <option value="bank|{{ $bank->id }}" data-saldo="{{ $bank->saldo }}">{{ $bank->nama_bank }} - {{ $bank->nama_pemilik }}</option>
                            @endforeach
                        </select>
                        <input type="hidden" name="jenis_pembayaran" id="jenis_pembayaran" value="cash">
                        <input type="hidden" name="account_bank_id" id="account_bank_id" value="">
                        <span class="text-xs text-muted-foreground mt-1" id="saldoInfo" style="display:none;">Saldo: <span class="text-mono font-medium text-primary" id="saldoValue"></span></span>
                    </div>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium text-foreground">Eviden</label>
                    <input type="file" name="eviden" id="eviden" class="kt-input" accept=".jpg,.jpeg,.png,.gif,.bmp,.webp,.pdf,.xlsx,.xls">
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium text-foreground">Catatan</label>
                    <textarea name="catatan" id="catatan" class="kt-input" rows="2" style="height: 60px;"></textarea>
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
var formFields = ['jenis_transaksi','tgl_kwitansi','aktivitas','kategori_transaksi_id','item_transaksi_id','nominal','tambak_id','sumber_dana_id','catatan'];

function onPembayaranChange() {
    var sel = document.getElementById('pembayaran_combo');
    var val = sel.value;
    var saldoInfo = document.getElementById('saldoInfo');
    var saldoValue = document.getElementById('saldoValue');
    var parts = val.split('|');
    document.getElementById('jenis_pembayaran').value = 'bank';
    document.getElementById('account_bank_id').value = parts[1] || '';
    var saldo = sel.options[sel.selectedIndex]?.getAttribute('data-saldo');
    if (saldo !== null && saldo !== '') {
        saldoValue.textContent = 'Rp ' + Number(saldo || 0).toLocaleString('id-ID');
        saldoInfo.style.display = '';
    } else {
        saldoInfo.style.display = 'none';
    }
}

function loadItemsByKategori() {
    var katId = document.getElementById('kategori_transaksi_id').value;
    var sel = document.getElementById('item_transaksi_id');
    if (!katId) { sel.innerHTML = '<option value="">-- Pilih Item --</option>'; return; }
    fetch('/keuangan/transaksi/items-by-kategori/' + katId)
        .then(r => r.json())
        .then(items => {
            sel.innerHTML = '<option value="">-- Pilih Item --</option>' + items.map(i => '<option value="'+i.id+'">'+(i.kode_item)+(i.deskripsi ? ' - '+i.deskripsi : '')+'</option>').join('');
        });
}

function loadBlokByTambak() {
    var tambakId = document.getElementById('tambak_id').value;
    var sel = document.getElementById('blok_id');
    document.getElementById('siklus_id').innerHTML = '<option value="">-- Pilih --</option>';
    if (!tambakId) { sel.innerHTML = '<option value="">-- Pilih --</option>'; return; }
    fetch('/budidaya/blok/by-tambak/' + tambakId)
        .then(r => r.json())
        .then(bloks => {
            sel.innerHTML = '<option value="">-- Pilih --</option>' + bloks.map(b => '<option value="'+b.id+'">'+b.nama_blok+'</option>').join('');
        });
}

function loadSiklusByBlok() {
    var blokId = document.getElementById('blok_id').value;
    var sel = document.getElementById('siklus_id');
    if (!blokId) { sel.innerHTML = '<option value="">-- Pilih --</option>'; return; }
    fetch('/budidaya/siklus/by-blok/' + blokId)
        .then(r => r.json())
        .then(sikluses => {
            sel.innerHTML = '<option value="">-- Pilih --</option>' + sikluses.map(s => '<option value="'+s.id+'">'+s.nama_siklus+'</option>').join('');
        });
}

function openCreateModal() {
    document.getElementById('modalTitle').textContent = 'Tambah Transaksi';
    document.getElementById('dataForm').action = "{{ route('transaksi.store') }}";
    document.getElementById('formMethod').value = 'POST';
    formFields.forEach(f => { var el = document.getElementById(f); if(el) el.value = ''; });
    document.getElementById('jenis_transaksi').value = 'uang_masuk';
    document.getElementById('pembayaran_combo').selectedIndex = 0;
    onPembayaranChange();
    document.getElementById('blok_id').innerHTML = '<option value="">-- Pilih --</option>';
    document.getElementById('siklus_id').innerHTML = '<option value="">-- Pilih --</option>';
    KTModal.getInstance(document.querySelector('#formModal')).show();
}

function openEditModal(id) {
    fetch('/keuangan/transaksi/' + id + '/edit')
        .then(r => r.json())
        .then(data => {
            document.getElementById('modalTitle').textContent = 'Edit Transaksi';
            document.getElementById('dataForm').action = '/keuangan/transaksi/' + id;
            document.getElementById('formMethod').value = 'PUT';
            formFields.forEach(f => {
                if (f === 'tgl_kwitansi') return;
                var el = document.getElementById(f);
                if (el) el.value = data[f] ?? '';
            });
            // Set pembayaran combo
            if (data.jenis_pembayaran === 'bank' && data.account_bank_id) {
                document.getElementById('pembayaran_combo').value = 'bank|' + data.account_bank_id;
            } else {
                document.getElementById('pembayaran_combo').selectedIndex = 0;
            }
            onPembayaranChange();
            // Load dependent dropdowns
            if (data.tambak_id) {
                fetch('/budidaya/blok/by-tambak/' + data.tambak_id)
                    .then(r => r.json())
                    .then(bloks => {
                        var sel = document.getElementById('blok_id');
                        sel.innerHTML = '<option value="">-- Pilih --</option>' + bloks.map(b => '<option value="'+b.id+'">'+b.nama_blok+'</option>').join('');
                        sel.value = data.blok_id || '';
                        if (data.blok_id) {
                            fetch('/budidaya/siklus/by-blok/' + data.blok_id)
                                .then(r => r.json())
                                .then(sikluses => {
                                    var ssel = document.getElementById('siklus_id');
                                    ssel.innerHTML = '<option value="">-- Pilih --</option>' + sikluses.map(s => '<option value="'+s.id+'">'+s.nama_siklus+'</option>').join('');
                                    ssel.value = data.siklus_id || '';
                                });
                        }
                    });
            }
            KTModal.getInstance(document.querySelector('#formModal')).show();
            setTimeout(function() {
                var el = document.getElementById('tgl_kwitansi');
                if (el && el._flatpickr) el._flatpickr.setDate(data.tgl_kwitansi, true);
                else if (el) el.value = data.tgl_kwitansi || '';
            }, 100);
        });
}
</script>
@endpush
