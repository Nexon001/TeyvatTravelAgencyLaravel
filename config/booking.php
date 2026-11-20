<?php

/*
|--------------------------------------------------------------------------
| Booking settings
|--------------------------------------------------------------------------
| Every booking uses one slot on each day of its stay. A day with no slots
| left is "full" (red); a day with 1-2 slots left is "limited" (orange).
*/

return [

    // Bookings that can overlap on a single day before it is fully booked.
    'daily_capacity' => (int) env('BOOKING_DAILY_CAPACITY', 5),

    // A day with this many slots left (or fewer) is shown as "limited".
    'limited_threshold' => 2,

    // Shortest stay we offer, and the longest ready-made package (in days).
    'min_days' => 2,
    'max_package_days' => 6,

    // Longest custom stay a traveller can request. Anything over
    // max_package_days is saved as "pending" until our team confirms it.
    'max_custom_days' => (int) env('BOOKING_MAX_CUSTOM_DAYS', 14),

    // Upcoming (not yet finished) bookings one email address may hold at a time.
    // Keeps a single person from filling the calendar with pending or fake bookings.
    'max_upcoming_per_email' => (int) env('BOOKING_MAX_PER_EMAIL', 3),

    // Earliest check-in is today + lead_days; latest is horizon_months ahead.
    'lead_days' => 1,
    'horizon_months' => 12,

];
