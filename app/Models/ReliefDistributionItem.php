<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReliefDistributionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'relief_distribution_event_id',
        'inventory_item_id',
        'quantity',
    ];

    public function distributionEvent(): BelongsTo
    {
        return $this->belongsTo(ReliefDistributionEvent::class, 'relief_distribution_event_id');
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }
}