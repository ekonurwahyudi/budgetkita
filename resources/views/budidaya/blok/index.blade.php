@extends('layouts.app')

@section('title', 'Daftar Blok/Kolam')
@section('page-title', 'Daftar Blok/Kolam')
@section('page-description', 'Kelola data blok/kolam')

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
            </form>
            @can('blok.create')
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
                            <th scope="col" data-kt-datatable-column="tambak">
                                <span class="kt-table-col">
                                    <span class="kt-table-col-label">Tambak</span>
                                    <span class="kt-table-col-sort"></span>
                                </span>
                            </th>
                            <th scope="col" data-kt-datatable-column="nama_blok">
                                <span class="kt-table-col">
                                    <span class="kt-table-col-label">Nama Blok</span>
                                    <span class="kt-table-col-sort"></span>
                                </span>
                            </th>
                            <th scope="col" data-kt-datatable-column="didirikan">
                                <span class="kt-table-col">
                                    <span class="kt-table-col-label">Didirikan</span>
                                    <span class="kt-table-col-sort"></span>
                                </span>
                            </th>
                            <th scope="col" data-kt-datatable-column="anco">
                                <span class="kt-table-col">
                                    <span class="kt-table-col-label">Anco</span>
                                    <span class="kt-table-col-sort"></span>
                                </span>
                            </th>
                            <th scope="col" data-kt-datatable-column="luas">
                                <span class="kt-table-col">
                                    <span class="kt-table-col-label">Luas m²</span>
                                    <span class="kt-table-col-sort"></span>
                                </span>
                            </th>
                            <th scope="col" data-kt-datatable-column="status">
                                <span class="kt-table-col">
                                    <span class="kt-table-col-label">Status</span>
                                    <span class="kt-table-col-sort"></span>
                                </span>
                            </th>
                            <th scope="col" data-kt-datatable-column="siklus">
                                <span class="kt-table-col">
                                    <span class="kt-table-col-label">Siklus</span>
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
                            <td>{{ $item->tambak->nama_tambak ?? '-' }}</td>
                            <td><a href="/budidaya/siklus?blok_id={{ $item->id }}" class="text-primary hover:underline">{{ $item->nama_blok }}</a></td>
                            <td>{{ $item->didirikan_pada?->format('d/m/Y') ?? '-' }}</td>
                            <td>{{ $item->jumlah_anco }}</td>
                            <td>{{ number_format(($item->panjang ?? 0) * ($item->lebar ?? 0), 2) }}</td>
                            <td>
                                @if($item->status_blok === 'aktif')
                                    <span class="kt-badge kt-badge-success">Aktif</span>
                                @elseif($item->status_blok === 'maintenance')
                                    <span class="kt-badge kt-badge-warning">Maintenance</span>
                                @else
                                    <span class="kt-badge kt-badge-destructive">Nonaktif</span>
                                @endif
                            </td>
                            <td>{{ $item->sikluses_count }}</td>
                            <td class="text-end">
                                <span class="inline-flex gap-2.5">
                                    @can('blok.edit')
                                    <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline" onclick="openEditModal('{{ $item->id }}')">
                                        <i class="ki-filled ki-pencil"></i>
                                    </button>
                                    @endcan
                                    @can('blok.delete')
                                    <form method="POST" action="{{ route('blok.destroy', $item) }}" onsubmit="return confirm('Yakin hapus data ini?')">
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
            <h3 class="kt-modal-title" id="modalTitle">Tambah Blok</h3>
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
                        <label class="text-sm font-medium text-foreground">Tambak <span class="text-danger">*</span></label>
                        <select name="tambak_id" id="tambak_id" class="kt-select" required>
                            <option value="">-- Pilih Tambak --</option>
                            @foreach($tambaks as $tambak)
                            <option value="{{ $tambak->id }}">{{ $tambak->nama_tambak }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Nama Blok <span class="text-danger">*</span></label>
                        <input type="text" name="nama_blok" id="nama_blok" class="kt-input" required>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Didirikan Pada</label>
                        <div class="kt-input">
                            <i class="ki-outline ki-calendar"></i>
                            <input class="grow" name="didirikan_pada" id="didirikan_pada" data-kt-date-picker="true" data-kt-date-picker-input-mode="true" placeholder="Pilih tanggal" readonly type="text"/>
                        </div>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Jumlah Anco</label>
                        <input type="number" name="jumlah_anco" id="jumlah_anco" class="kt-input">
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Panjang</label>
                        <div class="kt-input-group">
                            <input class="kt-input" type="text" name="panjang" id="panjang" step="0.01" placeholder="0"/>
                            <span class="kt-input-addon">m</span>
                        </div>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Lebar</label>
                        <div class="kt-input-group">
                            <input class="kt-input" type="text" name="lebar" id="lebar" step="0.01" placeholder="0"/>
                            <span class="kt-input-addon">m</span>
                        </div>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Kedalaman</label>
                        <div class="kt-input-group">
                            <input class="kt-input" type="text" name="kedalaman" id="kedalaman" step="0.01" placeholder="0"/>
                            <span class="kt-input-addon">m</span>
                        </div>
                    </div>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium text-foreground">Status Blok <span class="text-danger">*</span></label>
                    <select name="status_blok" id="status_blok" class="kt-select" required>
                        <option value="aktif">Aktif</option>
                        <option value="nonaktif">Nonaktif</option>
                        <option value="maintenance">Maintenance</option>
                    </select>
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
    document.getElementById('modalTitle').textContent = 'Tambah Blok';
    document.getElementById('dataForm').action = "{{ route('blok.store') }}";
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('tambak_id').value = '';
    document.getElementById('nama_blok').value = '';
    document.getElementById('didirikan_pada').value = '';
    document.getElementById('jumlah_anco').value = '';
    document.getElementById('panjang').value = '';
    document.getElementById('lebar').value = '';
    document.getElementById('kedalaman').value = '';
    document.getElementById('status_blok').value = 'aktif';
    KTModal.getInstance(document.querySelector('#formModal')).show();
}

function openEditModal(id) {
    fetch(`/budidaya/blok/${id}/edit`)
        .then(r => r.json())
        .then(data => {
            document.getElementById('modalTitle').textContent = 'Edit Blok';
            document.getElementById('dataForm').action = `/budidaya/blok/${id}`;
            document.getElementById('formMethod').value = 'PUT';
            document.getElementById('tambak_id').value = data.tambak_id;
            document.getElementById('nama_blok').value = data.nama_blok;
            document.getElementById('didirikan_pada').value = data.didirikan_pada ? data.didirikan_pada.substring(0, 10) : '';
            document.getElementById('jumlah_anco').value = data.jumlah_anco;
            document.getElementById('panjang').value = data.panjang;
            document.getElementById('lebar').value = data.lebar;
            document.getElementById('kedalaman').value = data.kedalaman;
            document.getElementById('status_blok').value = data.status_blok;
            KTModal.getInstance(document.querySelector('#formModal')).show();
        });
}
</script>
@endpush