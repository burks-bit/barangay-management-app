<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\ProgramEnrollment;
use App\Services\ProgramService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProgramController extends Controller
{
    public function __construct(private ProgramService $programs)
    {
        // Access is restricted to admin/moderator via `role:` middleware (see routes/web.php).
    }

    public function index(): Response
    {
        return Inertia::render('Programs/Index', [
            'programs' => $this->programs->list(),
        ]);
    }

    public function show(Program $program): Response
    {
        return Inertia::render('Programs/Show', $this->programs->show($program));
    }

    public function store(Request $request)
    {
        $this->programs->create($request, $request->user());

        return back()->with('success', 'Program created successfully.');
    }

    public function update(Request $request, Program $program)
    {
        $this->programs->update($request, $program);

        return back()->with('success', 'Program updated successfully.');
    }

    public function destroy(Program $program)
    {
        $this->programs->delete($program);

        return redirect()->route('programs.index')->with('success', 'Program deleted successfully.');
    }

    /**
     * Enroll a resident as a program beneficiary.
     */
    public function enroll(Request $request, Program $program)
    {
        $this->programs->enroll($request, $program, $request->user());

        return back()->with('success', 'Resident enrolled successfully.');
    }

    /**
     * Update an enrollment (mark completed/dropped, edit notes).
     */
    public function updateEnrollment(Request $request, Program $program, ProgramEnrollment $enrollment)
    {
        $this->programs->updateEnrollment($request, $program, $enrollment);

        return back()->with('success', 'Enrollment updated successfully.');
    }

    public function destroyEnrollment(Program $program, ProgramEnrollment $enrollment)
    {
        $this->programs->deleteEnrollment($program, $enrollment);

        return back()->with('success', 'Beneficiary removed from program.');
    }
}