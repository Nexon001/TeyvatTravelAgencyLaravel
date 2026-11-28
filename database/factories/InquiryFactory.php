<?php

namespace Database\Factories;

use App\Models\Inquiry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Inquiry>
 */
class InquiryFactory extends Factory
{
    protected $model = Inquiry::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'subject' => 'Other',
            'message' => fake()->sentence(12),
            'status' => 'pending',
            'consented_at' => now(),
        ];
    }
}
