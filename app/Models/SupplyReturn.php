<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplyReturn extends Model
{
    protected $fillable = [
        'release_item_id', 'supply_item_id', 'quantity', 'date_returned',
        'returned_by', 'condition', 'stock_entry_id', 'remarks', 'received_by',
    ];

    protected $casts = [
        'date_returned' => 'date',
    ];

    public function releaseItem()
    {
        return $this->belongsTo(ReleaseItem::class);
    }

    public function supplyItem()
    {
        return $this->belongsTo(SupplyItem::class);
    }

    public function stockEntry()
    {
        return $this->belongsTo(StockEntry::class);
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function isUsable(): bool
    {
        return $this->condition === 'usable';
    }
}