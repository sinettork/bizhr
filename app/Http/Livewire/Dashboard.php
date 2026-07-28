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
        $today = today();
        $attendances = Attendance::query()->whereDate('work_date', $today);

        return view('livewire.dashboard', [
            'totalEmployees' => Employee::query()->where('is_active', true)->count(),
            'presentToday' => (clone $attendances)->whereIn('status', ['present', 'late', 'half_day', 'remote_work', 'business_trip'])->count(),
            'lateToday' => (clone $attendances)->where('late_minutes', '>', 0)->count(),
            'onLeaveToday' => LeaveRequest::query()->where('status', 'approved')->whereDate('start_date', '<=', $today)->whereDate('end_date', '>=', $today)->count(),
            'pendingLeaveRequests' => LeaveRequest::query()->whereIn('status', ['pending', 'manager_approved'])->count(),
            'recentAttendances' => Attendance::query()->with('employee')->whereDate('work_date', $today)->latest('check_in_at')->limit(6)->get(),
        ])->layout('layouts.app');
    }
}
