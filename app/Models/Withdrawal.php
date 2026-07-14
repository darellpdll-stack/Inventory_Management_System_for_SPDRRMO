<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
{
    protected $fillable = [
        'withdrawn_by', 'date_withdrawn', 'date_returned', 'remark', 'recorded_by',
    ];

    protected $casts = [
        'date_withdrawn' => 'date',
        'date_returned' => 'date',
    ];

    public function items()
    {
        return $this->hasMany(WithdrawalItem::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}