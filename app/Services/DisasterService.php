<?php

namespace App\Services;

use App\Models\Calamity;
use App\Models\EvacuationCenter;
use App\Models\EvacuationEvent;
use App\Models\Household;
use App\Models\HouseholdMember;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class DisasterService extends Service
{
    public function calamities(Request $request): LengthAwarePaginator
    {
        return Calamity::with('puroks')
            ->when($request->input('status'), fn ($query, $status) => $query->where('status', $status))
            ->latest('started_at')
            ->paginate(15)
            ->withQueryString();
    }

    public function evacuationCenters(): Collection
    {
        return EvacuationCenter::withCount(['evacuationEvents as active_events_count' => fn ($query) => $query->where('status', 'active')])
            ->withCount(['evacuatedHouseholds as evacuated_households_count'])
            ->orderBy('name')
            ->get();
    }

    public function householdForUser(User $user): ?Household
    {
        $profile = $user?->memberProfile;

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

    public function selectEvacuationCenter(Request $request, User $user): array
    {
        $validated = $request->validate([
            'evacuation_center_id' => 'required|exists:evacuation_centers,id',
        ]);

        $selectedCenter = EvacuationCenter::findOrFail($validated['evacuation_center_id']);
        if ($selectedCenter->status === 'closed' || $selectedCenter->current_occupancy >= $selectedCenter->capacity) {
            return ['error' => 'This evacuation center is currently full or closed.'];
        }

        $household = $this->householdForUser($user);

        if (!$household) {
            return ['error' => 'You do not have an associated household to evacuate.'];
        }

        $oldCenterId = $household->evacuation_center_id;

        if ($oldCenterId === $selectedCenter->id && $household->evacuation_status === 'evacuated') {
            return ['info' => 'Your household is already assigned to this evacuation center.'];
        }

        $this->transaction(function () use ($household, $selectedCenter, $oldCenterId) {
            $household->update([
                'evacuation_center_id' => $selectedCenter->id,
                'evacuation_status' => 'evacuated',
                'evacuated_at' => now(),
            ]);

            $centers = collect([$oldCenterId, $selectedCenter->id])->filter()->unique();
            foreach ($centers as $centerId) {
                EvacuationCenter::find($centerId)?->recalculateOccupancy();
            }
        }, 'Failed to assign household to evacuation center.');

        return ['success' => 'Your household has been evacuated successfully.'];
    }

    public function returnHome(User $user): array
    {
        $household = $this->householdForUser($user);

        if (!$household) {
            return ['error' => 'You do not have an associated household record.'];
        }

        $centerId = $household->evacuation_center_id;

        $this->transaction(function () use ($household, $centerId) {
            $household->update([
                'evacuation_center_id' => null,
                'evacuation_status' => 'returned',
                'evacuated_at' => null,
            ]);

            if ($centerId) {
                EvacuationCenter::find($centerId)?->recalculateOccupancy();
            }
        }, 'Failed to mark household as returned home.');

        return ['success' => 'Your household has been marked as returned home.'];
    }

    public function evacuations(): Collection
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

        return $events;
    }
}