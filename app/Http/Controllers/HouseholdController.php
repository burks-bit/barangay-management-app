<?php

namespace App\Http\Controllers;

use App\Models\Household;
use App\Services\HouseholdService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HouseholdController extends Controller
{
    public function __construct(private HouseholdService $households)
    {
    }

    public function index(Request $request): Response
    {
        return Inertia::render('Households/Index', [
            'households' => $this->households->list($request),
            'filters' => $request->only(['search', 'purok_id', 'evacuation_status']),
            'puroks' => $this->households->puroks(),
            'evacuationCenters' => $this->households->evacuationCenters(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Households/Create', [
            'puroks' => $this->households->puroks(),
            'residents' => $this->households->residents(),
            'evacuationCenters' => $this->households->openEvacuationCenters(),
        ]);
    }

    public function store(Request $request)
    {
        return $this->handle(
            fn () => $this->households->create($request),
            fn () => redirect()->route('households.index')->with('success', 'Household created successfully.'),
            'HouseholdController::store'
        );
    }

    public function edit(Household $household): Response
    {
        return Inertia::render('Households/Edit', [
            'household' => $this->households->show($household),
            'puroks' => $this->households->puroks(),
            'residents' => $this->households->activeResidents(),
            'evacuationCenters' => $this->households->evacuationCenters(),
        ]);
    }

    public function update(Request $request, Household $household)
    {
        return $this->handle(
            fn () => $this->households->update($request, $household),
            fn () => redirect()->route('households.index')->with('success', 'Household updated successfully.'),
            'HouseholdController::update'
        );
    }

    public function destroy(Household $household)
    {
        return $this->handle(
            fn () => $this->households->delete($household),
            fn () => redirect()->route('households.index')->with('success', 'Household deleted successfully.'),
            'HouseholdController::destroy'
        );
    }

    public function evacuate(Request $request, Household $household)
    {
        return $this->handle(
            fn () => $this->households->evacuate($request, $household),
            fn () => back()->with('success', 'Household evacuated successfully.'),
            'HouseholdController::evacuate'
        );
    }

    public function returnHome(Household $household)
    {
        return $this->handle(
            fn () => $this->households->returnHome($household),
            fn () => back()->with('success', 'Household marked as returned home.'),
            'HouseholdController::returnHome'
        );
    }
}