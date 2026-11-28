<?php

use App\Mail\InquiryReplyMail;
use App\Models\Inquiry;
use App\Models\InquiryReply;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

function inboxAdmin(): User
{
    $user = User::factory()->create(['name' => 'Director Test']);
    $user->forceFill(['role' => 'admin'])->save();

    return $user;
}

function replyPayload(array $overrides = []): array
{
    return array_merge([
        'subject' => 'Re: Your Liyue inquiry',
        'body' => 'Thanks for asking about Liyue. We have a 4-day trip in March.',
    ], $overrides);
}

// ---- picking a message fills the reply form -------------------------------------------------

it('gives every message the data the reply form needs, with a locked recipient field', function () {
    $inquiry = Inquiry::factory()->create(['name' => 'Ana Reyes', 'email' => 'ana@example.com', 'subject' => 'Liyue']);

    $this->actingAs(inboxAdmin())
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('data-inquiry-id="'.$inquiry->id.'"', false)
        ->assertSee('data-email="ana@example.com"', false)
        ->assertSee('data-reply-subject="Re: Your Liyue inquiry"', false)
        ->assertSee('data-reply-url="'.route('admin.inquiries.reply', $inquiry).'"', false)
        ->assertSee('data-reply-form', false)
        ->assertSee('id="inquiry-email" type="email" placeholder="Filled in when you pick a message" readonly', false);
});

it('suggests a reply subject from what the visitor asked about', function (?string $subject, string $expected) {
    expect(Inquiry::factory()->make(['subject' => $subject])->replySubject())->toBe($expected);
})->with([
    'a nation' => ['Liyue', 'Re: Your Liyue inquiry'],
    'custom itinerary' => ['Custom itinerary', 'Re: Your custom itinerary inquiry'],
    'other' => ['Other', 'Re: Your inquiry'],
    'no subject' => [null, 'Re: Your inquiry'],
]);

// ---- sending a reply ------------------------------------------------------------------------

it('emails the reply, saves it in the history and marks the inquiry resolved', function () {
    Mail::fake();
    $admin = inboxAdmin();
    $inquiry = Inquiry::factory()->create(['email' => 'ana@example.com', 'subject' => 'Liyue']);

    $this->actingAs($admin)
        ->from(route('admin.dashboard'))
        ->post(route('admin.inquiries.reply', $inquiry), replyPayload())
        ->assertRedirect(route('admin.dashboard'))
        ->assertSessionHas('inquiry_status');

    Mail::assertSent(InquiryReplyMail::class, fn (InquiryReplyMail $mail) => $mail->hasTo('ana@example.com')
        && $mail->hasSubject('Re: Your Liyue inquiry')
        && $mail->hasReplyTo(config('app.desk_email')));

    $reply = InquiryReply::first();
    $inquiry->refresh();

    expect(InquiryReply::count())->toBe(1)
        ->and($reply->inquiry_id)->toBe($inquiry->id)
        ->and($reply->user_id)->toBe($admin->id)
        ->and($reply->body)->toContain('4-day trip')
        ->and($reply->sent_at)->not->toBeNull()
        ->and($inquiry->status)->toBe('resolved')
        ->and($inquiry->replied_at)->not->toBeNull()
        ->and($inquiry->handled_by)->toBe($admin->id);
});

it('always sends to the email on the inquiry, whatever is posted', function () {
    Mail::fake();
    $inquiry = Inquiry::factory()->create(['email' => 'ana@example.com']);

    $this->actingAs(inboxAdmin())
        ->post(route('admin.inquiries.reply', $inquiry), replyPayload(['email' => 'someone@else.com', 'to' => 'other@else.com']));

    Mail::assertSent(InquiryReplyMail::class, fn ($mail) => $mail->hasTo('ana@example.com'));
    Mail::assertNotSent(InquiryReplyMail::class, fn ($mail) => $mail->hasTo('someone@else.com') || $mail->hasTo('other@else.com'));
});

it('keeps the time of the first answer when a second reply is sent', function () {
    Mail::fake();
    $first = Carbon::parse('2026-11-20 09:00:00');
    $inquiry = Inquiry::factory()->create(['status' => 'resolved', 'replied_at' => $first]);

    $this->actingAs(inboxAdmin())->post(route('admin.inquiries.reply', $inquiry), replyPayload());

    expect($inquiry->fresh()->replied_at->toDateTimeString())->toBe($first->toDateTimeString())
        ->and($inquiry->replies()->count())->toBe(1);
});

it('refuses an empty or oversized reply and sends nothing', function (array $overrides, string $field) {
    Mail::fake();
    $inquiry = Inquiry::factory()->create();

    $this->actingAs(inboxAdmin())
        ->post(route('admin.inquiries.reply', $inquiry), replyPayload($overrides))
        ->assertSessionHasErrors($field);

    Mail::assertNothingSent();
    expect(InquiryReply::count())->toBe(0)
        ->and($inquiry->fresh()->status)->toBe('pending');
})->with([
    'no reply text' => [['body' => ''], 'body'],
    'no subject' => [['subject' => ''], 'subject'],
    'reply too long' => [['body' => str_repeat('a', 5001)], 'body'],
]);

it('saves nothing and keeps the status when the email cannot be sent', function () {
    Mail::shouldReceive('to')->once()->andReturnSelf();
    Mail::shouldReceive('send')->once()->andThrow(new RuntimeException('smtp down'));
    $inquiry = Inquiry::factory()->create();

    $this->actingAs(inboxAdmin())
        ->from(route('admin.dashboard'))
        ->post(route('admin.inquiries.reply', $inquiry), replyPayload())
        ->assertRedirect(route('admin.dashboard'))
        ->assertSessionHas('inquiry_error')
        ->assertSessionHasInput('body');

    expect(InquiryReply::count())->toBe(0)
        ->and($inquiry->fresh()->status)->toBe('pending')
        ->and($inquiry->fresh()->replied_at)->toBeNull();
});

it('does not let a customer reply', function () {
    Mail::fake();
    $inquiry = Inquiry::factory()->create();

    $this->actingAs(User::factory()->create())
        ->post(route('admin.inquiries.reply', $inquiry), replyPayload())
        ->assertForbidden();

    Mail::assertNothingSent();
    expect(InquiryReply::count())->toBe(0);
});

it('does not let a visitor who is not logged in reply', function () {
    Mail::fake();
    $inquiry = Inquiry::factory()->create();

    $this->post(route('admin.inquiries.reply', $inquiry), replyPayload())->assertRedirect();

    Mail::assertNothingSent();
    expect(InquiryReply::count())->toBe(0);
});

it('builds a plain-text email that quotes the original message', function () {
    $inquiry = Inquiry::factory()->create([
        'name' => 'Ana Reyes',
        'message' => "Original line one\nOriginal line two",
    ]);

    (new InquiryReplyMail($inquiry, 'Re: Your Liyue inquiry', "Hello Ana,\n\nThanks for writing."))
        ->assertSeeInText('Hello Ana,')
        ->assertSeeInText('Thanks for writing.')
        ->assertSeeInText('Ana Reyes wrote:')
        ->assertSeeInText('> Original line one')
        ->assertSeeInText('> Original line two');
});

// ---- reply history and status ---------------------------------------------------------------

it('shows the reply history under the message', function () {
    $inquiry = Inquiry::factory()->create();
    $inquiry->replies()->create([
        'user_id' => User::factory()->create(['name' => 'Director Test'])->id,
        'subject' => 'Re: Your inquiry',
        'body' => 'We can arrange a Liyue harbour tour in March',
        'sent_at' => now(),
    ]);

    $this->actingAs(inboxAdmin())
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('We can arrange a Liyue harbour tour in March')
        ->assertSee('Director Test');
});

it('lets the administrator change the status by hand', function () {
    $admin = inboxAdmin();
    $inquiry = Inquiry::factory()->create();

    $this->actingAs($admin)
        ->from(route('admin.dashboard'))
        ->patch(route('admin.inquiries.status', $inquiry), ['status' => 'in progress'])
        ->assertRedirect(route('admin.dashboard'))
        ->assertSessionHas('inquiry_status');

    expect($inquiry->fresh()->status)->toBe('in progress')
        ->and($inquiry->fresh()->handled_by)->toBe($admin->id);
});

it('refuses an unknown status and keeps customers out', function () {
    $inquiry = Inquiry::factory()->create();

    $this->actingAs(inboxAdmin())
        ->patch(route('admin.inquiries.status', $inquiry), ['status' => 'archived'])
        ->assertSessionHasErrors('status');

    $this->actingAs(User::factory()->create())
        ->patch(route('admin.inquiries.status', $inquiry), ['status' => 'resolved'])
        ->assertForbidden();

    expect($inquiry->fresh()->status)->toBe('pending');
});

// ---- unanswered and overdue -----------------------------------------------------------------

it('counts business days and flags an unanswered inquiry as overdue after three', function (string $created, string $now, int $days, bool $overdue) {
    $inquiry = Inquiry::factory()->make(['created_at' => $created, 'status' => 'pending']);
    $at = Carbon::parse($now);

    expect($inquiry->businessDaysOpen($at))->toBe($days)
        ->and($inquiry->isOverdue($at))->toBe($overdue);
})->with([
    'same day' => ['2026-12-04 10:00:00', '2026-12-04 15:00:00', 0, false],
    'Friday to Monday skips the weekend' => ['2026-12-04 10:00:00', '2026-12-07 10:00:00', 1, false],
    'Saturday to Monday' => ['2026-12-05 10:00:00', '2026-12-07 10:00:00', 1, false],
    'Monday to Wednesday' => ['2026-12-07 10:00:00', '2026-12-09 10:00:00', 2, false],
    'Friday to Wednesday' => ['2026-12-04 10:00:00', '2026-12-09 10:00:00', 3, true],
]);

it('never calls a resolved inquiry overdue', function () {
    $inquiry = Inquiry::factory()->make(['created_at' => '2026-01-05 10:00:00', 'status' => 'resolved']);

    expect($inquiry->isOverdue(Carbon::parse('2026-12-09 10:00:00')))->toBeFalse();
});

it('shows the unanswered and overdue counts on the Inquiries tab and in the inbox', function () {
    $this->travelTo(Carbon::parse('2026-12-09 10:00:00'));

    Inquiry::factory()->create(['created_at' => '2026-12-04 10:00:00', 'status' => 'pending']);      // overdue
    Inquiry::factory()->create(['created_at' => '2026-12-09 09:00:00', 'status' => 'in progress']);  // fresh
    Inquiry::factory()->create(['created_at' => '2026-12-01 10:00:00', 'status' => 'resolved']);     // answered

    $this->actingAs(inboxAdmin())
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('<span class="admin-tab__badge admin-tab__badge--overdue"', false)
        ->assertSee('2 unanswered')
        ->assertSee('1 overdue')
        ->assertSee('<span class="admin-inbox__overdue">Overdue</span>', false);
});

it('shows no badge when everything is answered', function () {
    Inquiry::factory()->create(['status' => 'resolved']);

    $this->actingAs(inboxAdmin())
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertDontSee('<span class="admin-tab__badge', false);
});

// ---- search and filters ---------------------------------------------------------------------

it('searches by name, email or message', function () {
    Inquiry::factory()->create(['name' => 'Zed Quimby', 'email' => 'zed@example.com', 'message' => 'Looking for a harbour tour']);
    Inquiry::factory()->create(['name' => 'Yara Ledesma', 'email' => 'yara@example.com', 'message' => 'Shrine visit dates please']);
    $admin = inboxAdmin();

    $this->actingAs($admin)->get(route('admin.dashboard', ['q' => 'Quimby']))
        ->assertSee('Zed Quimby')->assertDontSee('Yara Ledesma')->assertSee('1 of 2 messages match');

    $this->actingAs($admin)->get(route('admin.dashboard', ['q' => 'yara@example']))
        ->assertSee('Yara Ledesma')->assertDontSee('Zed Quimby');

    $this->actingAs($admin)->get(route('admin.dashboard', ['q' => 'shrine']))
        ->assertSee('Yara Ledesma')->assertDontSee('Zed Quimby');
});

it('filters by status and by subject', function () {
    Inquiry::factory()->create(['name' => 'Zed Quimby', 'subject' => 'Liyue', 'status' => 'pending']);
    Inquiry::factory()->create(['name' => 'Yara Ledesma', 'subject' => 'Inazuma', 'status' => 'resolved']);
    $admin = inboxAdmin();

    $this->actingAs($admin)->get(route('admin.dashboard', ['status' => 'resolved']))
        ->assertSee('Yara Ledesma')->assertDontSee('Zed Quimby');

    $this->actingAs($admin)->get(route('admin.dashboard', ['status' => 'open']))
        ->assertSee('Zed Quimby')->assertDontSee('Yara Ledesma');

    $this->actingAs($admin)->get(route('admin.dashboard', ['subject' => 'Inazuma']))
        ->assertSee('Yara Ledesma')->assertDontSee('Zed Quimby');
});

it('filters to overdue messages only', function () {
    $this->travelTo(Carbon::parse('2026-12-09 10:00:00'));
    Inquiry::factory()->create(['name' => 'Zed Quimby', 'created_at' => '2026-12-04 10:00:00', 'status' => 'pending']);
    Inquiry::factory()->create(['name' => 'Yara Ledesma', 'created_at' => '2026-12-09 08:00:00', 'status' => 'pending']);

    $this->actingAs(inboxAdmin())
        ->get(route('admin.dashboard', ['status' => 'overdue']))
        ->assertSee('Zed Quimby')
        ->assertDontSee('Yara Ledesma');
});

it('says so when no message matches the filters', function () {
    Inquiry::factory()->create(['name' => 'Zed Quimby']);

    $this->actingAs(inboxAdmin())
        ->get(route('admin.dashboard', ['q' => 'nobody-by-this-name']))
        ->assertOk()
        ->assertSee('No messages match these filters.');
});

it('opens the Inquiries tab after a filter, a page change or a reply, but not on a plain visit', function () {
    $admin = inboxAdmin();
    $marker = '<span hidden data-admin-start-tab="inquiries"></span>';

    $this->actingAs($admin)->get(route('admin.dashboard'))->assertDontSee($marker, false);
    $this->actingAs($admin)->get(route('admin.dashboard', ['q' => 'a']))->assertSee($marker, false);
    $this->actingAs($admin)->get(route('admin.dashboard', ['page' => 1]))->assertSee($marker, false);
    $this->actingAs($admin)->withSession(['inquiry_status' => 'Reply sent'])->get(route('admin.dashboard'))->assertSee($marker, false);
});

it('pages the inbox ten messages at a time', function () {
    Inquiry::factory()->count(12)->create();
    $admin = inboxAdmin();

    $this->actingAs($admin)->get(route('admin.dashboard'))
        ->assertSee('Page 1 of 2')
        ->assertSee('12 messages');

    $this->actingAs($admin)->get(route('admin.dashboard', ['page' => 2]))
        ->assertSee('Page 2 of 2');
});
