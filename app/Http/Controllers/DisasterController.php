<?php

namespace App\Http\Controllers;

use App\Services\DisasterService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DisasterController extends Controller
{
    public function __construct(private DisasterService $disasters)
    {
    }

    public function calamities(Request $request): Response
    {
        return Inertia::render('Calamities/Index', [
            'calamities' => $this->disasters->calamities($request),
            'filters' => $request->only(['status']),
        ]);
    }

    public function evacuationCenters(Request $request): Response
    {
        return Inertia::render('EvacuationCenters/Index', [
            'centers' => $this->disasters->evacuationCenters(),
            'userHousehold' => $this->disasters->householdForUser($request->user()),
        ]);
    }

    public function selectEvacuationCenter(Request $request)
    {
        $result = $this->disasters->selectEvacuationCenter($request, $request->user());

        $type = array_key_first($result);

        return back()->with($type, $result[$type]);
    }

    public function returnHome(Request $request)
    {
        $result = $this->disasters->returnHome($request->user());

        $type = array_key_first($result);

        return back()->with($type, $result[$type]);
    }

    public function evacuations(): Response
    {
        return Inertia::render('Evacuations/Index', [
            'events' => $this->disasters->evacuations(),
        ]);
    }
}