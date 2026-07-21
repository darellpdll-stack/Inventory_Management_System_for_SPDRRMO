<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyItem extends Model
{
    protected $fillable = [
    'type', 'category_id', 'personnel_id', 'description',
    'property_no', 'quantity', 'unit', 'unit_value', 'on_hand_per_count', 'remarks',
];

    public function category()
    {
        return $this->belongsTo(PropertyCategory::class, 'category_id');
    }

    public function personnel()
    {
        return $this->belongsTo(Personnel::class, 'personnel_id');
    }

    public function propertyNoList(): array
{
    if ($this->quantity <= 1 || !$this->property_no) {
        return [$this->property_no ?? '—'];
    }

    if (preg_match('/^(.*?)(\d+)$/', $this->property_no, $m)) {
        $prefix = $m[1];
        $start = (int) $m[2];
        $pad = strlen($m[2]);
        $numbers = [];
        for ($i = 0; $i < $this->quantity; $i++) {
            $numbers[] = $prefix . str_pad($start + $i, $pad, '0', STR_PAD_LEFT);
        }
        return $numbers;
    }

    return [$this->property_no];
}

public function propertyNoRange(): string
{
    if ($this->quantity <= 1 || !$this->property_no) {
        return $this->property_no ?? '—';
    }

    if (preg_match('/^(.*?)(\d+)$/', $this->property_no, $m)) {
        $prefix = $m[1];
        $start = (int) $m[2];
        $pad = strlen($m[2]);
        $end = $start + $this->quantity - 1;
        return $prefix . str_pad($start, $pad, '0', STR_PAD_LEFT)
             . ' to ' . $prefix . str_pad($end, $pad, '0', STR_PAD_LEFT);
    }

    return $this->property_no ?? '—';
}
}