<?php

namespace App\Services;

use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LeaveApprovalService
{
    public function approve(LeaveRequest $request, User $reviewer, ?string $note = null): LeaveRequest
    {
        return DB::transaction(function () use ($request, $reviewer, $note) {
            $request->refresh();

            if ($request->status !== 'pending') {
                throw ValidationException::withMessages(['status' => 'Only pending leave requests can be approved.']);
            }

            $balance = LeaveBalance::query()
                ->where('employee_id', $request->employee_id)
                ->where('leave_type_id', $request->leave_type_id)
                ->where('year', $request->start_date->year)
                ->lockForUpdate()
                ->firstOrFail();

            if ((float) $balance->remaining_days < (float) $request->total_days) {
                throw ValidationException::withMessages(['status' => 'The employee no longer has enough leave balance.']);
            }

            $balance->decrement('remaining_days', $request->total_days);
            $balance->increment('used_days', $request->total_days);

            $request->update([
                'status' => 'approved',
                'hr_id' => $reviewer->id,
                'hr_reviewed_at' => now(),
                'hr_note' => $note,
            ]);

            return $request->fresh();
        });
    }

    public function reject(LeaveRequest $request, User $reviewer, ?string $note = null): LeaveRequest
    {
        if (! in_array($request->status, ['pending', 'manager_approved'], true)) {
            throw ValidationException::withMessages(['status' => 'This leave request has already been finalized.']);
        }

        $request->update(['status' => 'rejected', 'hr_id' => $reviewer->id, 'hr_reviewed_at' => now(), 'hr_note' => $note]);

        return $request->fresh();
    }
}
