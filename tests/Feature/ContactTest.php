<?php

use App\Models\Inquiry;
use App\Models\Nation;
use App\Models\User;
use Database\Seeders\NationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// The contact form needs the seven nations in the database (the migration only creates the empty table).
beforeEach(fn () => $this->seed(NationSeeder::class));

function contactPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Ana Reyes',
        'email' => 'ana@example.com',
        'nation' => 'liyue',
        'message' => 'Hello, I would like a quote for a family of four.',
        'consent' => '1',
    ], $overrides);
}

function contactAdmin(): User
{
    $user = User::factory()->create();
    $user->forceFill(['role' => 'admin'])->save();

    return $user;
}

// ---- nations table -------------------------------------------------------------------------

it('seeds the seven nations from the data file and does not duplicate them', function () {
    expect(Nation::count())->toBe(7)
        ->and(Nation::where('slug', 'mondstadt')->value('nation_name'))->toBe('Mondstadt');

    $this->seed(NationSeeder::class);

    expect(Nation::count())->toBe(7);
});

// ---- the form on the home page (FR-30) -------------------------------------------------------

it('shows the contact form posting to the contact route, with honeypot and consent box', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('action="'.route('contact.store').'"', false)
        ->assertSee('name="website"', false)
        ->assertSee('name="consent"', false)
        ->assertSee('Privacy Notice');
});

it('shows the Privacy Notice page', function () {
    $this->get(route('privacy'))
        ->assertOk()
        ->assertSee('Privacy Notice')
        ->assertSee('Data Privacy Act');
});

// ---- sending a message (FR-31) ---------------------------------------------------------------

it('saves a valid message as a pending inquiry and thanks the visitor', function () {
    $this->post(route('contact.store'), contactPayload())
        ->assertRedirect(route('home').'#contact')
        ->assertSessionHas('contact_status');

    $inquiry = Inquiry::first();

    expect(Inquiry::count())->toBe(1)
        ->and($inquiry->name)->toBe('Ana Reyes')
        ->and($inquiry->email)->toBe('ana@example.com')
        ->and($inquiry->status)->toBe('pending')
        ->and($inquiry->subject)->toBe('Liyue')
        ->and($inquiry->nation_id)->toBe(Nation::where('slug', 'liyue')->value('id'))
        ->and($inquiry->consented_at)->not->toBeNull()
        ->and($inquiry->handled_by)->toBeNull();
});

it('shows the thank-you message on the home page after sending', function () {
    $this->followingRedirects()
        ->post(route('contact.store'), contactPayload())
        ->assertOk()
        ->assertSee('your message has been sent');
});

it('works for a visitor who is not logged in and for a logged-in customer', function () {
    $this->post(route('contact.store'), contactPayload(['email' => 'guest@example.com']))->assertSessionHasNoErrors();
    $this->actingAs(User::factory()->create())
        ->post(route('contact.store'), contactPayload(['email' => 'member@example.com']))
        ->assertSessionHasNoErrors();

    expect(Inquiry::count())->toBe(2);
});

it('records the subject for a custom itinerary and for other topics', function () {
    $this->post(route('contact.store'), contactPayload(['nation' => 'multi']));
    $this->post(route('contact.store'), contactPayload(['nation' => '']));

    $multi = Inquiry::where('subject', 'Custom itinerary')->first();
    $other = Inquiry::where('subject', 'Other')->first();

    expect($multi)->not->toBeNull()->and($multi->nation_id)->toBeNull()
        ->and($other)->not->toBeNull()->and($other->nation_id)->toBeNull();
});

it('tidies the name, email and message before saving', function () {
    $this->post(route('contact.store'), contactPayload([
        'name' => '  Ana Reyes  ',
        'email' => '  ANA@Example.COM ',
        'message' => "  Please send the Fontaine schedule.  \n",
    ]));

    $inquiry = Inquiry::first();

    expect($inquiry->name)->toBe('Ana Reyes')
        ->and($inquiry->email)->toBe('ana@example.com')
        ->and($inquiry->message)->toBe('Please send the Fontaine schedule.');
});

it('keeps the nation chosen on a nation page (custom itinerary link)', function () {
    $this->get(route('home', ['nation' => 'sumeru']))
        ->assertOk()
        ->assertSee('<option value="sumeru" selected>', false);
});

// ---- invalid input ---------------------------------------------------------------------------

it('refuses invalid messages, shows the error and saves nothing', function (array $overrides, string $field) {
    $this->post(route('contact.store'), contactPayload($overrides))
        ->assertRedirect(route('home').'#contact')
        ->assertSessionHasErrors($field);

    expect(Inquiry::count())->toBe(0);
})->with([
    'empty name' => [['name' => ''], 'name'],
    'empty email' => [['email' => ''], 'email'],
    'invalid email' => [['email' => 'not-an-email'], 'email'],
    'empty message' => [['message' => ''], 'message'],
    'message too short' => [['message' => 'Hi'], 'message'],
    'message too long' => [['message' => str_repeat('a', 2001)], 'message'],
    'consent not ticked' => [['consent' => ''], 'consent'],
    'unknown subject' => [['nation' => 'atlantis'], 'nation'],
    'honeypot filled in' => [['website' => 'http://spam.example'], 'website'],
]);

it('keeps what the visitor typed and shows the error banner when something is wrong', function () {
    $this->post(route('contact.store'), contactPayload(['email' => 'not-an-email']))
        ->assertSessionHasInput('name', 'Ana Reyes');

    $this->followingRedirects()
        ->post(route('contact.store'), contactPayload(['email' => 'not-an-email']))
        ->assertSee('Please check the highlighted fields');
});

it('does not show a thank-you inside the login pop-up (separate flash key)', function () {
    $this->post(route('contact.store'), contactPayload())
        ->assertSessionMissing('status');
});

it('limits how fast one visitor can send messages', function () {
    foreach (range(1, 5) as $i) {
        $this->post(route('contact.store'), contactPayload(['email' => "visitor{$i}@example.com"]))
            ->assertSessionHasNoErrors();
    }

    $this->post(route('contact.store'), contactPayload(['email' => 'visitor6@example.com']))
        ->assertStatus(429);

    expect(Inquiry::count())->toBe(5);
});

// ---- admin inbox -----------------------------------------------------------------------------

it('lists received messages for the administrator, newest first', function () {
    Inquiry::factory()->create([
        'name' => 'Older Sender', 'subject' => 'Liyue', 'message' => 'First question about Liyue',
        'created_at' => now()->subDay(),
    ]);
    Inquiry::factory()->create([
        'name' => 'Mika Santos', 'subject' => 'Inazuma', 'message' => 'Please send the Inazuma schedule',
        'created_at' => now(),
    ]);

    $this->actingAs(contactAdmin())
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Mika Santos')
        ->assertSee('Please send the Inazuma schedule')
        ->assertSee('2 messages')
        ->assertSeeInOrder(['Mika Santos', 'Older Sender']);
});

it('shows an empty inbox message when nobody has written yet', function () {
    $this->actingAs(contactAdmin())
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('No messages yet');
});

it('escapes message text in the admin inbox', function () {
    Inquiry::factory()->create(['message' => '<script>alert("x")</script> hello there']);

    $this->actingAs(contactAdmin())
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertDontSee('<script>alert("x")</script>', false);
});

it('keeps the inbox away from customers', function () {
    Inquiry::factory()->create(['name' => 'Private Sender']);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.dashboard'))
        ->assertForbidden()
        ->assertDontSee('Private Sender');
});

it('sends visitors who are not logged in away from the inbox', function () {
    Inquiry::factory()->create(['name' => 'Private Sender']);

    $this->get(route('admin.dashboard'))->assertRedirect();
});
