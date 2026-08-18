<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $logs = AuditLog::query()->with('user:id,name,email')
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = '%'.trim((string) $request->input('search')).'%';
                $query->where(fn ($inner) => $inner->where('event_uuid', 'like', $search)->orWhere('record_type', 'like', $search)->orWhere('record_id', 'like', $search)->orWhere('request_id', 'like', $search));
            })
            ->when($request->filled('module'), fn ($query) => $query->where('module', $request->string('module')))
            ->when($request->filled('action'), fn ($query) => $query->where('action', $request->string('action')))
            ->when($request->filled('user_id'), fn ($query) => $query->where('user_id', $request->integer('user_id')))
            ->when($request->date('from'), fn ($query, $date) => $query->whereDate('created_at', '>=', $date))
            ->when($request->date('to'), fn ($query, $date) => $query->whereDate('created_at', '<=', $date))
            ->latest('created_at')->latest('id')->paginate($this->perPage($request, 30))->withQueryString();

        return view('audit.index', [
            'logs' => $logs,
            'users' => User::query()->whereHas('auditLogs')->orderBy('name')->get(['id', 'name']),
            'modules' => AuditLog::query()->distinct()->orderBy('module')->pluck('module'),
            'actions' => AuditLog::query()->distinct()->orderBy('action')->pluck('action'),
        ]);
    }
}
