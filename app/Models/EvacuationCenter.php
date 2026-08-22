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

    public function households(): HasMany
    {
        return $this->hasMany(Household::class);
    }

    public function evacuatedHouseholds(): HasMany
    {
        return $this->hasMany(Household::class)->where('evacuation_status', 'evacuated');
    }

    public function getAvailableCapacityAttribute(): int
    {
        return max(0, $this->capacity - $this->current_occupancy);
    }

    public function recalculateOccupancy(): void
    {
        $occupancy = $this->evacuatedHouseholds()
            ->withCount('members')
            ->get()
            ->sum('members_count');

        $this->update([
            'current_occupancy' => $occupancy,
            'status' => $occupancy >= $this->capacity ? 'full' : ($occupancy > 0 ? 'occupied' : 'available'),
        ]);
    }
}