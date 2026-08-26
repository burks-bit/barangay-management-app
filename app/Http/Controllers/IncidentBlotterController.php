<?php

namespace App\Http\Controllers;

use App\Models\IncidentBlotter;
use App\Services\IncidentBlotterService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IncidentBlotterController extends Controller
{
    public function __construct(private IncidentBlotterService $blotters)
    {
        // Authorization is enforced via route-level middleware (see routes/web.php).
    }

    /**
     * List all blotter entries with optional filters.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('Incidents/Blotters/Index', [
            'blotters' => $this->blotters->list($request),
            'filters' => $request->only(['search', 'status', 'entry_type']),
        ]);
    }

    /**
     * Form to record a new blotter entry.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('Incidents/Blotters/Create', [
            'puroks' => $this->blotters->puroks(),
            'incidents' => $this->blotters->openIncidents(),
            'prefill' => $request->only(['incident_id']),
        ]);
    }

    /**
     * Store a newly recorded blotter entry.
     */
    public function store(Request $request)
    {
        $this->blotters->create($request, $request->user());

        return redirect()->route('incidents.blotter.index')
            ->with('success', 'Blotter entry recorded successfully.');
    }

    /**
     * Display a blotter entry.
     */
    public function show(IncidentBlotter $blotter): Response
    {
        return Inertia::render('Incidents/Blotters/Show', [
            'blotter' => $this->blotters->show($blotter),
        ]);
    }

    /**
     * Update the status of a blotter entry.
     */
    public function updateStatus(Request $request, IncidentBlotter $blotter)
    {
        $this->blotters->updateStatus($request, $blotter);

        return back()->with('success', 'Blotter status updated successfully.');
    }

    /**
     * Delete a blotter entry.
     */
    public function destroy(IncidentBlotter $blotter)
    {
        $this->blotters->delete($blotter);

        return redirect()->route('incidents.blotter.index')
            ->with('success', 'Blotter entry deleted successfully.');
    }
}