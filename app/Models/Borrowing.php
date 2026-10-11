<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Borrowing extends Model
{
    protected $fillable = [
        'personnel_id', 'date_needed', 'date_due', 'purpose', 'status',
        'decline_reason', 'reviewed_by', 'reviewed_at',
        'date_released', 'released_by', 'remarks',
    ];

    protected $casts = [
        'date_needed' => 'date',
        'date_due' => 'date',
        'date_released' => 'date',
        'reviewed_at' => 'datetime',
    ];

    // e.g. BRW-2026-0003
    public function borrowNo(): string
    {
        return 'BRW-' . $this->created_at->format('Y') . '-' . str_pad($this->id, 4, '0', STR_PAD_LEFT);
    }

    public function personnel()
    {
        return $this->belongsTo(Personnel::class, 'personnel_id');
    }

    public function units()
    {
        return $this->hasMany(BorrowedUnit::class);
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function releasedBy()
    {
        return $this->belongsTo(User::class, 'released_by');
    }

    // units still with the borrower
    public function outstandingUnits()
    {
        return $this->units()->whereNull('returned_at');
    }

    public function isFullyReturned(): bool
    {
        return $this->units->every(fn ($unit) => $unit->returned_at !== null);
    }

    // when the last outstanding unit came back
    public function dateReturned()
    {
        if (! $this->isFullyReturned()) {
            return null;
        }

        return $this->units->max('returned_at');
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'released' => 'Borrowed',
            default => ucfirst($this->status),
        };
    }

    // reuses the request badge colours so there is no new CSS to maintain
    public function badgeClass(): string
    {
        return match ($this->status) {
            'pending' => 'req-pending',
            'approved' => 'req-approved',
            'released' => 'req-for_release',
            'returned' => 'req-completed',
            'declined' => 'req-declined',
            default => 'req-pending',
        };
    }

    // the units grouped by property item, for display and for the Borrower's Form
    public function groupedUnits()
    {
        return $this->units
            ->groupBy('property_item_id')
            ->map(function ($units) {
                $first = $units->first();

                return (object) [
                    'item' => $first->propertyItem,
                    'quantity' => $units->count(),
                    'numbers' => $units->pluck('property_no')->filter()->values(),
                    'returned' => $units->whereNotNull('returned_at')->count(),
                ];
            })
            ->values();
    }
}
