<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyItem extends Model
{
    protected $fillable = [
        'type', 'category_id', 'personnel_id', 'description',
        'property_no', 'unit', 'unit_value', 'on_hand_per_count', 'remarks',
    ];

    public function category()
    {
        return $this->belongsTo(PropertyCategory::class, 'category_id');
    }

    public function personnel()
    {
        return $this->belongsTo(Personnel::class, 'personnel_id');
    }
}