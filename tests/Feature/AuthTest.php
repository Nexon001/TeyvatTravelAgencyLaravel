<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

/** A user with the given role. The role is set with forceFill because it is not mass assignable. */
function makeUser(string $role = 'customer', array $attributes = []): User
{
    $user = User::factory()->create(array_merge([
        'email' => $role.'@example.com',
        'password' => Hash::make('right-pass-1'),
    ], $attributes));

    $user->forceFill(['role' => $role])->save();

    return $user;
}

function registrationPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Ana Reyes',
        'email' => 'ana@example.com',
        'password' => 'long-secret-1',
        'password_confirmation' => 'long-secret-1',
    ], $overrides);
}

// ---------- login ----------

it('opens the login and sign-up pop-up from /login and /register', function () {
    $this->get(route('login'))->assertRedirect(route('home', ['auth' => 'login']));
    $this->get(route('register'))->assertRedirect(route('home', ['auth' => 'signup']));
});

it('puts the pop-up in the page for visitors only', function () {
    $this->get(route('home'))->assertSee('id="login-dialog"', false)->assertSee('name="email"', false);

    $this->actingAs(makeUser('customer'))->get(route('home'))->assertDontSee('id="login-dialog"', false);
});

it('answers the pop-up with JSON: stay on the page after a customer logs in', function () {
    makeUser('customer');

    $this->postJson(route('login.store'), ['email' => 'customer@example.com', 'password' => 'right-pass-1'])
        ->assertOk()
        ->assertExactJson(['redirect' => null]);

    $this->assertAuthenticated();
});

it('answers the pop-up with JSON: an administrator goes to the dashboard', function () {
    makeUser('admin');

    $this->postJson(route('login.store'), ['email' => 'admin@example.com', 'password' => 'right-pass-1'])
        ->assertOk()
        ->assertExactJson(['redirect' => route('admin.dashboard')]);
});

it('answers the pop-up with field errors as JSON', function () {
    makeUser('customer');

    $this->postJson(route('login.store'), ['email' => 'customer@example.com', 'password' => 'wrong'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['email']);

    $this->assertGuest();
});

it('returns a visitor to the page they were sent away from after logging in', function () {
    makeUser('admin');

    $this->get(route('admin.dashboard'))->assertRedirect(route('login'));

    $this->postJson(route('login.store'), ['email' => 'admin@example.com', 'password' => 'right-pass-1'])
        ->assertOk()
        ->assertExactJson(['redirect' => route('admin.dashboard')]);
});

it('registers through the pop-up and answers with JSON', function () {
    $this->postJson(route('register.store'), registrationPayload())
        ->assertOk()
        ->assertExactJson(['redirect' => null]);

    $this->assertAuthenticated();
});

it('answers a bad sign-up with field errors as JSON', function () {
    $this->postJson(route('register.store'), registrationPayload(['email' => 'not-an-email']))
        ->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

it('logs a customer in with email and password', function () {
    makeUser('customer');

    $this->post(route('login.store'), ['email' => 'customer@example.com', 'password' => 'right-pass-1'])
        ->assertRedirect(route('home'));

    $this->assertAuthenticated();
});

it('treats the email as case-insensitive when logging in', function () {
    makeUser('customer');

    $this->post(route('login.store'), ['email' => '  CUSTOMER@Example.com ', 'password' => 'right-pass-1'])
        ->assertRedirect(route('home'));

    $this->assertAuthenticated();
});

it('rejects a wrong password with a message that does not reveal the account', function () {
    makeUser('customer');

    $this->post(route('login.store'), ['email' => 'customer@example.com', 'password' => 'wrong'])
        ->assertSessionHasErrors(['email' => 'These credentials do not match our records.']);

    $this->post(route('login.store'), ['email' => 'nobody@example.com', 'password' => 'wrong'])
        ->assertSessionHasErrors(['email' => 'These credentials do not match our records.']);

    $this->assertGuest();
});

it('requires an email and a password', function () {
    $this->post(route('login.store'), [])->assertSessionHasErrors(['email', 'password']);
});

it('locks the login for a while after 5 failed attempts', function () {
    makeUser('customer');

    foreach (range(1, 5) as $attempt) {
        $this->post(route('login.store'), ['email' => 'customer@example.com', 'password' => 'wrong']);
    }

    // Even the right password is refused while locked.
    $this->post(route('login.store'), ['email' => 'customer@example.com', 'password' => 'right-pass-1'])
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('sends a logged-in user away from the login page', function () {
    $this->actingAs(makeUser('customer'))->get(route('login'))->assertRedirect();
});

// ---------- FR-03 register ----------

it('registers a customer, hashes the password and logs them in', function () {
    $this->post(route('register.store'), registrationPayload())->assertRedirect(route('home'));

    $user = User::where('email', 'ana@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->name)->toBe('Ana Reyes')
        ->and($user->role)->toBe('customer')
        ->and($user->password)->not->toBe('long-secret-1')
        ->and(Hash::check('long-secret-1', $user->password))->toBeTrue();

    $this->assertAuthenticatedAs($user);
});

it('never lets the sign-up form choose a role', function () {
    $this->post(route('register.store'), registrationPayload(['role' => 'admin']));

    expect(User::where('email', 'ana@example.com')->first()->role)->toBe('customer');
});

it('rejects a duplicate email, whatever its case', function () {
    makeUser('customer', ['email' => 'ana@example.com']);

    $this->post(route('register.store'), registrationPayload(['email' => 'ANA@example.com']))
        ->assertSessionHasErrors('email');

    expect(User::count())->toBe(1);
});

it('rejects a short or unconfirmed password', function () {
    $this->post(route('register.store'), registrationPayload(['password' => 'short', 'password_confirmation' => 'short']))
        ->assertSessionHasErrors('password');

    $this->post(route('register.store'), registrationPayload(['password_confirmation' => 'something-else-1']))
        ->assertSessionHasErrors('password');

    expect(User::count())->toBe(0);
});

it('requires a name and a valid email to register', function () {
    $this->post(route('register.store'), registrationPayload(['name' => '', 'email' => 'not-an-email']))
        ->assertSessionHasErrors(['name', 'email']);
});

it('rejects a sign-up that fills in the hidden honeypot field', function () {
    $this->post(route('register.store'), registrationPayload(['hp_website' => 'http://spam.example']))
        ->assertSessionHasErrors('hp_website');

    expect(User::count())->toBe(0);
});

// ---------- logout ----------

it('logs the user out and ends the session', function () {
    $this->actingAs(makeUser('customer'))
        ->post(route('logout'))
        ->assertRedirect(route('home'));

    $this->assertGuest();
});

it('only logs out with a POST request', function () {
    $this->actingAs(makeUser('customer'))->get('/logout')->assertStatus(405);

    $this->assertAuthenticated();
});

it('shows Log out in the menu when logged in and Log in / Sign up when not', function () {
    $this->get(route('home'))->assertSee('Log in / Sign up')->assertDontSee('Log out');

    $this->actingAs(makeUser('customer'))->get(route('home'))->assertSee('Log out')->assertDontSee('Log in / Sign up');
});

// ---------- FR-32 admin login ----------

it('sends an administrator to the dashboard after logging in', function () {
    makeUser('admin');

    $this->post(route('login.store'), ['email' => 'admin@example.com', 'password' => 'right-pass-1'])
        ->assertRedirect(route('admin.dashboard'));

    $this->assertAuthenticated();
});

it('shows the dashboard to an administrator', function () {
    $response = $this->actingAs(makeUser('admin'))->get(route('admin.dashboard'));

    $response->assertOk()
        ->assertSee('Hello Director!')
        ->assertSee('class="admin-greeting"', false)
        ->assertDontSee('Signed in as')
        ->assertDontSee('without opening an admin page')
        ->assertSee('id="admin-tab-home"', false)
        ->assertSee('id="admin-panel-home"', false)
        ->assertSee('Best-selling itineraries')
        ->assertDontSee('Demo data')
        ->assertSee('class="admin-stat-grid" data-count-up', false)
        ->assertSee('id="admin-tab-bookings"', false)
        ->assertSee('id="admin-tab-itineraries"', false)
        ->assertSee('id="admin-tab-inquiries"', false)
        ->assertSee('id="admin-tab-customers"', false)
        ->assertSee('for="booking-search"', false)
        ->assertSee('for="itinerary-title"', false)
        ->assertSee('id="itinerary-price"', false)
        ->assertSee('type="number" min="0" step="100" placeholder="12500"', false)
        ->assertSee('for="inquiry-email"', false)
        ->assertSee('for="customer-search"', false);

    // Back button after logout must not show a cached admin page.
    expect($response->headers->get('Cache-Control'))->toContain('no-store');
});

// ---------- FR-33 access control ----------

it('sends a visitor who is not logged in from the admin area to the login page', function () {
    $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
});

it('blocks a logged-in customer from the admin area', function () {
    $this->actingAs(makeUser('customer'))->get(route('admin.dashboard'))->assertForbidden();
});

it('blocks an account with an unknown role from the admin area', function () {
    $this->actingAs(makeUser('staff'))->get(route('admin.dashboard'))->assertForbidden();
});

// ---------- idle expiry ----------

it('logs an administrator out after being idle for too long', function () {
    config(['admin.idle_minutes' => 30]);

    $this->actingAs(makeUser('admin'))
        ->withSession(['admin_last_activity' => now()->subMinutes(31)->timestamp])
        ->get(route('admin.dashboard'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
});

it('keeps an administrator who was active recently', function () {
    config(['admin.idle_minutes' => 30]);

    $this->actingAs(makeUser('admin'))
        ->withSession(['admin_last_activity' => now()->subMinutes(5)->timestamp])
        ->get(route('admin.dashboard'))
        ->assertOk();

    $this->assertAuthenticated();
});

it('tells the administrator why they were logged out', function () {
    config(['admin.idle_minutes' => 30]);

    $this->actingAs(makeUser('admin'))
        ->withSession(['admin_last_activity' => now()->subMinutes(31)->timestamp])
        ->get(route('admin.dashboard'))
        ->assertSessionHas('status');
});

// ---------- admin:create command ----------

it('creates an administrator from the command line', function () {
    $this->artisan('admin:create', ['email' => 'Boss@Example.com', '--name' => 'Boss'])
        ->expectsQuestion('Password (min 8 characters)', 'long-secret-1')
        ->expectsQuestion('Confirm password', 'long-secret-1')
        ->assertSuccessful();

    $admin = User::where('email', 'boss@example.com')->first();

    expect($admin->role)->toBe('admin')
        ->and($admin->name)->toBe('Boss')
        ->and(Hash::check('long-secret-1', $admin->password))->toBeTrue();
});

it('refuses mismatched passwords in the admin:create command', function () {
    $this->artisan('admin:create', ['email' => 'boss@example.com'])
        ->expectsQuestion('Password (min 8 characters)', 'long-secret-1')
        ->expectsQuestion('Confirm password', 'different-1')
        ->assertFailed();

    expect(User::count())->toBe(0);
});

it('can promote an existing customer to administrator', function () {
    makeUser('customer', ['email' => 'ana@example.com']);

    $this->artisan('admin:create', ['email' => 'ana@example.com'])
        ->expectsConfirmation('ana@example.com already has a customer account. Make it an administrator?', 'yes')
        ->assertSuccessful();

    expect(User::where('email', 'ana@example.com')->first()->role)->toBe('admin');
});
