<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplyRequest extends Model
{
    protected $fillable = [
        'personnel_id', 'status', 'purpose',
        'decline_reason', 'reviewed_by', 'reviewed_at', 'withdrawal_id',
    ];

    protected $casts = ['reviewed_at' => 'datetime'];

    public function personnel()
    {
        return $this->belongsTo(Personnel::class, 'personnel_id');
    }

    public function items()
    {
        return $this->hasMany(SupplyRequestItem::class);
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function withdrawal()
    {
        return $this->belongsTo(Withdrawal::class, 'withdrawal_id');
    }
}