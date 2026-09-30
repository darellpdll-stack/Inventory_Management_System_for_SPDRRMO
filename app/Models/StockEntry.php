<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockEntry extends Model
{
    protected $fillable = [
        'supply_item_id', 'date_received', 'delivered_by', 'reference_no',
        'quantity', 'expiration_date', 'remarks', 'recorded_by', 'remaining_quantity',
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

        public function isEmpty(): bool
    {
        return $this->remaining_quantity <= 0;
    }

    public function expiryStatus(): string
    {
        if (!$this->expiration_date) {
            return 'none';
        }
        if ($this->expiration_date->isPast()) {
            return 'expired';
        }
        if ($this->expiration_date->lte(now()->addMonths(3))) {
            return 'expiring';
        }
        return 'safe';
    }
}