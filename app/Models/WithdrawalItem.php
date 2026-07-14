<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WithdrawalItem extends Model
{
    protected $fillable = ['withdrawal_id', 'supply_item_id', 'quantity'];

    public function withdrawal()
    {
        return $this->belongsTo(Withdrawal::class);
    }

    public function supplyItem()
    {
        return $this->belongsTo(SupplyItem::class, 'supply_item_id');
    }
}