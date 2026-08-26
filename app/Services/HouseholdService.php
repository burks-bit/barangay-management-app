<?php

namespace App\Services;

use App\Models\EvacuationCenter;
use App\Models\Household;
use App\Models\MemberProfile;
use App\Models\Purok;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class HouseholdService
{
    public function list(Request $request): LengthAwarePaginator
    {
        return Household::with(['purok', 'headOfFamily', 'evacuationCenter'])
            ->withCount('members')
            ->when($request->input('search'), function ($query, $search) {
                $query->where(function ($households) use ($search) {
                    $households->where('household_code', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%")
                        ->orWhere('contact_number', 'like', "%{$search}%")
                        ->orWhereHas('headOfFamily', function ($head) use ($search) {
                            $head->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->input('purok_id'), fn ($query, $purokId) => $query->where('purok_id', $purokId))
            ->when($request->input('evacuation_status'), fn ($query, $status) => $query->where('evacuation_status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();
    }

    public function puroks(): Collection
    {
        return Purok::orderBy('name')->get(['id', 'name']);
    }

    public function residents(): Collection
    {
        return MemberProfile::orderBy('last_name')->get(['id', 'first_name', 'middle_name', 'last_name', 'suffix']);
    }

    public function activeResidents(): Collection
    {
        return MemberProfile::where('verification_status', '!=', 'inactive')
            ->orderBy('last_name')->get(['id', 'first_name', 'middle_name', 'last_name', 'suffix']);
    }

    public function evacuationCenters(): Collection
    {
        return EvacuationCenter::orderBy('name')->get(['id', 'name', 'status', 'capacity', 'current_occupancy']);
    }

    public function openEvacuationCenters(): Collection
    {
        return EvacuationCenter::where('status', '!=', 'closed')->orderBy('name')->get(['id', 'name', 'location', 'capacity', 'current_occupancy']);
    }

    public function create(Request $request): void
    {
        $validated = $this->validate($request);

        $evacuationStatus = $validated['evacuation_status'] ?? 'none';
        $validated['evacuation_status'] = $evacuationStatus;
        if ($evacuationStatus === 'evacuated' && !empty($validated['evacuation_center_id'])) {
            $validated['evacuated_at'] = now();
        }

        $household = Household::create($validated);

        if (!empty($validated['evacuation_center_id']) && $evacuationStatus === 'evacuated') {
            EvacuationCenter::find($validated['evacuation_center_id'])->recalculateOccupancy();
        }
    }

    public function show(Household $household): Household
    {
        return $household->load(['purok', 'headOfFamily', 'evacuationCenter', 'members.memberProfile']);
    }

    public function update(Request $request, Household $household): void
    {
        $validated = $this->validate($request, $household);

        $oldCenterId = $household->evacuation_center_id;

        $evacuationStatus = $validated['evacuation_status'] ?? 'none';
        $validated['evacuation_status'] = $evacuationStatus;

        if ($evacuationStatus === 'evacuated') {
            $validated['evacuated_at'] = $household->evacuated_at ?? now();
        } else {
            $validated['evacuated_at'] = null;
            if ($evacuationStatus === 'returned' || $evacuationStatus === 'none') {
                $validated['evacuation_center_id'] = null;
                $validated['evacuated_at'] = null;
            }
        }

        $household->update($validated);

        // Recalculate occupancy for old and new centers
        $centersToRecalc = collect([$oldCenterId, $validated['evacuation_center_id'] ?? null])
            ->filter()
            ->unique();

        foreach ($centersToRecalc as $centerId) {
            EvacuationCenter::find($centerId)?->recalculateOccupancy();
        }
    }

    public function delete(Household $household): void
    {
        $centerId = $household->evacuation_center_id;
        $household->delete();

        if ($centerId) {
            EvacuationCenter::find($centerId)?->recalculateOccupancy();
        }
    }

    public function evacuate(Request $request, Household $household): void
    {
        $validated = $request->validate([
            'evacuation_center_id' => 'required|exists:evacuation_centers,id',
        ]);

        $oldCenterId = $household->evacuation_center_id;

        $household->update([
            'evacuation_center_id' => $validated['evacuation_center_id'],
            'evacuation_status' => 'evacuated',
            'evacuated_at' => now(),
        ]);

        $centers = collect([$oldCenterId, $validated['evacuation_center_id']])->unique();
        foreach ($centers as $centerId) {
            EvacuationCenter::find($centerId)?->recalculateOccupancy();
        }
    }

    public function returnHome(Household $household): void
    {
        $centerId = $household->evacuation_center_id;

        $household->update([
            'evacuation_center_id' => null,
            'evacuation_status' => 'returned',
            'evacuated_at' => null,
        ]);

        if ($centerId) {
            EvacuationCenter::find($centerId)?->recalculateOccupancy();
        }
    }

    private function validate(Request $request, ?Household $household = null): array
    {
        $uniqueRule = 'unique:households';
        if ($household) {
            $uniqueRule .= ',household_code,' . $household->id;
        }

        return $request->validate([
            'household_code' => 'required|string|max:50|' . $uniqueRule,
            'address' => 'required|string|max:500',
            'purok_id' => 'required|exists:puroks,id',
            'contact_number' => 'nullable|string|max:20',
            'head_of_family_id' => 'nullable|exists:member_profiles,id',
            'evacuation_center_id' => 'nullable|exists:evacuation_centers,id',
            'evacuation_status' => 'nullable|in:none,evacuated,returned',
            'vulnerability_indicators' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);
    }
}