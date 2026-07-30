<?php

namespace App\Providers;

use App\Http\Livewire\Ui\SidebarPreview;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\EmployeeSchedule;
use App\Models\EmploymentHistory;
use App\Models\EmploymentContract;
use App\Models\EmploymentType;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\KpiTemplate;
use App\Models\KpiTemplateItem;
use App\Models\EmployeeGoal;
use App\Models\PerformanceReview;
use App\Models\PerformanceReviewScore;
use App\Models\Task;
use App\Models\JobVacancy;
use App\Models\JobApplicant;
use App\Models\JobInterview;
use App\Models\JobOffer;
use App\Models\TrainingCourse;
use App\Models\TrainingEnrollment;
use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\ExpenseClaim;
use App\Models\Announcement;
use App\Models\PayrollAdjustment;
use App\Models\PayrollItem;
use App\Models\PayrollPeriod;
use App\Models\PayrollPayment;
use App\Models\PayrollSetting;
use App\Models\Position;
use App\Models\PublicHoliday;
use App\Models\User;
use App\Models\WorkShift;
use App\Observers\AuditLogObserver;
use App\Observers\StructureAuthorizationObserver;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Gate::before(fn (User $user, string $ability): ?bool => $user->hasRole('Super Admin') ? true : null);

        $this->configureDefaults();
        $this->registerAuditObservers();

        if (class_exists(SidebarPreview::class)) {
            Livewire::component('ui.sidebar-preview', SidebarPreview::class);
        }
    }

    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);
        DB::prohibitDestructiveCommands(app()->isProduction());

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)->mixedCase()->letters()->numbers()->symbols()->uncompromised()
            : null);
    }

    private function registerAuditObservers(): void
    {
        foreach ([
            Company::class,
            Branch::class,
            Department::class,
            Position::class,
            Employee::class,
            EmployeeDocument::class,
            EmploymentHistory::class,
            EmploymentContract::class,
            WorkShift::class,
            EmployeeSchedule::class,
            Attendance::class,
            AttendanceCorrection::class,
            LeaveType::class,
            LeaveBalance::class,
            LeaveRequest::class,
            KpiTemplate::class,
            KpiTemplateItem::class,
            EmployeeGoal::class,
            PerformanceReview::class,
            PerformanceReviewScore::class,
            Task::class,
            JobVacancy::class,
            JobApplicant::class,
            JobInterview::class,
            JobOffer::class,
            TrainingCourse::class,
            TrainingEnrollment::class,
            Asset::class,
            AssetAssignment::class,
            ExpenseClaim::class,
            Announcement::class,
            PublicHoliday::class,
            PayrollPeriod::class,
            PayrollPayment::class,
            PayrollItem::class,
            PayrollAdjustment::class,
            PayrollSetting::class,
            Role::class,
            Permission::class,
        ] as $model) {
            $model::observe(AuditLogObserver::class);
        }

        foreach ([
            Company::class,
            Branch::class,
            Department::class,
            Position::class,
            EmploymentType::class,
        ] as $model) {
            $model::observe(StructureAuthorizationObserver::class);
        }
    }
}
