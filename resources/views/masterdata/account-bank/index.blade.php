@extends('layouts.app')

@section('title', 'Account Bank')
@section('page-title', 'Account Bank')
@section('page-description', 'Kelola data account bank')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container {
        width: 100% !important;
    }

    .select2-container--default .select2-selection--single {
        min-height: 40px;
        border-color: var(--input);
        border-radius: 0.375rem;
        background-color: var(--background);
        display: flex;
        align-items: center;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: var(--foreground);
        font-size: 0.8125rem;
        line-height: normal;
        padding-left: 0.75rem;
        padding-right: 2rem;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 100%;
        right: 0.5rem;
    }

    .select2-dropdown {
        border-color: var(--input);
        border-radius: 0.375rem;
    }

    .select2-container--default .select2-results > .select2-results__options {
        max-height: 420px;
    }

    .account-bank-main-fields {
        grid-template-columns: minmax(180px, 0.7fr) minmax(0, 1.5fr);
    }

    .bank-combobox {
        position: relative;
    }

    .bank-combobox-panel {
        position: absolute;
        left: 0;
        right: 0;
        top: calc(100% + 0.25rem);
        z-index: 1050;
        max-height: 420px;
        overflow-y: auto;
        border: 1px solid var(--input);
        border-radius: 0.375rem;
        background: var(--background);
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.12);
    }

    .bank-combobox .kt-input {
        font-size: 0.8125rem;
    }

    .bank-combobox-option {
        width: 100%;
        padding: 0.5rem 0.75rem;
        text-align: left;
        font-size: 0.8125rem;
        cursor: pointer;
    }

    .bank-combobox-option:hover,
    .bank-combobox-option.is-active {
        background: var(--accent);
    }

    .bank-combobox-add {
        color: var(--primary);
        font-weight: 500;
    }

    @media (max-width: 640px) {
        .account-bank-main-fields {
            grid-template-columns: minmax(0, 1fr);
        }
    }
</style>
@endpush

@section('content')
<div class="grid w-full space-y-5">
    <div class="kt-card">
        <div class="kt-card-header min-h-16">
            <input type="text" placeholder="Cari..." class="kt-input" style="width:200px" data-kt-datatable-search="#account_bank_table" />
            <div class="flex items-center gap-2">
                @can('account-bank.edit')
                <button type="button" class="kt-btn kt-btn-outline" onclick="openTransferModal()">
                    <i class="ki-filled ki-transfer"></i> Transfer Saldo
                </button>
                @endcan
                @can('account-bank.create')
                <button type="button" class="kt-btn kt-btn-outline" onclick="openCreateModal()">
                    <i class="ki-filled ki-plus-squared"></i> Tambah
                </button>
                @endcan
            </div>
        </div>
        <div id="account_bank_table" class="kt-card-table" data-kt-datatable="true" data-kt-datatable-page-size="10" data-kt-datatable-state-save="true" data-kt-datatable-state-namespace="account_bank">
            <div class="kt-table-wrapper kt-scrollable">
                <table class="kt-table" data-kt-datatable-table="true">
                    <thead>
                        <tr>
                            <th scope="col" class="w-12" data-kt-datatable-column="no">
                                <span class="kt-table-col"><span class="kt-table-col-label">No</span><span class="kt-table-col-sort"></span></span>
                            </th>
                            <th scope="col" data-kt-datatable-column="kode">
                                <span class="kt-table-col"><span class="kt-table-col-label">Kode</span><span class="kt-table-col-sort"></span></span>
                            </th>
                            <th scope="col" data-kt-datatable-column="nama_bank">
                                <span class="kt-table-col"><span class="kt-table-col-label">Nama Bank</span><span class="kt-table-col-sort"></span></span>
                            </th>
                            <th scope="col" data-kt-datatable-column="nama_pemilik">
                                <span class="kt-table-col"><span class="kt-table-col-label">Nama Pemilik</span><span class="kt-table-col-sort"></span></span>
                            </th>
                            <th scope="col" data-kt-datatable-column="nomor_rekening">
                                <span class="kt-table-col"><span class="kt-table-col-label">No. Rekening</span><span class="kt-table-col-sort"></span></span>
                            </th>
                            <th scope="col" data-kt-datatable-column="saldo">
                                <span class="kt-table-col"><span class="kt-table-col-label">Saldo</span><span class="kt-table-col-sort"></span></span>
                            </th>
                            <th scope="col" data-kt-datatable-column="status">
                                <span class="kt-table-col"><span class="kt-table-col-label">Status</span><span class="kt-table-col-sort"></span></span>
                            </th>
                            <th scope="col" class="w-24" data-kt-datatable-column="aksi"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $i => $item)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $item->kode_account }}</td>
                            <td>{{ $item->nama_bank }}</td>
                            <td>{{ $item->nama_pemilik ?? '-' }}</td>
                            <td>{{ $item->nomor_rekening ?? '-' }}</td>
                            <td>Rp {{ number_format($item->saldo, 0, ',', '.') }}</td>
                            <td>
                                <span class="kt-badge kt-badge-sm {{ $item->status === 'aktif' ? 'kt-badge-success' : 'kt-badge-destructive' }}">
                                    {{ $item->status === 'aktif' ? 'Aktif' : 'Non Aktif' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <span class="inline-flex gap-2.5">
                                    @can('account-bank.edit')
                                    <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline" onclick="openEditModal('{{ $item->id }}')"><i class="ki-filled ki-pencil"></i></button>
                                    @endcan
                                    <a href="{{ route('account-bank.show', $item) }}" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline" title="History"><i class="ki-filled ki-time"></i></a>
                                    @can('account-bank.delete')
                                    <form method="POST" action="{{ route('account-bank.destroy', $item) }}" onsubmit="return confirm('Yakin hapus?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline text-danger"><i class="ki-filled ki-trash"></i></button>
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
                <div class="kt-datatable-length">Show <select class="kt-select kt-select-sm w-16" name="perpage" data-kt-datatable-size="true"></select> per page</div>
                <div class="kt-datatable-info"><span data-kt-datatable-info="true"></span><div class="kt-datatable-pagination" data-kt-datatable-pagination="true"></div></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="kt-modal" data-kt-modal="true" id="formModal">
    <div class="kt-modal-content max-w-[600px] top-5 lg:top-[10%]">
        <div class="kt-modal-header">
            <h3 class="kt-modal-title" id="modalTitle">Tambah Account Bank</h3>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost" data-kt-modal-dismiss="true"><i class="ki-filled ki-cross"></i></button>
        </div>
        <form id="dataForm" method="POST">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">
            <div class="kt-modal-body flex flex-col gap-4">
                <div class="grid grid-cols-2 gap-4 account-bank-main-fields">
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Kode Account <span class="text-danger">*</span></label>
                        <input type="text" name="kode_account" id="kode_account" class="kt-input" required>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Nama Bank <span class="text-danger">*</span></label>
                        <select name="nama_bank" id="nama_bank" class="kt-select" required>
                            <option value="">-- Pilih Bank --</option>
                            @foreach($banks as $b)
                            <option value="{{ $b }}">{{ $b }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Nama Pemilik</label>
                        <input type="text" name="nama_pemilik" id="nama_pemilik" class="kt-input">
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Nomor Rekening</label>
                        <input type="text" name="nomor_rekening" id="nomor_rekening" class="kt-input">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Saldo <span class="text-danger">*</span></label>
                        <input type="text" name="saldo" id="saldo" class="kt-input rupiah-input" inputmode="decimal" required>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Status <span class="text-danger">*</span></label>
                        <select name="status" id="status" class="kt-select" required>
                            <option value="aktif">Aktif</option>
                            <option value="nonaktif">Non Aktif</option>
                        </select>
                    </div>
                </div>
                <div class="flex flex-col gap-1" id="saldo_awal_wrapper" style="display:none;">
                    <label class="text-sm font-medium text-foreground">Saldo Awal</label>
                    <input type="text" name="saldo_awal" id="saldo_awal" class="kt-input rupiah-input" inputmode="decimal" placeholder="Saldo saat bank pertama kali dibuat">
                    <p class="text-xs text-muted-foreground">Isi dengan saldo awal saat bank dibuat untuk sinkronisasi otomatis. Kosongkan jika tidak ingin mengubah.</p>
                </div>
                <div class="flex flex-col gap-1" id="deskripsi_saldo_wrapper" style="display:none;">
                    <label class="text-sm font-medium text-foreground">Alasan Perubahan Saldo</label>
                    <textarea name="deskripsi_saldo" id="deskripsi_saldo" class="kt-input" rows="2" placeholder="Contoh: Koreksi saldo awal, Selisih tutup buku, dll."></textarea>
                    <p class="text-xs text-muted-foreground">Wajib diisi jika saldo diubah. Akan tercatat di history.</p>
                </div>
            </div>
            <div class="kt-modal-footer justify-end">
                <button type="button" class="kt-btn kt-btn-outline" data-kt-modal-dismiss="true">Batal</button>
                <button type="submit" class="kt-btn kt-btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
<!-- Modal Transfer Saldo -->
<div class="kt-modal" data-kt-modal="true" id="transferModal">
    <div class="kt-modal-content max-w-[600px] top-5 lg:top-[10%]">
        <div class="kt-modal-header">
            <h3 class="kt-modal-title">Transfer Saldo</h3>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost" data-kt-modal-dismiss="true"><i class="ki-filled ki-cross"></i></button>
        </div>
        <form id="transferForm" method="POST" action="{{ route('account-bank.transfer') }}">
            @csrf
            <div class="kt-modal-body flex flex-col gap-4">
                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-foreground">Dari Bank <span class="text-danger">*</span></label>
                    <select name="dari_account_bank_id" id="transfer_dari_id" class="kt-select" required onchange="onDariChange()">
                        <option value="">-- Pilih Bank Asal --</option>
                        @foreach($data as $bank)
                        <option value="{{ $bank->id }}" data-saldo="{{ $bank->saldo }}">
                            {{ $bank->nama_bank }} - {{ $bank->nama_pemilik }} (Rp {{ number_format($bank->saldo, 0, ',', '.') }})
                        </option>
                        @endforeach
                    </select>
                    <span class="text-xs text-muted-foreground" id="saldo_dari_info" style="display:none;">
                        Saldo: <span class="text-mono font-medium text-primary" id="saldo_dari_value"></span>
                    </span>
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-foreground">Ke Bank <span class="text-danger">*</span></label>
                    <select name="ke_account_bank_id" id="transfer_ke_id" class="kt-select" required>
                        <option value="">-- Pilih Bank Tujuan --</option>
                        @foreach($data as $bank)
                        <option value="{{ $bank->id }}">
                            {{ $bank->nama_bank }} - {{ $bank->nama_pemilik }} (Rp {{ number_format($bank->saldo, 0, ',', '.') }})
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-foreground">Nominal <span class="text-danger">*</span></label>
                    <div class="kt-input-group">
                        <span class="kt-input-addon">Rp.</span>
                        <input class="kt-input" type="number" name="nominal" id="transfer_nominal" step="0.01" min="1" placeholder="0" required/>
                    </div>
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-foreground">Catatan</label>
                    <textarea name="catatan" id="transfer_catatan" class="kt-input" rows="2"></textarea>
                </div>
            </div>
            <div class="kt-modal-footer justify-end">
                <button type="button" class="kt-btn kt-btn-outline" data-kt-modal-dismiss="true">Batal</button>
                <button type="submit" class="kt-btn kt-btn-primary">
                    <i class="ki-filled ki-transfer"></i> Transfer
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    initBankSelect2();
});

function initBankSelect2() {
    if (!window.jQuery || !jQuery.fn.select2) {
        initBankComboboxFallback();
        return;
    }

    jQuery('#nama_bank').select2({
        tags: true,
        width: '100%',
        dropdownParent: jQuery('#formModal'),
        placeholder: '-- Pilih Bank --',
        allowClear: true,
        createTag: function(params) {
            var term = jQuery.trim(params.term);
            if (term === '') return null;

            return {
                id: term,
                text: term,
                newTag: true
            };
        },
        insertTag: function(data, tag) {
            data.push(tag);
        },
        templateResult: function(data) {
            if (data.newTag) {
                return 'Tambah bank: ' + data.text;
            }
            return data.text;
        }
    });
}

function initBankComboboxFallback() {
    var select = document.getElementById('nama_bank');
    if (!select || select.dataset.comboboxReady === 'true') return;

    select.dataset.comboboxReady = 'true';
    select.classList.add('hidden');

    var wrapper = document.createElement('div');
    wrapper.className = 'bank-combobox';

    var input = document.createElement('input');
    input.type = 'text';
    input.className = 'kt-input';
    input.placeholder = '-- Pilih atau tambah bank --';
    input.autocomplete = 'off';

    var panel = document.createElement('div');
    panel.className = 'bank-combobox-panel hidden';

    select.parentNode.insertBefore(wrapper, select.nextSibling);
    wrapper.appendChild(input);
    wrapper.appendChild(panel);

    function getOptions() {
        return Array.from(select.options)
            .filter(function(option) { return option.value; })
            .map(function(option) { return option.value; });
    }

    function selectBank(value) {
        if (value && !Array.from(select.options).some(function(option) { return option.value === value; })) {
            select.add(new Option(value, value, true, true));
        }

        select.value = value;
        input.value = value;
        panel.classList.add('hidden');
        select.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function renderOptions() {
        var keyword = input.value.trim().toLowerCase();
        var options = getOptions().filter(function(value) {
            return value.toLowerCase().includes(keyword);
        });
        var exactMatch = getOptions().some(function(value) {
            return value.toLowerCase() === keyword;
        });

        panel.innerHTML = '';

        options.forEach(function(value) {
            var button = document.createElement('button');
            button.type = 'button';
            button.className = 'bank-combobox-option';
            button.textContent = value;
            button.addEventListener('click', function() {
                selectBank(value);
            });
            panel.appendChild(button);
        });

        if (input.value.trim() && !exactMatch) {
            var addButton = document.createElement('button');
            addButton.type = 'button';
            addButton.className = 'bank-combobox-option bank-combobox-add';
            addButton.textContent = 'Tambah bank: ' + input.value.trim();
            addButton.addEventListener('click', function() {
                selectBank(input.value.trim());
            });
            panel.appendChild(addButton);
        }

        panel.classList.toggle('hidden', panel.children.length === 0);
    }

    input.addEventListener('focus', renderOptions);
    input.addEventListener('input', renderOptions);
    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && input.value.trim()) {
            e.preventDefault();
            selectBank(input.value.trim());
        }
    });

    select.addEventListener('change', function() {
        input.value = select.value || '';
    });

    document.addEventListener('click', function(e) {
        if (!wrapper.contains(e.target)) panel.classList.add('hidden');
    });
}

function setBankValue(value) {
    var select = document.getElementById('nama_bank');
    var bankName = value || '';

    if (bankName && !Array.from(select.options).some(option => option.value === bankName)) {
        select.add(new Option(bankName, bankName, true, true));
    }

    select.value = bankName;

    if (window.jQuery && jQuery.fn.select2) {
        jQuery(select).val(bankName).trigger('change');
    } else {
        select.dispatchEvent(new Event('change', { bubbles: true }));
    }
}

function openTransferModal() {
    document.getElementById('transfer_dari_id').value = '';
    document.getElementById('transfer_ke_id').value = '';
    document.getElementById('transfer_nominal').value = '';
    document.getElementById('transfer_catatan').value = '';
    document.getElementById('saldo_dari_info').style.display = 'none';
    // Reset semua option visible
    var sel = document.getElementById('transfer_ke_id');
    for (var i = 0; i < sel.options.length; i++) sel.options[i].hidden = false;
    KTModal.getInstance(document.querySelector('#transferModal')).show();
}

function onDariChange() {
    var sel = document.getElementById('transfer_dari_id');
    var val = sel.value;
    var saldo = sel.options[sel.selectedIndex]?.getAttribute('data-saldo');
    var info = document.getElementById('saldo_dari_info');
    if (val && saldo) {
        document.getElementById('saldo_dari_value').textContent = 'Rp ' + Number(saldo).toLocaleString('id-ID');
        info.style.display = '';
    } else {
        info.style.display = 'none';
    }
    // Sembunyikan bank asal dari dropdown tujuan
    var ke = document.getElementById('transfer_ke_id');
    for (var i = 0; i < ke.options.length; i++) {
        ke.options[i].hidden = ke.options[i].value === val;
    }
    if (ke.value === val) ke.value = '';
}

// Format angka ribuan (Indonesia)
function formatRupiah(str) {
    if (!str) return '';
    var parts = str.toString().split('.');
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    return parts.join(',');
}

// Hapus tit ribuan, kembalikan desimal dengan titik
function unformatRupiah(str) {
    if (!str) return '';
    return str.toString().replace(/\./g, '').replace(',', '.');
}

// Pasang event listener pada input rupiah
document.querySelectorAll('.rupiah-input').forEach(function(input) {
    input.addEventListener('blur', function() {
        var raw = unformatRupiah(this.value);
        if (raw !== '') this.value = formatRupiah(raw);
    });
    input.addEventListener('focus', function() {
        this.value = unformatRupiah(this.value);
    });
});

// Bersihkan format sebelum submit
document.getElementById('dataForm').addEventListener('submit', function(e) {
    document.querySelectorAll('.rupiah-input').forEach(function(input) {
        input.value = unformatRupiah(input.value);
    });
});

function openCreateModal() {
    document.getElementById('modalTitle').textContent = 'Tambah Account Bank';
    document.getElementById('dataForm').action = "{{ route('account-bank.store') }}";
    document.getElementById('formMethod').value = 'POST';
    ['kode_account','nama_pemilik','nomor_rekening','deskripsi_saldo','saldo_awal'].forEach(f => document.getElementById(f).value = '');
    setBankValue('');
    document.getElementById('saldo').value = '0';
    document.getElementById('status').value = 'aktif';
    document.getElementById('deskripsi_saldo_wrapper').style.display = 'none';
    document.getElementById('saldo_awal_wrapper').style.display = 'none';
    KTModal.getInstance(document.querySelector('#formModal')).show();
}
function openEditModal(id) {
    fetch(`/masterdata/account-bank/${id}/edit`)
        .then(r => r.json())
        .then(data => {
            document.getElementById('modalTitle').textContent = 'Edit Account Bank';
            document.getElementById('dataForm').action = `/masterdata/account-bank/${id}`;
            document.getElementById('formMethod').value = 'PUT';
            document.getElementById('kode_account').value = data.kode_account;
            setBankValue(data.nama_bank);
            document.getElementById('nama_pemilik').value = data.nama_pemilik || '';
            document.getElementById('nomor_rekening').value = data.nomor_rekening || '';
            document.getElementById('saldo').value = formatRupiah(data.saldo);
            document.getElementById('status').value = data.status || 'aktif';
            document.getElementById('deskripsi_saldo').value = '';
            document.getElementById('saldo_awal').value = data.saldo_awal ? formatRupiah(data.saldo_awal) : '';
            document.getElementById('deskripsi_saldo_wrapper').style.display = '';
            document.getElementById('saldo_awal_wrapper').style.display = '';
            KTModal.getInstance(document.querySelector('#formModal')).show();
        });
}
</script>
@endpush
