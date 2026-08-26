<?php

namespace App\Http\Controllers;

use App\Models\EvacuationCenter;
use App\Services\EvacuationCenterService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EvacuationCenterController extends Controller
{
    public function __construct(private EvacuationCenterService $evacuationCenters)
    {
    }

    public function index(Request $request): Response
    {
        return Inertia::render('EvacuationCenters/Index', [
            'centers' => $this->evacuationCenters->list($request),
            'filters' => $request->only(['search', 'status']),
            'userHousehold' => $this->evacuationCenters->householdForUser($request->user()),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('EvacuationCenters/Create');
    }

    public function store(Request $request)
    {
        $this->evacuationCenters->create($request);

        return redirect()->route('evacuation-centers.index')->with('success', 'Evacuation center created successfully.');
    }

    public function edit(EvacuationCenter $evacuationCenter): Response
    {
        return Inertia::render('EvacuationCenters/Edit', [
            'center' => $evacuationCenter,
        ]);
    }

    public function update(Request $request, EvacuationCenter $evacuationCenter)
    {
        $this->evacuationCenters->update($request, $evacuationCenter);

        return redirect()->route('evacuation-centers.index')->with('success', 'Evacuation center updated successfully.');
    }

    public function destroy(EvacuationCenter $evacuationCenter)
    {
        $this->evacuationCenters->delete($evacuationCenter);

        return redirect()->route('evacuation-centers.index')->with('success', 'Evacuation center deleted successfully.');
    }
}