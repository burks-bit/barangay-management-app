<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use App\Services\ServiceRequestService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ServiceRequestController extends Controller
{
    public function __construct(private ServiceRequestService $requests)
    {
        // Authorization is enforced via route-level `can:` middleware (see routes/web.php).
    }

    /**
     * Admin/Moderator view of all requests.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('Requests/Index', [
            'requests' => $this->requests->list($request),
            'filters' => $request->only(['search', 'status', 'request_type_id']),
            'requestTypes' => $this->requests->requestTypes(),
        ]);
    }

    /**
     * Member view of their own requests.
     */
    public function myRequests(Request $request): Response
    {
        return Inertia::render('Requests/MyRequests', [
            'requests' => $this->requests->listForRequester($request->user(), $request),
            'filters' => $request->only(['status']),
        ]);
    }

    public function myShow(Request $request, ServiceRequest $service_request): Response
    {
        abort_unless($service_request->requester_id === $request->user()->id, 403);

        return Inertia::render('Requests/Show', [
            'serviceRequest' => $service_request->load(['requester.memberProfile.purok', 'requestType', 'assignedStaff', 'encoder', 'approverOfficial', 'statusHistories.user', 'attachments']),
            'backUrl' => route('my-requests'),
            'staff' => [],
            'captains' => [],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Requests/Create', [
            'requestTypes' => $this->requests->requestTypes(),
        ]);
    }

    public function store(Request $request)
    {
        return $this->handle(
            fn () => $this->requests->create($request, $request->user()),
            fn () => redirect()->route('my-requests')->with('success', 'Request submitted successfully. You can track it using your tracking number.'),
            'ServiceRequestController::store'
        );
    }

    /**
     * Walk-in form for admin/moderator: encode a request on behalf of a
     * resident who cannot submit one online (e.g., no phone).
     */
    public function createWalkIn(): Response
    {
        return Inertia::render('Requests/CreateWalkIn', [
            'residents' => $this->requests->walkInResidents(),
            'requestTypes' => $this->requests->requestTypes(),
        ]);
    }

    /**
     * Store a walk-in request encoded by staff on behalf of a resident.
     */
    public function storeWalkIn(Request $request)
    {
        return $this->handle(
            fn () => $this->requests->createWalkIn($request),
            fn ($serviceRequest) => redirect()->route('requests.show', $serviceRequest)
                ->with('success', "Walk-in request {$serviceRequest->tracking_number} created successfully."),
            'ServiceRequestController::storeWalkIn'
        );
    }

    public function show(ServiceRequest $service_request): Response
    {
        return Inertia::render('Requests/Show', $this->requests->show($service_request));
    }

    public function encode(Request $request, ServiceRequest $service_request)
    {
        return $this->handle(
            fn () => $this->requests->encode($request, $service_request),
            fn () => back()->with('success', 'Document encoded and ready for captain release.'),
            'ServiceRequestController::encode'
        );
    }

    public function release(Request $request, ServiceRequest $service_request)
    {
        return $this->handle(
            fn () => $this->requests->release($request, $service_request),
            fn () => back()->with('success', 'Document released successfully.'),
            'ServiceRequestController::release'
        );
    }

    public function download(Request $request, ServiceRequest $service_request)
    {
        abort_unless($service_request->requester_id === $request->user()->id || $request->user()->can('view requests'), 403);
        $isPreview = $request->boolean('preview');
        abort_unless(! $isPreview || $request->user()->can('process requests'), 403);
        abort_unless(($isPreview || $service_request->status === 'released') && $service_request->document_content, 404);

        return $this->requests->downloadPdf($request, $service_request);
    }

    public function edit(ServiceRequest $request_model): Response
    {
        return Inertia::render('Requests/Edit', [
            'serviceRequest' => $request_model,
            'requestTypes' => $this->requests->requestTypes(),
        ]);
    }

    public function update(Request $httpRequest, ServiceRequest $request_model)
    {
        return $this->handle(
            fn () => $this->requests->update($httpRequest, $request_model),
            fn () => redirect()->route('requests.show', $request_model)->with('success', 'Request updated successfully.'),
            'ServiceRequestController::update'
        );
    }

    public function destroy(ServiceRequest $request_model)
    {
        return $this->handle(
            fn () => $this->requests->delete($request_model),
            fn () => redirect()->route('requests.index')->with('success', 'Request deleted successfully.'),
            'ServiceRequestController::destroy'
        );
    }

    /**
     * Assign a staff member to process the request.
     */
    public function assign(Request $httpRequest, ServiceRequest $request_model)
    {
        return $this->handle(
            fn () => $this->requests->assign($httpRequest, $request_model),
            fn () => back()->with('success', 'Request assigned successfully.'),
            'ServiceRequestController::assign'
        );
    }

    /**
     * Update the status of a request through the workflow.
     */
    public function process(Request $httpRequest, ServiceRequest $request_model)
    {
        return $this->handle(
            fn () => $this->requests->process($httpRequest, $request_model),
            fn () => back()->with('success', 'Request status updated successfully.'),
            'ServiceRequestController::process'
        );
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