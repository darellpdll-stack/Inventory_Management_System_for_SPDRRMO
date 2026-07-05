<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class SupplyItem extends Model
{
    protected $fillable = [
        'category_id', 'item_name', 'unit',
        'current_stock', 'minimum_stock', 'status', 'expiration_date',
    ];

    protected $casts = [
        'expiration_date' => 'date',
    ];

    /**
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLowStock($query)
    {
    return $query->whereColumn('current_stock', '<=', 'minimum_stock');
    }      
    public function scopeNeedsExpiryAttention($query)
    {
        
        return $query->whereNotNull('expiration_date')
                    ->whereDate('expiration_date', '<=', \Carbon\Carbon::now()->addMonths(3));
    }
    
    public function category()
    {
        return $this->belongsTo(SupplyCategory::class, 'category_id');
    }

    public function isLowStock(): bool
    {
        return $this->current_stock <= $this->minimum_stock;
    }

    // returns: 'none', 'safe', 'expiring', or 'expired'
    public function expiryStatus(): string
    {
        if (!$this->expiration_date) {
            return 'none';
        }

        if ($this->expiration_date->isPast()) {
            return 'expired';
        }

        // within 3 months of expiring
        if ($this->expiration_date->lessThanOrEqualTo(Carbon::now()->addMonths(3))) {
            return 'expiring';
        }

        return 'safe';
    }
}