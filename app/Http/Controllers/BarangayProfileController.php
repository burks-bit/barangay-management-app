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
        return $this->handle(
            fn () => $this->barangays->create($request),
            fn () => redirect()->route('barangay.index')->with('success', 'Barangay profile created successfully.'),
            'BarangayProfileController::store'
        );
    }

    public function storeOfficial(Request $request, BarangayProfile $barangay)
    {
        return $this->handle(
            fn () => $this->barangays->createOfficial($request, $barangay),
            fn () => redirect()->route('barangay.show', $barangay)->with('success', 'Official added successfully.'),
            'BarangayProfileController::storeOfficial'
        );
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
        return $this->handle(
            fn () => $this->barangays->update($request, $barangay),
            fn () => redirect()->route('barangay.show', $barangay)->with('success', 'Barangay profile updated successfully.'),
            'BarangayProfileController::update'
        );
    }

    public function destroy(BarangayProfile $barangay)
    {
        return $this->handle(
            fn () => $this->barangays->delete($barangay),
            fn () => redirect()->route('barangay.index')->with('success', 'Barangay profile deleted successfully.'),
            'BarangayProfileController::destroy'
        );
    }

    public function updateOfficial(Request $request, BarangayProfile $barangay, BarangayOfficial $official)
    {
        return $this->handle(
            fn () => $this->barangays->updateOfficial($request, $official),
            fn () => redirect()->route('barangay.show', $barangay)->with('success', 'Official updated successfully.'),
            'BarangayProfileController::updateOfficial'
        );
    }

    public function destroyOfficial(BarangayProfile $barangay, BarangayOfficial $official)
    {
        return $this->handle(
            fn () => $this->barangays->deleteOfficial($official),
            fn () => redirect()->route('barangay.show', $barangay)->with('success', 'Official removed successfully.'),
            'BarangayProfileController::destroyOfficial'
        );
    }
}