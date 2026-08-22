<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReliefDistributionEvent extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'event_code',
        'name',
        'calamity_id',
        'location',
        'distribution_date',
        'status',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'distribution_date' => 'datetime',
        ];
    }

    public function calamity(): BelongsTo
    {
        return $this->belongsTo(Calamity::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReliefDistributionItem::class);
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(ReliefRecipient::class);
    }
}