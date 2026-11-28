<?php

namespace App\Http\Requests;

use App\Models\Nation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreInquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** Same address typed in a different case is the same person. */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'email' => Str::lower(trim((string) $this->input('email'))),
            'message' => trim((string) $this->input('message')),
        ]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:100'],
            // A nation slug from the NATIONS table, "multi" for a custom multi-nation journey, or empty for "Others".
            'nation' => ['nullable', 'string', Rule::in(array_merge(Nation::pluck('slug')->all(), ['multi']))],
            'message' => ['required', 'string', 'min:10', 'max:2000'],
            // Data Privacy Act consent (NFR-03): the box must be ticked.
            'consent' => ['accepted'],
            // Honeypot: hidden from people, so any value means a bot filled it in.
            'website' => ['prohibited'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'consent.accepted' => 'Please tick the box to agree to the Privacy Notice.',
            'message.min' => 'Please write a little more so the desk can help (at least :min characters).',
            'nation.in' => 'Choose a subject from the list.',
        ];
    }

    /** Errors bring the visitor back to the contact form, not the top of the home page. */
    protected function getRedirectUrl(): string
    {
        return route('home').'#contact';
    }
}
