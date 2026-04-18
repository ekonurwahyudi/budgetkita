@extends('layouts.app')

@section('title', 'Detail Pemberian Pakan')
@section('page-title', 'Detail Pemberian Pakan')
@section('page-description', 'Detail data pemberian pakan')

@section('content')
<div class="grid w-full space-y-5">
    <div class="kt-card">
        <div class="kt-card-header min-h-14">
            <h3 class="kt-card-title">Detail Pemberian Pakan</h3>
            <a href="{{ route('pemberian-pakan.index') }}" class="kt-btn kt-btn-sm kt-btn-outline">
                <i class="ki-filled ki-arrow-left"></i> Kembali
            </a>
        </div>
        <div class="kt-card-content py-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <table class="kt-table-auto">
                        <tbody>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8 w-40">Tambak</td>
                                <td class="text-sm pb-3">{{ $pemberianPakan->blok?->tambak?->nama_tambak ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Blok</td>
                                <td class="text-sm text-mono pb-3">{{ $pemberianPakan->blok?->nama_blok ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Siklus</td>
                                <td class="text-sm text-mono pb-3">{{ $pemberianPakan->siklus?->nama_siklus ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div>
                    <table class="kt-table-auto">
                        <tbody>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8 w-40">Tanggal & Jam</td>
                                <td class="text-sm text-mono pb-3">{{ $pemberianPakan->tgl_pakan?->format('d/m/Y H:i') ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Kategori</td>
                                <td class="text-sm pb-3"><span class="kt-badge kt-badge-sm kt-badge-outline">{{ $pemberianPakan->itemPersediaan?->kategoriPersediaan?->deskripsi ?? '-' }}</span></td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Item</td>
                                <td class="text-sm pb-3">{{ $pemberianPakan->itemPersediaan?->deskripsi ?? $pemberianPakan->itemPersediaan?->kode_item_persediaan ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Jumlah Pakan</td>
                                <td class="text-sm text-mono pb-3 font-semibold">{{ number_format($pemberianPakan->jumlah_pakan ?? 0, 2) }} {{ $pemberianPakan->unit ?? 'kg' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection