<?php

namespace App\Http\Controllers;

use App\Models\Household;
use App\Models\Purok;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HouseholdController extends Controller
{
    public function index(Request $request): Response
    {
        $households = Household::with(['purok', 'headOfFamily'])
            ->withCount('members')
            ->when($request->input('search'), function ($query, $search) {
                $query->where(function ($households) use ($search) {
                    $households->where('household_code', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%")
                        ->orWhere('contact_number', 'like', "%{$search}%")
                        ->orWhereHas('headOfFamily', function ($head) use ($search) {
                            $head->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->input('purok_id'), fn ($query, $purokId) => $query->where('purok_id', $purokId))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Households/Index', [
            'households' => $households,
            'filters' => $request->only(['search', 'purok_id']),
            'puroks' => Purok::orderBy('name')->get(['id', 'name']),
        ]);
    }
}