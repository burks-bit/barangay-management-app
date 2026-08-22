<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EvacuationEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_code',
        'calamity_id',
        'evacuation_center_id',
        'started_at',
        'ended_at',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function calamity(): BelongsTo
    {
        return $this->belongsTo(Calamity::class);
    }

    public function evacuationCenter(): BelongsTo
    {
        return $this->belongsTo(EvacuationCenter::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(EvacuationRegistration::class);
    }

    public function getRegisteredFamiliesAttribute(): int
    {
        return $this->registrations()->whereNotNull('household_id')->distinct('household_id')->count();
    }

    public function getRegisteredResidentsAttribute(): int
    {
        return $this->registrations()->whereNull('time_out')->count();
    }
}