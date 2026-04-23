<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class HutangPiutangPayment extends Model
{
    use HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['hutang_piutang_id', 'jumlah', 'account_bank_id', 'catatan'];

    protected function casts(): array
    {
        return ['jumlah' => 'decimal:2'];
    }

    public function hutangPiutang() { return $this->belongsTo(HutangPiutang::class); }
    public function accountBank() { return $this->belongsTo(AccountBank::class); }
}
