<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\ComplaintCategory;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ComplaintController extends Controller
{
    public function __construct(private AuditLogService $auditLog)
    {
        // Authorization is enforced via route-level `can:` middleware (see routes/web.php).
    }

    /**
     * Admin/Moderator view of all complaints.
     */
    public function index(Request $request): Response
    {
        $query = Complaint::with(['complainant.memberProfile', 'category', 'assignedModerator'])
            ->when($request->input('search'), function ($q, $search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('complaint_code', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%")
                        ->orWhereHas('complainant.memberProfile', function ($qp) use ($search) {
                            $qp->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->input('status'), fn ($q, $v) => $q->where('status', $v))
            ->when($request->input('category_id'), fn ($q, $v) => $q->where('category_id', $v))
            ->when($request->input('priority'), fn ($q, $v) => $q->where('priority', $v))
            ->latest();

        return Inertia::render('Complaints/Index', [
            'complaints' => $query->paginate(15)->withQueryString(),
            'filters' => $request->only(['search', 'status', 'category_id', 'priority']),
            'categories' => ComplaintCategory::orderBy('name')->get(),
            'moderators' => User::role('moderator')->get(['id', 'name']),
        ]);
    }

    /**
     * Member view of their own complaints.
     */
    public function myComplaints(Request $request): Response
    {
        $complaints = Complaint::with(['category'])
            ->where('complainant_id', $request->user()->id)
            ->when($request->input('status'), fn ($q, $v) => $q->where('status', $v))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Complaints/MyComplaints', [
            'complaints' => $complaints,
            'filters' => $request->only(['status']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Complaints/Create', [
            'categories' => ComplaintCategory::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:complaint_categories,id',
            'subject' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'location' => 'required|string|max:500',
            'incident_datetime' => 'required|date|before_or_equal:now',
            'priority' => 'required|in:low,medium,high,urgent',
        ]);

        DB::transaction(function () use ($validated, $request) {
            $year = now()->year;
            $lastNumber = Complaint::whereYear('created_at', $year)->count() + 1;
            $complaintCode = sprintf('CMP-%d-%06d', $year, $lastNumber);

            while (Complaint::where('complaint_code', $complaintCode)->exists()) {
                $lastNumber++;
                $complaintCode = sprintf('CMP-%d-%06d', $year, $lastNumber);
            }

            $complaint = Complaint::create(array_merge($validated, [
                'complaint_code' => $complaintCode,
                'complainant_id' => $request->user()->id,
                'status' => 'submitted',
            ]));

            $complaint->statusHistories()->create([
                'user_id' => $request->user()->id,
                'from_status' => null,
                'to_status' => 'submitted',
                'remarks' => 'Complaint submitted',
            ]);

            $this->auditLog->log('created', 'complaints', 'Complaint', $complaint->id, null, $complaint->toArray());
        });

        return redirect()->route('my-complaints')
            ->with('success', 'Complaint submitted successfully. Our team will review it shortly.');
    }

    public function show(Complaint $complaint): Response
    {
        $complaint->load([
            'complainant.memberProfile.purok',
            'category',
            'assignedModerator',
            'statusHistories.user',
            'attachments',
        ]);

        return Inertia::render('Complaints/Show', [
            'complaint' => $complaint,
            'moderators' => User::role('moderator')->get(['id', 'name']),
        ]);
    }

    public function edit(Complaint $complaint): Response
    {
        return Inertia::render('Complaints/Edit', [
            'complaint' => $complaint,
            'categories' => ComplaintCategory::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Complaint $complaint)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:complaint_categories,id',
            'subject' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'location' => 'required|string|max:500',
            'priority' => 'required|in:low,medium,high,urgent',
        ]);

        $oldValues = $complaint->only(array_keys($validated));
        $complaint->update($validated);

        $this->auditLog->log('updated', 'complaints', 'Complaint', $complaint->id, $oldValues, $complaint->fresh()->toArray());

        return redirect()->route('complaints.show', $complaint)
            ->with('success', 'Complaint updated successfully.');
    }

    public function destroy(Complaint $complaint)
    {
        $oldValues = $complaint->toArray();
        $complaint->delete();

        $this->auditLog->log('deleted', 'complaints', 'Complaint', $complaint->id, $oldValues, null);

        return redirect()->route('complaints.index')
            ->with('success', 'Complaint deleted successfully.');
    }

    /**
     * Assign a moderator to the complaint.
     */
    public function assign(Request $request, Complaint $complaint)
    {
        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $oldStatus = $complaint->status;
        $complaint->update([
            'assigned_to' => $validated['assigned_to'],
            'status' => 'assigned',
        ]);

        $complaint->statusHistories()->create([
            'user_id' => auth()->id(),
            'from_status' => $oldStatus,
            'to_status' => 'assigned',
            'remarks' => 'Complaint assigned to moderator',
        ]);

        $this->auditLog->log(
            'assigned complaint',
            'complaints',
            'Complaint',
            $complaint->id,
            ['assigned_to' => $complaint->getOriginal('assigned_to'), 'status' => $oldStatus],
            ['assigned_to' => $validated['assigned_to'], 'status' => 'assigned']
        );

        return back()->with('success', 'Complaint assigned successfully.');
    }

    /**
     * Update complaint status through the workflow.
     */
    public function process(Request $request, Complaint $complaint)
    {
        $validated = $request->validate([
            'status' => 'required|in:under_review,verified,assigned,under_investigation,for_mediation,action_taken,resolved,rejected,closed',
            'remarks' => 'nullable|string|max:2000',
        ]);

        $oldStatus = $complaint->status;
        $newStatus = $validated['status'];

        $updateData = ['status' => $newStatus];

        if (in_array($newStatus, ['resolved', 'closed'])) {
            $updateData['resolution'] = $validated['remarks'] ?? $complaint->resolution;
            $updateData['resolution_date'] = now();
        }

        $complaint->update($updateData);

        $complaint->statusHistories()->create([
            'user_id' => auth()->id(),
            'from_status' => $oldStatus,
            'to_status' => $newStatus,
            'remarks' => $validated['remarks'] ?? "Status changed to {$newStatus}",
        ]);

        // Notify the complainant
        $complaint->complainant?->notify(new \App\Notifications\ComplaintStatusChanged($complaint));

        $this->auditLog->log(
            'updated complaint status',
            'complaints',
            'Complaint',
            $complaint->id,
            ['status' => $oldStatus],
            ['status' => $newStatus]
        );

        return back()->with('success', 'Complaint status updated successfully.');
    }

    /**
     * Resolve a complaint with a resolution.
     */
    public function resolve(Request $request, Complaint $complaint)
    {
        $validated = $request->validate([
            'resolution' => 'required|string|max:5000',
        ]);

        $oldStatus = $complaint->status;

        $complaint->update([
            'status' => 'resolved',
            'resolution' => $validated['resolution'],
            'resolution_date' => now(),
        ]);

        $complaint->statusHistories()->create([
            'user_id' => auth()->id(),
            'from_status' => $oldStatus,
            'to_status' => 'resolved',
            'remarks' => $validated['resolution'],
        ]);

        $complaint->complainant?->notify(new \App\Notifications\ComplaintStatusChanged($complaint));

        $this->auditLog->log(
            'resolved complaint',
            'complaints',
            'Complaint',
            $complaint->id,
            ['status' => $oldStatus],
            ['status' => 'resolved']
        );

        return back()->with('success', 'Complaint resolved successfully.');
    }
}