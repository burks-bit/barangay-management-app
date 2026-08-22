<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EvacuationCenter extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'location',
        'capacity',
        'current_occupancy',
        'facilities',
        'contact_person',
        'contact_number',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'facilities' => 'array',
        ];
    }

    public function evacuationEvents(): HasMany
    {
        return $this->hasMany(EvacuationEvent::class);
    }

    public function getAvailableCapacityAttribute(): int
    {
        return max(0, $this->capacity - $this->current_occupancy);
    }
}