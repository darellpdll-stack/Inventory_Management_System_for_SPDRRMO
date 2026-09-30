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
}