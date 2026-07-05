<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplyCategory extends Model
{
    protected $fillable = ['name', 'description'];

    public function items()
    {
        return $this->hasMany(SupplyItem::class, 'category_id');
    }
}