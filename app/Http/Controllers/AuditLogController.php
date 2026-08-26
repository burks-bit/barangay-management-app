<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuditLogController extends Controller
{
    /**
     * Display a listing of audit logs.
     */
    public function index(Request $request): Response
    {
        $query = AuditLog::with('user')
            ->when($request->input('search'), function ($q, $search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('action', 'like', "%{$search}%")
                        ->orWhere('module', 'like', "%{$search}%")
                        ->orWhere('record_type', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($qp) use ($search) {
                            $qp->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->input('module'), fn ($q, $v) => $q->where('module', $v))
            ->when($request->input('action'), fn ($q, $v) => $q->where('action', $v))
            ->when($request->input('user_id'), fn ($q, $v) => $q->where('user_id', $v))
            ->latest();

        $modules = AuditLog::select('module')->distinct()->pluck('module');
        $actions = AuditLog::select('action')->distinct()->pluck('action');
        $users = User::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('AuditLogs/Index', [
            'logs' => $query->paginate(15)->withQueryString(),
            'filters' => $request->only(['search', 'module', 'action', 'user_id']),
            'modules' => $modules,
            'actions' => $actions,
            'users' => $users,
        ]);
    }

    /**
     * Display the specified audit log.
     */
    public function show(AuditLog $auditLog): Response
    {
        $auditLog->load('user');

        return Inertia::render('AuditLogs/Show', [
            'log' => $auditLog,
        ]);
    }
}
