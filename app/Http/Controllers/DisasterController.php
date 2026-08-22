<?php

namespace App\Http\Controllers;

use App\Models\Calamity;
use App\Models\EvacuationCenter;
use App\Models\EvacuationEvent;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DisasterController extends Controller
{
    public function calamities(Request $request): Response
    {
        $calamities = Calamity::with('puroks')
            ->when($request->input('status'), fn ($query, $status) => $query->where('status', $status))
            ->latest('started_at')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Calamities/Index', [
            'calamities' => $calamities,
            'filters' => $request->only(['status']),
        ]);
    }

    public function evacuationCenters(): Response
    {
        return Inertia::render('EvacuationCenters/Index', [
            'centers' => EvacuationCenter::withCount(['evacuationEvents as active_events_count' => fn ($query) => $query->where('status', 'active')])
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function evacuations(): Response
    {
        return Inertia::render('Evacuations/Index', [
            'events' => EvacuationEvent::with(['calamity', 'evacuationCenter'])
                ->withCount(['registrations as current_registrations_count' => fn ($query) => $query->whereNull('time_out')])
                ->latest('started_at')
                ->get(),
        ]);
    }
}