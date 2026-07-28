<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeeDocumentController;
use App\Http\Controllers\EmploymentHistoryController;
use App\Http\Controllers\AttendanceReportController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Livewire\Dashboard;

Route::view('/', 'welcome')
    ->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('/dashboard', Dashboard::class)
        ->name('dashboard');

    Route::livewire(
        '/company/settings',
        'pages::company.settings'
    )->name('company.settings');

    Route::livewire(
        '/branches',
        'pages::branches.index'
    )->name('branches.index');
    Route::livewire(
        '/branches/create',
        'pages::branches.create'
    )->name('branches.create');

    Route::livewire(
        '/departments',
        'pages::departments.index'
    )->name('departments.index');
    Route::livewire(
        '/departments/create',
        'pages::departments.create'
    )->name('departments.create');

    Route::livewire(
        '/positions',
        'pages::positions.index'
    )->name('positions.index');
    Route::livewire(
        '/positions/create',
        'pages::positions.create'
    )->name('positions.create');

    Route::livewire(
        '/employment-types',
        'pages::employment-types.index'
    )->name('employment-types.index');

    Route::livewire(
        '/work-shifts',
        'pages::work-shifts.index'
    )->name('work-shifts.index')
    ->middleware('permission:shift.view');

    Route::livewire(
        '/schedules',
        'pages::schedules.index'
    )->name('schedules.index')
    ->middleware('permission:schedule.view');

    Route::livewire(
    '/attendance',
    'pages::attendance.check-in-out'
)
    ->name('attendance.checkinout')
    ->middleware(
        'role_or_permission:Super Admin|attendance.checkin|attendance.checkout'
    );

    Route::livewire(
        '/attendance/corrections/request',
        'pages::attendance.correction-request'
        )
        ->name('attendance.corrections.request')
        ->middleware(
            'permission:attendance.correction.request'
    );

        Route::livewire(
            '/attendance/corrections/review',
            'pages::attendance.corrections-review'
        )->name('attendance.corrections.review')
            ->middleware('permission:attendance.approve');

    Route::get('/attendance/reports', [AttendanceReportController::class, 'index'])
        ->name('attendance.reports.index')
        ->middleware('permission:attendance.report');

    Route::livewire(
        '/leave/types',
        'pages::leaves.types'
    )
        ->name('leave.types.index')
        ->middleware('permission:leave.manage');

    Route::get('/leave/requests', [LeaveRequestController::class, 'index'])
        ->name('leave.requests.index')
        ->middleware('permission:leave.request');
    Route::post('/leave/requests', [LeaveRequestController::class, 'store'])
        ->name('leave.requests.store')
        ->middleware('permission:leave.request');
    Route::get('/leave/review', [LeaveRequestController::class, 'review'])
        ->name('leave.requests.review')
        ->middleware('permission:leave.approve');
    Route::post('/leave/requests/{leaveRequest}/approve', [LeaveRequestController::class, 'approve'])
        ->name('leave.requests.approve')
        ->middleware('permission:leave.approve');
    Route::post('/leave/requests/{leaveRequest}/reject', [LeaveRequestController::class, 'reject'])
        ->name('leave.requests.reject')
        ->middleware('permission:leave.approve');

    Route::livewire(
        '/employees',
        'pages::employees.index'
    )->name('employees.index')
    ->middleware('permission:employee.view');

    Route::livewire(
        '/employees/create',
        'pages::employees.create'
    )->name('employees.create');

    Route::livewire(
        '/employees/{employee}',
        'pages::employees.show'
    )->name('employees.show');

    Route::get('/employees/{employee}/documents', [EmployeeDocumentController::class, 'index'])->name('employees.documents.index');
    Route::post('/employees/{employee}/documents', [EmployeeDocumentController::class, 'store'])->name('employees.documents.store');
    Route::get('/employees/{employee}/documents/{document}/download', [EmployeeDocumentController::class, 'download'])->name('employees.documents.download');
    Route::delete('/employees/{employee}/documents/{document}', [EmployeeDocumentController::class, 'destroy'])->name('employees.documents.destroy');
    Route::get('/employees/{employee}/history', [EmploymentHistoryController::class, 'index'])->name('employees.history.index');
    Route::post('/employees/{employee}/history', [EmploymentHistoryController::class, 'store'])->name('employees.history.store');
    Route::delete('/employees/{employee}/history/{history}', [EmploymentHistoryController::class, 'destroy'])->name('employees.history.destroy');

    Route::livewire(
        '/roles',
        'pages::roles.index'
    )->name('roles.index')
    ->middleware('permission:role.manage');

    Route::livewire(
        '/users',
        'pages::users.index'
    )->name('users.index')
    ->middleware('permission:user.manage');

    Route::livewire(
    '/leave/balances',
    'pages::leaves.balances'
)
    ->name('leave.balances.index')
    ->middleware(
        'permission:leave.report|leave.manage'
    );


    Route::get('/debug-auth', function () {
    $user = request()->user();

    return response()->json([
        'logged_in' => (bool) $user,
        'id' => $user?->id,
        'name' => $user?->name,
        'email' => $user?->email,
        'roles' => $user?->getRoleNames()->all(),
        'attendance_checkin' => $user?->can('attendance.checkin'),
        'attendance_checkout' => $user?->can('attendance.checkout'),
    ]);
})->middleware('auth');
});

require __DIR__.'/settings.php';
