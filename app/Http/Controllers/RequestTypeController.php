<?php

namespace App\Http\Controllers;

use App\Models\RequestType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class RequestTypeController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('RequestTypes/Index', [
            'requestTypes' => RequestType::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateType($request);
        RequestType::create($validated);

        return back()->with('success', 'Document type created successfully.');
    }

    public function update(Request $request, RequestType $request_type)
    {
        $validated = $this->validateType($request, $request_type);
        $request_type->update($validated);

        return back()->with('success', 'Document type updated successfully.');
    }

    public function destroy(RequestType $request_type)
    {
        abort_if($request_type->serviceRequests()->exists(), 422, 'This document type is already used by requests.');
        $request_type->delete();

        return back()->with('success', 'Document type deleted successfully.');
    }

    private function validateType(Request $request, ?RequestType $requestType = null): array
    {
        $id = $requestType?->id;

        return $request->validate([
            'name' => ['required', 'string', 'max:200', Rule::unique('request_types', 'name')->ignore($id)],
            'slug' => ['required', 'string', 'max:200', 'alpha_dash', Rule::unique('request_types', 'slug')->ignore($id)],
            'description' => 'nullable|string',
            'fee' => 'required|numeric|min:0|max:99999999.99',
            'is_active' => 'boolean',
        ]);
    }
}