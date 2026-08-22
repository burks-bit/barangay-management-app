<?php

namespace App\Http\Controllers;

use App\Models\EvacuationCenter;
use App\Models\Household;
use App\Models\HouseholdMember;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EvacuationCenterController extends Controller
{
    public function index(Request $request): Response
    {
        $centers = EvacuationCenter::withCount(['evacuationEvents as active_events_count' => fn ($query) => $query->where('status', 'active')])
            ->withCount(['evacuatedHouseholds as evacuated_households_count'])
            ->when($request->input('search'), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%")
                        ->orWhere('contact_person', 'like', "%{$search}%");
                });
            })
            ->when($request->input('status'), fn ($query, $status) => $query->where('status', $status))
            ->orderBy('name')
            ->get();

        $userHousehold = null;
        $profile = $request->user()?->memberProfile;

        if ($profile) {
            $userHousehold = $profile->household_id
                ? Household::find($profile->household_id)
                : null;

            if (!$userHousehold) {
                $userHousehold = HouseholdMember::where('member_profile_id', $profile->id)
                    ->first()?->household;
            }

            if (!$userHousehold) {
                $userHousehold = Household::where('head_of_family_id', $profile->id)->first();
            }

            $userHousehold?->load('evacuationCenter');
        }

        return Inertia::render('EvacuationCenters/Index', [
            'centers' => $centers,
            'filters' => $request->only(['search', 'status']),
            'userHousehold' => $userHousehold,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('EvacuationCenters/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'location' => 'required|string|max:500',
            'capacity' => 'required|integer|min:0',
            'facilities' => 'nullable|array',
            'facilities.*' => 'string',
            'contact_person' => 'nullable|string|max:200',
            'contact_number' => 'nullable|string|max:20',
            'status' => 'required|in:available,occupied,full,closed',
            'notes' => 'nullable|string',
        ]);

        $validated['current_occupancy'] = 0;

        EvacuationCenter::create($validated);

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
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'location' => 'required|string|max:500',
            'capacity' => 'required|integer|min:0',
            'facilities' => 'nullable|array',
            'facilities.*' => 'string',
            'contact_person' => 'nullable|string|max:200',
            'contact_number' => 'nullable|string|max:20',
            'status' => 'required|in:available,occupied,full,closed',
            'notes' => 'nullable|string',
        ]);

        $evacuationCenter->update($validated);
        $evacuationCenter->recalculateOccupancy();

        return redirect()->route('evacuation-centers.index')->with('success', 'Evacuation center updated successfully.');
    }

    public function destroy(EvacuationCenter $evacuationCenter)
    {
        // Mark evacuated households as returned before deleting
        $evacuationCenter->households()
            ->where('evacuation_status', 'evacuated')
            ->update([
                'evacuation_status' => 'returned',
                'evacuation_center_id' => null,
                'evacuated_at' => null,
            ]);

        $evacuationCenter->delete();

        return redirect()->route('evacuation-centers.index')->with('success', 'Evacuation center deleted successfully.');
    }
}