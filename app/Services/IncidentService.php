<?php

namespace App\Services;

use App\Models\Calamity;
use App\Models\Incident;
use App\Models\Purok;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class IncidentService extends Service
{
    public function __construct(private AuditLogService $auditLog)
    {
    }

    /**
     * Admin/Moderator view of all active incidents.
     */
    public function list(Request $request): LengthAwarePaginator
    {
        $query = Incident::with(['purok', 'calamity', 'reporter'])
            ->when($request->input('search'), function ($q, $search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('incident_code', 'like', "%{$search}%")
                        ->orWhere('type', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($request->input('status'), fn ($q, $v) => $q->where('status', $v))
            ->when($request->input('type'), fn ($q, $v) => $q->where('type', $v))
            ->when($request->input('severity'), fn ($q, $v) => $q->where('severity', $v))
            ->whereNotIn('status', ['resolved', 'closed'])
            ->latest('incident_datetime');

        return $query->paginate(15)->withQueryString();
    }

    public function show(Incident $incident): Incident
    {
        return $incident->load(['purok', 'calamity', 'reporter', 'statusHistories.user', 'attachments']);
    }

    /**
     * Update the status of an incident (admin/moderator).
     */
    public function updateStatus(Request $request, Incident $incident, User $actor): void
    {
        $validated = $request->validate([
            'status' => 'required|in:reported,verified,under_response,contained,resolved,closed',
            'remarks' => 'nullable|string|max:2000',
            'actions_taken' => 'nullable|string|max:5000',
            'notes' => 'nullable|string|max:5000',
        ]);

        $oldStatus = $incident->status;
        $newStatus = $validated['status'];

        $updateData = ['status' => $newStatus];

        if (in_array($newStatus, ['resolved', 'closed'])) {
            $updateData['resolved_at'] = now();
        } else {
            $updateData['resolved_at'] = null;
        }

        if (!empty($validated['actions_taken'])) {
            $updateData['actions_taken'] = $validated['actions_taken'];
        }

        if (!empty($validated['notes'])) {
            $updateData['notes'] = $validated['notes'];
        }

        $this->transaction(function () use ($incident, $updateData, $oldStatus, $newStatus, $validated, $actor) {
            $incident->update($updateData);

            $incident->statusHistories()->create([
                'user_id' => $actor->id,
                'from_status' => $oldStatus,
                'to_status' => $newStatus,
                'remarks' => $validated['remarks'] ?? null,
            ]);

            $this->auditLog->log(
                'updated incident status',
                'incidents',
                'Incident',
                $incident->id,
                ['status' => $oldStatus],
                ['status' => $newStatus]
            );
        }, 'Failed to update incident status.');
    }

    /**
     * Member view of their own submitted incidents.
     */
    public function listForReporter(User $user, Request $request): LengthAwarePaginator
    {
        $query = Incident::with(['purok', 'calamity'])
            ->where('reported_by', $user->id)
            ->when($request->input('status'), fn ($q, $v) => $q->where('status', $v))
            ->latest('incident_datetime');

        return $query->paginate(15)->withQueryString();
    }

    public function puroks(): Collection
    {
        return Purok::orderBy('name')->get(['id', 'name']);
    }

    public function activeCalamities(): Collection
    {
        return Calamity::whereIn('status', ['reported', 'active', 'under_response'])
            ->orderBy('started_at', 'desc')
            ->get(['id', 'name', 'type']);
    }

    /**
     * Store a newly reported incident.
     */
    public function create(Request $request, User $reporter): Incident
    {
        Log::info('Storing new incident report.', [
            'user_id' => $reporter->id,
            'input' => $request->all(),
        ]);

        $validated = $request->validate([
            'calamity_id' => 'nullable|exists:calamities,id',
            'type' => 'required|in:flood,fire,earthquake,landslide,storm_surge,typhoon,accident,crime,other',
            'location' => 'required|string|max:500',
            'purok_id' => 'nullable|exists:puroks,id',
            'description' => 'required|string|max:5000',
            'severity' => 'required|in:low,moderate,high,severe,critical',
            'incident_datetime' => 'required|date',
            'affected_households' => 'nullable|integer|min:0',
            'affected_residents' => 'nullable|integer|min:0',
        ]);

        return $this->transaction(function () use ($validated, $reporter) {
            $year = now()->year;
            $lastNumber = Incident::whereYear('created_at', $year)->count() + 1;
            $incidentCode = sprintf('INC-%d-%06d', $year, $lastNumber);

            while (Incident::where('incident_code', $incidentCode)->exists()) {
                $lastNumber++;
                $incidentCode = sprintf('INC-%d-%06d', $year, $lastNumber);
            }

            $incident = Incident::create(array_merge($validated, [
                'incident_code' => $incidentCode,
                'reported_by' => $reporter->id,
                'status' => 'reported',
            ]));

            $incident->statusHistories()->create([
                'user_id' => $reporter->id,
                'from_status' => null,
                'to_status' => 'reported',
                'remarks' => 'Incident reported by resident',
            ]);

            $this->auditLog->log('created', 'incidents', 'Incident', $incident->id, null, $incident->toArray());

            return $incident;
        }, 'Failed to create incident.');
    }
}