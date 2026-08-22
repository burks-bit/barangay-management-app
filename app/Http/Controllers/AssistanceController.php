<?php

namespace App\Http\Controllers;

use App\Models\AssistanceRequest;
use App\Models\AssistanceType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AssistanceController extends Controller
{
    public function index(Request $request): Response
    {
        $requests = AssistanceRequest::with(['applicant.memberProfile', 'assistanceType'])
            ->when($request->input('status'), fn ($query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Assistance/Index', [
            'requests' => $requests,
            'filters' => $request->only(['status']),
        ]);
    }

    public function myAssistance(Request $request): Response
    {
        $requests = AssistanceRequest::with('assistanceType')
            ->where('applicant_id', $request->user()->id)
            ->when($request->input('status'), fn ($query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Assistance/MyAssistance', [
            'requests' => $requests,
            'filters' => $request->only(['status']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Assistance/Create', [
            'assistanceTypes' => AssistanceType::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'assistance_type_id' => 'required|exists:assistance_types,id',
            'reason' => 'required|string|max:5000',
            'amount' => 'nullable|numeric|min:0|max:99999999.99',
        ]);

        DB::transaction(function () use ($validated, $request) {
            $number = AssistanceRequest::whereYear('created_at', now()->year)->count() + 1;
            $code = sprintf('AST-%d-%06d', now()->year, $number);

            while (AssistanceRequest::where('assistance_code', $code)->exists()) {
                $number++;
                $code = sprintf('AST-%d-%06d', now()->year, $number);
            }

            AssistanceRequest::create([
                ...$validated,
                'assistance_code' => $code,
                'applicant_id' => $request->user()->id,
                'status' => 'submitted',
            ]);
        });

        return redirect()->route('my-assistance')
            ->with('success', 'Assistance request submitted successfully.');
    }
}