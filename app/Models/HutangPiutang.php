<?php

namespace App\Models;

class HutangPiutang extends BaseModel
{
    protected $fillable = [
        'nomor_transaksi', 'jenis', 'aktivitas', 'kategori_hutang_piutang_id',
        'nominal', 'total_bayar', 'jatuh_tempo', 'nominal_bayar', 'sisa_pembayaran',
        'jenis_pembayaran', 'account_bank_id', 'eviden', 'catatan', 'status',
        'created_by', 'reject_reason', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'jatuh_tempo' => 'date',
            'created_at' => 'datetime',
            'nominal' => 'decimal:2', 'total_bayar' => 'decimal:2',
            'nominal_bayar' => 'decimal:2', 'sisa_pembayaran' => 'decimal:2',
            'eviden' => 'array',
        ];
    }

    public function kategoriHutangPiutang() { return $this->belongsTo(KategoriHutangPiutang::class); }
    public function accountBank() { return $this->belongsTo(AccountBank::class); }
    public function payments() { return $this->hasMany(HutangPiutangPayment::class); }
    public function pembuat() { return $this->belongsTo(User::class, 'created_by'); }
}
