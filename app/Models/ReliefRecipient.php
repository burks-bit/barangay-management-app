<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReliefRecipient extends Model
{
    use HasFactory;

    protected $fillable = [
        'relief_distribution_event_id',
        'household_id',
        'member_profile_id',
        'assistance_category',
        'received_at',
        'signature_path',
        'notes',
        'distributed_by',
    ];

    protected function casts(): array
    {
        return [
            'received_at' => 'datetime',
        ];
    }

    public function distributionEvent(): BelongsTo
    {
        return $this->belongsTo(ReliefDistributionEvent::class, 'relief_distribution_event_id');
    }

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function memberProfile(): BelongsTo
    {
        return $this->belongsTo(MemberProfile::class);
    }

    public function distributor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'distributed_by');
    }
}