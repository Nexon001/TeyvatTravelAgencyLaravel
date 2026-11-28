<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreInquiryReplyRequest;
use App\Mail\InquiryReplyMail;
use App\Models\Inquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Throwable;

class InquiryController extends Controller
{
    /** FR-43: email a reply to the customer, keep it in the inquiry's history and mark the inquiry resolved. */
    public function reply(StoreInquiryReplyRequest $request, Inquiry $inquiry): RedirectResponse
    {
        $data = $request->validated();

        // Send first: if the mail server refuses, nothing is saved and the inquiry keeps its status.
        try {
            Mail::to($inquiry->email)->send(new InquiryReplyMail($inquiry, $data['subject'], $data['body']));
        } catch (Throwable $e) {
            report($e);

            return back(fallback: route('admin.dashboard'))
                ->withInput()
                ->with('inquiry_error', 'The email could not be sent, so nothing was saved. Check the mail settings and try again.');
        }

        DB::transaction(function () use ($inquiry, $data, $request) {
            $inquiry->replies()->create([
                'user_id' => $request->user()->id,
                'subject' => $data['subject'],
                'body' => $data['body'],
                'sent_at' => now(),
            ]);

            $inquiry->update([
                'status' => 'resolved',
                // replied_at keeps the time of the first answer, so it measures how fast the desk responds.
                'replied_at' => $inquiry->replied_at ?? now(),
                'handled_by' => $request->user()->id,
            ]);
        });

        return back(fallback: route('admin.dashboard'))
            ->with('inquiry_status', 'Reply sent to '.$inquiry->email.'.');
    }

    /** Change the status by hand, for example to "in progress" while the desk is still working on it. */
    public function updateStatus(Request $request, Inquiry $inquiry): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(Inquiry::STATUSES)],
        ]);

        $inquiry->update([
            'status' => $data['status'],
            'handled_by' => $inquiry->handled_by ?? $request->user()->id,
        ]);

        return back(fallback: route('admin.dashboard'))
            ->with('inquiry_status', 'Marked '.$inquiry->name.'\'s message as '.$data['status'].'.');
    }
}
