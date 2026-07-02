<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Models\System\AuditLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('system.audit.view');

        $logs = AuditLog::query()
            ->with('user')
            ->when($request->module, fn ($q, $v) => $q->where('module', $v))
            ->when($request->action, fn ($q, $v) => $q->where('action', $v))
            ->when($request->user_id, fn ($q, $v) => $q->where('user_id', $v))
            ->when($request->date_from, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($request->date_to, fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->latest('created_at')
            ->paginate($request->integer('per_page', 25))
            ->withQueryString();

        return Inertia::render('System/AuditLogs/Index', [
            'logs' => $logs,
            'filters' => $request->only(['module', 'action', 'user_id', 'date_from', 'date_to']),
        ]);
    }
}
