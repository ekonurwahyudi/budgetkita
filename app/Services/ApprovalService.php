<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\AccountBank;

class ApprovalService
{
    public function approve(Model $model): void
    {
        DB::transaction(function () use ($model) {
            $model->update(['status' => 'selesai']);
            $this->updateSaldo($model);
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

        return $model->thp ?? $model->total_penjualan ?? $model->nominal ?? 0;
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
}
