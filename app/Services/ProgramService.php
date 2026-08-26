<?php

namespace App\Services;

use App\Models\MemberProfile;
use App\Models\Program;
use App\Models\ProgramEnrollment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class ProgramService
{
    public function __construct(private AuditLogService $auditLog)
    {
    }

    public function list(): Collection
    {
        return Program::withCount(['enrollments', 'enrollments as active_enrollments_count' => fn ($q) => $q->where('status', 'enrolled')])
            ->orderBy('name')
            ->get();
    }

    public function show(Program $program): array
    {
        $program->load(['creator']);

        $enrollments = ProgramEnrollment::with('resident.purok')
            ->where('program_id', $program->id)
            ->orderByDesc('enrolled_at')
            ->get();

        // Residents available for enrollment (exclude already enrolled).
        $residents = MemberProfile::query()
            ->whereNotIn('id', $enrollments->pluck('member_profile_id'))
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get(['id', 'resident_id', 'first_name', 'middle_name', 'last_name', 'suffix']);

        return [
            'program' => array_merge($program->toArray(), [
                'active_enrollments_count' => $program->enrollments()->where('status', 'enrolled')->count(),
                'total_enrollments_count' => $program->enrollments()->count(),
            ]),
            'enrollments' => $enrollments,
            'residents' => $residents,
        ];
    }

    public function create(Request $request, User $creator): Program
    {
        $validated = $this->validateProgram($request);
        $validated['created_by'] = $creator->id;

        $program = Program::create($validated);

        $this->auditLog->log('created', 'programs', 'Program', $program->id, null, $program->toArray());

        return $program;
    }

    public function update(Request $request, Program $program): void
    {
        $validated = $this->validateProgram($request, $program);
        $oldValues = $program->only(array_keys($validated));

        $program->update($validated);

        $this->auditLog->log('updated', 'programs', 'Program', $program->id, $oldValues, $program->fresh()->toArray());
    }

    public function delete(Program $program): void
    {
        abort_if($program->enrollments()->exists(), 422, 'This program has enrolled beneficiaries and cannot be deleted.');

        $oldValues = $program->toArray();
        $program->delete();

        $this->auditLog->log('deleted', 'programs', 'Program', $program->id, $oldValues, null);
    }

    /**
     * Enroll a resident as a program beneficiary.
     */
    public function enroll(Request $request, Program $program, User $actor): ProgramEnrollment
    {
        $validated = $request->validate([
            'member_profile_id' => ['required', 'exists:member_profiles,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        abort_unless($program->status === 'active', 422, 'Only active programs can accept enrollments.');
        abort_if(
            ProgramEnrollment::where('program_id', $program->id)->where('member_profile_id', $validated['member_profile_id'])->exists(),
            422,
            'This resident is already enrolled in the program.'
        );

        $enrollment = ProgramEnrollment::create([
            ...$validated,
            'program_id' => $program->id,
            'status' => 'enrolled',
            'enrolled_at' => now(),
            'enrolled_by' => $actor->id,
        ]);

        $this->auditLog->log('enrolled beneficiary', 'programs', 'ProgramEnrollment', $enrollment->id, null, $enrollment->toArray());

        return $enrollment;
    }

    /**
     * Update an enrollment (mark completed/dropped, edit notes).
     */
    public function updateEnrollment(Request $request, Program $program, ProgramEnrollment $enrollment): void
    {
        abort_unless($enrollment->program_id === $program->id, 404);

        $validated = $request->validate([
            'status' => ['required', 'in:enrolled,completed,dropped'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $oldValues = $enrollment->only(array_keys($validated));
        $enrollment->update($validated);

        $this->auditLog->log('updated enrollment', 'programs', 'ProgramEnrollment', $enrollment->id, $oldValues, $enrollment->fresh()->toArray());
    }

    public function deleteEnrollment(Program $program, ProgramEnrollment $enrollment): void
    {
        abort_unless($enrollment->program_id === $program->id, 404);

        $oldValues = $enrollment->toArray();
        $enrollment->delete();

        $this->auditLog->log('removed enrollment', 'programs', 'ProgramEnrollment', $enrollment->id, $oldValues, null);
    }

    private function validateProgram(Request $request, ?Program $program = null): array
    {
        $id = $program?->id;

        return $request->validate([
            'name' => ['required', 'string', 'max:200', Rule::unique('programs', 'name')->ignore($id)],
            'code' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('programs', 'code')->ignore($id)],
            'description' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'max:100'],
            'funding_agency' => ['nullable', 'string', 'max:150'],
            'budget' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['required', 'in:planning,active,on_hold,completed'],
        ]);
    }
}