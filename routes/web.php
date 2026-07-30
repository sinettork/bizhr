<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeeDocumentController;
use App\Http\Controllers\EmploymentHistoryController;
use App\Http\Controllers\EmploymentContractController;
use App\Http\Controllers\AttendanceReportController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Livewire\Dashboard;
use Livewire\Volt\Volt;
Route::get('/', fn () => auth()->check()
    ? redirect()->route('dashboard')
    : redirect()->route('login'))
    ->name('home');

Route::get('/attendance/qr/{token}/start', function (
    \Illuminate\Http\Request $request,
    string $token,
) {
    abort_unless((bool) preg_match('/^[A-Za-z0-9]{80}$/', $token), 404);

    $tokenHash = hash('sha256', $token);
    $qrSession = \App\Models\AttendanceQrSession::query()
        ->where('token_hash', $tokenHash)
        ->where('expires_at', '>', now())
        ->whereHas('branch', fn ($query) => $query
            ->where('is_active', true)
            ->where('attendance_qr_enabled', true))
        ->first();

    abort_unless($qrSession, 410, 'QR Code នេះអស់សុពលភាព។ សូមស្កេន QR ថ្មី។');

    $request->session()->put(
        "attendance_qr_grant.{$tokenHash}",
        now()->addMinutes(5)->timestamp,
    );

    $destination = route('attendance.qr.verify', ['token' => $token]);

    if (! auth()->check()) {
        $request->session()->put('url.intended', $destination);

        return redirect()->route('login');
    }

    return redirect()->to($destination);
})
    ->middleware('throttle:30,1')
    ->name('attendance.qr.start');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('/dashboard', Dashboard::class)
        ->name('dashboard');

    Route::livewire(
        '/company/settings',
        'pages::company.settings'
    )->name('company.settings')
        ->middleware('permission:company.view');

    Route::livewire(
        '/branches',
        'pages::branches.index'
    )->name('branches.index')
        ->middleware('permission:branch.view');
    Route::livewire(
        '/branches/create',
        'pages::branches.create'
    )->name('branches.create')
        ->middleware('permission:branch.create');

    Route::livewire(
        '/departments',
        'pages::departments.index'
    )->name('departments.index')
        ->middleware('permission:department.view');
    Route::livewire(
        '/departments/create',
        'pages::departments.create'
    )->name('departments.create')
        ->middleware('permission:department.create');

    Route::livewire(
        '/positions',
        'pages::positions.index'
    )->name('positions.index')
        ->middleware('permission:position.view');
    Route::livewire(
        '/positions/create',
        'pages::positions.create'
    )->name('positions.create')
        ->middleware('permission:position.create');

    Route::livewire(
        '/employment-types',
        'pages::employment-types.index'
    )->name('employment-types.index')
        ->middleware('permission:employment-type.view');

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

    Route::get(
        '/attendance/reports',
        [AttendanceReportController::class, 'index']
        )
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
    )->name('employees.create')
        ->middleware('permission:employee.create');

    Route::livewire(
        '/employees/{employee}',
        'pages::employees.show'
    )->name('employees.show')
        ->middleware('permission:employee.view|employee.view-own');

    Route::get('/employees/{employee}/documents', [EmployeeDocumentController::class, 'index'])
        ->name('employees.documents.index')
        ->middleware('permission:employee.view-sensitive|employee.view-own');
    Route::post('/employees/{employee}/documents', [EmployeeDocumentController::class, 'store'])
        ->name('employees.documents.store')
        ->middleware('permission:employee.edit|employee.edit-own');
    Route::get('/employees/{employee}/documents/{document}/download', [EmployeeDocumentController::class, 'download'])
        ->name('employees.documents.download')
        ->middleware('permission:employee.view-sensitive|employee.view-own');
    Route::delete('/employees/{employee}/documents/{document}', [EmployeeDocumentController::class, 'destroy'])
        ->name('employees.documents.destroy')
        ->middleware('permission:employee.edit|employee.edit-own');
    Route::get('/employees/{employee}/history', [EmploymentHistoryController::class, 'index'])
        ->name('employees.history.index')
        ->middleware('permission:employee.view-sensitive|employee.view-own');
    Route::post('/employees/{employee}/history', [EmploymentHistoryController::class, 'store'])
        ->name('employees.history.store')
        ->middleware('permission:employee.edit');

    Route::get('/employment-contracts', [EmploymentContractController::class, 'index'])
        ->name('contracts.index')
        ->middleware('permission:contract.view');
    Route::get('/employment-contracts/create', [EmploymentContractController::class, 'create'])
        ->name('contracts.create')
        ->middleware('permission:contract.create');
    Route::post('/employment-contracts', [EmploymentContractController::class, 'store'])
        ->name('contracts.store')
        ->middleware('permission:contract.create');
    Route::post('/employment-contracts/{contract}/approve', [EmploymentContractController::class, 'approve'])
        ->name('contracts.approve')
        ->middleware('permission:contract.approve');
    Route::get('/employment-contracts/{contract}/renew', [EmploymentContractController::class, 'renew'])
        ->name('contracts.renew')
        ->middleware('permission:contract.create');
    Route::post('/employment-contracts/{contract}/terminate', [EmploymentContractController::class, 'terminate'])
        ->name('contracts.terminate')
        ->middleware('permission:contract.terminate');
    Route::get('/employment-contracts/{contract}/download', [EmploymentContractController::class, 'download'])
        ->name('contracts.download')
        ->middleware('permission:contract.view|contract.view-own');
    Route::get('/my-contracts', [EmploymentContractController::class, 'mine'])
        ->name('contracts.mine')
        ->middleware('permission:contract.view-own');
    Route::delete('/employees/{employee}/history/{history}', [EmploymentHistoryController::class, 'destroy'])
        ->name('employees.history.destroy')
        ->middleware('permission:employee.edit');

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
        '/audit-logs',
        'pages::audit-logs.index'
    )->name('audit-logs.index')
        ->middleware('permission:audit.view');

    Route::livewire(
    '/leave/balances',
    'pages::leaves.balances'
)
    ->name('leave.balances.index')
    ->middleware(
        'permission:leave.report|leave.manage'
    );
    Route::livewire(
        '/attendance/scan',
        'attendance.scan'
    )->name('attendance.scan')
        ->middleware(
            'role_or_permission:Super Admin|attendance.checkin|attendance.checkout'
        );

    Route::livewire(
        '/attendance/qr-display',
        'attendance.qr-display'
    )
        ->name('attendance.qr.display')
        ->middleware('permission:attendance.approve|attendance.report');

    Route::livewire(
        '/attendance/qr/{token}',
        'attendance.qr-verify'
    )
        ->name('attendance.qr.verify')
        ->middleware(
            'role_or_permission:Super Admin|attendance.checkin|attendance.checkout'
        );

    Route::livewire('/payroll', 'pages::payroll.periods')
        ->name('payroll.periods.index')
        ->middleware('permission:payroll.view');

    Route::livewire('/payroll/settings', 'pages::payroll.settings')
        ->name('payroll.settings')
        ->middleware('permission:payroll.view');

    Route::livewire('/payroll/review', 'pages::payroll.review')
        ->name('payroll.review')
        ->middleware('permission:payroll.approve');

    Route::livewire('/payroll/statutory-profiles', 'pages::payroll.statutory-profiles')
        ->name('payroll.statutory-profiles')
        ->middleware('permission:payroll.approve');

    Route::livewire('/payroll/reports', 'pages::payroll.reports')
        ->name('payroll.reports')
        ->middleware('permission:payroll.report');

    Route::livewire('/my-payroll', 'pages::payroll.my-payslips')
        ->name('payroll.my-payslips')
        ->middleware('permission:payroll.view-own');

    Route::livewire('/performance/kpi-templates', 'pages::performance.kpi-templates')
        ->name('performance.kpi-templates')
        ->middleware('permission:performance.manage-goals');

    Route::livewire('/performance/goals', 'pages::performance.employee-goals')
        ->name('performance.goals')
        ->middleware('permission:performance.view');

    Route::livewire('/my-goals', 'pages::performance.my-goals')
        ->name('performance.my-goals')
        ->middleware('permission:performance.view-own');

    Route::livewire('/performance/reviews', 'pages::performance.performance-reviews')
        ->name('performance.reviews')
        ->middleware('permission:performance.view');

    Route::livewire('/my-performance-reviews', 'pages::performance.my-performance-reviews')
        ->name('performance.my-reviews')
        ->middleware('permission:performance.view-own');

    Route::livewire('/tasks', 'pages::tasks.index')
        ->name('tasks.index')->middleware('permission:task.view');
    Route::livewire('/my-tasks', 'pages::tasks.mine')
        ->name('tasks.mine')->middleware('permission:task.view-own');
    Route::livewire('/recruitment', 'pages::recruitment.pipeline')
        ->name('recruitment.pipeline')->middleware('permission:recruitment.view');
    Route::livewire('/training', 'pages::training.index')
        ->name('training.index')->middleware('permission:training.view');
    Route::livewire('/my-training', 'pages::training.mine')
        ->name('training.mine')->middleware('permission:training.view-own');
    Route::livewire('/assets', 'pages::assets.index')
        ->name('assets.index')->middleware('permission:asset.view');
    Route::livewire('/my-assets', 'pages::assets.mine')
        ->name('assets.mine')->middleware('permission:asset.view-own');
    Route::livewire('/expenses', 'pages::expenses.index')
        ->name('expenses.index')->middleware('permission:expense.view');
    Route::livewire('/my-expenses', 'pages::expenses.mine')
        ->name('expenses.mine')->middleware('permission:expense.view-own');
    Route::livewire('/announcements', 'pages::announcements.index')
        ->name('announcements.index')->middleware('permission:announcement.manage');
    Route::livewire('/news', 'pages::announcements.feed')
        ->name('announcements.feed')->middleware('permission:announcement.view');
});

require __DIR__.'/settings.php';
