<?php

namespace App\Services;

use App\Models\Incident;
use App\Models\IncidentBlotter;
use App\Models\Purok;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class IncidentBlotterService extends Service
{
    public function __construct(
        private AuditLogService $auditLog,
        private UniSmsService $uniSms
    ) {
    }

    /**
     * List all blotter entries with optional filters.
     */
    public function list(Request $request): LengthAwarePaginator
    {
        $query = IncidentBlotter::with(['purok', 'recorder'])
            ->when($request->input('search'), function ($q, $search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('blotter_code', 'like', "%{$search}%")
                        ->orWhere('title', 'like', "%{$search}%")
                        ->orWhere('narrative', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%")
                        ->orWhere('complainant_name', 'like', "%{$search}%");
                });
            })
            ->when($request->input('status'), fn ($q, $v) => $q->where('status', $v))
            ->when($request->input('entry_type'), fn ($q, $v) => $q->where('entry_type', $v))
            ->latest('incident_datetime');

        return $query->paginate(15)->withQueryString();
    }

    public function puroks(): Collection
    {
        return Purok::orderBy('name')->get(['id', 'name']);
    }

    public function openIncidents(): Collection
    {
        return Incident::whereNotIn('status', ['resolved', 'closed'])
            ->orderByDesc('incident_datetime')
            ->limit(50)
            ->get(['id', 'incident_code', 'type', 'location']);
    }

    /**
     * Store a newly recorded blotter entry.
     */
    public function create(Request $request, User $recorder): IncidentBlotter
    {
        $validated = $request->validate([
            'incident_id' => 'nullable|exists:incidents,id',
            'purok_id' => 'nullable|exists:puroks,id',
            'entry_type' => 'required|in:accident,animal_incident,disturbance,theft,dispute,property_damage,other',
            'title' => 'required|string|max:255',
            'narrative' => 'required|string|max:5000',
            'location' => 'required|string|max:500',
            'incident_datetime' => 'required|date|before_or_equal:now',
            'complainant_name' => 'nullable|string|max:255',
            'complainant_contact' => 'nullable|string|max:50',
            'involved_persons' => 'nullable|string|max:2000',
            'injuries_reported' => 'boolean',
            'actions_taken' => 'nullable|string|max:2000',
            'remarks' => 'nullable|string|max:2000',
        ]);

        $blotter = $this->transaction(function () use ($validated, $recorder) {
            $year = now()->year;
            $lastNumber = IncidentBlotter::whereYear('created_at', $year)->count() + 1;
            $blotterCode = sprintf('BLT-%d-%06d', $year, $lastNumber);

            while (IncidentBlotter::where('blotter_code', $blotterCode)->exists()) {
                $lastNumber++;
                $blotterCode = sprintf('BLT-%d-%06d', $year, $lastNumber);
            }

            $blotter = IncidentBlotter::create(array_merge($validated, [
                'blotter_code' => $blotterCode,
                'recorded_by' => $recorder->id,
                'status' => 'recorded',
            ]));

            $this->auditLog->log('created', 'incident_blotters', 'IncidentBlotter', $blotter->id, null, $blotter->toArray());

            return $blotter;
        }, 'Failed to record blotter entry.');

        $this->uniSms->notifyOfficials($blotter);

        return $blotter;
    }

    public function show(IncidentBlotter $blotter): IncidentBlotter
    {
        return $blotter->load(['purok', 'recorder', 'incident']);
    }

    /**
     * Update the status of a blotter entry.
     */
    public function updateStatus(Request $request, IncidentBlotter $blotter): void
    {
        $validated = $request->validate([
            'status' => 'required|in:recorded,under_investigation,settled,referred,closed',
            'remarks' => 'nullable|string|max:2000',
        ]);

        $oldStatus = $blotter->status;
        $newStatus = $validated['status'];

        $updateData = ['status' => $newStatus];

        if (in_array($newStatus, ['settled', 'closed'])) {
            $updateData['settled_at'] = now();
        } else {
            $updateData['settled_at'] = null;
        }

        if (!empty($validated['remarks'])) {
            $updateData['remarks'] = $validated['remarks'];
        }

        $this->transaction(function () use ($blotter, $updateData, $oldStatus, $newStatus) {
            $blotter->update($updateData);

            $this->auditLog->log(
                'updated blotter status',
                'incident_blotters',
                'IncidentBlotter',
                $blotter->id,
                ['status' => $oldStatus],
                ['status' => $newStatus]
            );
        }, 'Failed to update blotter status.');
    }

    /**
     * Delete a blotter entry.
     */
    public function delete(IncidentBlotter $blotter): void
    {
        $this->transaction(function () use ($blotter) {
            $oldValues = $blotter->toArray();
            $blotter->delete();

            $this->auditLog->log('deleted', 'incident_blotters', 'IncidentBlotter', $blotter->id, $oldValues, null);
        }, 'Failed to delete blotter entry.');
    }
}