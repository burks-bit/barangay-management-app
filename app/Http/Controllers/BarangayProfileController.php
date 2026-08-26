<?php

namespace App\Http\Controllers;

use App\Models\BarangayOfficial;
use App\Models\BarangayProfile;
use App\Services\BarangayProfileService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BarangayProfileController extends Controller
{
    public function __construct(private BarangayProfileService $barangays)
    {
    }

    public function index(Request $request): Response
    {
        return Inertia::render('Barangay/Index', [
            'profiles' => $this->barangays->list(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Barangay/Create');
    }

    public function store(Request $request)
    {
        $this->barangays->create($request);

        return redirect()->route('barangay.index')->with('success', 'Barangay profile created successfully.');
    }

    public function storeOfficial(Request $request, BarangayProfile $barangay)
    {
        $this->barangays->createOfficial($request, $barangay);

        return redirect()->route('barangay.show', $barangay)->with('success', 'Official added successfully.');
    }

    public function show(BarangayProfile $barangay): Response
    {
        return Inertia::render('Barangay/Show', [
            'barangay' => $this->barangays->show($barangay),
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
        $this->barangays->update($request, $barangay);

        return redirect()->route('barangay.show', $barangay)->with('success', 'Barangay profile updated successfully.');
    }

    public function destroy(BarangayProfile $barangay)
    {
        $this->barangays->delete($barangay);

        return redirect()->route('barangay.index')->with('success', 'Barangay profile deleted successfully.');
    }

    public function updateOfficial(Request $request, BarangayProfile $barangay, BarangayOfficial $official)
    {
        $this->barangays->updateOfficial($request, $official);

        return redirect()->route('barangay.show', $barangay)->with('success', 'Official updated successfully.');
    }

    public function destroyOfficial(BarangayProfile $barangay, BarangayOfficial $official)
    {
        $this->barangays->deleteOfficial($official);

        return redirect()->route('barangay.show', $barangay)->with('success', 'Official removed successfully.');
    }
}