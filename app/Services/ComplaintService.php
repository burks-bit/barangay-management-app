<?php

namespace App\Services;

use App\Models\Complaint;
use App\Models\Purok;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ComplaintService
{
    public function list(Request $request): LengthAwarePaginator
    {
        $query = Complaint::with(['complainant', 'officer'])
            ->when($request->input('search'), function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('complaint_number', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('complainant', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->input('status'), fn ($query, $status) => $query->where('status', $status))
            ->when($request->input('priority'), fn ($query, $priority) => $query->where('priority', $priority))
            ->when($request->input('category_id'), fn ($query, $categoryId) => $query->where('category_id', $categoryId))
            ->when($request->input('date_from'), fn ($query, $date) => $query->whereDate('submitted_at', '>=', $date))
            ->when($request->input('date_to'), fn ($query, $date) => $query->whereDate('submitted_at', '<=', $date))
            ->with(['category', 'complainant', 'assignedTo'])
            ->orderByRaw('CASE WHEN status = "pending" THEN 0 ELSE 1 END, priority DESC, created_at DESC')
            ->paginate(15)
            ->withQueryString();
    }

    public function show(Complaint $complaint)
    {
        return $complaint->load([
            'category',
            'complainant.profile',
            'assignedTo.profile',
            'auditLogs' => fn ($query) => $query->latest(),
            'attachments',
            'notes' => fn ($query) => $query->latest(),
        ]);
    }

    public function updateStatus(Complaint $complaint, string $status, ?string $reason = null)
    {
        $validated = validator([
            'status' => $status,
            'reason' => $reason,
        ], [
            'status' => 'required|in:open,in_progress,resolved,closed',
            'reason' => 'nullable|string|max:1000',
        ])->validate();

        $complaint->update([
            'status' => $validated['status'],
            'resolution_notes' => $validated['reason'] ?? $complaint->resolution_notes,
            'resolved_at' => $validated['status'] === 'resolved' ? now() : null,
        ]);

        return $complaint;
    }
}