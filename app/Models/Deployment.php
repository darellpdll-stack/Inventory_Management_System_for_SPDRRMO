<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deployment extends Model
{
    protected $fillable = [
        'destination', 'purpose', 'authorized_by',
        'released_by', 'status', 'deployed_at',
    ];

    protected $casts = [
        'deployed_at' => 'date',
    ];

    public function items()
    {
        return $this->hasMany(DeploymentItem::class);
    }

    public function releasedBy()
    {
        return $this->belongsTo(User::class, 'released_by');
    }
}