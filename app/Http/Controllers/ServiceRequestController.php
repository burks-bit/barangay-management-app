<?php

namespace App\Http\Controllers;

use App\Models\RequestType;
use App\Models\ServiceRequest;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ServiceRequestController extends Controller
{
    public function __construct(private AuditLogService $auditLog)
    {
        // Authorization is enforced via route-level `can:` middleware (see routes/web.php).
    }

    /**
     * Admin/Moderator view of all requests.
     */
    public function index(Request $request): Response
    {
        $query = ServiceRequest::with(['requester.memberProfile', 'requestType', 'assignedStaff'])
            ->when($request->input('search'), function ($q, $search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('tracking_number', 'like', "%{$search}%")
                        ->orWhere('purpose', 'like', "%{$search}%")
                        ->orWhereHas('requester.memberProfile', function ($qp) use ($search) {
                            $qp->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->input('status'), fn ($q, $v) => $q->where('status', $v))
            ->when($request->input('request_type_id'), fn ($q, $v) => $q->where('request_type_id', $v))
            ->latest();

        return Inertia::render('Requests/Index', [
            'requests' => $query->paginate(15)->withQueryString(),
            'filters' => $request->only(['search', 'status', 'request_type_id']),
            'requestTypes' => RequestType::orderBy('name')->get(),
        ]);
    }

    /**
     * Member view of their own requests.
     */
    public function myRequests(Request $request): Response
    {
        $requests = ServiceRequest::with(['requestType'])
            ->where('requester_id', $request->user()->id)
            ->when($request->input('status'), fn ($q, $v) => $q->where('status', $v))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Requests/MyRequests', [
            'requests' => $requests,
            'filters' => $request->only(['status']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Requests/Create', [
            'requestTypes' => RequestType::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'request_type_id' => 'required|exists:request_types,id',
            'purpose' => 'required|string|max:500',
            'description' => 'nullable|string|max:2000',
        ]);

        DB::transaction(function () use ($validated, $request) {
            $year = now()->year;
            $lastNumber = ServiceRequest::whereYear('created_at', $year)->count() + 1;
            $trackingNumber = sprintf('REQ-%d-%06d', $year, $lastNumber);

            // Ensure uniqueness
            while (ServiceRequest::where('tracking_number', $trackingNumber)->exists()) {
                $lastNumber++;
                $trackingNumber = sprintf('REQ-%d-%06d', $year, $lastNumber);
            }

            $serviceRequest = ServiceRequest::create([
                'tracking_number' => $trackingNumber,
                'requester_id' => $request->user()->id,
                'request_type_id' => $validated['request_type_id'],
                'purpose' => $validated['purpose'],
                'description' => $validated['description'] ?? null,
                'status' => 'submitted',
                'submitted_at' => now(),
            ]);

            $serviceRequest->statusHistories()->create([
                'user_id' => $request->user()->id,
                'from_status' => null,
                'to_status' => 'submitted',
                'remarks' => 'Request submitted',
            ]);

            $this->auditLog->log('created', 'requests', 'ServiceRequest', $serviceRequest->id, null, $serviceRequest->toArray());
        });

        return redirect()->route('my-requests')
            ->with('success', 'Request submitted successfully. You can track it using your tracking number.');
    }

    public function show(ServiceRequest $service_request): Response
    {
        $service_request->load(['requester.memberProfile.purok', 'requestType', 'assignedStaff', 'statusHistories.user', 'attachments']);

        return Inertia::render('Requests/Show', [
            'serviceRequest' => $service_request,
        ]);
    }

    public function edit(ServiceRequest $request_model): Response
    {
        return Inertia::render('Requests/Edit', [
            'serviceRequest' => $request_model,
            'requestTypes' => RequestType::orderBy('name')->get(),
        ]);
    }

    public function update(Request $httpRequest, ServiceRequest $request_model)
    {
        $validated = $httpRequest->validate([
            'request_type_id' => 'required|exists:request_types,id',
            'purpose' => 'required|string|max:500',
            'description' => 'nullable|string|max:2000',
        ]);

        $oldValues = $request_model->only(array_keys($validated));
        $request_model->update($validated);

        $this->auditLog->log('updated', 'requests', 'ServiceRequest', $request_model->id, $oldValues, $request_model->fresh()->toArray());

        return redirect()->route('requests.show', $request_model)
            ->with('success', 'Request updated successfully.');
    }

    public function destroy(ServiceRequest $request_model)
    {
        $oldValues = $request_model->toArray();
        $request_model->delete();

        $this->auditLog->log('deleted', 'requests', 'ServiceRequest', $request_model->id, $oldValues, null);

        return redirect()->route('requests.index')
            ->with('success', 'Request deleted successfully.');
    }

    /**
     * Assign a staff member to process the request.
     */
    public function assign(Request $httpRequest, ServiceRequest $request_model)
    {
        $validated = $httpRequest->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $oldStatus = $request_model->status;
        $request_model->update([
            'assigned_to' => $validated['assigned_to'],
            'status' => 'for_verification',
        ]);

        $request_model->statusHistories()->create([
            'user_id' => auth()->id(),
            'from_status' => $oldStatus,
            'to_status' => 'for_verification',
            'remarks' => 'Assigned to staff for verification',
        ]);

        $this->auditLog->log(
            'assigned request',
            'requests',
            'ServiceRequest',
            $request_model->id,
            ['assigned_to' => $request_model->getOriginal('assigned_to'), 'status' => $oldStatus],
            ['assigned_to' => $validated['assigned_to'], 'status' => 'for_verification']
        );

        return back()->with('success', 'Request assigned successfully.');
    }

    /**
     * Update the status of a request through the workflow.
     */
    public function process(Request $httpRequest, ServiceRequest $request_model)
    {
        $validated = $httpRequest->validate([
            'status' => 'required|in:for_verification,approved,rejected,processing,ready_for_release,released,cancelled',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $oldStatus = $request_model->status;
        $newStatus = $validated['status'];

        $updateData = ['status' => $newStatus];

        if (in_array($newStatus, ['processing', 'ready_for_release'])) {
            $updateData['processed_at'] = now();
        }
        if ($newStatus === 'released') {
            $updateData['released_at'] = now();
        }

        $request_model->update($updateData);

        $request_model->statusHistories()->create([
            'user_id' => auth()->id(),
            'from_status' => $oldStatus,
            'to_status' => $newStatus,
            'remarks' => $validated['remarks'] ?? "Status changed to {$newStatus}",
        ]);

        // Notify the requester
        $request_model->requester?->notify(new \App\Notifications\RequestStatusChanged($request_model));

        $this->auditLog->log(
            'updated request status',
            'requests',
            'ServiceRequest',
            $request_model->id,
            ['status' => $oldStatus],
            ['status' => $newStatus]
        );

        return back()->with('success', 'Request status updated successfully.');
    }

    public function approve(Request $httpRequest, ServiceRequest $request_model)
    {
        return $this->process($httpRequest->merge(['status' => 'approved']), $request_model);
    }

    public function reject(Request $httpRequest, ServiceRequest $request_model)
    {
        $httpRequest->validate(['remarks' => 'required|string|max:1000']);
        return $this->process($httpRequest->merge(['status' => 'rejected']), $request_model);
    }
}