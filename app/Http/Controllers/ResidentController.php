<?php

namespace App\Http\Controllers;

use App\Models\Household;
use App\Models\MemberProfile;
use App\Models\Purok;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ResidentController extends Controller
{
    public function __construct(private AuditLogService $auditLog)
    {
        // Authorization is enforced via route-level `can:` middleware (see routes/web.php).
    }

    public function index(Request $request): Response
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

        return Inertia::render('Residents/Index', [
            'residents' => $query->paginate(15)->withQueryString(),
            'filters' => $request->only(['search', 'purok_id', 'verification_status']),
            'puroks' => Purok::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Residents/Create', [
            'puroks' => Purok::orderBy('name')->get(['id', 'name']),
            'households' => Household::with('headOfFamily')->orderBy('household_code')->get(),
        ]);
    }

    public function store(Request $request)
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
            'email' => 'nullable|email|max:255|unique:users,email',
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

        DB::transaction(function () use ($validated, $request) {
            $userId = null;

            if (! empty($validated['create_account'])) {
                $user = \App\Models\User::create([
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
        });

        return redirect()->route('residents.index')
            ->with('success', 'Resident created successfully.');
    }

    public function show(MemberProfile $resident): Response
    {
        $resident->load(['user', 'purok', 'household.headOfFamily', 'verifier']);

        return Inertia::render('Residents/Show', [
            'resident' => $resident,
        ]);
    }

    public function edit(MemberProfile $resident): Response
    {
        return Inertia::render('Residents/Edit', [
            'resident' => $resident,
            'puroks' => Purok::orderBy('name')->get(['id', 'name']),
            'households' => Household::with('headOfFamily')->orderBy('household_code')->get(),
        ]);
    }

    public function update(Request $request, MemberProfile $resident)
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
        $resident->update($validated);

        $this->auditLog->log('updated', 'residents', 'MemberProfile', $resident->id, $oldValues, $resident->fresh()->toArray());

        return redirect()->route('residents.show', $resident)
            ->with('success', 'Resident updated successfully.');
    }

    public function destroy(MemberProfile $resident)
    {
        $oldValues = $resident->toArray();
        $resident->delete();

        $this->auditLog->log('deleted', 'residents', 'MemberProfile', $resident->id, $oldValues, null);

        return redirect()->route('residents.index')
            ->with('success', 'Resident deleted successfully.');
    }

    public function verify(Request $request, MemberProfile $resident)
    {
        $oldStatus = $resident->verification_status;

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

        return back()->with('success', 'Resident verified successfully.');
    }

    public function rejectVerification(Request $request, MemberProfile $resident)
    {
        $request->validate(['reason' => 'required|string|max:500']);

        $oldStatus = $resident->verification_status;

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

        return back()->with('success', 'Resident verification rejected.');
    }
}