<?php

namespace App\Http\Controllers;

use App\Models\EvacuationCenter;
use App\Models\Household;
use App\Models\MemberProfile;
use App\Models\Purok;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HouseholdController extends Controller
{
    public function index(Request $request): Response
    {
        $households = Household::with(['purok', 'headOfFamily', 'evacuationCenter'])
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

        return Inertia::render('Households/Index', [
            'households' => $households,
            'filters' => $request->only(['search', 'purok_id', 'evacuation_status']),
            'puroks' => Purok::orderBy('name')->get(['id', 'name']),
            'evacuationCenters' => EvacuationCenter::orderBy('name')->get(['id', 'name', 'status', 'capacity', 'current_occupancy']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Households/Create', [
            'puroks' => Purok::orderBy('name')->get(['id', 'name']),
            'residents' => MemberProfile::orderBy('last_name')->get(['id', 'first_name', 'middle_name', 'last_name', 'suffix']),
            'evacuationCenters' => EvacuationCenter::where('status', '!=', 'closed')->orderBy('name')->get(['id', 'name', 'location', 'capacity', 'current_occupancy']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'household_code' => 'required|string|max:50|unique:households',
            'address' => 'required|string|max:500',
            'purok_id' => 'required|exists:puroks,id',
            'contact_number' => 'nullable|string|max:20',
            'head_of_family_id' => 'nullable|exists:member_profiles,id',
            'evacuation_center_id' => 'nullable|exists:evacuation_centers,id',
            'evacuation_status' => 'nullable|in:none,evacuated,returned',
            'vulnerability_indicators' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        $evacuationStatus = $validated['evacuation_status'] ?? 'none';
        $validated['evacuation_status'] = $evacuationStatus;
        if ($evacuationStatus === 'evacuated' && !empty($validated['evacuation_center_id'])) {
            $validated['evacuated_at'] = now();
        }

        $household = Household::create($validated);

        if (!empty($validated['evacuation_center_id']) && $evacuationStatus === 'evacuated') {
            EvacuationCenter::find($validated['evacuation_center_id'])->recalculateOccupancy();
        }

        return redirect()->route('households.index')->with('success', 'Household created successfully.');
    }

    public function edit(Household $household): Response
    {
        $household->load(['purok', 'headOfFamily', 'evacuationCenter', 'members.memberProfile']);

        return Inertia::render('Households/Edit', [
            'household' => $household,
            'puroks' => Purok::orderBy('name')->get(['id', 'name']),
            'residents' => MemberProfile::where('verification_status', '!=', 'inactive')
                ->orderBy('last_name')->get(['id', 'first_name', 'middle_name', 'last_name', 'suffix']),
            'evacuationCenters' => EvacuationCenter::orderBy('name')->get(['id', 'name', 'location', 'capacity', 'current_occupancy']),
        ]);
    }

    public function update(Request $request, Household $household)
    {
        $validated = $request->validate([
            'household_code' => 'required|string|max:50|unique:households,household_code,' . $household->id,
            'address' => 'required|string|max:500',
            'purok_id' => 'required|exists:puroks,id',
            'contact_number' => 'nullable|string|max:20',
            'head_of_family_id' => 'nullable|exists:member_profiles,id',
            'evacuation_center_id' => 'nullable|exists:evacuation_centers,id',
            'evacuation_status' => 'nullable|in:none,evacuated,returned',
            'vulnerability_indicators' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        $oldCenterId = $household->evacuation_center_id;
        $oldStatus = $household->evacuation_status;

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

        return redirect()->route('households.index')->with('success', 'Household updated successfully.');
    }

    public function destroy(Household $household)
    {
        $centerId = $household->evacuation_center_id;
        $household->delete();

        if ($centerId) {
            EvacuationCenter::find($centerId)?->recalculateOccupancy();
        }

        return redirect()->route('households.index')->with('success', 'Household deleted successfully.');
    }

    public function evacuate(Request $request, Household $household)
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

        return back()->with('success', 'Household evacuated successfully.');
    }

    public function returnHome(Household $household)
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

        return back()->with('success', 'Household marked as returned home.');
    }
}