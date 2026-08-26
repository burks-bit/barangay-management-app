<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class IncidentBlotter extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'blotter_code',
        'incident_id',
        'purok_id',
        'entry_type',
        'title',
        'narrative',
        'location',
        'incident_datetime',
        'complainant_name',
        'complainant_contact',
        'involved_persons',
        'injuries_reported',
        'actions_taken',
        'status',
        'recorded_by',
        'settled_at',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'incident_datetime' => 'datetime',
            'settled_at' => 'datetime',
            'injuries_reported' => 'boolean',
        ];
    }

    public function incident(): BelongsTo
    {
        return $this->belongsTo(Incident::class);
    }

    public function purok(): BelongsTo
    {
        return $this->belongsTo(Purok::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public const ENTRY_TYPES = [
        'accident' => 'Accident',
        'animal_incident' => 'Animal Incident',
        'disturbance' => 'Disturbance',
        'theft' => 'Theft',
        'dispute' => 'Dispute',
        'property_damage' => 'Property Damage',
        'other' => 'Other',
    ];

    public const STATUSES = ['recorded', 'under_investigation', 'settled', 'referred', 'closed'];
}