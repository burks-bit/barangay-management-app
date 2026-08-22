<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssistanceRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'assistance_code',
        'applicant_id',
        'assistance_type_id',
        'reason',
        'amount',
        'status',
        'assessment',
        'assessed_by',
        'assessed_at',
        'approved_by',
        'approved_at',
        'approval_notes',
        'released_at',
        'release_details',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'assessed_at' => 'datetime',
            'approved_at' => 'datetime',
            'released_at' => 'datetime',
        ];
    }

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applicant_id');
    }

    public function assistanceType(): BelongsTo
    {
        return $this->belongsTo(AssistanceType::class);
    }

    public function assessor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assessed_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(AssistanceAttachment::class);
    }
}