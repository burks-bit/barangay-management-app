<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportDefinition extends Model
{
    use HasFactory;

    public const DATASETS = ['service_requests', 'complaints', 'assistance_requests'];

    protected $fillable = [
        'name',
        'description',
        'dataset',
        'group_by',
        'filters',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'filters' => 'array',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}