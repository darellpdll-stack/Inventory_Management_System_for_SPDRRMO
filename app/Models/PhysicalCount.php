<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhysicalCount extends Model
{
    protected $fillable = [
        'count_date', 'status', 'remarks',
        'started_by', 'closed_at', 'closed_by',
    ];

    protected $casts = [
        'count_date' => 'date',
        'closed_at' => 'datetime',
    ];

    // e.g. PC-2026-0003
    public function countNo(): string
    {
        return 'PC-' . $this->created_at->format('Y') . '-' . str_pad($this->id, 4, '0', STR_PAD_LEFT);
    }

    public function scans()
    {
        return $this->hasMany(PhysicalCountScan::class);
    }

    public function startedBy()
    {
        return $this->belongsTo(User::class, 'started_by');
    }

    public function closedBy()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    // finished, and the results stand
    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    // given up on part-way; the scans are kept, but they are not a result
    public function isAbandoned(): bool
    {
        return $this->status === 'abandoned';
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'open' => 'In progress',
            'abandoned' => 'Abandoned',
            default => 'Closed',
        };
    }

    public function badgeClass(): string
    {
        return match ($this->status) {
            'open' => 'req-for_release',
            'abandoned' => 'req-declined',
            default => 'req-completed',
        };
    }

    // tags scanned that matched a real property record
    public function foundNumbers(): array
    {
        return $this->scans()
            ->whereNotNull('property_item_id')
            ->pluck('property_no')
            ->all();
    }

    // tags scanned that matched nothing in the records
    public function unexpectedScans()
    {
        return $this->scans()->whereNull('property_item_id');
    }
}
