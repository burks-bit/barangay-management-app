<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BarangayOfficial extends Model
{
    use HasFactory;

    protected $fillable = [
        'barangay_profile_id',
        'position',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'sex',
        'date_of_birth',
        'contact_number',
        'email',
        'committee',
        'term_start',
        'term_end',
        'photo_path',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'term_start' => 'integer',
            'term_end' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function barangayProfile(): BelongsTo
    {
        return $this->belongsTo(BarangayProfile::class);
    }

    public function getFullNameAttribute(): string
    {
        $name = trim($this->first_name . ' ' . ($this->middle_name ? $this->middle_name . ' ' : '') . $this->last_name);
        return $this->suffix ? $name . ' ' . $this->suffix : $name;
    }
}