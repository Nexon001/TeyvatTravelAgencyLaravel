<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Services\BookingAvailability;
use App\Services\NationCatalog;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function __construct(
        private BookingAvailability $availability,
        private NationCatalog $catalog,
    ) {}

    public function create(Request $request): View
    {
        return view('Booking', [
            'nations' => $this->catalog->all(),
            'preselect' => $this->preselect($request),
            'config' => [
                'today' => CarbonImmutable::today()->toDateString(),
                'earliest' => $this->availability->earliestBookable()->toDateString(),
                'latest' => $this->availability->latestBookable()->toDateString(),
                'minDays' => (int) config('booking.min_days'),
                'maxPackageDays' => (int) config('booking.max_package_days'),
                'maxCustomDays' => (int) config('booking.max_custom_days'),
                'limitedThreshold' => (int) config('booking.limited_threshold'),
                'capacity' => (int) config('booking.daily_capacity'),
                'availabilityUrl' => route('booking.availability'),
                'csrfUrl' => route('booking.csrf'),
            ],
        ]);
    }

    /**
     * Pre-fill the destination from the link the traveller came from:
     * /book?itinerary=liyue-1 (a "Book Now" button on an itinerary) or /book?nation=liyue.
     *
     * @return array{nation: ?string, itinerary: ?string}
     */
    private function preselect(Request $request): array
    {
        $itinerary = $this->catalog->itinerary((string) $request->query('itinerary', ''));

        if ($itinerary !== null) {
            return ['nation' => $itinerary['nation'], 'itinerary' => $itinerary['id']];
        }

        $nation = (string) $request->query('nation', '');

        return [
            'nation' => in_array($nation, $this->catalog->slugs(), true) ? $nation : null,
            'itinerary' => null,
        ];
    }

    /** A fresh CSRF token, so a tab left open past the session lifetime can retry without losing the form. */
    public function csrf(): JsonResponse
    {
        return response()->json(['token' => csrf_token()])->header('Cache-Control', 'no-store');
    }

    /** Slots left per day, e.g. {"data": {"2026-11-20": 2, "2026-11-21": 0}}. */
    public function availability(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from' => ['required', 'date_format:Y-m-d'],
            'to' => ['required', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);

        $from = CarbonImmutable::parse($validated['from']);
        $to = CarbonImmutable::parse($validated['to'])->min($from->addDays(120));

        return response()->json([
            'data' => $this->availability->remainingByDate($from, $to),
            'capacity' => (int) config('booking.daily_capacity'),
        ]);
    }

    public function store(StoreBookingRequest $request): JsonResponse
    {
        $data = $request->validated();
        $checkIn = CarbonImmutable::parse($data['check_in']);
        $days = (int) $data['days'];
        $isCustom = $days > (int) config('booking.max_package_days');
        $itinerary = $data['itinerary'] === 'custom' ? null : $this->catalog->itinerary($data['itinerary']);

        try {
            // One booking at a time: the lock stops two people taking the last slot at the same moment
            // (SQLite has no row locks). The check runs again inside it because the calendar may be stale.
            $booking = Cache::lock('bookings:write', 10)->block(5, function () use ($data, $checkIn, $days, $isCustom, $itinerary) {
                return DB::transaction(function () use ($data, $checkIn, $days, $isCustom, $itinerary) {
                    $this->availability->ensureAvailable($checkIn, $days);
                    $this->ensureNotDuplicate($data['email'], $checkIn, $days);
                    $this->ensureUnderLimit($data['email']);

                    do {
                        $reference = 'TTS-'.Str::upper(Str::random(6));
                    } while (Booking::where('reference', $reference)->exists());

                    return Booking::create([
                        'reference' => $reference,
                        'name' => $data['name'],
                        'email' => $data['email'],
                        'phone' => $data['phone'],
                        'check_in' => $checkIn->toDateString(),
                        'check_out' => $checkIn->addDays($days - 1)->toDateString(),
                        'days' => $days,
                        'is_custom' => $isCustom,
                        'status' => $isCustom ? 'pending' : 'confirmed',
                        'nation' => $data['nation'],
                        'itinerary' => $itinerary['id'] ?? null,
                        'itinerary_title' => $itinerary['title'] ?? null,
                    ]);
                });
            });
        } catch (LockTimeoutException) {
            return response()->json([
                'message' => 'We are handling a lot of bookings right now. Please try again in a moment.',
            ], 503);
        }

        return response()->json([
            'reference' => $booking->reference,
            'status' => $booking->status,
            'name' => $booking->name,
            'email' => $booking->email,
            'nation' => $this->catalog->name($booking->nation),
            'itinerary_title' => $booking->itinerary_title ?? 'Custom route',
            'check_in' => $booking->check_in,
            'check_out' => $booking->check_out,
            'days' => $booking->days,
            'nights' => $booking->nights,
        ], 201);
    }

    /** The same email booking the same dates twice (double submit, back button, retry). */
    private function ensureNotDuplicate(string $email, CarbonImmutable $checkIn, int $days): void
    {
        $exists = Booking::query()
            ->active()
            ->where('email', $email)
            ->where('check_in', $checkIn->toDateString())
            ->where('check_out', $checkIn->addDays($days - 1)->toDateString())
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'email' => 'This email already has a booking for these dates. Contact our desk if you need to change it.',
            ]);
        }
    }

    private function ensureUnderLimit(string $email): void
    {
        $limit = (int) config('booking.max_upcoming_per_email');

        $upcoming = Booking::query()
            ->active()
            ->where('email', $email)
            ->where('check_out', '>=', CarbonImmutable::today()->toDateString())
            ->count();

        if ($upcoming >= $limit) {
            throw ValidationException::withMessages([
                'email' => 'This email already has '.$limit.' upcoming bookings. Contact our desk to book more.',
            ]);
        }
    }
}
