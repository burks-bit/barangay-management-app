<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\ReliefDistributionEvent;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReliefController extends Controller
{
    public function inventory(Request $request): Response
    {
        $items = InventoryItem::withCount('distributionItems')
            ->when($request->input('category'), fn ($query, $category) => $query->where('category', $category))
            ->when($request->input('search'), function ($query, $search) {
                $query->where(fn ($items) => $items->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%"));
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('ReliefInventory/Index', [
            'items' => $items,
            'categories' => InventoryItem::whereNotNull('category')->distinct()->orderBy('category')->pluck('category'),
            'filters' => $request->only(['category', 'search']),
        ]);
    }

    public function distributions(): Response
    {
        return Inertia::render('ReliefDistributions/Index', [
            'events' => ReliefDistributionEvent::with(['calamity', 'items.inventoryItem'])
                ->withCount('recipients')
                ->latest('distribution_date')
                ->get(),
        ]);
    }
}