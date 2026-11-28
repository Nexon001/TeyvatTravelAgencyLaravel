<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private const INBOX_PAGE_SIZE = 10;

    public function index(Request $request): View
    {
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'status' => (string) $request->query('status', ''),
            'subject' => (string) $request->query('subject', ''),
        ];

        // Unanswered = pending or in progress. Overdue is worked out from business days, so it is done in PHP.
        $open = Inquiry::query()->whereIn('status', ['pending', 'in progress'])->get(['id', 'status', 'created_at']);
        $overdueIds = $open->filter(fn (Inquiry $inquiry) => $inquiry->isOverdue())->pluck('id')->all();

        $query = Inquiry::query()->with('replies.user');

        if ($filters['q'] !== '') {
            $term = '%'.$filters['q'].'%';
            $query->where(fn ($where) => $where
                ->where('name', 'like', $term)
                ->orWhere('email', 'like', $term)
                ->orWhere('message', 'like', $term));
        }

        match ($filters['status']) {
            'open' => $query->whereIn('status', ['pending', 'in progress']),
            'overdue' => $query->whereIn('id', $overdueIds),
            'pending', 'in progress', 'resolved' => $query->where('status', $filters['status']),
            default => null,
        };

        if ($filters['subject'] !== '') {
            $query->where('subject', $filters['subject']);
        }

        return view('admin.dashboard', [
            'inquiries' => $query->latest()->latest('id')->paginate(self::INBOX_PAGE_SIZE)->withQueryString(),
            'inquiryTotal' => Inquiry::query()->count(),
            'openCount' => $open->count(),
            'overdueCount' => count($overdueIds),
            'subjects' => Inquiry::query()->whereNotNull('subject')->distinct()->orderBy('subject')->pluck('subject'),
            'filters' => $filters,
        ]);
    }
}
