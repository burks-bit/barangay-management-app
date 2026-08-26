<?php

namespace App\Http\Controllers;

use App\Services\ReliefService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReliefController extends Controller
{
    public function __construct(private ReliefService $relief)
    {
    }

    public function inventory(Request $request): Response
    {
        return Inertia::render('ReliefInventory/Index', [
            'items' => $this->relief->inventory($request),
            'categories' => $this->relief->categories(),
            'filters' => $request->only(['category', 'search']),
        ]);
    }

    public function distributions(): Response
    {
        return Inertia::render('ReliefDistributions/Index', [
            'events' => $this->relief->distributions(),
        ]);
    }
}