<?php

namespace App\Models;

class HutangPiutang extends BaseModel
{
    protected $fillable = [
        'nomor_transaksi', 'jenis', 'aktivitas', 'kategori_hutang_piutang_id',
        'nominal', 'jatuh_tempo', 'nominal_bayar', 'sisa_pembayaran',
        'jenis_pembayaran', 'account_bank_id', 'catatan', 'status',
    ];

    protected function casts(): array
    {
        return [
            'jatuh_tempo' => 'date',
            'nominal' => 'decimal:2', 'nominal_bayar' => 'decimal:2',
            'sisa_pembayaran' => 'decimal:2',
        ];
    }

    public function kategoriHutangPiutang() { return $this->belongsTo(KategoriHutangPiutang::class); }
    public function accountBank() { return $this->belongsTo(AccountBank::class); }
}
