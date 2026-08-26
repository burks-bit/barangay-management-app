<?php

namespace App\Services;

use App\Models\BarangayOfficial;
use App\Models\BarangayProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class BarangayProfileService
{
    public function list(): Collection
    {
        return BarangayProfile::withCount('officials')
            ->orderBy('name')
            ->get();
    }

    public function create(Request $request): void
    {
        $validated = $this->validateProfile($request);

        BarangayProfile::create($validated);
    }

    public function show(BarangayProfile $barangay): BarangayProfile
    {
        $barangay->load(['officials' => function ($q) {
            $q->orderBy('term_start', 'desc')->orderBy('position');
        }]);

        return $barangay;
    }

    public function update(Request $request, BarangayProfile $barangay): void
    {
        $validated = $this->validateProfile($request);

        $barangay->update($validated);
    }

    public function delete(BarangayProfile $barangay): void
    {
        $barangay->delete();
    }

    public function createOfficial(Request $request, BarangayProfile $barangay): void
    {
        $validated = $this->validateOfficial($request);

        $barangay->officials()->create($validated);
    }

    public function updateOfficial(Request $request, BarangayOfficial $official): void
    {
        $validated = $this->validateOfficial($request);

        $official->update($validated);
    }

    public function deleteOfficial(BarangayOfficial $official): void
    {
        $official->delete();
    }

    private function validateProfile(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:200',
            'description' => 'nullable|string',
            'mission' => 'nullable|string',
            'vision' => 'nullable|string',
            'address' => 'nullable|string|max:500',
            'about' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
    }

    private function validateOfficial(Request $request): array
    {
        return $request->validate([
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
    }
}