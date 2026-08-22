<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BarangayProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'mission',
        'vision',
        'address',
        'about',
        'logo_path',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function officials(): HasMany
    {
        return $this->hasMany(BarangayOfficial::class);
    }

    public function activeOfficials(): HasMany
    {
        return $this->hasMany(BarangayOfficial::class)->where('is_active', true);
    }
}