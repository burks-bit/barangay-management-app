<?php

namespace App\Services;

use App\Models\EvacuationCenter;
use App\Models\Household;
use App\Models\HouseholdMember;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class EvacuationCenterService
{
    public function list(Request $request): Collection
    {
        return EvacuationCenter::withCount(['evacuationEvents as active_events_count' => fn ($query) => $query->where('status', 'active')])
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

    public function create(Request $request): void
    {
        $validated = $this->validate($request);
        $validated['current_occupancy'] = 0;

        EvacuationCenter::create($validated);
    }

    public function update(Request $request, EvacuationCenter $evacuationCenter): void
    {
        $validated = $this->validate($request);

        $evacuationCenter->update($validated);
        $evacuationCenter->recalculateOccupancy();
    }

    public function delete(EvacuationCenter $evacuationCenter): void
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
    }

    private function validate(Request $request): array
    {
        return $request->validate([
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
    }
}