<?php

namespace Database\Factories;

use App\Models\Booking;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        $checkIn = CarbonImmutable::today()->addDays(fake()->numberBetween(2, 60));
        $days = fake()->numberBetween(2, 6);

        return [
            'reference' => 'TTS-'.Str::upper(Str::random(6)),
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => '+63 917 000 0000',
            'check_in' => $checkIn->toDateString(),
            'check_out' => $checkIn->addDays($days - 1)->toDateString(),
            'days' => $days,
            'is_custom' => false,
            'status' => 'confirmed',
        ];
    }

    /** A stay that starts on $checkIn and lasts $days days. */
    public function stay(string $checkIn, int $days): static
    {
        return $this->state(fn () => [
            'check_in' => $checkIn,
            'check_out' => CarbonImmutable::parse($checkIn)->addDays($days - 1)->toDateString(),
            'days' => $days,
        ]);
    }
}
