<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\ReliefDistributionEvent;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ReliefService
{
    public function inventory(Request $request): LengthAwarePaginator
    {
        return InventoryItem::withCount('distributionItems')
            ->when($request->input('category'), fn ($query, $category) => $query->where('category', $category))
            ->when($request->input('search'), function ($query, $search) {
                $query->where(fn ($items) => $items->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%"));
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();
    }

    public function categories(): Collection
    {
        return InventoryItem::whereNotNull('category')->distinct()->orderBy('category')->pluck('category');
    }

    public function distributions(): Collection
    {
        return ReliefDistributionEvent::with(['calamity', 'items.inventoryItem'])
            ->withCount('recipients')
            ->latest('distribution_date')
            ->get();
    }
}