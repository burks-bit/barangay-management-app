<?php

namespace App\Services;

use App\Models\MemberProfile;
use App\Models\RequestType;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Mpdf\Mpdf;

class ServiceRequestService extends Service
{
    public function __construct(private AuditLogService $auditLog)
    {
    }

    /**
     * Admin/Moderator view of all requests.
     */
    public function list(Request $request): LengthAwarePaginator
    {
        $query = ServiceRequest::with(['requester.memberProfile', 'resident.purok', 'creator', 'requestType', 'assignedStaff', 'encoder', 'approver'])
            ->when($request->input('search'), function ($q, $search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('tracking_number', 'like', "%{$search}%")
                        ->orWhere('purpose', 'like', "%{$search}%")
                        ->orWhereHas('requester.memberProfile', function ($qp) use ($search) {
                            $qp->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('resident', function ($qp) use ($search) {
                            $qp->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->input('status'), fn ($q, $v) => $q->where('status', $v))
            ->when($request->input('request_type_id'), fn ($q, $v) => $q->where('request_type_id', $v))
            ->latest();

        return $query->paginate(15)->withQueryString();
    }

    /**
     * Member view of their own requests.
     */
    public function listForRequester(User $user, Request $request): LengthAwarePaginator
    {
        return ServiceRequest::with(['requestType'])
            ->where('requester_id', $user->id)
            ->when($request->input('status'), fn ($q, $v) => $q->where('status', $v))
            ->latest()
            ->paginate(15)
            ->withQueryString();
    }

    public function requestTypes()
    {
        return RequestType::orderBy('name')->get();
    }

    public function create(Request $request, User $requester): ServiceRequest
    {
        $validated = $request->validate([
            'request_type_id' => 'required|exists:request_types,id',
            'purpose' => 'required|string|max:500',
            'description' => 'nullable|string|max:2000',
        ]);

        return $this->transaction(function () use ($validated, $requester) {
            $serviceRequest = ServiceRequest::create([
                'tracking_number' => $this->generateTrackingNumber(),
                'requester_id' => $requester->id,
                'member_profile_id' => $requester->memberProfile?->id,
                'source' => 'online',
                'request_type_id' => $validated['request_type_id'],
                'purpose' => $validated['purpose'],
                'description' => $validated['description'] ?? null,
                'status' => 'submitted',
                'submitted_at' => now(),
            ]);

            $serviceRequest->statusHistories()->create([
                'user_id' => $requester->id,
                'from_status' => null,
                'to_status' => 'submitted',
                'remarks' => 'Request submitted',
            ]);

            $this->auditLog->log('created', 'requests', 'ServiceRequest', $serviceRequest->id, null, $serviceRequest->toArray());

            return $serviceRequest;
        }, 'ServiceRequestService::create');
    }

    /**
     * Walk-in form data for admin/moderator: encode a request on behalf of a
     * resident who cannot submit one online (e.g., no phone).
     */
    public function walkInResidents()
    {
        return MemberProfile::query()
            ->with('purok:id,name')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get(['id', 'user_id', 'resident_id', 'first_name', 'middle_name', 'last_name', 'suffix', 'contact_number']);
    }

    /**
     * Store a walk-in request encoded by staff on behalf of a resident.
     */
    public function createWalkIn(Request $request): ServiceRequest
    {
        $validated = $request->validate([
            'member_profile_id' => ['required', 'exists:member_profiles,id'],
            'request_type_id' => ['required', 'exists:request_types,id'],
            'purpose' => ['required', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $profile = MemberProfile::findOrFail($validated['member_profile_id']);

        return $this->transaction(function () use ($validated, $profile) {
            $serviceRequest = ServiceRequest::create([
                'tracking_number' => $this->generateTrackingNumber(),
                // Link the resident's account when they have one so they can
                // track the request online and receive notifications.
                'requester_id' => $profile->user_id,
                'member_profile_id' => $profile->id,
                'source' => 'walk_in',
                'created_by' => auth()->id(),
                'request_type_id' => $validated['request_type_id'],
                'purpose' => $validated['purpose'],
                'description' => $validated['description'] ?? null,
                'status' => 'submitted',
                'submitted_at' => now(),
            ]);

            $serviceRequest->statusHistories()->create([
                'user_id' => auth()->id(),
                'from_status' => null,
                'to_status' => 'submitted',
                'remarks' => 'Walk-in request encoded by staff on behalf of the resident',
            ]);

            $this->auditLog->log(
                'created walk-in request',
                'requests',
                'ServiceRequest',
                $serviceRequest->id,
                null,
                $serviceRequest->toArray()
            );

            return $serviceRequest;
        }, 'ServiceRequestService::createWalkIn');
    }

    private function generateTrackingNumber(): string
    {
        $year = now()->year;
        $lastNumber = ServiceRequest::whereYear('created_at', $year)->count() + 1;
        $trackingNumber = sprintf('REQ-%d-%06d', $year, $lastNumber);

        // Ensure uniqueness
        while (ServiceRequest::where('tracking_number', $trackingNumber)->exists()) {
            $lastNumber++;
            $trackingNumber = sprintf('REQ-%d-%06d', $year, $lastNumber);
        }

        return $trackingNumber;
    }

    public function show(ServiceRequest $serviceRequest): array
    {
        $serviceRequest->load(['requester.memberProfile.purok', 'resident.purok', 'creator', 'requestType', 'assignedStaff', 'encoder', 'approver', 'statusHistories.user', 'attachments']);

        return [
            'serviceRequest' => $serviceRequest,
            'staff' => User::role(['admin', 'moderator'])->orderBy('name')->get(['id', 'name']),
            'captains' => \App\Models\BarangayProfile::where('is_active', true)
                ->with(['officials' => fn ($query) => $query->where('position', 'captain')->where('is_active', true)])
                ->first()?->officials ?? collect(),
        ];
    }

    public function encode(Request $request, ServiceRequest $serviceRequest): void
    {
        $validated = $request->validate([
            'document_content' => 'required|string|max:30000',
            'encoded_by' => 'required|exists:users,id',
        ]);

        $this->attempt(function () use ($validated, $serviceRequest) {
            $serviceRequest->update([
                'document_content' => $validated['document_content'],
                'encoded_by' => $validated['encoded_by'],
                'encoded_at' => now(),
                'status' => 'ready_for_release',
            ]);
        }, 'ServiceRequestService::encode');
    }

    public function release(Request $request, ServiceRequest $serviceRequest): void
    {
        $validated = $request->validate([
            'approved_by' => 'required|exists:barangay_officials,id',
        ]);

        abort_unless($request->user()->can('approve requests'), 403);
        abort_unless($serviceRequest->document_content, 422, 'Encode the document before releasing it.');
        abort_unless(\App\Models\BarangayOfficial::whereKey($validated['approved_by'])
            ->where('position', 'captain')
            ->where('is_active', true)
            ->whereHas('barangayProfile', fn ($query) => $query->where('is_active', true))
            ->exists(), 422, 'Select an active barangay captain.');

        $this->transaction(function () use ($validated, $request, $serviceRequest) {
            $serviceRequest->update([
                'approved_by_official_id' => $validated['approved_by'],
                'status' => 'released',
                'released_at' => now(),
            ]);

            $serviceRequest->requester?->notify(new \App\Notifications\RequestStatusChanged($serviceRequest));
        }, 'ServiceRequestService::release');
    }

    public function downloadPdf(Request $request, ServiceRequest $serviceRequest)
    {
        $serviceRequest->load(['requester.memberProfile', 'resident', 'requestType', 'encoder', 'approverOfficial']);
        $barangay = \App\Models\BarangayProfile::where('is_active', true)->first();

        $mpdf = new Mpdf(['mode' => 'utf-8', 'format' => 'A4', 'tempDir' => storage_path('app/mpdf')]);
        $mpdf->WriteHTML(view('pdfs.requested_docs.service-request', [
            'serviceRequest' => $serviceRequest,
            'barangay' => $barangay,
        ])->render());

        $disposition = $request->boolean('inline') ? 'inline' : 'attachment';

        return response($mpdf->Output($serviceRequest->tracking_number . '.pdf', 'S'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', $disposition . '; filename="' . $serviceRequest->tracking_number . '.pdf"');
    }

    public function update(Request $request, ServiceRequest $serviceRequest): void
    {
        $validated = $request->validate([
            'request_type_id' => 'required|exists:request_types,id',
            'purpose' => 'required|string|max:500',
            'description' => 'nullable|string|max:2000',
        ]);

        $this->transaction(function () use ($validated, $serviceRequest) {
            $oldValues = $serviceRequest->only(array_keys($validated));
            $serviceRequest->update($validated);

            $this->auditLog->log('updated', 'requests', 'ServiceRequest', $serviceRequest->id, $oldValues, $serviceRequest->fresh()->toArray());
        }, 'ServiceRequestService::update');
    }

    public function delete(ServiceRequest $serviceRequest): void
    {
        $this->attempt(function () use ($serviceRequest) {
            $oldValues = $serviceRequest->toArray();
            $serviceRequest->delete();

            $this->auditLog->log('deleted', 'requests', 'ServiceRequest', $serviceRequest->id, $oldValues, null);
        }, 'ServiceRequestService::delete');
    }

    /**
     * Assign a staff member to process the request.
     */
    public function assign(Request $request, ServiceRequest $serviceRequest): void
    {
        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $oldStatus = $serviceRequest->status;

        $this->transaction(function () use ($validated, $oldStatus, $serviceRequest) {
            $serviceRequest->update([
                'assigned_to' => $validated['assigned_to'],
                'status' => 'for_verification',
            ]);

            $serviceRequest->statusHistories()->create([
                'user_id' => auth()->id(),
                'from_status' => $oldStatus,
                'to_status' => 'for_verification',
                'remarks' => 'Assigned to staff for verification',
            ]);

            $this->auditLog->log(
                'assigned request',
                'requests',
                'ServiceRequest',
                $serviceRequest->id,
                ['assigned_to' => $serviceRequest->getOriginal('assigned_to'), 'status' => $oldStatus],
                ['assigned_to' => $validated['assigned_to'], 'status' => 'for_verification']
            );
        }, 'ServiceRequestService::assign');
    }

    /**
     * Update the status of a request through the workflow.
     */
    public function process(Request $request, ServiceRequest $serviceRequest): void
    {
        $validated = $request->validate([
            'status' => 'required|in:for_verification,approved,rejected,processing,ready_for_release,released,cancelled',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $oldStatus = $serviceRequest->status;
        $newStatus = $validated['status'];

        $updateData = ['status' => $newStatus];

        if (in_array($newStatus, ['processing', 'ready_for_release'])) {
            $updateData['processed_at'] = now();
        }
        if ($newStatus === 'released') {
            $updateData['released_at'] = now();
        }

        $this->transaction(function () use ($updateData, $newStatus, $oldStatus, $validated, $serviceRequest) {
            $serviceRequest->update($updateData);

            $serviceRequest->statusHistories()->create([
                'user_id' => auth()->id(),
                'from_status' => $oldStatus,
                'to_status' => $newStatus,
                'remarks' => $validated['remarks'] ?? "Status changed to {$newStatus}",
            ]);

            // Notify the requester (null-safe for walk-in requests without an account)
            $serviceRequest->requester?->notify(new \App\Notifications\RequestStatusChanged($serviceRequest));

            $this->auditLog->log(
                'updated request status',
                'requests',
                'ServiceRequest',
                $serviceRequest->id,
                ['status' => $oldStatus],
                ['status' => $newStatus]
            );
        }, 'ServiceRequestService::process');
    }
}