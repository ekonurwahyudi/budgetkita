<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\AccountBank;
use App\Models\Persediaan;
use App\Models\RiwayatPersediaan;

class ApprovalService
{
    public function approve(Model $model): void
    {
        DB::transaction(function () use ($model) {
            $model->update(['status' => 'selesai']);
            $this->updateSaldo($model);
            $this->updatePersediaan($model);
        });
    }

    public function reject(Model $model): void
    {
        $model->update(['status' => 'cancel']);
    }

    public function pending(Model $model): void
    {
        $model->update(['status' => 'pending']);
    }

    public function proses(Model $model): void
    {
        $model->update(['status' => 'proses']);
    }

    private function updateSaldo(Model $model): void
    {
        if (!isset($model->account_bank_id) || !$model->account_bank_id) {
            return;
        }

        if ($model->jenis_pembayaran !== 'bank') {
            return;
        }

        $bank = AccountBank::findOrFail($model->account_bank_id);
        $nominal = $this->getNominal($model);
        $type = $this->getSaldoType($model);

        if ($type === 'tambah') {
            $bank->increment('saldo', $nominal);
        } elseif ($type === 'kurang') {
            $bank->decrement('saldo', $nominal);
        }
    }

    private function getNominal(Model $model): float
    {
        // Gaji → THP, Pembelian → sum items, lainnya → nominal
        if (method_exists($model, 'items') && $model->relationLoaded('items')) {
            return $model->items->sum('harga_total');
        }

        return $model->thp ?? $model->total_penjualan ?? $model->nominal_pembelian ?? $model->nominal ?? 0;
    }

    private function getSaldoType(Model $model): string
    {
        $class = class_basename($model);

        // Uang masuk ke saldo
        if ($class === 'Investasi') return 'tambah';
        if ($class === 'Panen') return 'tambah';
        if ($class === 'TransaksiKeuangan' && $model->jenis_transaksi === 'uang_masuk') return 'tambah';
        if ($class === 'HutangPiutang' && $model->jenis === 'hutang') return 'tambah';

        // Uang keluar dari saldo
        if ($class === 'GajiKaryawan') return 'kurang';
        if ($class === 'PembelianPersediaan') return 'kurang';
        if ($class === 'PembelianAset') return 'kurang';
        if ($class === 'TransaksiKeuangan' && $model->jenis_transaksi === 'uang_keluar') return 'kurang';
        if ($class === 'HutangPiutang' && $model->jenis === 'piutang') return 'kurang';

        return '';
    }

    private function updatePersediaan(Model $model): void
    {
        $class = class_basename($model);

        if ($class !== 'PembelianPersediaan') {
            return;
        }

        if (!$model->relationLoaded('items')) {
            $model->load('items');
        }

        foreach ($model->items as $item) {
            // Cari atau buat record persediaan
            $persediaan = Persediaan::firstOrCreate(
                ['item_persediaan_id' => $item->item_persediaan_id],
                ['qty' => 0, 'unit' => $item->satuan, 'harga_per_unit' => 0, 'total_harga' => 0]
            );

            // Update qty dan harga
            $persediaan->qty += $item->qty;
            $persediaan->unit = $item->satuan;
            $persediaan->harga_per_unit = $item->harga_satuan;
            $persediaan->total_harga = $persediaan->qty * $persediaan->harga_per_unit;
            $persediaan->save();

            // Catat riwayat
            RiwayatPersediaan::create([
                'persediaan_id' => $persediaan->id,
                'jenis'         => 'penambahan',
                'qty_masuk'     => $item->qty,
                'qty_keluar'    => 0,
                'harga_per_unit'=> $item->harga_satuan,
                'harga_total'   => $item->harga_total,
                'catatan'       => 'Pembelian ' . $model->nomor_transaksi,
            ]);
        }
    }
}
