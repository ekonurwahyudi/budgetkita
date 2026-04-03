@extends('layouts.app')

@section('title', 'Item Persediaan')
@section('page-title', 'Item Persediaan')
@section('page-description', 'Kelola data item persediaan')

@section('content')
<div class="grid w-full space-y-5">
    <div class="kt-card">
        <div class="kt-card-header min-h-16">
            <input type="text" placeholder="Cari..." class="kt-input" style="width:200px" data-kt-datatable-search="#kt_datatable" />
            @can('item-persediaan.create')
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
                            <th scope="col" data-kt-datatable-column="kategori">
                                <span class="kt-table-col">
                                    <span class="kt-table-col-label">Kategori</span>
                                    <span class="kt-table-col-sort"></span>
                                </span>
                            </th>
                            <th scope="col" data-kt-datatable-column="kode_item">
                                <span class="kt-table-col">
                                    <span class="kt-table-col-label">Kode Item</span>
                                    <span class="kt-table-col-sort"></span>
                                </span>
                            </th>
                            <th scope="col" data-kt-datatable-column="deskripsi">
                                <span class="kt-table-col">
                                    <span class="kt-table-col-label">Deskripsi</span>
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
                            <td>{{ $item->kategoriPersediaan->deskripsi ?? '-' }}</td>
                            <td>{{ $item->kode_item_persediaan }}</td>
                            <td>{{ $item->deskripsi }}</td>
                            <td class="text-end">
                                <span class="inline-flex gap-2.5">
                                    @can('item-persediaan.edit')
                                    <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline" onclick="openEditModal('{{ $item->id }}')">
                                        <i class="ki-filled ki-pencil"></i>
                                    </button>
                                    @endcan
                                    @can('item-persediaan.delete')
                                    <form method="POST" action="{{ route('item-persediaan.destroy', $item) }}" onsubmit="return confirm('Yakin hapus data ini?')">
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
    <div class="kt-modal-content max-w-[500px] top-5 lg:top-[15%]">
        <div class="kt-modal-header">
            <h3 class="kt-modal-title" id="modalTitle">Tambah Item Persediaan</h3>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost" data-kt-modal-dismiss="true">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <form id="dataForm" method="POST">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">
            <div class="kt-modal-body flex flex-col gap-4">
                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium text-foreground">Kategori Persediaan <span class="text-danger">*</span></label>
                    <select name="kategori_persediaan_id" id="kategori_persediaan_id" class="kt-select" required>
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($kategoriPersediaans as $kat)
                        <option value="{{ $kat->id }}">{{ $kat->deskripsi }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium text-foreground">Kode Item <span class="text-danger">*</span></label>
                    <input type="text" name="kode_item_persediaan" id="kode_item_persediaan" class="kt-input" required>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium text-foreground">Deskripsi <span class="text-danger">*</span></label>
                    <textarea name="deskripsi" id="deskripsi" class="kt-input" rows="3" required></textarea>
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
    document.getElementById('modalTitle').textContent = 'Tambah Item Persediaan';
    document.getElementById('dataForm').action = "{{ route('item-persediaan.store') }}";
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('kategori_persediaan_id').value = '';
    document.getElementById('kode_item_persediaan').value = '';
    document.getElementById('deskripsi').value = '';
    KTModal.getInstance(document.querySelector('#formModal')).show();
}

function openEditModal(id) {
    fetch(`/masterdata/item-persediaan/${id}/edit`)
        .then(r => r.json())
        .then(data => {
            document.getElementById('modalTitle').textContent = 'Edit Item Persediaan';
            document.getElementById('dataForm').action = `/masterdata/item-persediaan/${id}`;
            document.getElementById('formMethod').value = 'PUT';
            document.getElementById('kategori_persediaan_id').value = data.kategori_persediaan_id;
            document.getElementById('kode_item_persediaan').value = data.kode_item_persediaan;
            document.getElementById('deskripsi').value = data.deskripsi;
            KTModal.getInstance(document.querySelector('#formModal')).show();
        });
}
</script>
@endpush