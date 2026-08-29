<?php

namespace App\Services;

use App\Models\Complaint;
use App\Models\ComplaintCategory;
use App\Notifications\ComplaintStatusChanged;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class ComplaintService extends Service
{
    /**
     * Statuses staff are allowed to move a complaint to from the workflow
     * actions panel (see resources/js/Pages/Complaints/Show.vue).
     */
    public const PROCESSABLE_STATUSES = [
        'under_review',
        'verified',
        'under_investigation',
        'for_mediation',
        'action_taken',
        'rejected',
        'closed',
    ];

    public function __construct(private AuditLogService $auditLog)
    {
    }

    public function list(Request $request): LengthAwarePaginator
    {
        $query = Complaint::with(['complainant.memberProfile', 'category'])
            ->when($request->input('search'), function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('complaint_code', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%")
                        ->orWhereHas('complainant', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->input('status'), fn ($query, $status) => $query->where('status', $status))
            ->when($request->input('priority'), fn ($query, $priority) => $query->where('priority', $priority))
            ->when($request->input('category_id'), fn ($query, $categoryId) => $query->where('category_id', $categoryId))
            ->when($request->input('date_from'), fn ($query, $date) => $query->whereDate('created_at', '>=', $date))
            ->when($request->input('date_to'), fn ($query, $date) => $query->whereDate('created_at', '<=', $date))
            ->orderByRaw("CASE WHEN status = 'submitted' THEN 0 ELSE 1 END, priority DESC, created_at DESC")
            ->paginate(15)
            ->withQueryString();

        return $query;
    }

    public function listForComplainant(User $user, Request $request): LengthAwarePaginator
    {
        return Complaint::with('category')
            ->where('complainant_id', $user->id)
            ->when($request->input('status'), fn ($query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();
    }

    public function categories(): Collection
    {
        return ComplaintCategory::orderBy('name')->get();
    }

    /**
     * Active staff members who can be assigned to handle a complaint.
     */
    public function moderators(): Collection
    {
        return User::role(['admin', 'moderator'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }
    
    public function show(Complaint $complaint): Complaint
    {
        return $complaint->load([
            'category',
            'complainant.memberProfile',
            'assignedModerator',
            'statusHistories.user',
            'attachments',
        ]);
    }

    public function create(Request $request, User $complainant): Complaint
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:complaint_categories,id',
            'subject' => 'required|string|max:250',
            'description' => 'required|string|max:5000',
            'location' => 'nullable|string|max:500',
            'incident_datetime' => 'nullable|date|before_or_equal:now',
            'priority' => 'required|in:low,medium,high,urgent',
        ]);

        $complaint = $this->transaction(function () use ($validated, $complainant) {
            $year = now()->year;
            $number = Complaint::whereYear('created_at', $year)->count() + 1;
            $code = sprintf('CMP-%d-%06d', $year, $number);

            while (Complaint::where('complaint_code', $code)->exists()) {
                $number++;
                $code = sprintf('CMP-%d-%06d', $year, $number);
            }

            $complaint = Complaint::create([
                ...$validated,
                'complaint_code' => $code,
                'complainant_id' => $complainant->id,
                'status' => 'submitted',
            ]);

            $complaint->statusHistories()->create([
                'user_id' => $complainant->id,
                'from_status' => null,
                'to_status' => 'submitted',
                'remarks' => 'Complaint submitted',
            ]);

            $this->auditLog->log('created', 'complaints', 'Complaint', $complaint->id, null, $complaint->toArray());

            return $complaint;
        }, 'Failed to create complaint.');

        return $complaint;
    }

    public function update(Request $request, Complaint $complaint): void
    {
        $validated = $this->validateUpdate($request);
        $oldValues = $complaint->only(array_keys($validated));

        $this->transaction(function () use ($validated, $complaint, $oldValues) {
            $complaint->update($validated);

            $this->auditLog->log(
                'updated complaint',
                'complaints',
                'Complaint',
                $complaint->id,
                $oldValues,
                $complaint->fresh()->toArray()
            );
        }, 'Failed to update complaint.');
    }

    public function delete(Complaint $complaint): void
    {
        $this->transaction(function () use ($complaint) {
            $oldValues = $complaint->toArray();
            $complaint->delete();

            $this->auditLog->log('deleted', 'complaints', 'Complaint', $complaint->id, $oldValues, null);
        }, 'Failed to delete complaint.');
    }

    /**
     * Assign a moderator to handle the complaint.
     */
    public function assign(Request $request, Complaint $complaint, User $actor): void
    {
        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $oldStatus = $complaint->status;
        $oldAssignee = $complaint->assigned_to;

        $this->transaction(function () use ($validated, $complaint, $actor, $oldStatus, $oldAssignee) {
            $complaint->update([
                'assigned_to' => $validated['assigned_to'],
                'status' => 'assigned',
            ]);

            $complaint->statusHistories()->create([
                'user_id' => $actor->id,
                'from_status' => $oldStatus,
                'to_status' => 'assigned',
                'remarks' => 'Assigned to staff member for handling',
            ]);

            $this->auditLog->log(
                'assigned complaint',
                'complaints',
                'Complaint',
                $complaint->id,
                ['assigned_to' => $oldAssignee, 'status' => $oldStatus],
                ['assigned_to' => $validated['assigned_to'], 'status' => 'assigned']
            );
        }, 'Failed to assign complaint.');
    }
/**
     * Move a complaint through the workflow (update status).
     */
    public function process(Request $request, Complaint $complaint, User $actor): void
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(self::PROCESSABLE_STATUSES)],
            'remarks' => ['nullable', 'string', 'max:2000'],
        ]);

        $oldStatus = $complaint->status;
        $newStatus = $validated['status'];

        $this->transaction(function () use ($validated, $complaint, $actor, $oldStatus, $newStatus) {
            $complaint->update([
                'status' => $newStatus,
                'notes' => $validated['remarks'] ?? $complaint->notes,
            ]);

            $complaint->statusHistories()->create([
                'user_id' => $actor->id,
                'from_status' => $oldStatus,
                'to_status' => $newStatus,
                'remarks' => $validated['remarks'] ?? "Status changed to {$newStatus}",
            ]);

            $this->auditLog->log(
                'processed complaint',
                'complaints',
                'Complaint',
                $complaint->id,
                ['status' => $oldStatus],
                ['status' => $newStatus]
            );
        }, 'Failed to update complaint status.');

        // Notify the complainant (only if they have an account).
        $complaint->complainant?->notify(new ComplaintStatusChanged($complaint));
    }

    /**
     * Mark a complaint as resolved with a resolution note.
     */
    public function resolve(Request $request, Complaint $complaint, User $actor): void
    {
        $validated = $request->validate([
            'resolution' => 'required|string|max:5000',
        ]);

        $oldStatus = $complaint->status;

        $this->transaction(function () use ($validated, $complaint, $actor, $oldStatus) {
            $complaint->update([
                'status' => 'resolved',
                'resolution' => $validated['resolution'],
                'resolution_date' => now(),
            ]);

            $complaint->statusHistories()->create([
                'user_id' => $actor->id,
                'from_status' => $oldStatus,
                'to_status' => 'resolved',
                'remarks' => 'Complaint resolved',
            ]);

            $this->auditLog->log(
                'resolved complaint',
                'complaints',
                'Complaint',
                $complaint->id,
                ['status' => $oldStatus],
                ['status' => 'resolved']
            );
        }, 'Failed to resolve complaint.');

        // Notify the complainant (only if they have an account).
        $complaint->complainant?->notify(new ComplaintStatusChanged($complaint));
    }

    /**
     * Status-only update (legacy helper kept for compatibility).
     */
    public function updateStatus(Complaint $complaint, string $status, ?string $reason = null): Complaint
    {
        $validated = validator([
            'status' => $status,
            'reason' => $reason,
        ], [
            'status' => ['required', Rule::in([
                'submitted', 'under_review', 'verified', 'assigned',
                'under_investigation', 'for_mediation', 'action_taken',
                'resolved', 'rejected', 'closed',
            ])],
            'reason' => 'nullable|string|max:1000',
        ])->validate();

        $this->attempt(function () use ($validated, $complaint) {
            $complaint->update([
                'status' => $validated['status'],
                'resolution' => $validated['reason'] ?? $complaint->resolution,
                'resolution_date' => $validated['status'] === 'resolved' ? now() : null,
            ]);
        }, 'Failed to update complaint status.');

        return $complaint;
    }

    private function validateUpdate(Request $request): array
    {
        return $request->validate([
            'category_id' => 'required|exists:complaint_categories,id',
            'subject' => 'required|string|max:250',
            'description' => 'required|string|max:5000',
            'location' => 'nullable|string|max:500',
            'incident_datetime' => 'nullable|date|before_or_equal:now',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => ['required', Rule::in([
                'submitted', 'under_review', 'verified', 'assigned',
                'under_investigation', 'for_mediation', 'action_taken',
                'resolved', 'rejected', 'closed',
            ])],
            'notes' => 'nullable|string|max:2000',
        ]);
    }
}