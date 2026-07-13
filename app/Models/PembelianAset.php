<?php

namespace App\Models;

use App\Traits\BelongsToActiveTambak;
use Carbon\Carbon;

class PembelianAset extends BaseModel
{
    use BelongsToActiveTambak;

    protected $fillable = [
        'tambak_id', 'blok_id', 'siklus_id', 'nomor_transaksi', 'nama_aset', 'kategori_aset_id', 'tgl_pembelian', 'qty', 'qty_tersedia', 'qty_rusak', 'harga_satuan', 'nominal_pembelian',
        'umur_manfaat', 'nilai_residu', 'metode_depresiasi', 'persen_depresiasi',
        'jenis_pembayaran', 'account_bank_id', 'status_pembayaran', 'nominal_dibayar', 'hutang_piutang_id', 'status', 'created_by', 'reject_reason',
        'catatan', 'eviden', 'foto_aset',
    ];

    protected function casts(): array
    {
        return [
            'tgl_pembelian' => 'date',
            'qty' => 'integer',
            'qty_tersedia' => 'integer',
            'qty_rusak' => 'integer',
            'harga_satuan' => 'decimal:2',
            'nominal_pembelian' => 'decimal:2',
            'nilai_residu' => 'decimal:2',
            'persen_depresiasi' => 'decimal:2',
            'nominal_dibayar' => 'decimal:2',
            'eviden' => 'array',
            'foto_aset' => 'array',
        ];
    }

    public function kategoriAset() { return $this->belongsTo(KategoriAset::class); }
    public function blok() { return $this->belongsTo(Blok::class); }
    public function siklus() { return $this->belongsTo(Siklus::class); }
    public function accountBank() { return $this->belongsTo(AccountBank::class); }
    public function hutangPiutang() { return $this->belongsTo(HutangPiutang::class); }
    public function penjualanAsets() { return $this->hasMany(PenjualanAset::class); }
    public function pembuat() { return $this->belongsTo(User::class, 'created_by'); }

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
