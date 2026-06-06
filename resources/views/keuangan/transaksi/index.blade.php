@extends('layouts.app')

@section('title', 'Transaksi Keuangan')
@section('page-title', 'Transaksi Keuangan')
@section('page-description', 'Kelola transaksi keuangan')

@section('content')
<script>
    localStorage.removeItem('transaksi_v2');
</script>
@php
    $sampleIncomeItem = $itemTransaksis->first(fn ($item) => str_contains(strtolower($item->kategoriTransaksi?->deskripsi ?? ''), 'masuk')) ?: $itemTransaksis->first();
    $sampleExpenseItem = $itemTransaksis->first(fn ($item) => str_contains(strtolower($item->kategoriTransaksi?->deskripsi ?? ''), 'keluar')) ?: $itemTransaksis->first();
    $sampleTambak = $tambaks->first();
    $sampleBlok = $bloks->first();
    $sampleSiklus = $sikluses->first();
    $sampleSumberDana = $sumberDanas->first();
    $sampleAccountBank = $accountBanks->first();
@endphp
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
                       data-kt-datatable-search="#transaksi_table" value="{{ request('search') }}" />

                {{-- Filter Button --}}
                <button type="button" id="filter-btn" class="kt-btn kt-btn-outline flex items-center gap-2">
                    <i class="ki-filled ki-filter"></i> Filter
                    @if(request()->hasAny(['tgl_dari','tgl_sampai','kategori_transaksi_id','blok_id','siklus_id','status']))
                        <span class="w-2 h-2 rounded-full bg-primary inline-block"></span>
                    @endif
                </button>

                <a href="{{ route('transaksi.export', request()->query()) }}"
                   class="kt-btn flex items-center gap-2 text-white border-0" style="background-color:#16a34a;">
                    <i class="ki-filled ki-tablet-text-up"></i> Export Excel
                </a>

                @can('transaksi-keuangan.create')
                <button type="button" onclick="openImportModal()" class="kt-btn kt-btn-outline flex items-center gap-2">
                    <i class="ki-filled ki-file-up"></i> Import Excel
                </button>

                <a href="{{ route('transaksi.create') }}" class="kt-btn kt-btn-primary">
                    <i class="ki-filled ki-plus-squared"></i> Tambah
                </a>
                @endcan
            </div>
        </div>

        {{-- Table --}}
        <div id="transaksi_table" class="kt-card-table" data-kt-datatable="true" data-kt-datatable-page-size="10" data-kt-datatable-state-save="false" data-kt-datatable-state-namespace="transaksi_v2">
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
                                    <form method="POST" action="{{ route('transaksi.approve', $item) }}" class="inline">@csrf<button type="submit" class="kt-btn kt-btn-primary kt-btn-sm kt-btn-icon" title="Approve"><i class="ki-filled ki-check"></i></button></form>
                                    <button type="button" class="kt-btn kt-btn-destructive kt-btn-sm kt-btn-icon" title="Reject"
                                        onclick="openRejectModal('{{ route('transaksi.reject', $item) }}', '{{ e($item->nomor_transaksi) }}')">
                                        <i class="ki-filled ki-cross"></i>
                                    </button>
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

{{-- Import Modal --}}
<div id="importModal" style="display:none; position:fixed; inset:0; z-index:99999; background:rgba(0,0,0,0.5); align-items:center; justify-content:center; padding:1rem;" onclick="closeImportModal()">
    <div class="transaksi-import-dialog" style="background:var(--card); border:1px solid var(--border); border-radius:0.75rem; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25); width:100%; max-width:72rem; max-height:92vh; overflow:hidden;" onclick="event.stopPropagation();">
        <div class="flex items-center justify-between p-4 border-b border-border">
            <div>
                <h3 class="text-base font-semibold text-foreground">Import Transaksi Excel</h3>
                <p class="text-xs text-muted-foreground mt-1">Upload format CSV/Excel, lalu sistem membuat transaksi awaiting otomatis.</p>
            </div>
            <button type="button" onclick="closeImportModal()" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('transaksi.import') }}" enctype="multipart/form-data">
            @csrf
            <div class="p-4 space-y-5" style="max-height:calc(92vh - 132px); overflow:auto;">
                <div class="transaksi-import-note">
                    <div class="font-semibold text-gray-900 mb-1">Nomor transaksi dan status tidak perlu diisi.</div>
                    <div class="text-xs text-muted-foreground">Kolom No. Transaksi dan Status boleh ada di file, tetapi akan diabaikan. Sistem memakai nomor otomatis prefix INVT dan semua data import masuk sebagai Awaiting.</div>
                </div>

                <div class="transaksi-import-upload-row">
                    <div>
                        <label class="text-sm font-medium text-foreground" for="import_file">Upload File <span class="text-danger">*</span></label>
                        <input id="import_file" type="file" name="file" accept=".xlsx,.xls,.csv" class="kt-input w-full mt-2" required>
                        <!-- <div class="text-xs text-muted-foreground mt-2">Bisa pakai .xlsx, .xls, atau .csv. Untuk aman, gunakan tombol download format di samping.</div> -->
                    </div>

                    <div class="flex items-end">
                        <button type="button" id="downloadImportSample" class="kt-btn w-full justify-center text-white border-0" style="background-color:#111827;">
                            <i class="ki-filled ki-tablet-text-down"></i> Download Format CSV
                        </button>
                    </div>
                </div>

                @if($errors->has('file'))
                    <div class="rounded-md border border-red-200 bg-red-50 p-3 text-xs text-red-700 space-y-1">
                        @foreach($errors->get('file') as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <div class="transaksi-import-grid">
                    <div class="transaksi-import-format">
                        <div class="font-semibold text-gray-900 mb-2">Format Kolom</div>
                        <div class="transaksi-import-sample kt-scrollable">
                            <table class="kt-table text-xs">
                                <thead>
                                    <tr>
                                        <th>Jenis</th>
                                        <th>Tanggal</th>
                                        <th>Aktivitas</th>
                                        <th>Kategori</th>
                                        <th>Item Transaksi</th>
                                        <th>Tambak</th>
                                        <th>Blok</th>
                                        <th>Siklus</th>
                                        <th>Nominal</th>
                                        <th>Jenis Pembayaran</th>
                                        <th>Sumber Dana</th>
                                        <th>Account Bank</th>
                                        <th>Catatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Uang Masuk</td>
                                        <td>{{ now()->format('Y-m-d') }}</td>
                                        <td>Contoh pemasukan</td>
                                        <td>{{ $sampleIncomeItem?->kategoriTransaksi?->deskripsi ?? 'KATEGORI' }}</td>
                                        <td>{{ $sampleIncomeItem?->kode_item ?? 'KODE_ITEM' }}</td>
                                        <td>{{ $sampleTambak?->nama_tambak ?? 'NAMA_TAMBAK' }}</td>
                                        <td>{{ $sampleBlok?->nama_blok ?? '' }}</td>
                                        <td>{{ $sampleSiklus?->nama_siklus ?? '' }}</td>
                                        <td>250000</td>
                                        <td>Cash</td>
                                        <td>{{ $sampleSumberDana?->kode_sumber_dana ?? $sampleSumberDana?->deskripsi ?? 'SUMBER_DANA' }}</td>
                                        <td></td>
                                        <td>Contoh import pemasukan</td>
                                    </tr>
                                    <tr>
                                        <td>Uang Keluar</td>
                                        <td>{{ now()->format('Y-m-d') }}</td>
                                        <td>Contoh pengeluaran</td>
                                        <td>{{ $sampleExpenseItem?->kategoriTransaksi?->deskripsi ?? 'KATEGORI' }}</td>
                                        <td>{{ $sampleExpenseItem?->kode_item ?? 'KODE_ITEM' }}</td>
                                        <td>{{ $sampleTambak?->nama_tambak ?? 'NAMA_TAMBAK' }}</td>
                                        <td>{{ $sampleBlok?->nama_blok ?? '' }}</td>
                                        <td>{{ $sampleSiklus?->nama_siklus ?? '' }}</td>
                                        <td>100000</td>
                                        <td>Bank</td>
                                        <td>{{ $sampleSumberDana?->kode_sumber_dana ?? $sampleSumberDana?->deskripsi ?? 'SUMBER_DANA' }}</td>
                                        <td>{{ $sampleAccountBank?->kode_account ?? $sampleAccountBank?->nama_bank ?? 'KODE_BANK' }}</td>
                                        <td>Contoh import pengeluaran</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-xs text-muted-foreground mt-2">Gunakan kode atau nama/deskripsi sesuai referensi di bawah. Account Bank wajib diisi hanya untuk pembayaran Bank.</div>
                    </div>

                    <div class="transaksi-import-col">
                        <div class="transaksi-import-reference">
                            <div>
                                <div class="transaksi-import-reference-head">
                                    <div class="font-semibold text-gray-900">Kode Item Transaksi</div>
                                    <div class="text-xs text-muted-foreground">Gunakan kode pada kolom Item Transaksi.</div>
                                </div>
                                <div class="transaksi-import-reference-body">
                                    <table class="kt-table text-xs">
                                        <thead><tr><th>Kode</th><th>Nama Item</th></tr></thead>
                                        <tbody>
                                            @forelse($itemTransaksis as $item)
                                                <tr>
                                                    <td><span class="transaksi-import-code">{{ $item->kode_item }}</span></td>
                                                    <td>{{ $item->deskripsi }}</td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="2" class="text-center text-gray-400 py-5">Belum ada item transaksi.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="transaksi-import-col">
                        <div class="transaksi-import-reference">
                            <div class="transaksi-import-reference-head">
                                <div class="font-semibold text-gray-900">Kode Rekening Aktif</div>
                                <div class="text-xs text-muted-foreground">Gunakan kode/nama bank/no rekening pada kolom Account Bank.</div>
                            </div>
                            <div class="transaksi-import-reference-body">
                                <table class="kt-table text-xs">
                                    <thead><tr><th>Kode</th><th>Bank</th><th>Pemilik</th></tr></thead>
                                    <tbody>
                                        @forelse($accountBanks as $account)
                                            <tr>
                                                <td><span class="transaksi-import-code">{{ $account->kode_account }}</span></td>
                                                <td>{{ $account->nama_bank }}</td>
                                                <td>{{ $account->nama_pemilik ?? '-' }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="3" class="text-center text-gray-400 py-5">Belum ada rekening aktif.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="transaksi-import-col">
                        <div class="transaksi-import-reference">
                            <div class="transaksi-import-reference-head">
                                <div class="font-semibold text-gray-900">Sumber Dana</div>
                                <div class="text-xs text-muted-foreground">Gunakan kode atau deskripsi pada kolom Sumber Dana.</div>
                            </div>
                            <div class="transaksi-import-reference-body">
                                <table class="kt-table text-xs">
                                    <thead><tr><th>Kode</th><th>Deskripsi</th></tr></thead>
                                    <tbody>
                                        @forelse($sumberDanas as $sumber)
                                            <tr>
                                                <td><span class="transaksi-import-code">{{ $sumber->kode_sumber_dana }}</span></td>
                                                <td>{{ $sumber->deskripsi }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="2" class="text-center text-gray-400 py-5">Belum ada sumber dana.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="transaksi-import-col">
                        <div class="transaksi-import-reference">
                            <div class="transaksi-import-reference-head">
                                <div class="font-semibold text-gray-900">Tambak Yang Bisa Diakses</div>
                                <div class="text-xs text-muted-foreground">Gunakan nama tambak pada kolom Tambak.</div>
                            </div>
                            <div class="transaksi-import-reference-body">
                                <table class="kt-table text-xs">
                                    <thead><tr><th>Tambak</th><th>Lokasi</th></tr></thead>
                                    <tbody>
                                        @forelse($tambaks as $tambak)
                                            <tr>
                                                <td><span class="transaksi-import-code">{{ $tambak->nama_tambak }}</span></td>
                                                <td>{{ $tambak->lokasi ?? '-' }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="2" class="text-center text-gray-400 py-5">Belum ada tambak yang bisa diakses.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="transaksi-import-col">
                        <div class="transaksi-import-reference">
                            <div class="transaksi-import-reference-head">
                                <div class="font-semibold text-gray-900">Blok</div>
                                <div class="text-xs text-muted-foreground">Gunakan nama blok yang sesuai dengan tambaknya.</div>
                            </div>
                            <div class="transaksi-import-reference-body">
                                <table class="kt-table text-xs">
                                    <thead><tr><th>Blok</th><th>Tambak</th></tr></thead>
                                    <tbody>
                                        @forelse($bloks as $blok)
                                            <tr>
                                                <td><span class="transaksi-import-code">{{ $blok->nama_blok }}</span></td>
                                                <td>{{ $blok->tambak?->nama_tambak ?? '-' }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="2" class="text-center text-gray-400 py-5">Belum ada blok.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="transaksi-import-col">
                        <div class="transaksi-import-reference">
                            <div class="transaksi-import-reference-head">
                                <div class="font-semibold text-gray-900">Siklus</div>
                                <div class="text-xs text-muted-foreground">Siklus wajib cocok dengan blok pada baris import.</div>
                            </div>
                            <div class="transaksi-import-reference-body">
                                <table class="kt-table text-xs">
                                    <thead><tr><th>Siklus</th><th>Blok</th></tr></thead>
                                    <tbody>
                                        @forelse($sikluses as $siklus)
                                            <tr>
                                                <td><span class="transaksi-import-code">{{ $siklus->nama_siklus }}</span></td>
                                                <td>{{ $siklus->blok?->nama_blok ?? '-' }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="2" class="text-center text-gray-400 py-5">Belum ada siklus.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-end gap-2 p-4 border-t border-border">
                <button type="button" onclick="closeImportModal()" class="kt-btn kt-btn-outline">Batal</button>
                <button type="submit" class="kt-btn kt-btn-primary">
                    <i class="ki-filled ki-file-up"></i> Import
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Reject Modal --}}
<div id="rejectModal" style="display:none; position:fixed; inset:0; z-index:99999; background:rgba(0,0,0,0.5); align-items:center; justify-content:center; padding:1rem;">
    <div style="background:var(--card); border:1px solid var(--border); border-radius:0.75rem; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25); width:100%; max-width:30rem;" onclick="event.stopPropagation();">
        <div class="flex items-center justify-between p-4 border-b border-border">
            <div>
                <h3 class="text-base font-semibold text-foreground">Reject Transaksi</h3>
                <p class="text-xs text-muted-foreground mt-1" id="rejectNomor">-</p>
            </div>
            <button type="button" onclick="closeRejectModal()" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <form method="POST" id="rejectForm">
            @csrf
            <div class="p-4 flex flex-col gap-3">
                <label class="text-sm font-medium text-foreground" for="alasan_reject">Alasan Reject <span class="text-danger">*</span></label>
                <textarea id="alasan_reject" name="alasan_reject" class="kt-input" rows="4" style="height:120px;" placeholder="Tuliskan alasan transaksi ditolak..." required minlength="5"></textarea>
                <p class="text-xs text-muted-foreground">Alasan ini akan dikirim sebagai notifikasi kepada pembuat transaksi.</p>
            </div>
            <div class="flex items-center justify-end gap-2 p-4 border-t border-border">
                <button type="button" onclick="closeRejectModal()" class="kt-btn kt-btn-outline">Batal</button>
                <button type="submit" class="kt-btn kt-btn-destructive">
                    <i class="ki-filled ki-cross"></i> Reject
                </button>
            </div>
        </form>
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
    .transaksi-import-note {
        border: 1px dashed #cbdaf5;
        border-radius: 12px;
        background: #f8fbff;
        padding: 14px;
    }
    .transaksi-import-upload-row {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(220px, 320px);
        gap: 1rem;
        align-items: end;
    }
    .transaksi-import-grid {
        display: grid;
        grid-template-columns: repeat(12, minmax(0, 1fr));
        gap: 1.25rem;
    }
    .transaksi-import-format {
        grid-column: span 12;
    }
    .transaksi-import-col {
        grid-column: span 4;
        min-width: 0;
    }
    .transaksi-import-sample,
    .transaksi-import-reference {
        border: 1px solid #e4e8f0;
        border-radius: 12px;
        background: #fff;
        overflow: hidden;
    }
    .transaksi-import-sample {
        overflow: auto;
    }
    .transaksi-import-sample table,
    .transaksi-import-reference table {
        margin-bottom: 0;
        min-width: 100%;
    }
    .transaksi-import-reference-head {
        padding: 12px 14px;
        border-bottom: 1px solid #edf1f7;
        background: #f8fbff;
    }
    .transaksi-import-reference-body {
        max-height: 240px;
        overflow: auto;
    }
    .transaksi-import-reference th {
        position: sticky;
        top: 0;
        z-index: 1;
        background: #f3f6fa;
        white-space: nowrap;
    }
    .transaksi-import-code {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        padding: 4px 8px;
        background: #eef6ff;
        color: #1b84ff;
        font-weight: 600;
        letter-spacing: .02em;
        white-space: nowrap;
    }
    @media (max-width: 1024px) {
        .transaksi-import-col {
            grid-column: span 6;
        }
    }
    @media (max-width: 768px) {
        .transaksi-import-upload-row,
        .transaksi-import-grid {
            grid-template-columns: 1fr;
        }
        .transaksi-import-format,
        .transaksi-import-col {
            grid-column: auto;
        }
    }
</style>
<script>
function openRejectModal(action, nomor) {
    var modal = document.getElementById('rejectModal');
    var form = document.getElementById('rejectForm');
    var label = document.getElementById('rejectNomor');
    var textarea = document.getElementById('alasan_reject');

    form.action = action;
    label.textContent = nomor || '-';
    textarea.value = '';
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    setTimeout(function() { textarea.focus(); }, 50);
}

function closeRejectModal() {
    document.getElementById('rejectModal').style.display = 'none';
    document.body.style.overflow = '';
}

function openImportModal() {
    var modal = document.getElementById('importModal');
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    setTimeout(function() {
        var input = document.getElementById('import_file');
        if (input) input.focus();
    }, 50);
}

function closeImportModal() {
    document.getElementById('importModal').style.display = 'none';
    document.body.style.overflow = '';
}

@if($errors->has('file'))
    document.addEventListener('DOMContentLoaded', openImportModal);
@endif

document.getElementById('downloadImportSample')?.addEventListener('click', function() {
    var csv = [
        ['Jenis','Tanggal','Aktivitas','Kategori','Item Transaksi','Tambak','Blok','Siklus','Nominal','Jenis Pembayaran','Sumber Dana','Account Bank','Catatan'],
        [
            'Uang Masuk',
            @js(now()->format('Y-m-d')),
            'Contoh pemasukan',
            @js($sampleIncomeItem?->kategoriTransaksi?->deskripsi ?? 'KATEGORI'),
            @js($sampleIncomeItem?->kode_item ?? 'KODE_ITEM'),
            @js($sampleTambak?->nama_tambak ?? 'NAMA_TAMBAK'),
            @js($sampleBlok?->nama_blok ?? ''),
            @js($sampleSiklus?->nama_siklus ?? ''),
            '250000',
            'Cash',
            @js($sampleSumberDana?->kode_sumber_dana ?? $sampleSumberDana?->deskripsi ?? 'SUMBER_DANA'),
            '',
            'Contoh import pemasukan'
        ],
        [
            'Uang Keluar',
            @js(now()->format('Y-m-d')),
            'Contoh pengeluaran',
            @js($sampleExpenseItem?->kategoriTransaksi?->deskripsi ?? 'KATEGORI'),
            @js($sampleExpenseItem?->kode_item ?? 'KODE_ITEM'),
            @js($sampleTambak?->nama_tambak ?? 'NAMA_TAMBAK'),
            @js($sampleBlok?->nama_blok ?? ''),
            @js($sampleSiklus?->nama_siklus ?? ''),
            '100000',
            'Bank',
            @js($sampleSumberDana?->kode_sumber_dana ?? $sampleSumberDana?->deskripsi ?? 'SUMBER_DANA'),
            @js($sampleAccountBank?->kode_account ?? $sampleAccountBank?->nama_bank ?? 'KODE_BANK'),
            'Contoh import pengeluaran'
        ]
    ].map(function(row) {
        return row.map(function(value) {
            value = String(value || '');
            return '"' + value.replace(/"/g, '""') + '"';
        }).join(',');
    }).join('\r\n');

    var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    var url = URL.createObjectURL(blob);
    var link = document.createElement('a');
    link.href = url;
    link.download = 'format-import-transaksi-keuangan.csv';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
});

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
