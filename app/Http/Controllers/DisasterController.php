<?php

namespace App\Http\Controllers;

use App\Models\Calamity;
use App\Models\EvacuationCenter;
use App\Models\EvacuationEvent;
use App\Models\Household;
use App\Models\HouseholdMember;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DisasterController extends Controller
{
    public function calamities(Request $request): Response
    {
        $calamities = Calamity::with('puroks')
            ->when($request->input('status'), fn ($query, $status) => $query->where('status', $status))
            ->latest('started_at')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Calamities/Index', [
            'calamities' => $calamities,
            'filters' => $request->only(['status']),
        ]);
    }

    public function evacuationCenters(Request $request): Response
    {
        $centers = EvacuationCenter::withCount(['evacuationEvents as active_events_count' => fn ($query) => $query->where('status', 'active')])
            ->withCount(['evacuatedHouseholds as evacuated_households_count'])
            ->orderBy('name')
            ->get();

        $userHousehold = $this->householdForUser($request);

        return Inertia::render('EvacuationCenters/Index', [
            'centers' => $centers,
            'userHousehold' => $userHousehold,
        ]);
    }

    public function selectEvacuationCenter(Request $request)
    {
        $validated = $request->validate([
            'evacuation_center_id' => 'required|exists:evacuation_centers,id',
        ]);

        $selectedCenter = EvacuationCenter::findOrFail($validated['evacuation_center_id']);
        if ($selectedCenter->status === 'closed' || $selectedCenter->current_occupancy >= $selectedCenter->capacity) {
            return back()->with('error', 'This evacuation center is currently full or closed.');
        }

        $household = $this->householdForUser($request);

        if (!$household) {
            return back()->with('error', 'You do not have an associated household to evacuate.');
        }

        $oldCenterId = $household->evacuation_center_id;

        if ($oldCenterId === $selectedCenter->id && $household->evacuation_status === 'evacuated') {
            return back()->with('info', 'Your household is already assigned to this evacuation center.');
        }

        $household->update([
            'evacuation_center_id' => $selectedCenter->id,
            'evacuation_status' => 'evacuated',
            'evacuated_at' => now(),
        ]);

        $centers = collect([$oldCenterId, $selectedCenter->id])->filter()->unique();
        foreach ($centers as $centerId) {
            EvacuationCenter::find($centerId)?->recalculateOccupancy();
        }

        return back()->with('success', 'Your household has been evacuated successfully.');
    }

    public function returnHome(Request $request)
    {
        $household = $this->householdForUser($request);

        if (!$household) {
            return back()->with('error', 'You do not have an associated household record.');
        }

        $centerId = $household->evacuation_center_id;

        $household->update([
            'evacuation_center_id' => null,
            'evacuation_status' => 'returned',
            'evacuated_at' => null,
        ]);

        if ($centerId) {
            EvacuationCenter::find($centerId)?->recalculateOccupancy();
        }

        return back()->with('success', 'Your household has been marked as returned home.');
    }

    public function evacuations(): Response
    {
        $events = EvacuationEvent::with(['calamity', 'evacuationCenter'])
            ->withCount(['registrations as current_registrations_count' => fn ($query) => $query->whereNull('time_out')])
            ->latest('started_at')
            ->get();

        // Calculate total evacuees per event based on household member counts
        foreach ($events as $event) {
            $registrations = $event->registrations()
                ->whereNull('time_out')
                ->with('household')
                ->get();

            $event->total_evacuees = $registrations->sum(function ($reg) {
                return $reg->household ? $reg->household->members()->count() : 1;
            });

            $event->evacuated_households = $registrations
                ->whereNotNull('household_id')
                ->pluck('household_id')
                ->unique()
                ->count();
        }

        return Inertia::render('Evacuations/Index', [
            'events' => $events,
        ]);
    }

    private function householdForUser(Request $request): ?Household
    {
        $profile = $request->user()?->memberProfile;

        if (!$profile) {
            return null;
        }

        $household = $profile->household_id
            ? Household::find($profile->household_id)
            : null;

        if (!$household) {
            $household = HouseholdMember::where('member_profile_id', $profile->id)
                ->first()?->household;
        }

        if (!$household) {
            $household = Household::where('head_of_family_id', $profile->id)->first();
        }

        return $household?->load('evacuationCenter');
    }
}