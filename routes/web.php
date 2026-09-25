<?php

use App\Http\Controllers\AccessAdministrationController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceCorrectionController;
use App\Http\Controllers\AttendanceQrController;
use App\Http\Controllers\AttendanceReportController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\BulkImportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DashboardPreferenceController;
use App\Http\Controllers\DataExportController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeDocumentController;
use App\Http\Controllers\EmployeeScheduleController;
use App\Http\Controllers\EmploymentContractController;
use App\Http\Controllers\EmploymentHistoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\LeaveBalanceController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\LeaveTypeController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\PerformanceController;
use App\Http\Controllers\RecruitmentController;
use App\Http\Controllers\StandardPageController;
use App\Http\Controllers\TablePreferenceController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TrainingController;
use App\Http\Controllers\WorkShiftController;
use App\Models\AttendanceQrSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    $checks = [];
    $status = 200;

    // Database check
    try {
        \Illuminate\Support\Facades\DB::selectOne('SELECT 1 AS ok');
        $checks['database'] = 'ok';
    } catch (\Throwable) {
        $checks['database'] = 'fail';
        $status = 503;
    }

    // Cache / Redis check
    try {
        $key = 'health:' . str()->random(6);
        \Illuminate\Support\Facades\Cache::put($key, true, 5);
        \Illuminate\Support\Facades\Cache::forget($key);
        $checks['cache'] = 'ok';
    } catch (\Throwable) {
        $checks['cache'] = 'fail';
        $status = 503;
    }

    // Queue check (verify failed_jobs table is reachable — lightweight)
    try {
        \Illuminate\Support\Facades\DB::table('jobs')->count();
        $checks['queue_table'] = 'ok';
    } catch (\Throwable) {
        $checks['queue_table'] = 'fail';
        $status = 503;
    }

    return response()->json([
        'status'  => $status === 200 ? 'healthy' : 'degraded',
        'checks'  => $checks,
        'version' => config('app.version', '1.0'),
        'env'     => config('app.env'),
        'time'    => now()->toIso8601String(),
    ], $status);
})->name('health.deep');

Route::get('/', fn () => auth()->check() ? redirect()->route('dashboard') : redirect()->route('login'))->name('home');

Route::get('/verify/id-card/{employee}/{token}', [EmployeeController::class, 'idCardVerify'])->middleware('throttle:30,1')->name('employees.id-card.verify');

Route::get('/attendance/qr/{token}/start', function (Request $request, string $token) {
    abort_unless((bool) preg_match('/^[A-Za-z0-9]{80}$/', $token), 404);
    $tokenHash = hash('sha256', $token);
    $session = AttendanceQrSession::query()->where('token_hash', $tokenHash)->where('expires_at', '>', now())->whereHas('branch', fn ($query) => $query->where('is_active', true)->where('attendance_qr_enabled', true))->first();
    abort_unless($session !== null, 410, 'This QR code has expired. Please scan a new code.');
    $request->session()->put("attendance_qr_grant.{$tokenHash}", now()->addMinutes(5)->timestamp);
    $destination = route('attendance.qr.verify', ['token' => $token]);
    if (! auth()->check()) {
        $request->session()->put('url.intended', $destination);

        return redirect()->route('login');
    }

    return redirect()->to($destination);
})->middleware('throttle:30,1')->name('attendance.qr.start');

Route::middleware(['auth', 'active', 'verified'])->group(function () {
    Route::put('/preferences/table-columns', [TablePreferenceController::class, 'update'])->name('preferences.table-columns.update');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::put('/preferences/dashboard', [DashboardPreferenceController::class, 'update'])->name('preferences.dashboard.update');
    Route::delete('/preferences/dashboard', [DashboardPreferenceController::class, 'destroy'])->name('preferences.dashboard.destroy');

    Route::get('/exports', [DataExportController::class, 'index'])->name('exports.index');
    Route::post('/exports/{type}', [DataExportController::class, 'store'])->whereIn('type', ['employees', 'attendance', 'payroll'])->middleware('throttle:10,1')->name('exports.store');
    Route::get('/exports/{dataExport}/download', [DataExportController::class, 'download'])->name('exports.download');
    Route::get('/imports', [BulkImportController::class, 'index'])->name('imports.index');
    Route::get('/imports/{type}/template', [BulkImportController::class, 'template'])->whereIn('type', ['branches', 'departments', 'positions', 'employment-types', 'employees'])->name('imports.template');
    Route::post('/imports/{type}/preview', [BulkImportController::class, 'preview'])->whereIn('type', ['branches', 'departments', 'positions', 'employment-types', 'employees'])->name('imports.preview');
    Route::get('/imports/{type}/preview/{token}', [BulkImportController::class, 'showPreview'])->whereIn('type', ['branches', 'departments', 'positions', 'employment-types', 'employees'])->name('imports.preview.show');
    Route::post('/imports/{type}/confirm', [BulkImportController::class, 'confirm'])->whereIn('type', ['branches', 'departments', 'positions', 'employment-types', 'employees'])->name('imports.confirm');

    $page = static fn (string $uri, string $name, string $key, string|array $middleware) => Route::get($uri, [StandardPageController::class, 'show'])->defaults('page', $key)->middleware($middleware)->name($name);

    Route::get('/company/settings', [OrganizationController::class, 'company'])->middleware('permission:company.view')->name('company.settings');
    Route::put('/company/settings', [OrganizationController::class, 'updateCompany'])->middleware('permission:company.edit')->name('company.settings.update');
    Route::get('/branches', [OrganizationController::class, 'branches'])->middleware('permission:branch.view')->name('branches.index');
    Route::post('/branches', [OrganizationController::class, 'storeBranch'])->middleware('permission:branch.create')->name('branches.store');
    Route::put('/branches/{branch}', [OrganizationController::class, 'updateBranch'])->middleware('permission:branch.edit')->name('branches.update');
    Route::delete('/branches/{branch}', [OrganizationController::class, 'destroyBranch'])->middleware('permission:branch.delete')->name('branches.destroy');
    Route::redirect('/branches/create', '/branches')->middleware('permission:branch.create')->name('branches.create');
    Route::get('/departments', [OrganizationController::class, 'departments'])->middleware('permission:department.view')->name('departments.index');
    Route::post('/departments', [OrganizationController::class, 'storeDepartment'])->middleware('permission:department.create')->name('departments.store');
    Route::put('/departments/{department}', [OrganizationController::class, 'updateDepartment'])->middleware('permission:department.edit')->name('departments.update');
    Route::delete('/departments/{department}', [OrganizationController::class, 'destroyDepartment'])->middleware('permission:department.delete')->name('departments.destroy');
    Route::redirect('/departments/create', '/departments')->middleware('permission:department.create')->name('departments.create');
    Route::get('/positions', [OrganizationController::class, 'positions'])->middleware('permission:position.view')->name('positions.index');
    Route::post('/positions', [OrganizationController::class, 'storePosition'])->middleware('permission:position.create')->name('positions.store');
    Route::put('/positions/{position}', [OrganizationController::class, 'updatePosition'])->middleware('permission:position.edit')->name('positions.update');
    Route::delete('/positions/{position}', [OrganizationController::class, 'destroyPosition'])->middleware('permission:position.delete')->name('positions.destroy');
    Route::redirect('/positions/create', '/positions')->middleware('permission:position.create')->name('positions.create');
    Route::get('/employment-types', [OrganizationController::class, 'employmentTypes'])->middleware('permission:employment-type.view')->name('employment-types.index');
    Route::post('/employment-types', [OrganizationController::class, 'storeEmploymentType'])->middleware('permission:employment-type.create')->name('employment-types.store');
    Route::put('/employment-types/{employmentType}', [OrganizationController::class, 'updateEmploymentType'])->middleware('permission:employment-type.edit')->name('employment-types.update');
    Route::delete('/employment-types/{employmentType}', [OrganizationController::class, 'destroyEmploymentType'])->middleware('permission:employment-type.delete')->name('employment-types.destroy');
    Route::get('/work-shifts', [WorkShiftController::class, 'index'])->middleware('permission:shift.view')->name('work-shifts.index');
    Route::post('/work-shifts', [WorkShiftController::class, 'store'])->middleware('permission:shift.create')->name('work-shifts.store');
    Route::put('/work-shifts/{workShift}', [WorkShiftController::class, 'update'])->middleware('permission:shift.edit')->name('work-shifts.update');
    Route::delete('/work-shifts/{workShift}', [WorkShiftController::class, 'destroy'])->middleware('permission:shift.delete')->name('work-shifts.destroy');
    Route::get('/schedules', [EmployeeScheduleController::class, 'index'])->middleware('permission:schedule.view')->name('schedules.index');
    Route::post('/schedules', [EmployeeScheduleController::class, 'store'])->middleware('permission:schedule.create')->name('schedules.store');
    Route::put('/schedules/{schedule}', [EmployeeScheduleController::class, 'update'])->middleware('permission:schedule.edit')->name('schedules.update');
    Route::delete('/schedules/{schedule}', [EmployeeScheduleController::class, 'destroy'])->middleware('permission:schedule.delete')->name('schedules.destroy');
    Route::get('/attendance', [AttendanceController::class, 'index'])->middleware('role_or_permission:Super Admin|attendance.checkin|attendance.checkout|attendance.report')->name('attendance.checkinout');
    Route::get('/attendance/corrections/request', [AttendanceCorrectionController::class, 'index'])->middleware('permission:attendance.correction.request')->name('attendance.corrections.request');
    Route::post('/attendance/corrections', [AttendanceCorrectionController::class, 'store'])->middleware('permission:attendance.correction.request')->name('attendance.corrections.store');
    Route::get('/attendance/corrections/review', [AttendanceCorrectionController::class, 'review'])->middleware('permission:attendance.approve')->name('attendance.corrections.review');
    Route::post('/attendance/corrections/{correction}/approve', [AttendanceCorrectionController::class, 'approve'])->middleware('permission:attendance.approve')->name('attendance.corrections.approve');
    Route::post('/attendance/corrections/{correction}/reject', [AttendanceCorrectionController::class, 'reject'])->middleware('permission:attendance.approve')->name('attendance.corrections.reject');
    Route::post('/attendance/corrections/{correction}/reopen', [AttendanceCorrectionController::class, 'reopen'])->middleware('permission:attendance.correction.request')->name('attendance.corrections.reopen');
    Route::get('/attendance/reports', [AttendanceReportController::class, 'index'])->middleware('permission:attendance.report')->name('attendance.reports.index');
    Route::get('/leave/types', [LeaveTypeController::class, 'index'])->middleware('permission:leave.manage')->name('leave.types.index');
    Route::post('/leave/types', [LeaveTypeController::class, 'store'])->middleware('permission:leave.manage')->name('leave.types.store');
    Route::put('/leave/types/{leaveType}', [LeaveTypeController::class, 'update'])->middleware('permission:leave.manage')->name('leave.types.update');
    Route::delete('/leave/types/{leaveType}', [LeaveTypeController::class, 'destroy'])->middleware('permission:leave.manage')->name('leave.types.destroy');

    Route::get('/leave/requests', [LeaveRequestController::class, 'index'])->middleware('permission:leave.request')->name('leave.requests.index');
    Route::post('/leave/requests', [LeaveRequestController::class, 'store'])->middleware('permission:leave.request')->name('leave.requests.store');
    Route::post('/leave/requests/{leaveRequest}/withdraw', [LeaveRequestController::class, 'withdraw'])->middleware('permission:leave.request')->name('leave.requests.withdraw');
    Route::get('/leave/review', [LeaveRequestController::class, 'review'])->middleware('permission:leave.approve')->name('leave.requests.review');
    Route::post('/leave/requests/{leaveRequest}/approve', [LeaveRequestController::class, 'approve'])->middleware('permission:leave.approve')->name('leave.requests.approve');
    Route::post('/leave/requests/{leaveRequest}/reject', [LeaveRequestController::class, 'reject'])->middleware('permission:leave.approve')->name('leave.requests.reject');

    Route::get('/employees/{employee}/documents', [EmployeeDocumentController::class, 'index'])->middleware('permission:employee.view-sensitive|employee.view-own')->name('employees.documents.index');
    Route::post('/employees/{employee}/documents', [EmployeeDocumentController::class, 'store'])->middleware('permission:employee.edit|employee.edit-own')->name('employees.documents.store');
    Route::get('/employees/{employee}/documents/{document}/download', [EmployeeDocumentController::class, 'download'])->middleware('permission:employee.view-sensitive|employee.view-own')->name('employees.documents.download');
    Route::delete('/employees/{employee}/documents/{document}', [EmployeeDocumentController::class, 'destroy'])->middleware('permission:employee.edit|employee.edit-own')->name('employees.documents.destroy');
    Route::post('/employees/{employee}/documents/{document}/verify', [EmployeeDocumentController::class, 'verify'])->middleware('permission:employee.view-sensitive')->name('employees.documents.verify');
    Route::post('/employees/{employee}/documents/{document}/revoke', [EmployeeDocumentController::class, 'revoke'])->middleware('permission:employee.view-sensitive')->name('employees.documents.revoke');
    Route::get('/employees/{employee}/history', [EmploymentHistoryController::class, 'index'])->middleware('permission:employee.view-sensitive|employee.view-own')->name('employees.history.index');
    Route::post('/employees/{employee}/history', [EmploymentHistoryController::class, 'store'])->middleware('permission:employee.edit')->name('employees.history.store');
    Route::delete('/employees/{employee}/history/{history}', [EmploymentHistoryController::class, 'destroy'])->middleware('permission:employee.edit')->name('employees.history.destroy');
    Route::get('/employees', [EmployeeController::class, 'index'])->middleware('permission:employee.view|employee.view-own')->name('employees.index');
    Route::get('/employees/create', [EmployeeController::class, 'create'])->middleware('permission:employee.create')->name('employees.create');
    Route::post('/employees', [EmployeeController::class, 'store'])->middleware('permission:employee.create')->name('employees.store');
    Route::get('/employees/{employee}', [EmployeeController::class, 'show'])->middleware('permission:employee.view|employee.view-own')->name('employees.show');
    Route::get('/employees/{employee}/id-card', [EmployeeController::class, 'idCard'])->middleware('permission:employee.view|employee.view-own')->name('employees.id-card');
    Route::get('/employees/{employee}/id-card/qr', [EmployeeController::class, 'idCardQr'])->middleware('permission:employee.view|employee.view-own')->name('employees.id-card.qr');
    Route::post('/employees/{employee}/id-card/verification-token/revoke', [EmployeeController::class, 'revokeIdCardVerificationToken'])->middleware('permission:employee.edit|employee.edit-own')->name('employees.id-card.verify.revoke');
    Route::get('/employees/{employee}/photo', [EmployeeController::class, 'photo'])->middleware('permission:employee.view|employee.view-own')->name('employees.photo');
    Route::get('/employees/{employee}/edit', [EmployeeController::class, 'edit'])->middleware('permission:employee.edit|employee.edit-own')->name('employees.edit');
    Route::put('/employees/{employee}', [EmployeeController::class, 'update'])->middleware('permission:employee.edit|employee.edit-own')->name('employees.update');
    Route::post('/employees/{employee}/rehire-request', [EmployeeController::class, 'requestRehire'])->middleware('permission:employee.edit')->name('employees.rehire.request');
    Route::post('/employees/{employee}/rehire-approve', [EmployeeController::class, 'approveRehire'])->middleware('permission:employee.approve')->name('employees.rehire.approve');
    Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy'])->middleware('permission:employee.delete')->name('employees.destroy');

    Route::get('/employment-contracts', [EmploymentContractController::class, 'index'])->middleware('permission:contract.view')->name('contracts.index');
    Route::get('/employment-contracts/create', [EmploymentContractController::class, 'create'])->middleware('permission:contract.create')->name('contracts.create');
    Route::post('/employment-contracts', [EmploymentContractController::class, 'store'])->middleware('permission:contract.create')->name('contracts.store');
    Route::post('/employment-contracts/{contract}/approve', [EmploymentContractController::class, 'approve'])->middleware('permission:contract.approve')->name('contracts.approve');
    Route::get('/employment-contracts/{contract}/renew', [EmploymentContractController::class, 'renew'])->middleware('permission:contract.create')->name('contracts.renew');
    Route::post('/employment-contracts/{contract}/terminate', [EmploymentContractController::class, 'terminate'])->middleware('permission:contract.terminate')->name('contracts.terminate');
    Route::get('/employment-contracts/{contract}/download', [EmploymentContractController::class, 'download'])->middleware('permission:contract.view|contract.view-own')->name('contracts.download');
    Route::get('/my-contracts', [EmploymentContractController::class, 'mine'])->middleware('permission:contract.view-own')->name('contracts.mine');

    Route::get('/roles', [AccessAdministrationController::class, 'roles'])->middleware('permission:role.manage')->name('roles.index');
    Route::post('/roles', [AccessAdministrationController::class, 'storeRole'])->middleware('permission:role.manage')->name('roles.store');
    Route::put('/roles/{role}', [AccessAdministrationController::class, 'updateRole'])->middleware('permission:role.manage')->name('roles.update');
    Route::delete('/roles/{role}', [AccessAdministrationController::class, 'destroyRole'])->middleware('permission:role.manage')->name('roles.destroy');
    Route::get('/users', [AccessAdministrationController::class, 'users'])->middleware('permission:user.manage')->name('users.index');
    Route::post('/users', [AccessAdministrationController::class, 'storeUser'])->middleware('permission:user.manage')->name('users.store');
    Route::put('/users/{user}', [AccessAdministrationController::class, 'updateUser'])->middleware('permission:user.manage')->name('users.update');
    Route::put('/users/{user}/status', [AccessAdministrationController::class, 'status'])->middleware('permission:user.manage')->name('users.status');
    Route::post('/users/{user}/password-reset', [AccessAdministrationController::class, 'resetPassword'])->middleware('permission:user.manage')->name('users.password-reset');
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->middleware('permission:audit.view')->name('audit-logs.index');
    Route::get('/leave/balances', [LeaveBalanceController::class, 'index'])->middleware('permission:leave.report|leave.manage')->name('leave.balances.index');
    Route::post('/leave/balances/initialize', [LeaveBalanceController::class, 'initialize'])->middleware('permission:leave.manage')->name('leave.balances.initialize');
    Route::post('/leave/balances/synchronize', [LeaveBalanceController::class, 'synchronize'])->middleware('permission:leave.manage')->name('leave.balances.synchronize');
    Route::post('/leave/balances/{balance}/adjust', [LeaveBalanceController::class, 'adjust'])->middleware('permission:leave.manage')->name('leave.balances.adjust');
    Route::redirect('/attendance/scan', '/attendance')->middleware('role_or_permission:Super Admin|attendance.checkin|attendance.checkout')->name('attendance.scan');
    Route::get('/attendance/qr-display', [AttendanceQrController::class, 'display'])->middleware('permission:attendance.approve|attendance.report')->name('attendance.qr.display');
    Route::post('/attendance/qr-sessions', [AttendanceQrController::class, 'create'])->middleware('permission:attendance.approve|attendance.report')->name('attendance.qr.sessions.store');
    Route::get('/attendance/qr/{token}', [AttendanceQrController::class, 'verify'])->middleware('role_or_permission:Super Admin|attendance.checkin|attendance.checkout')->name('attendance.qr.verify');
    Route::post('/attendance/qr/{token}/record', [AttendanceQrController::class, 'record'])->middleware('role_or_permission:Super Admin|attendance.checkin|attendance.checkout')->name('attendance.qr.record');
    Route::get('/payroll', [PayrollController::class, 'periods'])->middleware('permission:payroll.view')->name('payroll.periods.index');
    Route::post('/payroll', [PayrollController::class, 'storePeriod'])->middleware('permission:payroll.edit')->name('payroll.periods.store');
    Route::post('/payroll/{period}/generate', [PayrollController::class, 'generate'])->middleware('permission:payroll.process')->name('payroll.periods.generate');
    Route::post('/payroll/{period}/approve', [PayrollController::class, 'approve'])->middleware('permission:payroll.approve')->name('payroll.periods.approve');
    Route::post('/payroll/{period}/payment', [PayrollController::class, 'pay'])->middleware('permission:payroll.process')->name('payroll.periods.pay');
    Route::get('/payroll/settings', [PayrollController::class, 'settings'])->middleware('permission:payroll.view')->name('payroll.settings');
    Route::put('/payroll/settings', [PayrollController::class, 'updateSettings'])->middleware('permission:payroll.approve')->name('payroll.settings.update');
    Route::get('/payroll/review', [PayrollController::class, 'review'])->middleware('permission:payroll.approve')->name('payroll.review');
    Route::post('/payroll/overtime/{attendance}/{decision}', [PayrollController::class, 'reviewOvertime'])->whereIn('decision', ['approve', 'reject'])->middleware('permission:payroll.approve')->name('payroll.overtime.review');
    $page('/payroll/statutory-profiles', 'payroll.statutory-profiles', 'payroll-statutory', 'permission:payroll.approve');
    $page('/payroll/reports', 'payroll.reports', 'payroll-reports', 'permission:payroll.report');
    Route::get('/my-payroll', [PayrollController::class, 'payslips'])->middleware('permission:payroll.view-own')->name('payroll.my-payslips');
    Route::get('/performance/kpi-templates', [PerformanceController::class, 'templates'])->middleware('permission:performance.manage-goals')->name('performance.kpi-templates');
    Route::post('/performance/kpi-templates', [PerformanceController::class, 'storeTemplate'])->middleware('permission:performance.manage-goals')->name('performance.kpi-templates.store');
    Route::get('/performance/goals', [PerformanceController::class, 'goals'])->middleware('permission:performance.view')->name('performance.goals');
    Route::post('/performance/goals', [PerformanceController::class, 'storeGoal'])->middleware('permission:performance.manage-goals')->name('performance.goals.store');
    Route::get('/my-goals', [PerformanceController::class, 'myGoals'])->middleware('permission:performance.view-own')->name('performance.my-goals');
    Route::post('/my-goals/{goal}', [PerformanceController::class, 'updateGoal'])->middleware('permission:performance.view-own')->name('performance.my-goals.update');
    Route::get('/performance/reviews', [PerformanceController::class, 'reviews'])->middleware('permission:performance.view')->name('performance.reviews');
    Route::post('/performance/reviews', [PerformanceController::class, 'createReview'])->middleware('permission:performance.create')->name('performance.reviews.store');
    Route::post('/performance/reviews/{review}/submit', [PerformanceController::class, 'submitReview'])->middleware('permission:performance.review')->name('performance.reviews.submit');
    Route::post('/performance/reviews/{review}/{action}', [PerformanceController::class, 'transition'])->whereIn('action', ['approve', 'close', 'reopen'])->middleware('permission:performance.approve|performance.reopen')->name('performance.reviews.transition');
    Route::get('/my-performance-reviews', [PerformanceController::class, 'myReviews'])->middleware('permission:performance.view-own')->name('performance.my-reviews');
    Route::post('/my-performance-reviews/{review}/acknowledge', [PerformanceController::class, 'acknowledge'])->middleware('permission:performance.view-own')->name('performance.my-reviews.acknowledge');
    Route::get('/tasks', [TaskController::class, 'index'])->middleware('permission:task.view')->name('tasks.index');
    Route::post('/tasks', [TaskController::class, 'store'])->middleware('permission:task.assign')->name('tasks.store');
    Route::put('/tasks/{task}', [TaskController::class, 'update'])->middleware('permission:task.assign')->name('tasks.update');
    Route::post('/tasks/{task}/cancel', [TaskController::class, 'cancel'])->middleware('permission:task.assign')->name('tasks.cancel');
    Route::post('/tasks/{task}/verify/{decision}', [TaskController::class, 'verify'])->whereIn('decision', ['approve', 'return'])->middleware('permission:task.verify')->name('tasks.verify');
    Route::get('/my-tasks', [TaskController::class, 'mine'])->middleware('permission:task.view-own')->name('tasks.mine');
    Route::post('/my-tasks/{task}/progress', [TaskController::class, 'progress'])->middleware('permission:task.view-own')->name('tasks.progress');
    Route::get('/recruitment', [RecruitmentController::class, 'index'])->middleware('permission:recruitment.view')->name('recruitment.pipeline');
    Route::post('/recruitment/vacancies', [RecruitmentController::class, 'storeVacancy'])->middleware('permission:recruitment.manage')->name('recruitment.vacancies.store');
    Route::post('/recruitment/vacancies/{vacancy}/applicants', [RecruitmentController::class, 'storeApplicant'])->middleware('permission:recruitment.manage')->name('recruitment.applicants.store');
    Route::post('/recruitment/applicants/{applicant}/transition', [RecruitmentController::class, 'transition'])->middleware('permission:recruitment.manage')->name('recruitment.applicants.transition');
    Route::get('/recruitment/applicants/{applicant}/cv', [RecruitmentController::class, 'downloadCv'])->middleware('permission:recruitment.view')->name('recruitment.applicants.cv');
    Route::get('/training', [TrainingController::class, 'index'])->middleware('permission:training.view')->name('training.index');
    Route::post('/training', [TrainingController::class, 'store'])->middleware('permission:training.manage')->name('training.store');
    Route::put('/training/{course}', [TrainingController::class, 'update'])->middleware('permission:training.manage')->name('training.update');
    Route::delete('/training/{course}', [TrainingController::class, 'destroy'])->middleware('permission:training.manage')->name('training.destroy');
    Route::post('/training/{course}/enroll', [TrainingController::class, 'enroll'])->middleware('permission:training.manage')->name('training.enroll');
    Route::get('/my-training', [TrainingController::class, 'mine'])->middleware('permission:training.view-own')->name('training.mine');
    Route::post('/my-training/{enrollment}/progress', [TrainingController::class, 'progress'])->middleware('permission:training.view-own')->name('training.progress');
    Route::get('/assets', [AssetController::class, 'index'])->middleware('permission:asset.view')->name('assets.index');
    Route::post('/assets', [AssetController::class, 'store'])->middleware('permission:asset.manage')->name('assets.store');
    Route::put('/assets/{asset}', [AssetController::class, 'update'])->middleware('permission:asset.manage')->name('assets.update');
    Route::delete('/assets/{asset}', [AssetController::class, 'destroy'])->middleware('permission:asset.manage')->name('assets.destroy');
    Route::post('/assets/{asset}/assign', [AssetController::class, 'assign'])->middleware('permission:asset.manage')->name('assets.assign');
    Route::get('/my-assets', [AssetController::class, 'mine'])->middleware('permission:asset.view-own')->name('assets.mine');
    Route::post('/asset-assignments/{assignment}/receive', [AssetController::class, 'receive'])->middleware('permission:asset.manage|asset.view-own')->name('assets.receive');
    Route::get('/expenses', [ExpenseController::class, 'index'])->middleware('permission:expense.view')->name('expenses.index');
    Route::get('/my-expenses', [ExpenseController::class, 'mine'])->middleware('permission:expense.view-own')->name('expenses.mine');
    Route::post('/my-expenses', [ExpenseController::class, 'store'])->middleware('permission:expense.view-own')->name('expenses.store');
    Route::post('/expenses/{claim}/{stage}/{decision}', [ExpenseController::class, 'review'])->whereIn('stage', ['manager', 'accounting'])->whereIn('decision', ['approve', 'reject'])->middleware('permission:expense.approve-manager|expense.approve-accounting')->name('expenses.review');
    Route::post('/expenses/{claim}/pay', [ExpenseController::class, 'pay'])->middleware('permission:expense.pay')->name('expenses.pay');
    Route::get('/expenses/{claim}/receipt', [ExpenseController::class, 'receipt'])->middleware('permission:expense.view|expense.view-own')->name('expenses.receipt');
    Route::get('/announcements', [AnnouncementController::class, 'index'])->middleware('permission:announcement.manage')->name('announcements.index');
    Route::post('/announcements', [AnnouncementController::class, 'store'])->middleware('permission:announcement.manage')->name('announcements.store');
    Route::put('/announcements/{announcement}', [AnnouncementController::class, 'update'])->middleware('permission:announcement.manage')->name('announcements.update');
    Route::post('/announcements/{announcement}/publish', [AnnouncementController::class, 'publish'])->middleware('permission:announcement.manage')->name('announcements.publish');
    Route::delete('/announcements/{announcement}', [AnnouncementController::class, 'destroy'])->middleware('permission:announcement.manage')->name('announcements.destroy');
    Route::get('/news', [AnnouncementController::class, 'feed'])->middleware('permission:announcement.view')->name('announcements.feed');
    Route::post('/news/{announcement}/acknowledge', [AnnouncementController::class, 'acknowledge'])->middleware('permission:announcement.view')->name('announcements.acknowledge');
});

require __DIR__.'/settings.php';
