<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssistanceAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'assistance_request_id',
        'file_path',
        'original_name',
        'mime_type',
        'file_size',
    ];

    public function assistanceRequest(): BelongsTo
    {
        return $this->belongsTo(AssistanceRequest::class);
    }
}