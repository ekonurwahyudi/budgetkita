@extends('layouts.app')

@section('title', 'Tambah Pemberian Kimia/Antibiotik')
@section('page-title', 'Tambah Pemberian Kimia/Antibiotik Kimia/Antibiotik')
@section('page-description', 'Catat pemberian kimia/antibiotik baru')

@section('content')
<div class="grid w-full space-y-5">
    <div class="kt-card">
        <div class="kt-card-header min-h-14">
            <h3 class="kt-card-title">Tambah Pemberian Kimia/Antibiotik</h3>
            <a href="{{ route('pemberian-kimia.index') }}" class="kt-btn kt-btn-outline kt-btn-sm">
                <i class="ki-filled ki-arrow-left"></i> Kembali
            </a>
        </div>
        <div class="kt-card-content py-6">
            <form method="POST" action="{{ route('pemberian-kimia.store') }}" id="formPakan">
                @csrf
                <div class="flex flex-col gap-5 max-w-3xl">
                    {{-- Tambak --}}
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-foreground">Tambak <span class="text-danger">*</span></label>
                        <select id="tambak_id" class="kt-select" required onchange="loadBlokByTambak()">
                            <option value="">-- Pilih Tambak --</option>
                            @foreach($tambaks as $t)
                            <option value="{{ $t->id }}">{{ $t->nama_tambak }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Blok + Siklus --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">Blok <span class="text-danger">*</span></label>
                            <select name="blok_id" id="blok_id" class="kt-select" required onchange="loadSiklusByBlok()">
                                <option value="">-- Pilih Blok --</option>
                            </select>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">Siklus <span class="text-danger">*</span></label>
                            <select name="siklus_id" id="siklus_id" class="kt-select" required>
                                <option value="">-- Pilih Siklus --</option>
                            </select>
                        </div>
                    </div>

                    {{-- Kategori + Item --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">Kategori <span class="text-danger">*</span></label>
                            <select id="kategori_id" class="kt-select" required onchange="filterItem()">
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($kategoriPersediaans as $kat)
                                <option value="{{ $kat->id }}">{{ $kat->deskripsi }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">Item <span class="text-danger">*</span></label>
                            <select name="item_persediaan_id" id="item_persediaan_id" class="kt-select" required>
                                <option value="">-- Pilih Item --</option>
                            </select>
                            <span class="text-xs text-muted-foreground" id="stokInfo" style="display:none;">
                                Stok: <span class="text-mono font-medium text-primary" id="stokValue"></span>
                            </span>
                        </div>
                    </div>

                    {{-- Jumlah + Unit --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">Jumlah <span class="text-danger">*</span></label>
                            <input class="kt-input" type="number" name="jumlah_pakan" step="0.01" min="0.01" placeholder="0" required value="{{ old('jumlah_pakan') }}"/>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">Unit <span class="text-danger">*</span></label>
                            <select name="unit" class="kt-select" required>
                                <option value="kg">Kilogram (kg)</option>
                                <option value="gram">Gram (g)</option>
                                <option value="liter">Liter (L)</option>
                                <option value="ml">Mililiter (ml)</option>
                            </select>
                        </div>
                    </div>

                    {{-- Tanggal & Jam (KT 24h datetime picker) --}}
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-foreground">Tanggal & Jam <span class="text-danger">*</span></label>
                        <div class="kt-input">
                            <i class="ki-outline ki-calendar"></i>
                            <input class="grow" name="tgl_pakan" id="tgl_pakan"
                                   data-kt-date-picker="true"
                                   data-kt-date-picker-input-mode="true"
                                   data-kt-date-picker-selection-time-mode="24"
                                   placeholder="Pilih tanggal & jam"
                                   readonly type="text" required/>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 mt-8 pt-5 border-t border-border">
                    <a href="{{ route('pemberian-kimia.index') }}" class="kt-btn kt-btn-outline">Batal</a>
                    <button type="submit" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-check"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
var allItems = @json($itemPakans->values());

// Set default datetime to now
document.addEventListener('DOMContentLoaded', function() {
    var el = document.getElementById('tgl_pakan');
    if (el && !el.value) {
        setTimeout(function() {
            var now = new Date();
            var formatted = now.getFullYear() + '-' +
                String(now.getMonth()+1).padStart(2,'0') + '-' +
                String(now.getDate()).padStart(2,'0') + ' ' +
                String(now.getHours()).padStart(2,'0') + ':' +
                String(now.getMinutes()).padStart(2,'0');
            if (el._flatpickr) {
                el._flatpickr.setDate(formatted, true);
            } else {
                el.value = formatted;
            }
        }, 200);
    }
});

function filterItem() {
    var katId = document.getElementById('kategori_id').value;
    var sel = document.getElementById('item_persediaan_id');
    var filtered = katId ? allItems.filter(function(i) { return i.kategori_id === katId; }) : allItems;
    sel.innerHTML = '<option value="">-- Pilih Item --</option>' +
        filtered.map(function(i) {
            return '<option value="'+i.id+'" data-stok="'+i.stok+'" data-unit="'+i.unit+'">'+i.nama+' (Stok: '+Number(i.stok).toLocaleString('id-ID')+' '+i.unit+')</option>';
        }).join('');
    document.getElementById('stokInfo').style.display = 'none';
    sel.onchange = showStok;
}

function showStok() {
    var sel = document.getElementById('item_persediaan_id');
    var opt = sel.options[sel.selectedIndex];
    if (opt && opt.value) {
        document.getElementById('stokValue').textContent = Number(opt.getAttribute('data-stok')).toLocaleString('id-ID') + ' ' + opt.getAttribute('data-unit');
        document.getElementById('stokInfo').style.display = '';
    } else {
        document.getElementById('stokInfo').style.display = 'none';
    }
}

function loadBlokByTambak() {
    var tambakId = document.getElementById('tambak_id').value;
    var sel = document.getElementById('blok_id');
    document.getElementById('siklus_id').innerHTML = '<option value="">-- Pilih Siklus --</option>';
    if (!tambakId) { sel.innerHTML = '<option value="">-- Pilih Blok --</option>'; return; }
    fetch('/budidaya/blok/by-tambak/' + tambakId).then(r => r.json()).then(bloks => {
        sel.innerHTML = '<option value="">-- Pilih Blok --</option>' + bloks.map(b => '<option value="'+b.id+'">'+b.nama_blok+'</option>').join('');
    });
}

function loadSiklusByBlok() {
    var blokId = document.getElementById('blok_id').value;
    var sel = document.getElementById('siklus_id');
    if (!blokId) { sel.innerHTML = '<option value="">-- Pilih Siklus --</option>'; return; }
    fetch('/budidaya/siklus/by-blok/' + blokId).then(r => r.json()).then(sikluses => {
        sel.innerHTML = '<option value="">-- Pilih Siklus --</option>' + sikluses.map(s => '<option value="'+s.id+'">'+s.nama_siklus+'</option>').join('');
    });
}
</script>
@endpush
