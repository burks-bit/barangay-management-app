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
        return $this->handle(
            fn () => $this->evacuationCenters->create($request),
            fn () => redirect()->route('evacuation-centers.index')->with('success', 'Evacuation center created successfully.'),
            'EvacuationCenterController::store'
        );
    }

    public function edit(EvacuationCenter $evacuationCenter): Response
    {
        return Inertia::render('EvacuationCenters/Edit', [
            'center' => $evacuationCenter,
        ]);
    }

    public function update(Request $request, EvacuationCenter $evacuationCenter)
    {
        return $this->handle(
            fn () => $this->evacuationCenters->update($request, $evacuationCenter),
            fn () => redirect()->route('evacuation-centers.index')->with('success', 'Evacuation center updated successfully.'),
            'EvacuationCenterController::update'
        );
    }

    public function destroy(EvacuationCenter $evacuationCenter)
    {
        return $this->handle(
            fn () => $this->evacuationCenters->delete($evacuationCenter),
            fn () => redirect()->route('evacuation-centers.index')->with('success', 'Evacuation center deleted successfully.'),
            'EvacuationCenterController::destroy'
        );
    }
}