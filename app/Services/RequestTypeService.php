<?php

namespace App\Services;

use App\Models\RequestType;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class RequestTypeService
{
    public function list(): Collection
    {
        return RequestType::orderBy('name')->get();
    }

    public function create(Request $request): void
    {
        $validated = $this->validate($request);

        RequestType::create($validated);
    }

    public function update(Request $request, RequestType $requestType): void
    {
        $validated = $this->validate($request, $requestType);

        $requestType->update($validated);
    }

    public function delete(RequestType $requestType): void
    {
        abort_if($requestType->serviceRequests()->exists(), 422, 'This document type is already used by requests.');

        $requestType->delete();
    }

    private function validate(Request $request, ?RequestType $requestType = null): array
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