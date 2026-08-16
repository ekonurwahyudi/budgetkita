<?php

namespace App\Models;

use App\Traits\BelongsToActiveTambak;

class PenjualanAset extends BaseModel
{
    use BelongsToActiveTambak;

    protected $fillable = [
        'tambak_id', 'pembelian_aset_id', 'nomor_transaksi', 'tgl_penjualan',
        'qty', 'kondisi', 'harga_satuan', 'total_penjualan', 'pembeli',
        'status_pembayaran', 'nominal_dibayar', 'account_bank_id', 'piutang_id',
        'catatan', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tgl_penjualan' => 'date',
            'qty' => 'integer',
            'harga_satuan' => 'decimal:2',
            'total_penjualan' => 'decimal:2',
            'nominal_dibayar' => 'decimal:2',
        ];
    }

    public function pembelianAset() { return $this->belongsTo(PembelianAset::class); }
    public function accountBank() { return $this->belongsTo(AccountBank::class); }
    public function piutang() { return $this->belongsTo(HutangPiutang::class, 'piutang_id'); }
    public function pembuat() { return $this->belongsTo(User::class, 'created_by'); }
}
