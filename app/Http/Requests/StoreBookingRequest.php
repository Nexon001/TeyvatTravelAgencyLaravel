<?php

namespace App\Http\Requests;

use App\Services\NationCatalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** Same address typed in a different case is the same person. */
    protected function prepareForValidation(): void
    {
        $this->merge(['email' => Str::lower(trim((string) $this->input('email')))]);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        $minDays = (int) config('booking.min_days');
        $maxDays = (int) config('booking.max_custom_days');

        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^[0-9+()\-\s.]{7,30}$/'],
            'check_in' => ['required', 'date_format:Y-m-d'],
            'days' => ['required', 'integer', 'between:'.$minDays.','.$maxDays],
            'nation' => ['required', 'string', Rule::in(app(NationCatalog::class)->slugs())],
            // An itinerary id such as "liyue-1", or "custom" when the traveller wants the desk to plan a route.
            'itinerary' => ['required', 'string', 'regex:/^(custom|[a-z]+-\d+)$/'],
            // Honeypot: hidden from people, so any value means a bot filled it in.
            'bk_extra' => ['prohibited'],
        ];
    }

    /** The itinerary must belong to the chosen nation, and the stay must be exactly as long as the itinerary. */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->hasAny(['nation', 'itinerary', 'days']) || $this->input('itinerary') === 'custom') {
                return;
            }

            $itinerary = app(NationCatalog::class)->itinerary((string) $this->input('itinerary'));

            if ($itinerary === null || $itinerary['nation'] !== $this->input('nation')) {
                $validator->errors()->add('itinerary', 'Choose one of the itineraries for this nation, or a custom route.');
            } elseif ($itinerary['days'] !== (int) $this->input('days')) {
                $validator->errors()->add('days', 'This itinerary runs '.$itinerary['days'].' days.');
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone.regex' => 'Enter a phone number using digits, spaces, + or -.',
            'days.between' => 'Choose a stay of :min to :max days.',
        ];
    }
}
