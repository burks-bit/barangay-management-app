<?php

namespace App\Http\Controllers;

use App\Models\AssistanceRequest;
use App\Services\AssistanceService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AssistanceController extends Controller
{
    public function __construct(private AssistanceService $assistance)
    {
        // Authorization is enforced via route-level `can:` middleware (see routes/web.php).
    }

    public function index(Request $request): Response
    {
        return Inertia::render('Assistance/Index', [
            'requests' => $this->assistance->list($request),
            'filters' => $request->only(['status']),
        ]);
    }

    public function myAssistance(Request $request): Response
    {
        return Inertia::render('Assistance/MyAssistance', [
            'requests' => $this->assistance->listForApplicant($request->user(), $request),
            'filters' => $request->only(['status']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Assistance/Create', [
            'assistanceTypes' => $this->assistance->types(),
        ]);
    }

    public function store(Request $request)
    {
        return $this->handle(
            fn () => $this->assistance->create($request, $request->user()),
            fn () => redirect()->route('my-assistance')->with('success', 'Assistance request submitted successfully.'),
            'AssistanceController::store'
        );
    }

    /**
     * Update the status of an assistance request (staff workflow actions).
     */
    public function updateStatus(Request $httpRequest, AssistanceRequest $assistance_request)
    {
        $status = str_replace('_', ' ', $httpRequest->input('status'));

        return $this->handle(
            fn () => $this->assistance->updateStatus($httpRequest, $assistance_request, $httpRequest->user()),
            fn () => back()->with('success', "Assistance request marked as {$status}."),
            'AssistanceController::updateStatus'
        );
    }
}