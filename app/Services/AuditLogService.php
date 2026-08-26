<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Request;

class AuditLogService
{
    public function distinctModules(): Collection
    {
        return AuditLog::select('module')->distinct()->pluck('module');
    }

    public function distinctActions(): Collection
    {
        return AuditLog::select('action')->distinct()->pluck('action');
    }

    public function users(): Collection
    {
        return User::select('id', 'name')->orderBy('name')->get();
    }

    public function log(
        string $action,
        string $module,
        ?string $recordType = null,
        ?int $recordId = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): void {
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'module' => $module,
            'record_type' => $recordType,
            'record_id' => $recordId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }
}