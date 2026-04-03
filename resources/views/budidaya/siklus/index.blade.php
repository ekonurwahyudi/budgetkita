@extends('layouts.app')

@section('title', 'Daftar Siklus')
@section('page-title', 'Daftar Siklus')
@section('page-description', 'Kelola data siklus budidaya')

@section('content')
<div class="grid w-full space-y-5">
    <div class="kt-card">
        <div class="kt-card-header min-h-16">
            <form method="GET" class="flex items-center gap-2">
                <input type="text" name="search" placeholder="Cari..." class="kt-input" style="width:200px" data-kt-datatable-search="#kt_datatable" value="{{ request('search') }}" />
                <select name="tambak_id" class="kt-select sm:w-48" onchange="this.form.submit()">
                    <option value="">-- Semua Tambak --</option>
                    @foreach($tambaks as $tambak)
                    <option value="{{ $tambak->id }}" {{ request('tambak_id') == $tambak->id ? 'selected' : '' }}>{{ $tambak->nama_tambak }}</option>
                    @endforeach
                </select>
                <select name="blok_id" class="kt-select sm:w-48" onchange="this.form.submit()">
                    <option value="">-- Semua Blok --</option>
                    @foreach($bloks as $blok)
                    <option value="{{ $blok->id }}" {{ request('blok_id') == $blok->id ? 'selected' : '' }}>{{ $blok->nama_blok }}</option>
                    @endforeach
                </select>
            </form>
            @can('siklus.create')
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
                            <th scope="col" class="w-16" data-kt-datatable-column="no">
                                <span class="kt-table-col">
                                    <span class="kt-table-col-label">No</span>
                                    <span class="kt-table-col-sort"></span>
                                </span>
                            </th>
                            <th scope="col" data-kt-datatable-column="blok">
                                <span class="kt-table-col">
                                    <span class="kt-table-col-label">Blok</span>
                                    <span class="kt-table-col-sort"></span>
                                </span>
                            </th>
                            <th scope="col" data-kt-datatable-column="nama_siklus">
                                <span class="kt-table-col">
                                    <span class="kt-table-col-label">Nama Siklus</span>
                                    <span class="kt-table-col-sort"></span>
                                </span>
                            </th>
                            <th scope="col" data-kt-datatable-column="tgl_siklus">
                                <span class="kt-table-col">
                                    <span class="kt-table-col-label">Tgl Siklus</span>
                                    <span class="kt-table-col-sort"></span>
                                </span>
                            </th>
                            <th scope="col" data-kt-datatable-column="tgl_tebar">
                                <span class="kt-table-col">
                                    <span class="kt-table-col-label">Tgl Tebar</span>
                                    <span class="kt-table-col-sort"></span>
                                </span>
                            </th>
                            <th scope="col" data-kt-datatable-column="total_tebar">
                                <span class="kt-table-col">
                                    <span class="kt-table-col-label">Total Tebar</span>
                                    <span class="kt-table-col-sort"></span>
                                </span>
                            </th>
                            <th scope="col" data-kt-datatable-column="spesies">
                                <span class="kt-table-col">
                                    <span class="kt-table-col-label">Spesies</span>
                                    <span class="kt-table-col-sort"></span>
                                </span>
                            </th>
                            <th scope="col" data-kt-datatable-column="status">
                                <span class="kt-table-col">
                                    <span class="kt-table-col-label">Status</span>
                                    <span class="kt-table-col-sort"></span>
                                </span>
                            </th>
                            <th scope="col" class="w-24" data-kt-datatable-column="aksi"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $i => $item)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>
                                {{ $item->blok->nama_blok ?? '-' }}
                                @if($item->blok?->tambak)
                                <small class="block text-muted-foreground">{{ $item->blok->tambak->nama_tambak }}</small>
                                @endif
                            </td>
                            <td>{{ $item->nama_siklus }}</td>
                            <td>{{ $item->tgl_siklus?->format('d/m/Y') ?? '-' }}</td>
                            <td>{{ $item->tgl_tebar?->format('d/m/Y') ?? '-' }}</td>
                            <td>{{ number_format($item->total_tebar) }} ekor</td>
                            <td>{{ $item->spesies_udang }}</td>
                            <td>
                                @if($item->status === 'aktif')
                                    <span class="kt-badge kt-badge-success">Aktif</span>
                                @elseif($item->status === 'selesai')
                                    <span class="kt-badge kt-badge-primary">Selesai</span>
                                @else
                                    <span class="kt-badge kt-badge-destructive">Gagal</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <span class="inline-flex gap-2.5">
                                    <a href="{{ route('siklus.show', $item) }}" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline" title="Lihat Detail">
                                        <i class="ki-filled ki-eye"></i>
                                    </a>
                                    @can('siklus.edit')
                                    <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline" onclick="openEditModal('{{ $item->id }}')">
                                        <i class="ki-filled ki-pencil"></i>
                                    </button>
                                    @endcan
                                    @can('siklus.delete')
                                    <form method="POST" action="{{ route('siklus.destroy', $item) }}" onsubmit="return confirm('Yakin hapus data ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline text-danger">
                                            <i class="ki-filled ki-trash"></i>
                                        </button>
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
                <div class="kt-datatable-length">
                    Show <select class="kt-select kt-select-sm w-16" name="perpage" data-kt-datatable-size="true"></select> per page
                </div>
                <div class="kt-datatable-info">
                    <span data-kt-datatable-info="true"></span>
                    <div class="kt-datatable-pagination" data-kt-datatable-pagination="true"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Create/Edit -->
<div class="kt-modal" data-kt-modal="true" id="formModal">
    <div class="kt-modal-content max-w-[600px] top-5 lg:top-[10%]">
        <div class="kt-modal-header">
            <h3 class="kt-modal-title" id="modalTitle">Tambah Siklus</h3>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost" data-kt-modal-dismiss="true">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <form id="dataForm" method="POST">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">
            <div class="kt-modal-body flex flex-col gap-4" style="max-height:75vh;overflow-y:auto;">
                {{-- 7 Field Utama --}}
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Blok <span class="text-danger">*</span></label>
                        <select name="blok_id" id="blok_id" class="kt-select" required>
                            <option value="">-- Pilih Blok --</option>
                            @foreach($bloks as $blok)
                            <option value="{{ $blok->id }}">{{ $blok->nama_blok }} ({{ $blok->tambak->nama_tambak ?? '-' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Nama Siklus <span class="text-danger">*</span></label>
                        <input type="text" name="nama_siklus" id="nama_siklus" class="kt-input" required>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Tanggal Siklus <span class="text-danger">*</span></label>
                        <div class="kt-input">
                            <i class="ki-outline ki-calendar"></i>
                            <input class="grow" name="tgl_siklus" id="tgl_siklus" data-kt-date-picker="true" data-kt-date-picker-input-mode="true" placeholder="Pilih tanggal" readonly type="text" required/>
                        </div>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Tanggal Tebar <span class="text-danger">*</span></label>
                        <div class="kt-input">
                            <i class="ki-outline ki-calendar"></i>
                            <input class="grow" name="tgl_tebar" id="tgl_tebar" data-kt-date-picker="true" data-kt-date-picker-input-mode="true" placeholder="Pilih tanggal" readonly type="text" required/>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Umur Awal <span class="text-danger">*</span></label>
                        <div class="kt-input-group">
                            <input class="kt-input" type="text" name="umur_awal" id="umur_awal" placeholder="0" required/>
                            <span class="kt-input-addon">Hari</span>
                        </div>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Total Tebar <span class="text-danger">*</span></label>
                        <div class="kt-input-group">
                            <input class="kt-input" type="text" name="total_tebar" id="total_tebar" placeholder="0" required/>
                            <span class="kt-input-addon">Ekor</span>
                        </div>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Spesies Udang</label>
                        <input type="text" name="spesies_udang" id="spesies_udang" class="kt-input" required>
                    </div>
                </div>

                {{-- Toggle lebih banyak --}}
                <button type="button" onclick="toggleAdvanced()" class="flex items-center gap-2 text-sm text-primary hover:underline w-fit" id="toggleBtn">
                    <i class="ki-filled ki-plus-squared text-base" id="toggleIcon"></i>
                    Tampilkan data tambahan
                </button>

                {{-- Field Tersembunyi --}}
                <div id="advancedFields" class="hidden flex flex-col gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Lama Persiapan</label>
                        <div class="kt-input-group">
                            <input class="kt-input" type="text" name="lama_persiapan" id="lama_persiapan" placeholder="0"/>
                            <span class="kt-input-addon">Hari</span>
                        </div>
                    </div>
                    <div class="grid grid-cols-4 gap-4">
                        <div class="flex flex-col gap-1">
                            <label class="text-sm font-medium text-foreground">Kecerahan</label>
                            <input type="number" name="kecerahan" id="kecerahan" class="kt-input" step="0.01">
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-sm font-medium text-foreground">Suhu</label>
                            <input type="number" name="suhu" id="suhu" class="kt-input" step="0.01">
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-sm font-medium text-foreground">DO Level</label>
                            <input type="number" name="do_level" id="do_level" class="kt-input" step="0.01">
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-sm font-medium text-foreground">Salinitas</label>
                            <input type="number" name="salinitas" id="salinitas" class="kt-input" step="0.01">
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <div class="flex flex-col gap-1">
                            <label class="text-sm font-medium text-foreground">pH Pagi</label>
                            <input type="number" name="ph_pagi" id="ph_pagi" class="kt-input" step="0.01" onchange="calcPH()">
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-sm font-medium text-foreground">pH Sore</label>
                            <input type="number" name="ph_sore" id="ph_sore" class="kt-input" step="0.01" onchange="calcPH()">
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-sm font-medium text-foreground">Selisih pH</label>
                            <input type="text" id="selisih_ph_display" class="kt-input" style="background:var(--muted);" readonly>
                        </div>
                    </div>
                    <div class="grid grid-cols-5 gap-4">
                        <div class="flex flex-col gap-1">
                            <label class="text-sm font-medium text-foreground">FCR</label>
                            <input type="number" name="fcr" id="fcr" class="kt-input" step="0.01">
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-sm font-medium text-foreground">ADG</label>
                            <input type="number" name="adg" id="adg" class="kt-input" step="0.01">
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-sm font-medium text-foreground">SR</label>
                            <input type="number" name="sr" id="sr" class="kt-input" step="0.01">
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-sm font-medium text-foreground">MBW</label>
                            <input type="number" name="mbw" id="mbw" class="kt-input" step="0.01">
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-sm font-medium text-foreground">Size</label>
                            <input type="number" name="size" id="size" class="kt-input" step="0.01">
                        </div>
                    </div>
                    <div class="flex flex-col gap-1">
                        
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Harga Pakan (/kg) <span class="text-xs text-muted-foreground">(estimasi profit)</span></label>
                        <div class="kt-input-group">
                        <!-- <span class="text-sm text-muted-foreground font-medium">Rp</span> -->
                        <span class="kt-input-addon">Rp.</span>    
                        <input class="kt-input" type="text" name="harga_pakan" id="harga_pakan" step="0.01" min="0" placeholder="0"/>
                            
                        </div>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Status <span class="text-danger">*</span></label>
                    <select name="status" id="status" class="kt-select" required>
                        <option value="aktif" active>Aktif</option>
                        <option value="selesai">Selesai</option>
                        <option value="gagal">Gagal</option>
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
function toggleAdvanced() {
    var el = document.getElementById('advancedFields');
    var icon = document.getElementById('toggleIcon');
    var btn = document.getElementById('toggleBtn');
    if (el.classList.contains('hidden')) {
        el.classList.remove('hidden');
        el.classList.add('flex', 'flex-col', 'gap-4');
        icon.className = 'ki-filled ki-minus-squared text-base';
        btn.querySelector('span') && (btn.lastChild.textContent = ' Sembunyikan data tambahan');
    } else {
        el.classList.add('hidden');
        el.classList.remove('flex', 'flex-col', 'gap-4');
        icon.className = 'ki-filled ki-plus-squared text-base';
    }
}

function calcPH() {
    var pagi = parseFloat(document.getElementById('ph_pagi').value) || 0;
    var sore = parseFloat(document.getElementById('ph_sore').value) || 0;
    document.getElementById('selisih_ph_display').value = (pagi && sore) ? Math.abs(pagi - sore).toFixed(2) : '';
}

const siklusFields = [
    'blok_id', 'nama_siklus', 'tgl_siklus', 'lama_persiapan', 'tgl_tebar',
    'total_tebar', 'spesies_udang', 'umur_awal',
    'kecerahan', 'suhu', 'do_level', 'salinitas',
    'ph_pagi', 'ph_sore', 'fcr', 'adg', 'sr', 'mbw', 'size', 'status', 'harga_pakan'
];

function resetAdvanced() {
    document.getElementById('advancedFields').classList.add('hidden');
    document.getElementById('advancedFields').classList.remove('flex', 'flex-col', 'gap-4');
    document.getElementById('toggleIcon').className = 'ki-filled ki-plus-squared text-base';
}

function openCreateModal() {
    document.getElementById('modalTitle').textContent = 'Tambah Siklus';
    document.getElementById('dataForm').action = "{{ route('siklus.store') }}";
    document.getElementById('formMethod').value = 'POST';
    siklusFields.forEach(f => {
        var el = document.getElementById(f);
        if (el) el.value = (f === 'status') ? 'aktif' : '';
    });
    document.getElementById('selisih_ph_display').value = '';
    resetAdvanced();
    KTModal.getInstance(document.querySelector('#formModal')).show();
}

function setDatePickerValue(inputId, dateStr) {
    var el = document.getElementById(inputId);
    if (!el) return;
    var val = dateStr ? dateStr.substring(0, 10) : '';
    // Try flatpickr instance first (used by KT date picker)
    if (el._flatpickr) {
        el._flatpickr.setDate(val, true);
    } else {
        el.value = val;
        el.dispatchEvent(new Event('input', { bubbles: true }));
        el.dispatchEvent(new Event('change', { bubbles: true }));
    }
}

function openEditModal(id) {
    fetch(`/budidaya/siklus/${id}/edit`)
        .then(r => r.json())
        .then(data => {
            document.getElementById('modalTitle').textContent = 'Edit Siklus';
            document.getElementById('dataForm').action = `/budidaya/siklus/${id}`;
            document.getElementById('formMethod').value = 'PUT';
            siklusFields.forEach(f => {
                if (f === 'tgl_siklus' || f === 'tgl_tebar') return;
                var el = document.getElementById(f);
                if (el) el.value = data[f] ?? '';
            });
            calcPH();
            var hasAdvanced = ['lama_persiapan','kecerahan','suhu','do_level','salinitas','ph_pagi','ph_sore','fcr','adg','sr','mbw','size','harga_pakan'].some(f => data[f]);
            if (hasAdvanced) {
                var adv = document.getElementById('advancedFields');
                if (adv.classList.contains('hidden')) toggleAdvanced();
            } else resetAdvanced();
            KTModal.getInstance(document.querySelector('#formModal')).show();
            // Set date pickers after modal is shown
            setTimeout(function() {
                setDatePickerValue('tgl_siklus', data.tgl_siklus);
                setDatePickerValue('tgl_tebar', data.tgl_tebar);
            }, 100);
        });
}
</script>
@endpush