<?php

namespace App\Http\Controllers;

use App\Models\RequestType;
use App\Services\RequestTypeService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RequestTypeController extends Controller
{
    public function __construct(private RequestTypeService $requestTypes)
    {
    }

    public function index(): Response
    {
        return Inertia::render('RequestTypes/Index', [
            'requestTypes' => $this->requestTypes->list(),
        ]);
    }

    public function store(Request $request)
    {
        return $this->handle(
            fn () => $this->requestTypes->create($request),
            fn () => back()->with('success', 'Document type created successfully.'),
            'RequestTypeController::store'
        );
    }

    public function update(Request $request, RequestType $request_type)
    {
        return $this->handle(
            fn () => $this->requestTypes->update($request, $request_type),
            fn () => back()->with('success', 'Document type updated successfully.'),
            'RequestTypeController::update'
        );
    }

    public function destroy(RequestType $request_type)
    {
        return $this->handle(
            fn () => $this->requestTypes->delete($request_type),
            fn () => back()->with('success', 'Document type deleted successfully.'),
            'RequestTypeController::destroy'
        );
    }
}