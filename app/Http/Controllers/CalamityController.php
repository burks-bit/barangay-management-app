<?php

namespace App\Http\Controllers;

use App\Models\Calamity;
use App\Services\CalamityService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CalamityController extends Controller
{
    public function __construct(private CalamityService $calamities)
    {
    }

    public function index(Request $request): Response
    {
        return Inertia::render('Calamities/Index', [
            'calamities' => $this->calamities->list($request),
            'filters' => $request->only(['search', 'status', 'type']),
            'puroks' => $this->calamities->puroks(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Calamities/Create', [
            'puroks' => $this->calamities->puroks(),
        ]);
    }

    public function store(Request $request)
    {
        $this->calamities->create($request);

        return redirect()->route('calamities.index')->with('success', 'Calamity created successfully.');
    }

    public function edit(Calamity $calamity): Response
    {
        return Inertia::render('Calamities/Edit', [
            'calamity' => $this->calamities->show($calamity),
            'puroks' => $this->calamities->puroks(),
        ]);
    }

    public function update(Request $request, Calamity $calamity)
    {
        $this->calamities->update($request, $calamity);

        return redirect()->route('calamities.index')->with('success', 'Calamity updated successfully.');
    }

    public function destroy(Calamity $calamity)
    {
        $this->calamities->delete($calamity);

        return redirect()->route('calamities.index')->with('success', 'Calamity deleted successfully.');
    }
}