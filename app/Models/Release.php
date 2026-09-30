<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Release extends Model
{
    protected $fillable = [
        'supply_request_id', 'date_released', 'received_by', 'remarks', 'released_by',
    ];

    protected $casts = ['date_released' => 'date'];

    public function request()
    {
        return $this->belongsTo(SupplyRequest::class, 'supply_request_id');
    }

    public function items()
    {
        return $this->hasMany(ReleaseItem::class);
    }

    public function releasedBy()
    {
        return $this->belongsTo(User::class, 'released_by');
    }
}