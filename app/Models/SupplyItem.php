<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SupplyItem extends Model
{
    protected $fillable = [
        'category_id', 'product_code', 'stock_no', 'description',
        'unit', 'unit_value', 'balance_per_card', 'on_hand_per_count',
        'minimum_stock', 'expiration_date', 'remarks',
    ];

    protected $casts = [
        'expiration_date' => 'date',
    ];

    public function category()
    {
        return $this->belongsTo(SupplyCategory::class, 'category_id');
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('balance_per_card', '<=', 'minimum_stock');
    }

    public function scopeNeedsExpiryAttention($query)
    {
        return $query->whereNotNull('expiration_date')
                     ->whereDate('expiration_date', '<=', Carbon::now()->addMonths(3));
    }

    public function isLowStock(): bool
    {
        return $this->balance_per_card <= $this->minimum_stock;
    }

    public function expiryStatus(): string
    {
        if (!$this->expiration_date) return 'none';
        if ($this->expiration_date->isPast()) return 'expired';
        if ($this->expiration_date->lessThanOrEqualTo(Carbon::now()->addMonths(3))) return 'expiring';
        return 'safe';
    }

    // shortage/overage calculated, not stored
    public function shortageQty(): ?int
    {
        if ($this->on_hand_per_count === null) return null;
        return $this->on_hand_per_count - $this->balance_per_card;
    }

    public function shortageValue(): ?float
    {
        $qty = $this->shortageQty();
        return $qty === null ? null : $qty * (float) $this->unit_value;
    }
    
    public function withdrawalItems()
    {
        return $this->hasMany(WithdrawalItem::class, 'supply_item_id');
    }
}