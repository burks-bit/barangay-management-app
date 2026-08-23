<?php

namespace App\Models;

use App\Models\BarangayOfficial;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tracking_number',
        'requester_id',
        'member_profile_id',
        'request_type_id',
        'purpose',
        'description',
        'source',
        'status',
        'assigned_to',
        'created_by',
        'encoded_by',
        'approved_by',
        'approved_by_official_id',
        'document_content',
        'encoded_at',
        'remarks',
        'submitted_at',
        'processed_at',
        'released_at',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'processed_at' => 'datetime',
            'released_at' => 'datetime',
            'encoded_at' => 'datetime',
        ];
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    /**
     * The resident this request is for. Unlike the requester (a user account),
     * this is always set and works for walk-in residents without an account.
     */
    public function resident(): BelongsTo
    {
        return $this->belongsTo(MemberProfile::class, 'member_profile_id');
    }

    /**
     * Staff member who encoded a walk-in request.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function requestType(): BelongsTo
    {
        return $this->belongsTo(RequestType::class);
    }

    public function assignedStaff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function encoder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'encoded_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function approverOfficial(): BelongsTo
    {
        return $this->belongsTo(BarangayOfficial::class, 'approved_by_official_id');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(RequestStatusHistory::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(RequestAttachment::class);
    }
}