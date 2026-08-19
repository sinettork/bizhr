<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LeaveApprovalService
{
    private const MANAGER_ROLES = ['Manager'];

    private const HR_ROLES = ['HR Administrator', 'Owner', 'Super Admin'];

    public function __construct(
        private readonly LeaveDayCalculator $dayCalculator,
        private readonly NotificationService $notifications,
    ) {}

    public function approve(LeaveRequest $leaveRequest, User $reviewer, ?string $note = null): LeaveRequest
    {
        return DB::transaction(function () use ($leaveRequest, $reviewer, $note): LeaveRequest {
            $leaveRequest = LeaveRequest::query()->with('employee')->lockForUpdate()->findOrFail($leaveRequest->id);
            $this->assertSameCompany($leaveRequest, $reviewer);

            if ($leaveRequest->status === 'pending') {
                $this->assertManagerCanReview($leaveRequest, $reviewer);
                $leaveRequest->update([
                    'status' => 'manager_approved',
                    'manager_id' => $reviewer->id,
                    'manager_reviewed_at' => now(),
                    'manager_note' => $note,
                ]);
                $this->notifyEmployee(
                    $leaveRequest,
                    'leave_manager_approved',
                    'Leave request moved to HR review',
                    'Your manager approved the leave request. It is now waiting for final HR review.',
                    'info',
                );

                return $leaveRequest->fresh();
            }

            if ($leaveRequest->status !== 'manager_approved') {
                throw ValidationException::withMessages(['status' => 'សំណើនេះត្រូវបានបញ្ចប់រួចហើយ។']);
            }

            $this->assertHrCanReview($reviewer);
            $daysByYear = $this->dayCalculator->daysByYear(
                $leaveRequest->employee,
                CarbonImmutable::parse($leaveRequest->start_date),
                CarbonImmutable::parse($leaveRequest->end_date),
            );

            if (array_sum($daysByYear) !== (int) $leaveRequest->total_days) {
                throw ValidationException::withMessages([
                    'status' => 'ប្រតិទិនការងារបានផ្លាស់ប្តូរ។ សូមពិនិត្យ និងបង្កើតសំណើឡើងវិញ។',
                ]);
            }

            foreach ($daysByYear as $year => $days) {
                $balance = LeaveBalance::query()
                    ->where('employee_id', $leaveRequest->employee_id)
                    ->where('leave_type_id', $leaveRequest->leave_type_id)
                    ->where('year', $year)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ((float) $balance->remaining_days < $days) {
                    throw ValidationException::withMessages([
                        'status' => "សមតុល្យឈប់សម្រាកឆ្នាំ {$year} មិនគ្រប់គ្រាន់ទេ។",
                    ]);
                }

                $balance->decrement('remaining_days', $days);
                $balance->increment('used_days', $days);
            }

            $leaveRequest->update([
                'status' => 'approved',
                'hr_id' => $reviewer->id,
                'hr_reviewed_at' => now(),
                'hr_note' => $note,
            ]);
            $this->notifyEmployee(
                $leaveRequest,
                'leave_approved',
                'Leave request approved',
                'Your leave request has received final approval.',
                'success',
            );

            return $leaveRequest->fresh();
        });
    }

    public function reject(LeaveRequest $leaveRequest, User $reviewer, ?string $note = null): LeaveRequest
    {
        return DB::transaction(function () use ($leaveRequest, $reviewer, $note): LeaveRequest {
            $leaveRequest = LeaveRequest::query()->with('employee')->lockForUpdate()->findOrFail($leaveRequest->id);
            $this->assertSameCompany($leaveRequest, $reviewer);

            if ($leaveRequest->status === 'pending') {
                $this->assertManagerCanReview($leaveRequest, $reviewer);
                $leaveRequest->update([
                    'status' => 'rejected',
                    'manager_id' => $reviewer->id,
                    'manager_reviewed_at' => now(),
                    'manager_note' => $note,
                ]);
                $this->notifyEmployee(
                    $leaveRequest,
                    'leave_rejected',
                    'Leave request rejected',
                    'Your leave request was rejected by your manager. Review the request for any reviewer note.',
                    'warning',
                );

                return $leaveRequest->fresh();
            }

            if ($leaveRequest->status === 'manager_approved') {
                $this->assertHrCanReview($reviewer);
                $leaveRequest->update([
                    'status' => 'rejected',
                    'hr_id' => $reviewer->id,
                    'hr_reviewed_at' => now(),
                    'hr_note' => $note,
                ]);
                $this->notifyEmployee(
                    $leaveRequest,
                    'leave_rejected',
                    'Leave request rejected',
                    'Your leave request was rejected during final HR review. Review the request for any reviewer note.',
                    'warning',
                );

                return $leaveRequest->fresh();
            }

            throw ValidationException::withMessages(['status' => 'សំណើនេះត្រូវបានបញ្ចប់រួចហើយ។']);
        });
    }

    private function assertSameCompany(LeaveRequest $leaveRequest, User $reviewer): void
    {
        $companyId = $reviewer->companyId();
        abort_unless($companyId !== null, 403);
        abort_unless((int) $leaveRequest->employee->company_id === $companyId, 404);
    }

    private function assertManagerCanReview(LeaveRequest $leaveRequest, User $reviewer): void
    {
        abort_unless($reviewer->hasAnyRole(self::MANAGER_ROLES), 403);
        $reviewerEmployee = Employee::query()
            ->where('user_id', $reviewer->id)
            ->where('company_id', $leaveRequest->employee->company_id)
            ->first();
        abort_unless($reviewerEmployee !== null, 403);
        abort_if((int) $reviewerEmployee->id === (int) $leaveRequest->employee_id, 403);
        abort_unless((int) $reviewerEmployee->department_id === (int) $leaveRequest->employee->department_id, 403);
    }

    private function assertHrCanReview(User $reviewer): void
    {
        abort_unless($reviewer->hasAnyRole(self::HR_ROLES), 403);
    }

    private function notifyEmployee(
        LeaveRequest $leaveRequest,
        string $type,
        string $title,
        string $message,
        string $level,
    ): void {
        $userId = $leaveRequest->employee->user_id;
        if ($userId === null) {
            return;
        }

        $this->notifications->notify(
            (int) $userId,
            $type,
            $title,
            $message,
            '/leave/requests',
            'calendar-check',
            $level,
            ['leave_request_id' => $leaveRequest->public_id],
            (int) $leaveRequest->employee->company_id,
        );
    }
}
