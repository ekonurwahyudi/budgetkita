<?php

namespace App\Models;

use Carbon\Carbon;

class PembelianAset extends BaseModel
{
    protected $fillable = [
        'nama_aset', 'kategori_aset_id', 'tgl_pembelian', 'nominal_pembelian',
        'umur_manfaat', 'nilai_residu', 'metode_depresiasi', 'persen_depresiasi',
        'jenis_pembayaran', 'account_bank_id', 'status', 'catatan', 'eviden',
    ];

    protected function casts(): array
    {
        return [
            'tgl_pembelian' => 'date',
            'nominal_pembelian' => 'decimal:2',
            'nilai_residu' => 'decimal:2',
            'persen_depresiasi' => 'decimal:2',
            'eviden' => 'array',
        ];
    }

    public function kategoriAset() { return $this->belongsTo(KategoriAset::class); }
    public function accountBank() { return $this->belongsTo(AccountBank::class); }

    public function getDepresiasiPerTahunAttribute(): float
    {
        if ($this->metode_depresiasi === 'persen') {
            $persen = (float) ($this->persen_depresiasi ?? 0);
            return ($this->nominal_pembelian - ($this->nilai_residu ?? 0)) * ($persen / 100);
        }

        if ($this->metode_depresiasi === 'tanpa' || $this->umur_manfaat <= 0) {
            return 0;
        }

        return ($this->nominal_pembelian - ($this->nilai_residu ?? 0)) / $this->umur_manfaat;
    }

    public function getUmurBerjalanAttribute(): int
    {
        if ($this->metode_depresiasi === 'tanpa') return 0;
        return max(0, Carbon::now()->year - Carbon::parse($this->tgl_pembelian)->year);
    }

    public function getAkumulasiDepresiasiAttribute(): float
    {
        if ($this->metode_depresiasi === 'tanpa') return 0;

        $maxDepresiasi = max(0, (float) $this->nominal_pembelian - (float) ($this->nilai_residu ?? 0));
        $akumulasi = $this->depresiasi_per_tahun * min($this->umur_berjalan, $this->umur_manfaat);
        return min($akumulasi, $maxDepresiasi);
    }

    public function getNilaiBukuAsetAttribute(): float
    {
        return max(0, (float) $this->nominal_pembelian - (float) $this->akumulasi_depresiasi);
    }

    public function getDepresiasiPerBulanAttribute(): float
    {
        $bulan = $this->umur_berjalan * 12;
        if ($bulan <= 0) return 0;
        return (float) $this->akumulasi_depresiasi / $bulan;
    }
}
