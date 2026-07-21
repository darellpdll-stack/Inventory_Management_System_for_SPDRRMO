<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyCategory extends Model
{
    protected $fillable = ['name', 'code'];

    public function items()
    {
        return $this->hasMany(PropertyItem::class, 'category_id');
    }
}