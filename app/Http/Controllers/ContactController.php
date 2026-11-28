<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInquiryRequest;
use App\Models\Inquiry;
use App\Models\Nation;
use Illuminate\Http\RedirectResponse;

class ContactController extends Controller
{
    /** FR-30, FR-31: validate the contact form and keep the message for the Director's admin inbox. */
    public function store(StoreInquiryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $choice = $data['nation'] ?? null;

        $nation = ($choice !== null && $choice !== 'multi')
            ? Nation::where('slug', $choice)->first()
            : null;

        Inquiry::create([
            'nation_id' => $nation?->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'subject' => $choice === 'multi' ? 'Custom itinerary' : ($nation?->nation_name ?? 'Other'),
            'message' => $data['message'],
            'status' => 'pending',
            'consented_at' => now(),
        ]);

        // Own flash key: "status" is also shown inside the login pop-up (idle logout message).
        return redirect(route('home').'#contact')
            ->with('contact_status', 'Thank you, your message has been sent. The Director\'s desk will reply by email, usually within 3–5 business days.');
    }
}
