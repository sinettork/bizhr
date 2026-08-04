<?php

namespace App\Http\Livewire;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\LeaveRequest;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $user = auth()->user();
        $today = today();
        
        $data = [
            'isManagerOrAdmin' => $user->can('attendance.report') || $user->can('employee.view'),
            'today' => $today,
        ];

        if ($data['isManagerOrAdmin']) {
            $attendances = Attendance::query()->whereDate('work_date', $today);
            $data['totalEmployees'] = Employee::active()->count();
            $data['presentToday'] = (clone $attendances)->whereIn('status', ['present', 'late', 'half_day', 'remote_work', 'business_trip'])->count();
            $data['lateToday'] = (clone $attendances)->where('late_minutes', '>', 0)->count();
            $data['onLeaveToday'] = LeaveRequest::query()->where('status', 'approved')->whereDate('start_date', '<=', $today)->whereDate('end_date', '>=', $today)->count();
            $data['pendingLeaveRequests'] = LeaveRequest::query()->whereIn('status', ['pending', 'manager_approved'])->count();
            $data['recentAttendances'] = Attendance::query()->with('employee')->whereDate('work_date', $today)->latest('check_in_at')->limit(6)->get();
        } else {
            $employee = $user->employee;
            if ($employee) {
                $data['myLeaveBalance'] = $employee->leaveBalances()->sum('remaining_days');
                $data['myPresentDays'] = Attendance::query()->where('employee_id', $employee->id)->whereIn('status', ['present', 'late'])->whereMonth('work_date', now()->month)->count();
                $data['lastCheckIn'] = Attendance::query()->where('employee_id', $employee->id)->latest('check_in_at')->first();
                $data['myRecentLeaveRequests'] = LeaveRequest::query()->where('employee_id', $employee->id)->latest()->limit(5)->get();
            }
        }

        return view('livewire.dashboard', $data)->layout('layouts.app');
    }
}
