<?php

use App\Models\Booking;
use App\Services\NationCatalog;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function bookingPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Ana Reyes',
        'email' => 'ana@example.com',
        'phone' => '+63 917 123 4567',
        'nation' => 'mondstadt',
        'itinerary' => 'custom',
        'check_in' => CarbonImmutable::today()->addDays(10)->toDateString(),
        'days' => 3,
    ], $overrides);
}

/** Fill every slot of $days days starting at $checkIn. */
function fillDays(string $checkIn, int $days, string $status = 'confirmed'): void
{
    Booking::factory()
        ->count((int) config('booking.daily_capacity'))
        ->stay($checkIn, $days)
        ->state(['status' => $status])
        ->create();
}

it('shows the booking page', function () {
    $this->get(route('booking'))
        ->assertOk()
        ->assertSee('Choose your dates and stay length');
});

it('reports the slots left on each day', function () {
    $day = CarbonImmutable::today()->addDays(5);
    Booking::factory()->count(3)->stay($day->toDateString(), 2)->create();

    $this->getJson(route('booking.availability', [
        'from' => $day->toDateString(),
        'to' => $day->addDays(2)->toDateString(),
    ]))
        ->assertOk()
        ->assertJsonPath('data.'.$day->toDateString(), config('booking.daily_capacity') - 3)
        ->assertJsonPath('data.'.$day->addDay()->toDateString(), config('booking.daily_capacity') - 3)
        ->assertJsonPath('data.'.$day->addDays(2)->toDateString(), config('booking.daily_capacity'));
});

it('books a package stay and confirms it straight away', function () {
    $checkIn = CarbonImmutable::today()->addDays(10);

    $this->postJson(route('booking.store'), bookingPayload(['check_in' => $checkIn->toDateString(), 'days' => 3]))
        ->assertCreated()
        ->assertJsonPath('status', 'confirmed')
        ->assertJsonPath('nights', 2)
        ->assertJsonPath('check_out', $checkIn->addDays(2)->toDateString());

    $this->assertDatabaseHas('bookings', [
        'email' => 'ana@example.com',
        'check_in' => $checkIn->toDateString(),
        'check_out' => $checkIn->addDays(2)->toDateString(),
        'is_custom' => false,
        'status' => 'confirmed',
    ]);
});

it('keeps custom stays over the package length pending for the team', function () {
    $this->postJson(route('booking.store'), bookingPayload(['days' => 9]))
        ->assertCreated()
        ->assertJsonPath('status', 'pending');

    $this->assertDatabaseHas('bookings', ['days' => 9, 'is_custom' => true, 'status' => 'pending']);
});

it('rejects a stay that runs into a fully booked day', function () {
    $checkIn = CarbonImmutable::today()->addDays(10);
    fillDays($checkIn->addDay()->toDateString(), 1);

    $this->postJson(route('booking.store'), bookingPayload(['check_in' => $checkIn->toDateString(), 'days' => 3]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('check_in');

    $this->assertDatabaseCount('bookings', config('booking.daily_capacity'));
});

it('also needs a free slot on the check-out day', function () {
    $checkIn = CarbonImmutable::today()->addDays(10);
    fillDays($checkIn->addDays(2)->toDateString(), 1);   // the 3rd day = check-out day

    $this->postJson(route('booking.store'), bookingPayload(['check_in' => $checkIn->toDateString(), 'days' => 3]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('check_in');
});

it('still allows a stay that only touches limited days', function () {
    $checkIn = CarbonImmutable::today()->addDays(10);
    Booking::factory()->count(config('booking.daily_capacity') - 1)->stay($checkIn->toDateString(), 3)->create();

    $this->postJson(route('booking.store'), bookingPayload(['check_in' => $checkIn->toDateString(), 'days' => 3]))
        ->assertCreated();
});

it('does not count cancelled bookings against availability', function () {
    $checkIn = CarbonImmutable::today()->addDays(10);
    fillDays($checkIn->toDateString(), 3, 'cancelled');

    $this->postJson(route('booking.store'), bookingPayload(['check_in' => $checkIn->toDateString(), 'days' => 3]))
        ->assertCreated();
});

it('rejects stay lengths outside the allowed range', function (int $days) {
    $this->postJson(route('booking.store'), bookingPayload(['days' => $days]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('days');
})->with([1, 15]);

it('rejects check-in days outside the booking window', function (string $offset) {
    $checkIn = CarbonImmutable::today()->modify($offset)->toDateString();

    $this->postJson(route('booking.store'), bookingPayload(['check_in' => $checkIn]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('check_in');
})->with(['yesterday' => '-1 day', 'today' => '+0 days', 'too far ahead' => '+2 years']);

it('requires the traveller details', function () {
    $this->postJson(route('booking.store'), bookingPayload(['name' => '', 'email' => 'nope', 'phone' => 'abc']))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'email', 'phone']);
});

it('turns away the same email booking the same dates twice', function () {
    $payload = bookingPayload();

    $this->postJson(route('booking.store'), $payload)->assertCreated();
    $this->postJson(route('booking.store'), $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors('email');

    $this->assertDatabaseCount('bookings', 1);
});

it('treats the same email in a different case as the same person', function () {
    $this->postJson(route('booking.store'), bookingPayload(['email' => 'ana@example.com']))->assertCreated();

    $this->postJson(route('booking.store'), bookingPayload(['email' => 'ANA@Example.com']))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('email');
});

it('caps the upcoming bookings one email can hold', function () {
    config(['booking.max_upcoming_per_email' => 2]);
    $start = CarbonImmutable::today()->addDays(10);

    foreach ([0, 10] as $offset) {
        $this->postJson(route('booking.store'), bookingPayload(['check_in' => $start->addDays($offset)->toDateString()]))->assertCreated();
    }

    $this->postJson(route('booking.store'), bookingPayload(['check_in' => $start->addDays(20)->toDateString()]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('email');
});

it('rejects submissions that fill in the hidden honeypot field', function () {
    $this->postJson(route('booking.store'), bookingPayload(['bk_extra' => 'https://spam.example']))
        ->assertUnprocessable();

    $this->assertDatabaseCount('bookings', 0);
});

it('hands out a fresh csrf token for retrying after the session expired', function () {
    $this->getJson(route('booking.csrf'))
        ->assertOk()
        ->assertJsonStructure(['token'])
        ->assertHeader('Cache-Control');
});


it('books a listed itinerary and keeps its nation and title', function () {
    $itinerary = app(NationCatalog::class)->itinerary('liyue-0');

    $this->postJson(route('booking.store'), bookingPayload([
        'nation' => 'liyue',
        'itinerary' => 'liyue-0',
        'days' => $itinerary['days'],
    ]))
        ->assertCreated()
        ->assertJsonPath('nation', 'Liyue')
        ->assertJsonPath('itinerary_title', $itinerary['title']);

    $this->assertDatabaseHas('bookings', ['nation' => 'liyue', 'itinerary' => 'liyue-0', 'itinerary_title' => $itinerary['title']]);
});

it('saves a custom route without an itinerary', function () {
    $this->postJson(route('booking.store'), bookingPayload(['nation' => 'natlan', 'itinerary' => 'custom']))
        ->assertCreated()
        ->assertJsonPath('itinerary_title', 'Custom route');

    $this->assertDatabaseHas('bookings', ['nation' => 'natlan', 'itinerary' => null, 'itinerary_title' => null]);
});

it('requires a nation and an itinerary choice', function () {
    $this->postJson(route('booking.store'), bookingPayload(['nation' => '', 'itinerary' => '']))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['nation', 'itinerary']);
});

it('rejects an unknown nation', function () {
    $this->postJson(route('booking.store'), bookingPayload(['nation' => 'atlantis']))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('nation');
});

it('rejects an itinerary that belongs to a different nation', function () {
    $this->postJson(route('booking.store'), bookingPayload(['nation' => 'mondstadt', 'itinerary' => 'liyue-0', 'days' => 4]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('itinerary');
});

it('rejects a stay length that does not match the itinerary', function () {
    $days = app(NationCatalog::class)->itinerary('liyue-0')['days'];

    $this->postJson(route('booking.store'), bookingPayload(['nation' => 'liyue', 'itinerary' => 'liyue-0', 'days' => $days + 1]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('days');
});

it('opens the booking page with the destination pre-selected from a link', function () {
    $this->get(route('booking', ['itinerary' => 'liyue-featured']))->assertOk();
    $this->get(route('booking', ['nation' => 'sumeru']))->assertOk();
    $this->get(route('booking', ['itinerary' => 'not-real']))->assertOk();
});

it('resolves itinerary ids, including the home page featured ids', function () {
    $catalog = app(NationCatalog::class);

    expect($catalog->itinerary('liyue-featured')['id'])->toBe('liyue-0')
        ->and($catalog->itinerary('liyue-9'))->toBeNull()
        ->and($catalog->itinerary('nope-0'))->toBeNull()
        ->and($catalog->name('fontaine'))->toBe('Fontaine');
});
