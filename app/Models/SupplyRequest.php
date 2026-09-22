<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplyRequest extends Model
{
    protected $fillable = [
        'personnel_id', 'request_date', 'status', 'purpose',
        'decline_reason', 'reviewed_by', 'reviewed_at', 'withdrawal_id',
    ];

    protected $casts = [
        'request_date' => 'date',
        'reviewed_at' => 'datetime',
    ];

    // e.g. REQ-2026-0003
    public function requestNo(): string
    {
        return 'REQ-' . $this->created_at->format('Y') . '-' . str_pad($this->id, 4, '0', STR_PAD_LEFT);
    }

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