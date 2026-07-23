<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplyRequestItem extends Model
{
    protected $fillable = ['supply_request_id', 'supply_item_id', 'quantity'];

    public function request()
    {
        return $this->belongsTo(SupplyRequest::class, 'supply_request_id');
    }

    public function supplyItem()
    {
        return $this->belongsTo(SupplyItem::class, 'supply_item_id');
    }
}