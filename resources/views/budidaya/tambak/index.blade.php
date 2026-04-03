@extends('layouts.app')

@section('title', 'Daftar Tambak')
@section('page-title', 'Daftar Tambak')
@section('page-description', 'Kelola data tambak')

@section('content')
<div class="grid w-full space-y-5">
    <div class="kt-card">
        <div class="kt-card-header min-h-16">
            <input type="text" placeholder="Cari..." class="kt-input" style="width:200px" data-kt-datatable-search="#kt_datatable" />
            @can('tambak.create')
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
                            <th scope="col" data-kt-datatable-column="nama_tambak">
                                <span class="kt-table-col">
                                    <span class="kt-table-col-label">Nama Tambak</span>
                                    <span class="kt-table-col-sort"></span>
                                </span>
                            </th>
                            <th scope="col" data-kt-datatable-column="lokasi">
                                <span class="kt-table-col">
                                    <span class="kt-table-col-label">Lokasi</span>
                                    <span class="kt-table-col-sort"></span>
                                </span>
                            </th>
                            <th scope="col" data-kt-datatable-column="total_lahan">
                                <span class="kt-table-col">
                                    <span class="kt-table-col-label">Total Lahan (m²)</span>
                                    <span class="kt-table-col-sort"></span>
                                </span>
                            </th>
                            <th scope="col" data-kt-datatable-column="didirikan">
                                <span class="kt-table-col">
                                    <span class="kt-table-col-label">Didirikan</span>
                                    <span class="kt-table-col-sort"></span>
                                </span>
                            </th>
                            <th scope="col" data-kt-datatable-column="jumlah_blok">
                                <span class="kt-table-col">
                                    <span class="kt-table-col-label">Jumlah Blok</span>
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
                            <td><a href="/budidaya/blok?tambak_id={{ $item->id }}" class="text-primary hover:underline">{{ $item->nama_tambak }}</a></td>
                            <td>{{ $item->lokasi }}</td>
                            <td>{{ number_format($item->total_lahan, 2) }}</td>
                            <td>{{ $item->didirikan_pada?->format('d/m/Y') ?? '-' }}</td>
                            <td>{{ $item->bloks_count }}</td>
                            <td class="text-end">
                                <span class="inline-flex gap-2.5">
                                    @can('tambak.edit')
                                    <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline" onclick="openEditModal('{{ $item->id }}')">
                                        <i class="ki-filled ki-pencil"></i>
                                    </button>
                                    @endcan
                                    <a href="{{ route('tambak.anggota.index', $item) }}" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline" title="Kelola Anggota">
                                        <i class="ki-filled ki-people"></i>
                                    </a>
                                    @can('tambak.delete')
                                    <form method="POST" action="{{ route('tambak.destroy', $item) }}" onsubmit="return confirm('Yakin hapus data ini?')">
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
    <div class="kt-modal-content max-w-[600px] top-5 lg:top-[15%]">
        <div class="kt-modal-header">
            <h3 class="kt-modal-title" id="modalTitle">Tambah Tambak</h3>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost" data-kt-modal-dismiss="true">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <form id="dataForm" method="POST">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">
            <div class="kt-modal-body flex flex-col gap-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Nama Tambak <span class="text-danger">*</span></label>
                        <input type="text" name="nama_tambak" id="nama_tambak" class="kt-input" required>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Lokasi <span class="text-danger">*</span></label>
                        <div class="relative">
                            <input type="text" name="lokasi" id="lokasi" class="kt-input" autocomplete="off" required placeholder="Ketik nama kecamatan..." oninput="searchLokasi(this)">
                            <div class="lokasi-dropdown hidden absolute w-full mt-1 bg-background border border-border rounded-lg shadow-lg max-h-[200px] overflow-y-auto" style="z-index:9999;"></div>
                        </div>
                    </div>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium text-foreground">Alamat</label>
                    <input type="text" name="alamat" id="alamat" class="kt-input" required>
                    <!-- <textarea name="alamat" id="alamat" class="kt-input" rows="2"></textarea> -->
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Total Lahan (m²)</label>
                        <input type="number" name="total_lahan" id="total_lahan" class="kt-input" step="0.01">
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Didirikan Pada <span class="text-danger">*</span></label>
                        <div class="kt-input">
                            <i class="ki-outline ki-calendar"></i>
                            <input class="grow" name="didirikan_pada" id="didirikan_pada" data-kt-date-picker="true" data-kt-date-picker-input-mode="true" placeholder="Pilih tanggal" readonly type="text" required/>
                        </div>
                    </div>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium text-foreground">Catatan</label>
                    <textarea name="catatan" id="catatan" class="kt-input" rows="2" style="height: 94px;"></textarea>
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
    document.getElementById('modalTitle').textContent = 'Tambah Tambak';
    document.getElementById('dataForm').action = "{{ route('tambak.store') }}";
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('nama_tambak').value = '';
    document.getElementById('lokasi').value = '';
    document.getElementById('alamat').value = '';
    document.getElementById('total_lahan').value = '';
    document.getElementById('didirikan_pada').value = '';
    document.getElementById('catatan').value = '';
    KTModal.getInstance(document.querySelector('#formModal')).show();
}

function openEditModal(id) {
    fetch(`/budidaya/tambak/${id}/edit`)
        .then(r => r.json())
        .then(data => {
            document.getElementById('modalTitle').textContent = 'Edit Tambak';
            document.getElementById('dataForm').action = `/budidaya/tambak/${id}`;
            document.getElementById('formMethod').value = 'PUT';
            document.getElementById('nama_tambak').value = data.nama_tambak;
            document.getElementById('lokasi').value = data.lokasi;
            document.getElementById('alamat').value = data.alamat;
            document.getElementById('total_lahan').value = data.total_lahan;
            document.getElementById('didirikan_pada').value = data.didirikan_pada ? data.didirikan_pada.substring(0, 10) : '';
            document.getElementById('catatan').value = data.catatan;
            KTModal.getInstance(document.querySelector('#formModal')).show();
        });
}
</script>
@endpush