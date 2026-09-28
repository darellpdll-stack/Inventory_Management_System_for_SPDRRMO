<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockEntry extends Model
{
    protected $fillable = [
        'supply_item_id', 'date_received', 'delivered_by', 'reference_no',
        'quantity', 'expiration_date', 'remarks', 'recorded_by',
    ];

    protected $casts = [
        'date_received' => 'date',
        'expiration_date' => 'date',
    ];

    public function supplyItem()
    {
        return $this->belongsTo(SupplyItem::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}