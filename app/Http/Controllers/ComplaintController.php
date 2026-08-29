<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Services\ComplaintService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ComplaintController extends Controller
{
    public function __construct(private ComplaintService $complaints)
    {
        // Authorization is enforced via route-level `can:` middleware (see routes/web.php).
    }

    /**
     * Admin/Moderator view of all complaints.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('Complaints/Index', [
            'complaints' => $this->complaints->list($request),
            'filters' => $request->only(['search', 'status', 'category_id', 'priority']),
            'categories' => $this->complaints->categories(),
            'moderators' => $this->complaints->moderators(),
        ]);
    }

    /**
     * Member view of their own complaints.
     */
    public function myComplaints(Request $request): Response
    {
        return Inertia::render('Complaints/MyComplaints', [
            'complaints' => $this->complaints->listForComplainant($request->user(), $request),
            'filters' => $request->only(['status']),
        ]);
    }

    public function myShow(Request $request, Complaint $complaint): Response
    {
        abort_unless($complaint->complainant_id === $request->user()->id, 403);

        return Inertia::render('Complaints/Show', [
            'complaint' => $this->complaints->show($complaint),
            'moderators' => [],
            'backUrl' => route('my-complaints'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Complaints/Create', [
            'categories' => $this->complaints->categories(),
        ]);
    }

    public function store(Request $request)
    {
        return $this->handle(
            fn () => $this->complaints->create($request, $request->user()),
            fn () => redirect()->route('my-complaints')->with('success', 'Complaint submitted successfully. Our team will review it shortly.'),
            'ComplaintController::store'
        );
    }

    public function show(Complaint $complaint): Response
    {
        return Inertia::render('Complaints/Show', [
            'complaint' => $this->complaints->show($complaint),
            'moderators' => $this->complaints->moderators(),
        ]);
    }

    public function edit(Complaint $complaint): Response
    {
        return Inertia::render('Complaints/Edit', [
            'complaint' => $complaint,
            'categories' => $this->complaints->categories(),
        ]);
    }

    public function update(Request $request, Complaint $complaint)
    {
        return $this->handle(
            fn () => $this->complaints->update($request, $complaint),
            fn () => redirect()->route('complaints.show', $complaint)->with('success', 'Complaint updated successfully.'),
            'ComplaintController::update'
        );
    }

    public function destroy(Complaint $complaint)
    {
        return $this->handle(
            fn () => $this->complaints->delete($complaint),
            fn () => redirect()->route('complaints.index')->with('success', 'Complaint deleted successfully.'),
            'ComplaintController::destroy'
        );
    }

    /**
     * Assign a moderator to the complaint.
     */
    public function assign(Request $request, Complaint $complaint)
    {
        return $this->handle(
            fn () => $this->complaints->assign($request, $complaint, $request->user()),
            fn () => back()->with('success', 'Complaint assigned successfully.'),
            'ComplaintController::assign'
        );
    }

    /**
     * Update complaint status through the workflow.
     */
    public function process(Request $request, Complaint $complaint)
    {
        return $this->handle(
            fn () => $this->complaints->process($request, $complaint, $request->user()),
            fn () => back()->with('success', 'Complaint status updated successfully.'),
            'ComplaintController::process'
        );
    }

    /**
     * Resolve a complaint with a resolution.
     */
    public function resolve(Request $request, Complaint $complaint)
    {
        return $this->handle(
            fn () => $this->complaints->resolve($request, $complaint, $request->user()),
            fn () => back()->with('success', 'Complaint resolved successfully.'),
            'ComplaintController::resolve'
        );
    }
}