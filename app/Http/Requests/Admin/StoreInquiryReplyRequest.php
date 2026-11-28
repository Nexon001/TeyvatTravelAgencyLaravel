<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreInquiryReplyRequest extends FormRequest
{
    /** The route already sits behind the admin middleware. */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * The recipient is never read from the form: the reply always goes to the email on the inquiry.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:150'],
            'body' => ['required', 'string', 'max:5000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'subject.required' => 'Add a subject for the email.',
            'body.required' => 'Write a reply before sending.',
        ];
    }
}
