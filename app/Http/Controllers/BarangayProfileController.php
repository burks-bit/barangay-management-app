<?php

namespace App\Http\Controllers;

use App\Models\BarangayOfficial;
use App\Models\BarangayProfile;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BarangayProfileController extends Controller
{
    public function index(Request $request): Response
    {
        $profiles = BarangayProfile::withCount('officials')
            ->orderBy('name')
            ->get();

        return Inertia::render('Barangay/Index', [
            'profiles' => $profiles,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Barangay/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'description' => 'nullable|string',
            'mission' => 'nullable|string',
            'vision' => 'nullable|string',
            'address' => 'nullable|string|max:500',
            'about' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        BarangayProfile::create($validated);

        return redirect()->route('barangay.index')->with('success', 'Barangay profile created successfully.');
    }

    public function storeOfficial(Request $request, BarangayProfile $barangay)
    {
        $validated = $request->validate([
            'position' => 'required|in:captain,vice_captain,kagawad,secretary,treasurer,sangguniang_kabataan_chairperson,barangay_tanod,health_worker,other',
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'suffix' => 'nullable|string|max:10',
            'sex' => 'nullable|in:male,female,other',
            'date_of_birth' => 'nullable|date',
            'contact_number' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'committee' => 'nullable|string|max:200',
            'term_start' => 'required|integer|min:1900|max:2100',
            'term_end' => 'nullable|integer|min:1900|max:2100',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $barangay->officials()->create($validated);

        return redirect()->route('barangay.show', $barangay)->with('success', 'Official added successfully.');
    }

    public function show(BarangayProfile $barangay): Response
    {
        $barangay->load(['officials' => function ($q) {
            $q->orderBy('term_start', 'desc')->orderBy('position');
        }]);

        return Inertia::render('Barangay/Show', [
            'barangay' => $barangay,
        ]);
    }

    public function edit(BarangayProfile $barangay): Response
    {
        return Inertia::render('Barangay/Edit', [
            'barangay' => $barangay,
        ]);
    }

    public function update(Request $request, BarangayProfile $barangay)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'description' => 'nullable|string',
            'mission' => 'nullable|string',
            'vision' => 'nullable|string',
            'address' => 'nullable|string|max:500',
            'about' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $barangay->update($validated);

        return redirect()->route('barangay.show', $barangay)->with('success', 'Barangay profile updated successfully.');
    }

    public function destroy(BarangayProfile $barangay)
    {
        $barangay->delete();

        return redirect()->route('barangay.index')->with('success', 'Barangay profile deleted successfully.');
    }

    public function updateOfficial(Request $request, BarangayProfile $barangay, BarangayOfficial $official)
    {
        $validated = $request->validate([
            'position' => 'required|in:captain,vice_captain,kagawad,secretary,treasurer,sangguniang_kabataan_chairperson,barangay_tanod,health_worker,other',
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'suffix' => 'nullable|string|max:10',
            'sex' => 'nullable|in:male,female,other',
            'date_of_birth' => 'nullable|date',
            'contact_number' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'committee' => 'nullable|string|max:200',
            'term_start' => 'required|integer|min:1900|max:2100',
            'term_end' => 'nullable|integer|min:1900|max:2100',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $official->update($validated);

        return redirect()->route('barangay.show', $barangay)->with('success', 'Official updated successfully.');
    }

    public function destroyOfficial(BarangayProfile $barangay, BarangayOfficial $official)
    {
        $official->delete();

        return redirect()->route('barangay.show', $barangay)->with('success', 'Official removed successfully.');
    }
}