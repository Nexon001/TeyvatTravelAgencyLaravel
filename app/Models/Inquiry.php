<?php

namespace App\Models;

use Database\Factories\InquiryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

#[Fillable([
    'nation_id', 'handled_by', 'name', 'email', 'subject', 'message',
    'status', 'consented_at', 'replied_at',
])]
class Inquiry extends Model
{
    /** @use HasFactory<InquiryFactory> */
    use HasFactory;

    public const STATUSES = ['pending', 'in progress', 'resolved'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'consented_at' => 'datetime',
            'replied_at' => 'datetime',
        ];
    }

    public function nation(): BelongsTo
    {
        return $this->belongsTo(Nation::class);
    }

    /** The staff or admin user who handled the inquiry (null until assigned). */
    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    /** Replies emailed to the customer, oldest first. */
    public function replies(): HasMany
    {
        return $this->hasMany(InquiryReply::class)->orderBy('sent_at')->orderBy('id');
    }

    /** Still waiting for an answer (pending or in progress). */
    public function isOpen(): bool
    {
        return $this->status !== 'resolved';
    }

    /** Weekdays (Mon-Fri, Manila time) that have passed since the message arrived. Holidays are not counted. */
    public function businessDaysOpen(?Carbon $now = null): int
    {
        $zone = 'Asia/Manila';
        $day = $this->created_at->copy()->timezone($zone)->startOfDay();
        $today = ($now ?? now())->copy()->timezone($zone)->startOfDay();

        $days = 0;
        while ($day->lt($today)) {
            $day = $day->addDay();
            if ($day->isWeekday()) {
                $days++;
            }
        }

        return $days;
    }

    /** Open for longer than config('inquiries.overdue_business_days') business days. */
    public function isOverdue(?Carbon $now = null): bool
    {
        return $this->isOpen()
            && $this->businessDaysOpen($now) >= (int) config('inquiries.overdue_business_days');
    }

    /** Suggested subject line for the admin's reply. */
    public function replySubject(): string
    {
        return match (true) {
            $this->subject === 'Custom itinerary' => 'Re: Your custom itinerary inquiry',
            filled($this->subject) && $this->subject !== 'Other' => 'Re: Your '.$this->subject.' inquiry',
            default => 'Re: Your inquiry',
        };
    }
}
