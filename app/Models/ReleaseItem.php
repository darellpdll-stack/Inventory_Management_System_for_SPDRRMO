<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReleaseItem extends Model
{
    protected $fillable = ['release_id', 'supply_item_id', 'quantity'];

    public function release()
    {
        return $this->belongsTo(Release::class);
    }

    public function supplyItem()
    {
        return $this->belongsTo(SupplyItem::class);
    }

        public function returns()
    {
        return $this->hasMany(SupplyReturn::class);
    }

    // how much of this released line has already come back
    public function returnedQuantity(): int
    {
        return (int) $this->returns()->sum('quantity');
    }

    // how much can still be returned
    public function returnableQuantity(): int
    {
        return max(0, $this->quantity - $this->returnedQuantity());
    }
}