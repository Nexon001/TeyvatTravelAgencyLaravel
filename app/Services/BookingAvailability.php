<?php

namespace App\Services;

use App\Models\Booking;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class BookingAvailability
{
    public function earliestBookable(): CarbonImmutable
    {
        return CarbonImmutable::today()->addDays((int) config('booking.lead_days'));
    }

    public function latestBookable(): CarbonImmutable
    {
        return CarbonImmutable::today()->addMonths((int) config('booking.horizon_months'));
    }

    /**
     * Slots still free on each day from $from to $to (inclusive), keyed by Y-m-d.
     *
     * @return array<string, int>
     */
    public function remainingByDate(CarbonImmutable $from, CarbonImmutable $to): array
    {
        $capacity = (int) config('booking.daily_capacity');
        $taken = [];

        Booking::query()
            ->active()
            ->overlapping($from->toDateString(), $to->toDateString())
            ->get(['check_in', 'check_out'])
            ->each(function (Booking $booking) use (&$taken, $from, $to) {
                $start = CarbonImmutable::parse($booking->check_in)->max($from);
                $end = CarbonImmutable::parse($booking->check_out)->min($to);

                foreach (CarbonPeriod::create($start, $end) as $day) {
                    $key = $day->toDateString();
                    $taken[$key] = ($taken[$key] ?? 0) + 1;
                }
            });

        $remaining = [];

        foreach (CarbonPeriod::create($from, $to) as $day) {
            $key = $day->toDateString();
            $remaining[$key] = max(0, $capacity - ($taken[$key] ?? 0));
        }

        return $remaining;
    }

    /**
     * Throws a validation error on "check_in" when any day of the stay is
     * fully booked or outside the booking window. Every day of the stay,
     * including the check-out day, needs a free slot.
     *
     * @throws ValidationException
     */
    public function ensureAvailable(CarbonImmutable $checkIn, int $days): void
    {
        $checkOut = $checkIn->addDays($days - 1);
        $earliest = $this->earliestBookable();
        $latest = $this->latestBookable();

        $full = [];
        $outside = false;

        foreach ($this->remainingByDate($checkIn, $checkOut) as $date => $left) {
            $day = CarbonImmutable::parse($date);

            if ($day->lt($earliest) || $day->gt($latest)) {
                $outside = true;
            } elseif ($left <= 0) {
                $full[] = $day->format('M j');
            }
        }

        if ($outside) {
            throw ValidationException::withMessages([
                'check_in' => 'Those dates are outside the booking window. Choose dates from '
                    .$earliest->format('M j, Y').' to '.$latest->format('M j, Y').'.',
            ]);
        }

        if ($full !== []) {
            throw ValidationException::withMessages([
                'check_in' => Arr::join($full, ', ', ' and ').(count($full) === 1 ? ' is' : ' are')
                    .' fully booked. Choose a shorter stay or different dates.',
            ]);
        }
    }
}
