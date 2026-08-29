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
        return $this->handle(
            fn () => $this->programs->create($request, $request->user()),
            fn () => back()->with('success', 'Program created successfully.'),
            'ProgramController::store'
        );
    }

    public function update(Request $request, Program $program)
    {
        return $this->handle(
            fn () => $this->programs->update($request, $program),
            fn () => back()->with('success', 'Program updated successfully.'),
            'ProgramController::update'
        );
    }

    public function destroy(Program $program)
    {
        return $this->handle(
            fn () => $this->programs->delete($program),
            fn () => redirect()->route('programs.index')->with('success', 'Program deleted successfully.'),
            'ProgramController::destroy'
        );
    }

    /**
     * Enroll a resident as a program beneficiary.
     */
    public function enroll(Request $request, Program $program)
    {
        return $this->handle(
            fn () => $this->programs->enroll($request, $program, $request->user()),
            fn () => back()->with('success', 'Resident enrolled successfully.'),
            'ProgramController::enroll'
        );
    }

    /**
     * Update an enrollment (mark completed/dropped, edit notes).
     */
    public function updateEnrollment(Request $request, Program $program, ProgramEnrollment $enrollment)
    {
        return $this->handle(
            fn () => $this->programs->updateEnrollment($request, $program, $enrollment),
            fn () => back()->with('success', 'Enrollment updated successfully.'),
            'ProgramController::updateEnrollment'
        );
    }

    public function destroyEnrollment(Program $program, ProgramEnrollment $enrollment)
    {
        return $this->handle(
            fn () => $this->programs->deleteEnrollment($program, $enrollment),
            fn () => back()->with('success', 'Beneficiary removed from program.'),
            'ProgramController::destroyEnrollment'
        );
    }
}