<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BorrowedUnit extends Model
{
    protected $fillable = [
        'borrowing_id', 'property_item_id', 'property_no',
        'returned_at', 'condition', 'received_by', 'remarks',
    ];

    protected $casts = [
        'returned_at' => 'date',
    ];

    public function borrowing()
    {
        return $this->belongsTo(Borrowing::class);
    }

    public function propertyItem()
    {
        return $this->belongsTo(PropertyItem::class);
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function isOut(): bool
    {
        return $this->returned_at === null;
    }

    // units that are physically with a borrower right now
    public function scopeStillOut($query)
    {
        return $query->whereNull('returned_at')
            ->whereHas('borrowing', fn ($b) => $b->where('status', 'released'));
    }

    // units spoken for by a live borrowing — reserved, ready, or already out.
    // a unit that is only reserved is still on the shelf, but it cannot be
    // promised to someone else, so availability is measured against this.
    public function scopeCommitted($query)
    {
        return $query->whereNull('returned_at')
            ->whereHas('borrowing', fn ($b) => $b->whereIn('status', ['pending', 'approved', 'released']));
    }
}
