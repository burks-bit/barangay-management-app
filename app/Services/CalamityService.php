<?php

namespace App\Services;

use App\Models\Calamity;
use App\Models\Purok;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CalamityService
{
    public function list(Request $request): LengthAwarePaginator
    {
        return Calamity::with('puroks')
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
    }

    public function puroks(): Collection
    {
        return Purok::orderBy('name')->get(['id', 'name']);
    }

    public function create(Request $request): void
    {
        $validated = $this->validate($request);

        $purokIds = $validated['purok_ids'] ?? [];
        unset($validated['purok_ids']);

        $calamity = Calamity::create($validated);
        if ($purokIds) {
            $calamity->puroks()->sync($purokIds);
        }
    }

    public function show(Calamity $calamity): Calamity
    {
        return $calamity->load('puroks');
    }

    public function update(Request $request, Calamity $calamity): void
    {
        $validated = $this->validate($request, $calamity);

        $purokIds = $validated['purok_ids'] ?? [];
        unset($validated['purok_ids']);

        $calamity->update($validated);
        $calamity->puroks()->sync($purokIds);
    }

    public function delete(Calamity $calamity): void
    {
        $calamity->delete();
    }

    private function validate(Request $request, ?Calamity $calamity = null): array
    {
        $uniqueRule = 'unique:calamities';
        if ($calamity) {
            $uniqueRule .= ',event_code,' . $calamity->id;
        }

        return $request->validate([
            'event_code' => 'required|string|max:50|' . $uniqueRule,
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
    }
}