<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use App\Services\IncidentService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IncidentController extends Controller
{
    public function __construct(private IncidentService $incidents)
    {
        // Authorization is enforced via route-level middleware (see routes/web.php).
    }

    /**
     * Admin/Moderator view of all active incidents.
     * Members are redirected to their own incidents page.
     */
    public function index(Request $request)
    {
        if ($request->user()->hasRole('member')) {
            return redirect()->route('my-incidents');
        }

        return Inertia::render('Incidents/Index', [
            'incidents' => $this->incidents->list($request),
            'filters' => $request->only(['search', 'status', 'type', 'severity']),
        ]);
    }

    /**
     * Admin/Moderator view of a single incident.
     * Pure members are redirected to their own incidents view.
     */
    public function show(Request $request, Incident $incident)
    {
        if ($request->user()->hasRole('member')
            && !($request->user()->hasRole('admin') || $request->user()->hasRole('moderator'))) {
            return redirect()->route('my-incidents');
        }

        return Inertia::render('Incidents/Show', [
            'incident' => $this->incidents->show($incident),
            'backUrl' => route('incidents.index'),
        ]);
    }

    /**
     * Update the status of an incident (admin/moderator).
     */
    public function updateStatus(Request $request, Incident $incident)
    {
        $this->incidents->updateStatus($request, $incident, $request->user());

        return back()->with('success', 'Incident status updated successfully.');
    }

    /**
     * Member view of their own submitted incidents.
     */
    public function myIncidents(Request $request): Response
    {
        return Inertia::render('Incidents/MyIncidents', [
            'incidents' => $this->incidents->listForReporter($request->user(), $request),
            'filters' => $request->only(['status']),
        ]);
    }

    /**
     * Form to report a new incident (member-facing).
     */
    public function create(): Response
    {
        return Inertia::render('Incidents/Create', [
            'puroks' => $this->incidents->puroks(),
            'calamities' => $this->incidents->activeCalamities(),
        ]);
    }

    /**
     * Store a newly reported incident.
     */
    public function store(Request $request)
    {
        $this->incidents->create($request, $request->user());

        return redirect()->route('my-incidents')
            ->with('success', 'Incident reported successfully. Barangay officials will review and respond to your report.');
    }

    /**
     * Member view of a single incident.
     */
    public function myShow(Request $request, Incident $incident): Response
    {
        abort_unless($incident->reported_by === $request->user()->id, 403);

        return Inertia::render('Incidents/MyShow', [
            'incident' => $this->incidents->show($incident),
            'backUrl' => route('my-incidents'),
        ]);
    }
}