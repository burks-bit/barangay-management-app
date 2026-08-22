<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Household extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'household_code',
        'address',
        'purok_id',
        'contact_number',
        'head_of_family_id',
        'vulnerability_indicators',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'vulnerability_indicators' => 'array',
        ];
    }

    public function purok(): BelongsTo
    {
        return $this->belongsTo(Purok::class);
    }

    public function headOfFamily(): BelongsTo
    {
        return $this->belongsTo(MemberProfile::class, 'head_of_family_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(HouseholdMember::class);
    }

    public function memberProfiles()
    {
        return $this->belongsToMany(MemberProfile::class, 'household_members');
    }

    public function getMemberCountAttribute(): int
    {
        return $this->members()->count();
    }
}