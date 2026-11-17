<?php

namespace Database\Seeders;

use App\Models\Booking;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

/**
 * Optional demo data so the calendar shows red (full) and orange (limited) days.
 * Run with:  php artisan db:seed --class=BookingDemoSeeder
 * Dates are relative to today, so re-run it whenever you want fresh examples.
 */
class BookingDemoSeeder extends Seeder
{
    public function run(): void
    {
        $capacity = (int) config('booking.daily_capacity');
        $today = CarbonImmutable::today();

        // [days from today, stay length, number of bookings]
        $stays = [
            [4, 2, $capacity],          // fully booked
            [9, 3, $capacity - 2],      // 2 slots left
            [15, 2, $capacity - 1],     // 1 slot left
            [20, 2, $capacity],         // fully booked
            [24, 3, $capacity - 2],     // 2 slots left
        ];

        foreach ($stays as [$offset, $length, $count]) {
            Booking::factory()
                ->count($count)
                ->stay($today->addDays($offset)->toDateString(), $length)
                ->create();
        }
    }
}
