<?php

namespace App\Http\Controllers;

use App\Models\Calamity;
use App\Models\Purok;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CalamityController extends Controller
{
    public function index(Request $request): Response
    {
        $calamities = Calamity::with('puroks')
            ->when($request->input('search'), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('event_code', 'like', "%{$search}%");
                });
            })
            ->when($request->input('status'), fn ($query, $status) => $query->where('status', $status))
            ->when($request->input('type'), fn ($query, $type) => $query->where('type', $type))
            ->latest('started_at')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Calamities/Index', [
            'calamities' => $calamities,
            'filters' => $request->only(['search', 'status', 'type']),
            'puroks' => Purok::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Calamities/Create', [
            'puroks' => Purok::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'event_code' => 'required|string|max:50|unique:calamities',
            'name' => 'required|string|max:200',
            'type' => 'required|in:typhoon,flood,earthquake,fire,landslide,storm_surge,other',
            'description' => 'nullable|string',
            'started_at' => 'required|date',
            'ended_at' => 'nullable|date|after:started_at',
            'severity' => 'required|in:low,moderate,high,severe,critical',
            'status' => 'required|in:reported,active,under_response,contained,resolved,archived',
            'affected_households' => 'nullable|integer|min:0',
            'affected_residents' => 'nullable|integer|min:0',
            'purok_ids' => 'nullable|array',
            'purok_ids.*' => 'exists:puroks,id',
            'notes' => 'nullable|string',
        ]);

        $purokIds = $validated['purok_ids'] ?? [];
        unset($validated['purok_ids']);

        $calamity = Calamity::create($validated);
        if ($purokIds) {
            $calamity->puroks()->sync($purokIds);
        }

        return redirect()->route('calamities.index')->with('success', 'Calamity created successfully.');
    }

    public function edit(Calamity $calamity): Response
    {
        $calamity->load('puroks');

        return Inertia::render('Calamities/Edit', [
            'calamity' => $calamity,
            'puroks' => Purok::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Calamity $calamity)
    {
        $validated = $request->validate([
            'event_code' => 'required|string|max:50|unique:calamities,event_code,' . $calamity->id,
            'name' => 'required|string|max:200',
            'type' => 'required|in:typhoon,flood,earthquake,fire,landslide,storm_surge,other',
            'description' => 'nullable|string',
            'started_at' => 'required|date',
            'ended_at' => 'nullable|date|after:started_at',
            'severity' => 'required|in:low,moderate,high,severe,critical',
            'status' => 'required|in:reported,active,under_response,contained,resolved,archived',
            'affected_households' => 'nullable|integer|min:0',
            'affected_residents' => 'nullable|integer|min:0',
            'purok_ids' => 'nullable|array',
            'purok_ids.*' => 'exists:puroks,id',
            'notes' => 'nullable|string',
        ]);

        $purokIds = $validated['purok_ids'] ?? [];
        unset($validated['purok_ids']);

        $calamity->update($validated);
        $calamity->puroks()->sync($purokIds);

        return redirect()->route('calamities.index')->with('success', 'Calamity updated successfully.');
    }

    public function destroy(Calamity $calamity)
    {
        $calamity->delete();

        return redirect()->route('calamities.index')->with('success', 'Calamity deleted successfully.');
    }
}