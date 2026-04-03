<?php

namespace App\Models;

use Carbon\Carbon;

class PembelianAset extends BaseModel
{
    protected $fillable = [
        'nama_aset', 'kategori_aset_id', 'tgl_pembelian', 'nominal_pembelian',
        'umur_manfaat', 'nilai_residu', 'jenis_pembayaran', 'account_bank_id',
        'status', 'catatan',
    ];

    protected function casts(): array
    {
        return ['tgl_pembelian' => 'date', 'nominal_pembelian' => 'decimal:2', 'nilai_residu' => 'decimal:2'];
    }

    public function kategoriAset() { return $this->belongsTo(KategoriAset::class); }
    public function accountBank() { return $this->belongsTo(AccountBank::class); }

    // Computed: Depresiasi per tahun
    public function getDepresiasiPerTahunAttribute(): float
    {
        if ($this->umur_manfaat <= 0) return 0;
        return ($this->nominal_pembelian - $this->nilai_residu) / $this->umur_manfaat;
    }

    // Computed: Umur berjalan (tahun)
    public function getUmurBerjalanAttribute(): int
    {
        return max(0, Carbon::now()->year - Carbon::parse($this->tgl_pembelian)->year);
    }

    // Computed: Akumulasi depresiasi
    public function getAkumulasiDepresiasiAttribute(): float
    {
        return $this->depresiasi_per_tahun * min($this->umur_berjalan, $this->umur_manfaat);
    }

    // Computed: Nilai buku aset
    public function getNilaiBukuAsetAttribute(): float
    {
        return max(0, $this->nominal_pembelian - $this->akumulasi_depresiasi);
    }

    // Computed: Depresiasi per bulan
    public function getDepresiasiPerBulanAttribute(): float
    {
        $bulan = $this->umur_berjalan * 12;
        if ($bulan <= 0) return 0;
        return $this->akumulasi_depresiasi / $bulan;
    }
}
