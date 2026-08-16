@extends('layouts.app')

@section('title', ($sharingRevenue ? 'Edit' : 'Tambah') . ' Sharing Revenue')
@section('page-title', ($sharingRevenue ? 'Edit' : 'Tambah') . ' Sharing Revenue')
@section('page-description', 'Pembagian keuntungan berdasarkan blok dan siklus')

@section('content')
@php
    $selectedBlok = old('blok_id', $sharingRevenue?->blok_id ?? request('blok_id'));
    $selectedSiklus = old('siklus_id', $sharingRevenue?->siklus_id ?? request('siklus_id'));
    $siklusByBlok = $bloks->mapWithKeys(fn($blok) => [
        $blok->id => $blok->sikluses->map(fn($s) => [
            'id' => $s->id,
            'nama' => $s->nama_siklus,
            'profit' => (float) ($siklusProfits[$s->id] ?? 0),
            'remaining' => (float) ($siklusRemaining[$s->id] ?? 0),
        ])->values(),
    ]);
@endphp
<div class="kt-card">
    <div class="kt-card-header min-h-14">
        <h3 class="kt-card-title">{{ $sharingRevenue ? 'Edit Data' : 'Input Data' }}</h3>
        <a href="{{ route('sharing-revenue.index') }}" class="kt-btn kt-btn-outline kt-btn-sm">
            <i class="ki-filled ki-arrow-left"></i> Kembali
        </a>
    </div>
    <form method="POST" action="{{ $sharingRevenue ? route('sharing-revenue.update', $sharingRevenue) : route('sharing-revenue.store') }}" enctype="multipart/form-data">
        @csrf
        @if($sharingRevenue) @method('PUT') @endif
        <div class="kt-card-content py-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-foreground">Nama Penerima <span class="text-danger">*</span></label>
                    <input type="text" name="nama_penerima" class="kt-input" value="{{ old('nama_penerima', $sharingRevenue?->nama_penerima) }}" required>
                    @error('nama_penerima')<span class="text-xs text-danger">{{ $message }}</span>@enderror
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-foreground">Blok <span class="text-danger">*</span></label>
                    <select name="blok_id" id="blok_id" class="kt-select" required onchange="loadSiklusOptions()">
                        <option value="">-- Pilih Blok --</option>
                        @foreach($bloks as $blok)
                        <option value="{{ $blok->id }}" {{ $selectedBlok === $blok->id ? 'selected' : '' }}>
                            {{ $blok->nama_blok }}{{ $blok->tambak ? ' - ' . $blok->tambak->nama_tambak : '' }}
                        </option>
                        @endforeach
                    </select>
                    @error('blok_id')<span class="text-xs text-danger">{{ $message }}</span>@enderror
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-foreground">Siklus <span class="text-danger">*</span></label>
                    <select name="siklus_id" id="siklus_id" class="kt-select" required onchange="updateSharingPreview()">
                        <option value="">-- Pilih Siklus --</option>
                    </select>
                    @error('siklus_id')<span class="text-xs text-danger">{{ $message }}</span>@enderror
                </div>

                <div class="rounded-lg border border-border p-4 bg-accent/20">
                    <p class="text-xs text-muted-foreground mb-1">Keuntungan Siklus</p>
                    <p class="text-xl font-semibold text-mono" id="profit_label">Rp 0</p>
                    <p class="text-xs text-muted-foreground mt-1">Sisa sharing: <span class="font-medium text-foreground" id="remaining_label">0%</span></p>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-foreground">Persentase Pembagian <span class="text-danger">*</span></label>
                    <div class="kt-input-group">
                        <input type="number" step="0.01" min="0.01" max="100" name="persentase" id="persentase" class="kt-input" value="{{ old('persentase', $sharingRevenue?->persentase) }}" required oninput="updateSharingPreview()">
                        <span class="kt-input-addon">%</span>
                    </div>
                    @error('persentase')<span class="text-xs text-danger">{{ $message }}</span>@enderror
                </div>

                <div class="rounded-lg border border-border p-4 bg-primary/5">
                    <p class="text-xs text-muted-foreground mb-1">Nominal Sharing</p>
                    <p class="text-xl font-semibold text-primary text-mono" id="nominal_label">Rp 0</p>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-foreground">Account Bank <span class="text-danger">*</span></label>
                    <select name="account_bank_id" id="account_bank_id" class="kt-select" required onchange="updateSaldoInfo()">
                        <option value="">-- Pilih Account Bank --</option>
                        @foreach($accountBanks as $bank)
                        <option value="{{ $bank->id }}" data-saldo="{{ $bank->saldo }}" {{ old('account_bank_id', $sharingRevenue?->account_bank_id) === $bank->id ? 'selected' : '' }}>
                            {{ $bank->nama_bank }} - {{ $bank->nama_pemilik }}
                        </option>
                        @endforeach
                    </select>
                    <span class="text-xs text-muted-foreground mt-1" id="saldoInfo">Saldo: <span class="text-mono font-medium text-primary" id="saldoValue"></span></span>
                    @error('account_bank_id')<span class="text-xs text-danger">{{ $message }}</span>@enderror
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-foreground">Eviden</label>
                    <input type="file" name="eviden[]" id="evidenInput" class="kt-input" multiple accept=".png,.jpg,.jpeg,.pdf" onchange="previewEviden(this)">
                    <p class="text-xs text-muted-foreground">Maksimal 5MB per file. Format: PNG, JPG, JPEG, PDF.</p>
                    @error('eviden.*')<span class="text-xs text-danger">{{ $message }}</span>@enderror
                </div>

                <div class="flex flex-col gap-1.5 lg:col-span-2">
                    <div id="previewContainer" class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-3"></div>
                    @if($sharingRevenue && !empty($sharingRevenue->eviden))
                    <div id="existingEviden" class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-3 mt-2">
                        @foreach($sharingRevenue->eviden as $idx => $ev)
                        @php
                            $isPdf = \Illuminate\Support\Str::endsWith(strtolower($ev), ['.pdf']);
                            $url = \Illuminate\Support\Facades\Storage::url($ev);
                        @endphp
                        <div class="relative group rounded-xl border border-border overflow-hidden bg-muted hover:ring-2 hover:ring-primary hover:shadow-md transition-all" id="existing-ev-{{ $idx }}">
                            @if($isPdf)
                                <a href="{{ $url }}" target="_blank" class="flex flex-col items-center justify-center w-full h-24 p-3">
                                    <i class="ki-filled ki-document text-3xl text-primary mb-2"></i>
                                    <span class="text-[10px] text-muted-foreground text-center truncate w-full">PDF</span>
                                </a>
                            @else
                                <img src="{{ $url }}" class="w-full h-24 object-cover cursor-pointer lb-thumb" alt="Eviden {{ $idx + 1 }}" data-src="{{ $url }}">
                            @endif
                            <div class="flex items-center justify-end px-2 py-1.5 border-t border-border">
                                <button type="button" onclick="hapusExistingEviden('{{ $ev }}', 'existing-ev-{{ $idx }}')" class="size-5 rounded-full bg-destructive text-white flex items-center justify-center hover:bg-destructive/80 transition-colors" title="Hapus">
                                    <i class="ki-filled ki-cross text-[10px]"></i>
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>

                <div class="flex flex-col gap-1.5 lg:col-span-2">
                    <label class="text-sm font-medium text-foreground">Catatan</label>
                    <textarea name="catatan" class="kt-input" rows="3" style="height:110px;">{{ old('catatan', $sharingRevenue?->catatan) }}</textarea>
                    @error('catatan')<span class="text-xs text-danger">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>
        <div class="kt-card-footer justify-end gap-2">
            <a href="{{ route('sharing-revenue.index') }}" class="kt-btn kt-btn-outline">Batal</a>
            <button type="submit" class="kt-btn kt-btn-primary">
                <i class="ki-filled ki-check"></i> Simpan
            </button>
        </div>
    </form>
</div>

<div id="lb-modal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.85);align-items:center;justify-content:center;padding:1rem;">
    <button id="lb-close" style="position:absolute;top:1rem;right:1rem;color:#fff;font-size:1.5rem;background:none;border:none;cursor:pointer;">
        <i class="ki-filled ki-cross" style="font-size:1.75rem;"></i>
    </button>
    <img id="lb-img" src="" style="max-width:100%;max-height:90vh;object-fit:contain;border-radius:0.5rem;box-shadow:0 25px 50px -12px rgba(0,0,0,0.5);">
</div>
@endsection

@push('scripts')
<script>
var siklusByBlok = @json($siklusByBlok);
var selectedSiklus = @json($selectedSiklus);

function formatRupiah(value) {
    return 'Rp ' + Math.round(value || 0).toLocaleString('id-ID');
}

function loadSiklusOptions() {
    var blokId = document.getElementById('blok_id').value;
    var siklusSelect = document.getElementById('siklus_id');
    var options = siklusByBlok[blokId] || [];

    siklusSelect.innerHTML = '<option value="">-- Pilih Siklus --</option>';
    options.forEach(function(item) {
        var option = document.createElement('option');
        option.value = item.id;
        option.textContent = item.nama + ' - sisa ' + Number(item.remaining || 0).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 }) + '%';
        option.dataset.profit = item.profit;
        option.dataset.remaining = item.remaining;
        if (selectedSiklus === item.id) option.selected = true;
        siklusSelect.appendChild(option);
    });

    updateSharingPreview();
}

function getSelectedProfit() {
    var selected = document.getElementById('siklus_id').selectedOptions[0];
    return selected ? parseFloat(selected.dataset.profit || 0) : 0;
}

function getSelectedRemaining() {
    var selected = document.getElementById('siklus_id').selectedOptions[0];
    return selected ? parseFloat(selected.dataset.remaining || 0) : 0;
}

function updateSharingPreview() {
    var profit = getSelectedProfit();
    var remaining = getSelectedRemaining();
    var percent = parseFloat(document.getElementById('persentase').value || 0);
    var nominal = Math.max(0, profit) * percent / 100;

    document.getElementById('profit_label').textContent = formatRupiah(profit);
    document.getElementById('profit_label').className = 'text-xl font-semibold text-mono ' + (profit >= 0 ? 'text-success' : 'text-danger');
    document.getElementById('nominal_label').textContent = formatRupiah(nominal);
    document.getElementById('remaining_label').textContent = remaining.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 }) + '%';
}

function updateSaldoInfo() {
    var sel = document.getElementById('account_bank_id');
    var saldo = sel.options[sel.selectedIndex]?.getAttribute('data-saldo');
    var saldoInfo = document.getElementById('saldoInfo');
    var saldoValue = document.getElementById('saldoValue');

    if (saldo !== null && saldo !== '') {
        saldoValue.textContent = formatRupiah(Number(saldo || 0));
        saldoInfo.style.display = '';
    } else {
        saldoInfo.style.display = 'none';
    }
}

function previewEviden(input) {
    var container = document.getElementById('previewContainer');
    container.innerHTML = '';
    if (!input.files) return;

    Array.from(input.files).forEach(function(file, index) {
        var isPdf = file.type === 'application/pdf';
        var div = document.createElement('div');
        div.className = 'relative group rounded-xl border border-border overflow-hidden bg-muted hover:ring-2 hover:ring-primary hover:shadow-md transition-all';
        div.id = 'preview-' + index;

        if (isPdf) {
            div.innerHTML =
                '<div class="flex flex-col items-center justify-center w-full h-24 p-3">' +
                    '<i class="ki-filled ki-document text-3xl text-primary mb-2"></i>' +
                    '<span class="text-[10px] text-muted-foreground text-center truncate w-full">' + file.name + '</span>' +
                '</div>' +
                '<div class="flex items-center justify-end px-2 py-1.5 border-t border-border">' +
                    '<button type="button" onclick="removePreview(' + index + ')" class="size-5 rounded-full bg-destructive text-white flex items-center justify-center hover:bg-destructive/80 transition-colors" title="Hapus">' +
                        '<i class="ki-filled ki-cross text-[10px]"></i>' +
                    '</button>' +
                '</div>';
            container.appendChild(div);
            return;
        }

        var reader = new FileReader();
        reader.onload = function(e) {
            div.innerHTML =
                '<img src="' + e.target.result + '" class="w-full h-24 object-cover cursor-pointer lb-preview" alt="Preview">' +
                '<div class="flex items-center justify-end px-2 py-1.5 border-t border-border">' +
                    '<button type="button" onclick="removePreview(' + index + ')" class="size-5 rounded-full bg-destructive text-white flex items-center justify-center hover:bg-destructive/80 transition-colors" title="Hapus">' +
                        '<i class="ki-filled ki-cross text-[10px]"></i>' +
                    '</button>' +
                '</div>';
        };
        reader.readAsDataURL(file);
        container.appendChild(div);
    });
}

function removePreview(index) {
    var el = document.getElementById('preview-' + index);
    if (el) el.remove();
}

var hapusEvidenList = [];
function hapusExistingEviden(path, elementId) {
    if (!confirm('Yakin hapus eviden ini?')) return;
    hapusEvidenList.push(path);
    var form = document.querySelector('form[enctype="multipart/form-data"]');
    document.querySelectorAll('input[name^="hapus_eviden["]').forEach(function(el) { el.remove(); });
    hapusEvidenList.forEach(function(p, i) {
        var inp = document.createElement('input');
        inp.type = 'hidden';
        inp.name = 'hapus_eviden[' + i + ']';
        inp.value = p;
        form.appendChild(inp);
    });
    document.getElementById(elementId).remove();
}

document.addEventListener('DOMContentLoaded', function() {
    loadSiklusOptions();
    updateSaldoInfo();

    var modal = document.getElementById('lb-modal');
    var img = document.getElementById('lb-img');
    var closeBtn = document.getElementById('lb-close');
    if (modal) {
        function openLb(src) {
            img.src = src;
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
        function closeLb() {
            modal.style.display = 'none';
            img.src = '';
            document.body.style.overflow = '';
        }

        document.addEventListener('click', function(e) {
            var thumb = e.target.closest('.lb-thumb');
            if (thumb) {
                e.preventDefault();
                openLb(thumb.dataset.src);
                return;
            }
            var preview = e.target.closest('.lb-preview');
            if (preview) {
                e.preventDefault();
                openLb(preview.src);
            }
        });

        closeBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            closeLb();
        });

        modal.addEventListener('click', function(e) {
            if (e.target === modal || e.target === img) closeLb();
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeLb();
        });
    }
});
</script>
@endpush
