<?php

namespace App\Http\Controllers;

use App\Models\MemberProfile;
use App\Services\ResidentService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ResidentController extends Controller
{
    public function __construct(private ResidentService $residents)
    {
        // Authorization is enforced via route-level `can:` middleware (see routes/web.php).
    }

    public function index(Request $request): Response
    {
        return Inertia::render('Residents/Index', [
            'residents' => $this->residents->list($request),
            'filters' => $request->only(['search', 'purok_id', 'verification_status']),
            'puroks' => $this->residents->puroks(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Residents/Create', [
            'puroks' => $this->residents->puroks(),
            'households' => $this->residents->households(),
        ]);
    }

    public function store(Request $request)
    {
        $this->residents->create($request);

        return redirect()->route('residents.index')
            ->with('success', 'Resident created successfully.');
    }

    public function show(MemberProfile $resident): Response
    {
        return Inertia::render('Residents/Show', [
            'resident' => $this->residents->show($resident),
        ]);
    }

    public function edit(MemberProfile $resident): Response
    {
        return Inertia::render('Residents/Edit', [
            'resident' => $resident,
            'puroks' => $this->residents->puroks(),
            'households' => $this->residents->households(),
        ]);
    }

    public function update(Request $request, MemberProfile $resident)
    {
        $this->residents->update($request, $resident);

        return redirect()->route('residents.show', $resident)
            ->with('success', 'Resident updated successfully.');
    }

    public function destroy(MemberProfile $resident)
    {
        $this->residents->delete($resident);

        return redirect()->route('residents.index')
            ->with('success', 'Resident deleted successfully.');
    }

    public function verify(Request $request, MemberProfile $resident)
    {
        $this->residents->verify($resident);

        return back()->with('success', 'Resident verified successfully.');
    }

    public function rejectVerification(Request $request, MemberProfile $resident)
    {
        $this->residents->rejectVerification($request, $resident);

        return back()->with('success', 'Resident verification rejected.');
    }
}