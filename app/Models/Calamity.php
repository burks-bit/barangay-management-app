<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Calamity extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'event_code',
        'name',
        'type',
        'description',
        'started_at',
        'ended_at',
        'severity',
        'status',
        'affected_households',
        'affected_residents',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function puroks(): BelongsToMany
    {
        return $this->belongsToMany(Purok::class, 'calamity_puroks');
    }

    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class);
    }

    public function evacuationEvents(): HasMany
    {
        return $this->hasMany(EvacuationEvent::class);
    }

    public function reliefDistributionEvents(): HasMany
    {
        return $this->hasMany(ReliefDistributionEvent::class);
    }
}