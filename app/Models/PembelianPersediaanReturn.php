<?php

namespace App\Models;

use App\Traits\ScopedByActiveTambakRelation;

class PembelianPersediaanReturn extends BaseModel
{
    use ScopedByActiveTambakRelation;

    public const ACTIVE_TAMBAK_RELATION = 'pembelianPersediaan';

    protected $fillable = [
        'pembelian_persediaan_id',
        'pembelian_persediaan_item_id',
        'persediaan_id',
        'qty',
        'harga_satuan',
        'harga_total',
        'nominal_potong_hutang',
        'nominal_refund',
        'catatan',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'decimal:2',
            'harga_satuan' => 'decimal:2',
            'harga_total' => 'decimal:2',
            'nominal_potong_hutang' => 'decimal:2',
            'nominal_refund' => 'decimal:2',
        ];
    }

    public function pembelianPersediaan() { return $this->belongsTo(PembelianPersediaan::class); }
    public function item() { return $this->belongsTo(PembelianPersediaanItem::class, 'pembelian_persediaan_item_id'); }
    public function persediaan() { return $this->belongsTo(Persediaan::class); }
}
