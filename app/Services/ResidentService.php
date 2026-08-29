<?php

namespace App\Services;

use App\Models\Household;
use App\Models\MemberProfile;
use App\Models\Purok;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ResidentService extends Service
{
    public function __construct(private AuditLogService $auditLog)
    {
    }

    public function list(Request $request): LengthAwarePaginator
    {
        $query = MemberProfile::with(['user', 'purok', 'household'])
            ->when($request->input('search'), function ($q, $search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('middle_name', 'like', "%{$search}%")
                        ->orWhere('resident_id', 'like', "%{$search}%")
                        ->orWhere('contact_number', 'like', "%{$search}%");
                });
            })
            ->when($request->input('purok_id'), fn ($q, $v) => $q->where('purok_id', $v))
            ->when($request->input('verification_status'), fn ($q, $v) => $q->where('verification_status', $v))
            ->latest();

        return $query->paginate(15)->withQueryString();
    }

    public function puroks(): Collection
    {
        return Purok::orderBy('name')->get(['id', 'name']);
    }

    public function households(): Collection
    {
        return Household::with('headOfFamily')->orderBy('household_code')->get();
    }

    public function create(Request $request): void
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'suffix' => 'nullable|string|max:10',
            'date_of_birth' => 'required|date|before:today',
            'sex' => 'required|in:male,female',
            'civil_status' => 'required|in:single,married,widowed,separated,divorced',
            'contact_number' => 'nullable|string|max:20',
            // The email is only required when an account is being created for
            // the resident, otherwise the users.email column would receive null.
            'email' => ['nullable', 'required_if:create_account,true', 'email', 'max:255', 'unique:users,email'],
            'address' => 'required|string|max:500',
            'purok_id' => 'required|exists:puroks,id',
            'household_id' => 'nullable|exists:households,id',
            'occupation' => 'nullable|string|max:100',
            'residency_status' => 'required|in:permanent,temporary,transient',
            'emergency_contact_name' => 'nullable|string|max:200',
            'emergency_contact_number' => 'nullable|string|max:20',
            'create_account' => 'boolean',
            'password' => 'required_if:create_account,true|min:8',
        ]);

        $this->transaction(function () use ($validated) {
            $userId = null;

            if (! empty($validated['create_account'])) {
                $user = User::create([
                    'name' => trim("{$validated['first_name']} {$validated['last_name']}"),
                    'email' => $validated['email'],
                    'password' => bcrypt($validated['password']),
                    'is_active' => true,
                ]);
                $user->assignRole('member');
                $userId = $user->id;
            }

            $resident = MemberProfile::create(array_merge(
                collect($validated)->except(['create_account', 'password'])->all(),
                ['user_id' => $userId]
            ));

            $this->auditLog->log('created', 'residents', 'MemberProfile', $resident->id, null, $resident->toArray());
        }, 'ResidentService::create');
    }

    public function show(MemberProfile $resident): MemberProfile
    {
        return $resident->load(['user', 'purok', 'household.headOfFamily', 'verifier']);
    }

    public function update(Request $request, MemberProfile $resident): void
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'suffix' => 'nullable|string|max:10',
            'date_of_birth' => 'required|date|before:today',
            'sex' => 'required|in:male,female',
            'civil_status' => 'required|in:single,married,widowed,separated,divorced',
            'contact_number' => 'nullable|string|max:20',
            'address' => 'required|string|max:500',
            'purok_id' => 'required|exists:puroks,id',
            'household_id' => 'nullable|exists:households,id',
            'occupation' => 'nullable|string|max:100',
            'residency_status' => 'required|in:permanent,temporary,transient',
            'emergency_contact_name' => 'nullable|string|max:200',
            'emergency_contact_number' => 'nullable|string|max:20',
        ]);

        $oldValues = $resident->only(array_keys($validated));

        $this->transaction(function () use ($validated, $oldValues, $resident) {
            $resident->update($validated);

            $this->auditLog->log('updated', 'residents', 'MemberProfile', $resident->id, $oldValues, $resident->fresh()->toArray());
        }, 'ResidentService::update');
    }

    public function delete(MemberProfile $resident): void
    {
        $this->attempt(function () use ($resident) {
            $oldValues = $resident->toArray();
            $resident->delete();

            $this->auditLog->log('deleted', 'residents', 'MemberProfile', $resident->id, $oldValues, null);
        }, 'ResidentService::delete');
    }

    public function verify(MemberProfile $resident): void
    {
        $oldStatus = $resident->verification_status;

        $this->attempt(function () use ($oldStatus, $resident) {
            $resident->update([
                'verification_status' => 'verified',
                'verified_by' => auth()->id(),
                'verified_at' => now(),
            ]);

            $this->auditLog->log(
                'verified resident',
                'residents',
                'MemberProfile',
                $resident->id,
                ['verification_status' => $oldStatus],
                ['verification_status' => 'verified']
            );
        }, 'ResidentService::verify');
    }

    public function rejectVerification(Request $request, MemberProfile $resident): void
    {
        $request->validate(['reason' => 'required|string|max:500']);

        $oldStatus = $resident->verification_status;

        $this->attempt(function () use ($oldStatus, $request, $resident) {
            $resident->update([
                'verification_status' => 'rejected',
                'verified_by' => auth()->id(),
                'verified_at' => now(),
            ]);

            $this->auditLog->log(
                'rejected resident verification',
                'residents',
                'MemberProfile',
                $resident->id,
                ['verification_status' => $oldStatus],
                ['verification_status' => 'rejected', 'reason' => $request->reason]
            );
        }, 'ResidentService::rejectVerification');
    }
}