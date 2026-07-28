<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Services\LeaveApprovalService;
use App\Services\LeaveRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaveRequestController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(
            $request->user()?->can('leave.request'),
            403
        );

        $employee = $request->user()?->employee;

        abort_unless($employee, 403);

        $types = LeaveType::query()
            ->where('company_id', $employee->company_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $requests = $employee
            ->leaveRequests()
            ->with([
                'leaveType',
                'manager',
                'hr',
            ])
            ->latest('start_date')
            ->latest('id')
            ->paginate(15);

        $balances = $employee
            ->leaveBalances()
            ->with('leaveType')
            ->where('year', now()->year)
            ->orderBy('leave_type_id')
            ->get();

        $statistics = [
            'total' => $employee
                ->leaveRequests()
                ->count(),

            'pending' => $employee
                ->leaveRequests()
                ->whereIn('status', [
                    'pending',
                    'manager_approved',
                ])
                ->count(),

            'approved' => $employee
                ->leaveRequests()
                ->where('status', 'approved')
                ->count(),

            'remaining' => (float) $balances
                ->sum('remaining_days'),
        ];

        return view(
            'leave.requests.index',
            compact(
                'employee',
                'types',
                'requests',
                'balances',
                'statistics'
            )
        );
    }

    public function store(
        Request $request,
        LeaveRequestService $service
    ): RedirectResponse {
        abort_unless(
            $request->user()?->can('leave.request'),
            403
        );

        $employee = $request->user()?->employee;

        abort_unless($employee, 403);

        $data = $request->validate(
            [
                'leave_type_id' => [
                    'required',
                    'integer',
                    'exists:leave_types,id',
                ],

                'start_date' => [
                    'required',
                    'date',
                ],

                'end_date' => [
                    'required',
                    'date',
                    'after_or_equal:start_date',
                ],

                'reason' => [
                    'nullable',
                    'string',
                    'max:2000',
                ],
            ],
            [
                'leave_type_id.required' =>
                    'សូមជ្រើសរើសប្រភេទការឈប់សម្រាក។',

                'leave_type_id.exists' =>
                    'ប្រភេទការឈប់សម្រាកដែលបានជ្រើសរើសមិនត្រឹមត្រូវ។',

                'start_date.required' =>
                    'សូមបញ្ចូលថ្ងៃចាប់ផ្ដើម។',

                'end_date.required' =>
                    'សូមបញ្ចូលថ្ងៃបញ្ចប់។',

                'end_date.after_or_equal' =>
                    'ថ្ងៃបញ្ចប់ត្រូវតែក្រោយ ឬស្មើថ្ងៃចាប់ផ្ដើម។',

                'reason.max' =>
                    'មូលហេតុមិនអាចលើសពី ២,០០០ តួអក្សរ។',
            ]
        );

        $type = LeaveType::query()
            ->whereKey($data['leave_type_id'])
            ->where('company_id', $employee->company_id)
            ->where('is_active', true)
            ->firstOrFail();

        $service->submit(
            $employee,
            $type,
            $data['start_date'],
            $data['end_date'],
            filled($data['reason'] ?? null)
                ? trim($data['reason'])
                : null
        );

        return back()->with(
            'status',
            'បានដាក់សំណើការឈប់សម្រាកដោយជោគជ័យ។'
        );
    }

    public function review(Request $request): View
    {
        abort_unless(
            $request->user()?->can('leave.approve'),
            403
        );

        $requests = LeaveRequest::query()
            ->with([
                'employee.branch',
                'employee.department',
                'employee.position',
                'leaveType',
                'manager',
                'hr',
            ])
            ->whereIn('status', [
                'pending',
                'manager_approved',
            ])
            ->latest('start_date')
            ->latest('id')
            ->paginate(20);

        $statistics = [
            'pending' => LeaveRequest::query()
                ->where('status', 'pending')
                ->count(),

            'manager_approved' => LeaveRequest::query()
                ->where('status', 'manager_approved')
                ->count(),

            'approved_today' => LeaveRequest::query()
                ->where('status', 'approved')
                ->whereDate('updated_at', today())
                ->count(),

            'rejected_today' => LeaveRequest::query()
                ->where('status', 'rejected')
                ->whereDate('updated_at', today())
                ->count(),
        ];

        return view(
            'leave.requests.review',
            compact(
                'requests',
                'statistics'
            )
        );
    }

    public function approve(
        LeaveRequest $leaveRequest,
        Request $request,
        LeaveApprovalService $service
    ): RedirectResponse {
        abort_unless(
            $request->user()?->can('leave.approve'),
            403
        );

        $validated = $request->validate([
            'note' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        $service->approve(
            $leaveRequest,
            $request->user(),
            filled($validated['note'] ?? null)
                ? trim($validated['note'])
                : null
        );

        return back()->with(
            'status',
            'បានអនុម័តសំណើការឈប់សម្រាកដោយជោគជ័យ។'
        );
    }

    public function reject(
        LeaveRequest $leaveRequest,
        Request $request,
        LeaveApprovalService $service
    ): RedirectResponse {
        abort_unless(
            $request->user()?->can('leave.approve'),
            403
        );

        $validated = $request->validate(
            [
                'note' => [
                    'required',
                    'string',
                    'min:3',
                    'max:1000',
                ],
            ],
            [
                'note.required' =>
                    'សូមបញ្ចូលមូលហេតុនៃការបដិសេធ។',

                'note.min' =>
                    'មូលហេតុត្រូវមានយ៉ាងតិច ៣ តួអក្សរ។',
            ]
        );

        $service->reject(
            $leaveRequest,
            $request->user(),
            trim($validated['note'])
        );

        return back()->with(
            'status',
            'បានបដិសេធសំណើការឈប់សម្រាក។'
        );
    }
}
