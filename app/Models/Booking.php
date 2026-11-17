<?php

namespace App\Models;

use Database\Factories\BookingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'reference', 'name', 'email', 'phone', 'check_in', 'check_out',
    'days', 'is_custom', 'status', 'nation', 'itinerary', 'itinerary_title',
])]
class Booking extends Model
{
    /** @use HasFactory<BookingFactory> */
    use HasFactory;

    /**
     * Dates are kept as plain Y-m-d strings (no datetime cast) on purpose.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_custom' => 'boolean',
            'days' => 'integer',
        ];
    }

    protected function nights(): Attribute
    {
        return Attribute::get(fn () => max(0, $this->days - 1));
    }

    /** Cancelled bookings no longer hold a slot. */
    public function scopeActive(Builder $query): void
    {
        $query->where('status', '!=', 'cancelled');
    }

    /** Bookings whose stay touches any day between $from and $to (Y-m-d, inclusive). */
    public function scopeOverlapping(Builder $query, string $from, string $to): void
    {
        $query->where('check_in', '<=', $to)->where('check_out', '>=', $from);
    }
}
