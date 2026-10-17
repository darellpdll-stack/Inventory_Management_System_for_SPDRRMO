<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhysicalCountScan extends Model
{
    protected $fillable = [
        'physical_count_id', 'property_no', 'property_item_id', 'scanned_by',
    ];

    public function physicalCount()
    {
        return $this->belongsTo(PhysicalCount::class);
    }

    public function propertyItem()
    {
        return $this->belongsTo(PropertyItem::class);
    }

    public function scannedBy()
    {
        return $this->belongsTo(User::class, 'scanned_by');
    }

    public function isUnexpected(): bool
    {
        return $this->property_item_id === null;
    }
}
