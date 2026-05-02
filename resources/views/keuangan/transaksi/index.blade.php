@extends('layouts.app')

@section('title', 'Transaksi Keuangan')
@section('page-title', 'Transaksi Keuangan')
@section('page-description', 'Kelola transaksi keuangan')

@section('content')
<div class="grid w-full space-y-5">
    <div class="kt-card">
        {{-- Header: Tabs kiri, Search/Filter/Export kanan --}}
        <div class="kt-card-header min-h-16 flex-wrap gap-3">
            {{-- Tabs kiri - bg grey, aktif putih --}}
            <div class="flex items-center rounded-lg p-1" style="background-color: #f1f5f9;">
                @php
                    $activeTab = request('jenis_transaksi', '');
                    $tabs = [
                        '' => 'Semua (' . $counts['all'] . ')',
                        'uang_masuk' => 'Uang Masuk (' . $counts['uang_masuk'] . ')',
                        'uang_keluar' => 'Uang Keluar (' . $counts['uang_keluar'] . ')',
                    ];
                @endphp
                @foreach($tabs as $val => $label)
                    <a href="{{ request()->fullUrlWithQuery(['jenis_transaksi' => $val, 'page' => null]) }}"
                       class="px-4 py-1.5 rounded-md text-sm whitespace-nowrap transition-all
                              {{ $activeTab === $val
                                  ? 'text-gray-900 font-semibold'
                                  : 'text-gray-500 hover:text-gray-700' }}"
                       @if($activeTab === $val) style="background-color: #ffffff; box-shadow: 0 1px 2px rgba(0,0,0,0.06);" @endif>
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            {{-- Kanan: Search, Filter, Export, Tambah --}}
            <div class="flex items-center gap-2 flex-wrap">
                <input type="text" placeholder="Cari kegiatan..." class="kt-input" style="width:200px"
                       data-kt-datatable-search="#kt_datatable" value="{{ request('search') }}" />

                {{-- Filter Button --}}
                <button type="button" id="filter-btn" class="kt-btn kt-btn-outline flex items-center gap-2">
                    <i class="ki-filled ki-filter"></i> Filter
                    @if(request()->hasAny(['tgl_dari','tgl_sampai','kategori_transaksi_id','blok_id','siklus_id','status']))
                        <span class="w-2 h-2 rounded-full bg-primary inline-block"></span>
                    @endif
                </button>

                <a href="{{ route('transaksi.export', request()->query()) }}"
                   class="kt-btn flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white border-0">
                    <i class="ki-filled ki-file-sheet"></i> Export
                </a>

                @can('transaksi-keuangan.create')
                <a href="{{ route('transaksi.create') }}" class="kt-btn kt-btn-primary">
                    <i class="ki-filled ki-plus-squared"></i> Tambah
                </a>
                @endcan
            </div>
        </div>

        {{-- Table --}}
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
                            <th data-kt-datatable-column="kategori"><span class="kt-table-col"><span class="kt-table-col-label">Kategori</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="nominal"><span class="kt-table-col"><span class="kt-table-col-label">Nominal</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="status"><span class="kt-table-col"><span class="kt-table-col-label">Status</span><span class="kt-table-col-sort"></span></span></th>
                            <th class="w-28" data-kt-datatable-column="aksi"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $i => $item)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td class="text-mono">{{ $item->nomor_transaksi }}</td>
                            <td>
                                @if($item->jenis_transaksi === 'uang_masuk')
                                    <span class="kt-badge kt-badge-sm kt-badge-success kt-badge-outline">Uang Masuk</span>
                                @elseif($item->jenis_transaksi === 'uang_keluar')
                                    <span class="kt-badge kt-badge-sm kt-badge-destructive kt-badge-outline">Uang Keluar</span>
                                @else
                                    <span class="kt-badge kt-badge-sm kt-badge-outline">{{ ucfirst($item->jenis_transaksi) }}</span>
                                @endif
                            </td>
                            <td>{{ $item->tgl_kwitansi?->format('d/m/Y') ?? '-' }}</td>
                            <td>
                                <div>{{ Str::limit($item->aktivitas, 40) }}</div>
                                @if($item->blok || $item->siklus)
                                <div class="text-xs text-gray-400 mt-0.5">
                                    {{ $item->blok?->nama_blok }} {{ $item->siklus ? '· '.$item->siklus->nama_siklus : '' }}
                                </div>
                                @endif
                            </td>
                            <td class="text-sm text-gray-500">{{ $item->kategoriTransaksi?->deskripsi ?? '-' }}</td>
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
                                    <form method="POST" action="{{ route('transaksi.approve', $item) }}" class="inline">@csrf<button type="submit" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline text-success" title="Approve"><i class="ki-filled ki-check"></i></button></form>
                                    <form method="POST" action="{{ route('transaksi.reject', $item) }}" class="inline" onsubmit="return confirm('Yakin reject?')">@csrf<button type="submit" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline text-danger" title="Reject"><i class="ki-filled ki-cross"></i></button></form>
                                    @endif
                                    <a href="{{ route('transaksi.show', $item) }}" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline" title="Lihat"><i class="ki-filled ki-eye"></i></a>
                                    @can('transaksi-keuangan.edit')
                                    @if(auth()->user()->hasRole('Owner') || in_array($item->status, ['awaiting_approval','pending']))
                                    <a href="{{ route('transaksi.edit', $item) }}" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline" title="Edit"><i class="ki-filled ki-pencil"></i></a>
                                    @endif
                                    @endcan
                                    @can('transaksi-keuangan.delete')
                                    @if(auth()->user()->hasRole('Owner') || $item->status === 'awaiting_approval')
                                    <form method="POST" action="{{ route('transaksi.destroy', $item) }}" onsubmit="return confirm('Yakin hapus?')">@csrf @method('DELETE')<button type="submit" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline text-danger" title="Hapus"><i class="ki-filled ki-trash"></i></button></form>
                                    @endif
                                    @endcan
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-gray-400 py-8">Tidak ada data transaksi.</td>
                        </tr>
                        @endforelse
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

{{-- Filter Panel - TIDAK pakai overlay, langsung fixed panel saja --}}
<div id="filter-panel" class="hidden bg-white border border-gray-200 rounded-xl shadow-2xl p-5 w-80"
     style="position:fixed; z-index:100;">
    <form method="GET" id="filter-form">
        @if(request('jenis_transaksi'))
            <input type="hidden" name="jenis_transaksi" value="{{ request('jenis_transaksi') }}">
        @endif

        <p class="font-semibold text-gray-800 mb-4">Filter Transaksi</p>

        <div class="space-y-4">
            {{-- Range Tanggal --}}
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal Kwitansi</label>
                <div class="kt-input w-full">
                    <i class="ki-outline ki-calendar"></i>
                    <input
                        id="tgl-range-picker"
                        class="grow"
                        type="text"
                        placeholder="Pilih rentang tanggal"
                        readonly
                        data-kt-date-picker="true"
                        data-kt-date-picker-action-buttons="true"
                        data-kt-date-picker-display-months-count="2"
                        data-kt-date-picker-input-mode="true"
                        data-kt-date-picker-months-to-switch="1"
                        data-kt-date-picker-position-to-input="left"
                        data-kt-date-picker-preset-last-month="true"
                        data-kt-date-picker-preset-last30-days="true"
                        data-kt-date-picker-preset-last7-days="true"
                        data-kt-date-picker-preset-this-month="true"
                        data-kt-date-picker-preset-this-week="true"
                        data-kt-date-picker-presets="true"
                        data-kt-date-picker-selection-dates-mode="multiple-ranged"
                        data-kt-date-picker-type="multiple"
                        @if(request('tgl_dari') && request('tgl_sampai'))
                            value="{{ request('tgl_dari') }} - {{ request('tgl_sampai') }}"
                        @endif
                    />
                </div>
                <input type="hidden" name="tgl_dari" id="input-tgl-dari" value="{{ request('tgl_dari') }}">
                <input type="hidden" name="tgl_sampai" id="input-tgl-sampai" value="{{ request('tgl_sampai') }}">
            </div>

            {{-- Kategori --}}
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Kategori</label>
                <select name="kategori_transaksi_id" class="kt-select w-full">
                    <option value="">Semua Kategori</option>
                    @foreach($kategoriTransaksis as $kat)
                        <option value="{{ $kat->id }}" {{ request('kategori_transaksi_id') == $kat->id ? 'selected' : '' }}>
                            {{ $kat->deskripsi }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Blok --}}
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Blok</label>
                <select name="blok_id" class="kt-select w-full">
                    <option value="">Semua Blok</option>
                    @foreach($bloks as $blok)
                        <option value="{{ $blok->id }}" {{ request('blok_id') == $blok->id ? 'selected' : '' }}>
                            {{ $blok->nama_blok }} ({{ $blok->tambak?->nama_tambak }})
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Siklus --}}
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Siklus</label>
                <select name="siklus_id" class="kt-select w-full">
                    <option value="">Semua Siklus</option>
                    @foreach($sikluses as $siklus)
                        <option value="{{ $siklus->id }}" {{ request('siklus_id') == $siklus->id ? 'selected' : '' }}>
                            {{ $siklus->nama_siklus }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Status --}}
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                <select name="status" class="kt-select w-full">
                    <option value="">Semua Status</option>
                    <option value="awaiting_approval" {{ request('status')=='awaiting_approval'?'selected':'' }}>Awaiting</option>
                    <option value="proses" {{ request('status')=='proses'?'selected':'' }}>Proses</option>
                    <option value="selesai" {{ request('status')=='selesai'?'selected':'' }}>Selesai</option>
                    <option value="cancel" {{ request('status')=='cancel'?'selected':'' }}>Cancel</option>
                    <option value="pending" {{ request('status')=='pending'?'selected':'' }}>Pending</option>
                </select>
            </div>
        </div>

        <div class="flex gap-2 mt-5">
            <button type="submit" class="kt-btn kt-btn-primary flex-1">Terapkan</button>
            <a href="{{ route('transaksi.index') }}" class="kt-btn kt-btn-outline flex-1 text-center">Reset</a>
        </div>
    </form>
</div>

@endsection

@push('scripts')
<style>
    /* Paksa datepicker popup (popper) selalu di atas filter panel */
    [data-popper-placement] {
        z-index: 9999 !important;
    }
</style>
<script>
(function() {
    var filterBtn = document.getElementById('filter-btn');
    var panel = document.getElementById('filter-panel');
    var isOpen = false;
    var datePickerActive = false;

    function positionPanel() {
        var rect = filterBtn.getBoundingClientRect();
        panel.style.top = (rect.bottom + 8) + 'px';
        panel.style.left = Math.max(0, rect.right - 320) + 'px';
    }

    function openFilter() {
        positionPanel();
        panel.classList.remove('hidden');
        isOpen = true;
    }

    function closeFilter() {
        panel.classList.add('hidden');
        isOpen = false;
    }

    filterBtn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        if (isOpen) { closeFilter(); } else { openFilter(); }
    });

    // Detect datepicker open/close via MutationObserver
    var dpObserver = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            mutation.addedNodes.forEach(function(node) {
                if (node.nodeType === 1 && node.hasAttribute && node.hasAttribute('data-popper-placement')) {
                    datePickerActive = true;
                }
            });
            mutation.removedNodes.forEach(function(node) {
                if (node.nodeType === 1 && node.hasAttribute && node.hasAttribute('data-popper-placement')) {
                    // Delay reset so mousedown handler doesn't close filter
                    setTimeout(function() { datePickerActive = false; }, 200);
                }
            });
        });
    });
    dpObserver.observe(document.body, { childList: true, subtree: true });

    // Close hanya jika klik di luar panel, filter button, dan datepicker popup
    document.addEventListener('mousedown', function(e) {
        if (!isOpen) return;
        // Jangan tutup jika datepicker sedang aktif
        if (datePickerActive) return;
        if (filterBtn.contains(e.target)) return;
        if (panel.contains(e.target)) return;
        // Cek apakah klik di datepicker popup (popper element)
        if (e.target.closest('[data-popper-placement]')) return;
        closeFilter();
    });

    window.addEventListener('scroll', function() {
        if (isOpen) positionPanel();
    }, true);

    window.addEventListener('resize', function() {
        if (isOpen) positionPanel();
    });

    // Parse date range picker value into hidden inputs before form submit
    document.getElementById('filter-form').addEventListener('submit', function() {
        var pickerVal = (document.getElementById('tgl-range-picker').value || '').trim();
        if (pickerVal && pickerVal.indexOf(' - ') !== -1) {
            var parts = pickerVal.split(' - ');
            document.getElementById('input-tgl-dari').value = parts[0].trim();
            document.getElementById('input-tgl-sampai').value = parts[1].trim();
        } else {
            document.getElementById('input-tgl-dari').value = '';
            document.getElementById('input-tgl-sampai').value = '';
        }
    });
})();
</script>
@endpush
