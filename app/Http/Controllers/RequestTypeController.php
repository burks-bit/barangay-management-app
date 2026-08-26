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
        $this->requestTypes->create($request);

        return back()->with('success', 'Document type created successfully.');
    }

    public function update(Request $request, RequestType $request_type)
    {
        $this->requestTypes->update($request, $request_type);

        return back()->with('success', 'Document type updated successfully.');
    }

    public function destroy(RequestType $request_type)
    {
        $this->requestTypes->delete($request_type);

        return back()->with('success', 'Document type deleted successfully.');
    }
}