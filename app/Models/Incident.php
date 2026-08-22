<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Incident extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'incident_code',
        'calamity_id',
        'type',
        'location',
        'purok_id',
        'description',
        'severity',
        'reported_by',
        'status',
        'assigned_to',
        'actions_taken',
        'affected_households',
        'affected_residents',
        'notes',
        'incident_datetime',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'incident_datetime' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function calamity(): BelongsTo
    {
        return $this->belongsTo(Calamity::class);
    }

    public function purok(): BelongsTo
    {
        return $this->belongsTo(Purok::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function assignedResponder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(IncidentAttachment::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(IncidentStatusHistory::class);
    }
}