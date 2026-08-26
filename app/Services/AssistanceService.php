<?php

namespace App\Services;

use App\Models\AssistanceRequest;
use App\Models\AssistanceType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class AssistanceService
{
    /**
     * Status transitions allowed from the staff assistance page, mapped to
     * the permission required to perform them.
     */
    public const STATUS_PERMISSIONS = [
        'for_verification' => 'process assistance',
        'under_assessment' => 'process assistance',
        'cancelled' => 'process assistance',
        'approved' => 'approve assistance',
        'rejected' => 'approve assistance',
        'for_release' => 'approve assistance',
        'released' => 'approve assistance',
    ];

    public function __construct(private AuditLogService $auditLog)
    {
    }

    public function list(Request $request): LengthAwarePaginator
    {
        return AssistanceRequest::with(['applicant.memberProfile', 'assistanceType'])
            ->when($request->input('status'), fn ($query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();
    }

    public function listForApplicant(User $user, Request $request): LengthAwarePaginator
    {
        return AssistanceRequest::with('assistanceType')
            ->where('applicant_id', $user->id)
            ->when($request->input('status'), fn ($query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();
    }

    public function types(): \Illuminate\Support\Collection
    {
        return AssistanceType::orderBy('name')->get();
    }

    public function create(Request $request, User $applicant): void
    {
        $validated = $request->validate([
            'assistance_type_id' => 'required|exists:assistance_types,id',
            'reason' => 'required|string|max:5000',
            'amount' => 'nullable|numeric|min:0|max:99999999.99',
        ]);

        DB::transaction(function () use ($validated, $applicant) {
            $number = AssistanceRequest::whereYear('created_at', now()->year)->count() + 1;
            $code = sprintf('AST-%d-%06d', now()->year, $number);

            while (AssistanceRequest::where('assistance_code', $code)->exists()) {
                $number++;
                $code = sprintf('AST-%d-%06d', now()->year, $number);
            }

            AssistanceRequest::create([
                ...$validated,
                'assistance_code' => $code,
                'applicant_id' => $applicant->id,
                'status' => 'submitted',
            ]);
        });
    }

    /**
     * Update the status of an assistance request (staff workflow actions).
     */
    public function updateStatus(Request $request, AssistanceRequest $assistanceRequest, User $actor): void
    {
        $validated = $request->validate([
            'status' => ['required', 'in:' . implode(',', array_keys(self::STATUS_PERMISSIONS))],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        $newStatus = $validated['status'];
        $permission = self::STATUS_PERMISSIONS[$newStatus];

        abort_unless($actor->can($permission), 403, "You do not have permission to set this status.");

        $oldStatus = $assistanceRequest->status;
        $updateData = ['status' => $newStatus];

        if ($newStatus === 'under_assessment') {
            $updateData['assessed_by'] = $actor->id;
            $updateData['assessed_at'] = now();
        }
        if ($newStatus === 'approved') {
            $updateData['approved_by'] = $actor->id;
            $updateData['approved_at'] = now();
            $updateData['approval_notes'] = $validated['remarks'] ?? $assistanceRequest->approval_notes;
        }
        if ($newStatus === 'released') {
            $updateData['released_at'] = now();
        }

        $assistanceRequest->update($updateData);

        $this->auditLog->log(
            'updated assistance status',
            'assistance',
            'AssistanceRequest',
            $assistanceRequest->id,
            ['status' => $oldStatus],
            ['status' => $newStatus]
        );

        // Notify the applicant (only if they have an account).
        $assistanceRequest->applicant?->notify(new \App\Notifications\AssistanceStatusChanged($assistanceRequest));
    }
}