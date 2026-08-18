<?php

use App\Models\Announcement;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeSchedule;
use App\Models\EmploymentType;
use App\Models\LeaveBalance;
use App\Models\LeaveBalanceAdjustment;
use App\Models\LeaveType;
use App\Models\Position;
use App\Models\Task;
use App\Models\User;
use App\Models\WorkShift;
use App\Services\LeaveBalanceService;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Http\UploadedFile;
use Spatie\SimpleExcel\SimpleExcelWriter;

beforeEach(function (): void {
    $this->seed([DatabaseSeeder::class, DemoDataSeeder::class]);
    $this->owner = User::query()->where('email', 'demo.owner@bizhr.local')->firstOrFail();
});

it('keeps generated reference codes optional and rejects duplicates consistently', function () {
    $company = Company::query()->firstOrFail();
    $existingBranch = Branch::query()->firstOrFail();

    $this->actingAs($this->owner)
        ->get(route('branches.index'))
        ->assertOk()
        ->assertSee('name="code"', false)
        ->assertSee('placeholder="auto-generate"', false)
        ->assertDontSee('name="code" required', false);

    $this->actingAs($this->owner)
        ->post(route('branches.store'), ['name' => 'Generated branch'])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $this->actingAs($this->owner)
        ->post(route('branches.store'), ['name' => 'Duplicate branch', 'code' => $existingBranch->code])
        ->assertRedirect()
        ->assertSessionHasErrors('code');

    expect(Branch::query()->where('company_id', $company->id)->where('name', 'Generated branch')->exists())->toBeTrue();
});

it('lets an authorized administrator manage leave types safely', function () {
    $payload = [
        'name' => 'Bereavement leave',
        'code' => 'BEREAVE',
        'days_per_year' => 3,
        'maximum_carry_forward_days' => 0,
        'is_paid' => '1',
        'requires_attachment' => '0',
        'carry_forward_allowed' => '0',
        'is_active' => '1',
    ];

    $this->actingAs($this->owner)->post(route('leave.types.store'), $payload)->assertRedirect();

    $type = LeaveType::query()->where('code', 'BEREAVE')->firstOrFail();
    expect($type->name)->toBe('Bereavement leave');

    $this->actingAs($this->owner)->put(route('leave.types.update', $type), [...$payload, 'days_per_year' => 5, 'is_active' => '0'])->assertRedirect();
    expect((float) $type->fresh()->days_per_year)->toBe(5.0)
        ->and($type->fresh()->is_active)->toBeFalse();

    $this->actingAs($this->owner)->delete(route('leave.types.destroy', $type))->assertRedirect();
    expect(LeaveType::query()->whereKey($type->id)->exists())->toBeFalse();
});

it('lets an authorized administrator manage work shifts safely', function () {
    $payload = [
        'name' => 'Evening shift',
        'code' => 'EVENING',
        'start_time' => '13:00',
        'end_time' => '21:00',
        'break_minutes' => 60,
        'late_grace_minutes' => 5,
        'early_leave_grace_minutes' => 5,
        'is_night_shift' => '0',
        'is_active' => '1',
    ];

    $this->actingAs($this->owner)->post(route('work-shifts.store'), $payload)->assertRedirect()->assertSessionHasNoErrors();

    $shift = WorkShift::query()->where('code', 'EVENING')->firstOrFail();
    expect($shift->name)->toBe('Evening shift');

    $this->actingAs($this->owner)->put(route('work-shifts.update', $shift), [...$payload, 'late_grace_minutes' => 10, 'is_active' => '0'])->assertRedirect();
    expect($shift->fresh()->late_grace_minutes)->toBe(10)
        ->and($shift->fresh()->is_active)->toBeFalse();

    $this->actingAs($this->owner)->delete(route('work-shifts.destroy', $shift))->assertRedirect();
    expect(WorkShift::query()->whereKey($shift->id)->exists())->toBeFalse();
});

it('completes organization master data update and safe delete workflows', function () {
    $company = Company::query()->firstOrFail();
    $branch = Branch::query()->create(['company_id' => $company->id, 'name' => 'Temporary branch', 'code' => 'TEMP-BR', 'is_active' => true]);
    $this->actingAs($this->owner)->put(route('branches.update', $branch), ['name' => 'Updated branch', 'code' => 'TEMP-BR', 'is_active' => '0'])->assertRedirect()->assertSessionHasNoErrors();
    expect($branch->fresh()->name)->toBe('Updated branch')->and($branch->fresh()->is_active)->toBeFalse();

    $department = Department::query()->create(['company_id' => $company->id, 'branch_id' => $branch->id, 'name' => 'Temporary department', 'code' => 'TEMP-DEPT', 'is_active' => true]);
    $this->actingAs($this->owner)->put(route('departments.update', $department), ['name' => 'Updated department', 'code' => 'TEMP-DEPT', 'branch_id' => $branch->id, 'is_active' => '0'])->assertRedirect()->assertSessionHasNoErrors();
    expect($department->fresh()->name)->toBe('Updated department');

    $position = Position::query()->create(['company_id' => $company->id, 'branch_id' => $branch->id, 'department_id' => $department->id, 'title' => 'Temporary position', 'code' => 'TEMP-POS', 'is_active' => true]);
    $this->actingAs($this->owner)->put(route('positions.update', $position), ['title' => 'Updated position', 'code' => 'TEMP-POS', 'branch_id' => $branch->id, 'department_id' => $department->id, 'is_active' => '0'])->assertRedirect()->assertSessionHasNoErrors();
    expect($position->fresh()->title)->toBe('Updated position');

    $type = EmploymentType::query()->create(['company_id' => $company->id, 'name' => 'Temporary type', 'code' => 'TEMP-TYPE', 'is_active' => true]);
    $this->actingAs($this->owner)->put(route('employment-types.update', $type), ['name' => 'Updated type', 'code' => 'TEMP-TYPE', 'is_active' => '0'])->assertRedirect()->assertSessionHasNoErrors();
    expect($type->fresh()->name)->toBe('Updated type');

    $this->actingAs($this->owner)->delete(route('positions.destroy', $position))->assertRedirect();
    $this->actingAs($this->owner)->delete(route('departments.destroy', $department))->assertRedirect();
    $this->actingAs($this->owner)->delete(route('branches.destroy', $branch))->assertRedirect();
    $this->actingAs($this->owner)->delete(route('employment-types.destroy', $type))->assertRedirect();
    expect(Position::query()->whereKey($position->id)->exists())->toBeFalse()
        ->and(Department::query()->whereKey($department->id)->exists())->toBeFalse()
        ->and(Branch::query()->whereKey($branch->id)->exists())->toBeFalse()
        ->and(EmploymentType::query()->whereKey($type->id)->exists())->toBeFalse();
});

it('prevents deleting organization records that employees still reference', function () {
    $employee = Employee::query()->whereNotNull('branch_id')->whereNotNull('department_id')->whereNotNull('position_id')->whereNotNull('employment_type_id')->firstOrFail();

    $this->actingAs($this->owner)->delete(route('branches.destroy', $employee->branch))->assertRedirect()->assertSessionHasErrors('branch');
    $this->actingAs($this->owner)->delete(route('departments.destroy', $employee->department))->assertRedirect()->assertSessionHasErrors('department');
    $this->actingAs($this->owner)->delete(route('positions.destroy', $employee->position))->assertRedirect()->assertSessionHasErrors('position');
    $this->actingAs($this->owner)->delete(route('employment-types.destroy', $employee->employmentType))->assertRedirect()->assertSessionHasErrors('employment_type');
});

it('previews and imports branches from the supported CSV template', function () {
    $csv = implode("\n", [
        'code,name,manager_name,email,phone,city,address,is_head_office,is_active',
        'BULK-01,Bulk Import Branch,Import Manager,bulk@example.com,012345678,Phnom Penh,Street 99,no,yes',
    ]);
    $file = UploadedFile::fake()->createWithContent('branches.csv', $csv);

    $response = $this->actingAs($this->owner)
        ->post(route('imports.preview', 'branches'), ['file' => $file])
        ->assertRedirect();

    preg_match('/\/imports\/branches\/preview\/([^?\"]+)/', $response->headers->get('Location'), $matches);
    expect($matches[1] ?? null)->not->toBeEmpty();

    $this->actingAs($this->owner)
        ->get(route('imports.preview.show', ['type' => 'branches', 'token' => $matches[1]]))
        ->assertOk()
        ->assertSee('Bulk Import Branch');

    $this->actingAs($this->owner)
        ->post(route('imports.confirm', 'branches'), ['token' => $matches[1]])
        ->assertRedirect(route('imports.index'))
        ->assertSessionHasNoErrors();

    expect(Branch::query()->where('code', 'BULK-01')->where('name', 'Bulk Import Branch')->exists())->toBeTrue();
});

it('paginates large import previews before confirmation', function () {
    $rows = collect(range(1, 51))->map(fn (int $number) => sprintf(
        'PAGE-%02d,Page Branch %02d,Manager,page%02d@example.com,012345678,Phnom Penh,Street %d,no,yes',
        $number,
        $number,
        $number,
        $number,
    ));
    $csv = implode("\n", array_merge([
        'code,name,manager_name,email,phone,city,address,is_head_office,is_active',
    ], $rows->all()));
    $file = UploadedFile::fake()->createWithContent('branches-page.csv', $csv);

    $response = $this->actingAs($this->owner)
        ->post(route('imports.preview', 'branches'), ['file' => $file])
        ->assertRedirect();
    preg_match('/\/imports\/branches\/preview\/([^?\"]+)/', $response->headers->get('Location'), $matches);
    expect($matches[1] ?? null)->not->toBeEmpty();

    $previewUrl = route('imports.preview.show', ['type' => 'branches', 'token' => $matches[1]]);
    $this->actingAs($this->owner)->get($previewUrl)
        ->assertOk()
        ->assertSee('Page Branch 01')
        ->assertDontSee('Page Branch 51');
    $this->actingAs($this->owner)->get($previewUrl.'?page=2')
        ->assertOk()
        ->assertSee('Page Branch 51')
        ->assertDontSee('Page Branch 01');
});

it('keeps the master-data form open after save and new', function () {
    $this->actingAs($this->owner)
        ->post(route('branches.store'), [
            'name' => 'Save New Branch',
            'code' => 'SAVE-NEW',
            'is_active' => '1',
            'save_action' => 'new',
        ])
        ->assertRedirect()
        ->assertSessionHas('open_modal', 'branchForm');

    expect(Branch::query()->where('code', 'SAVE-NEW')->exists())->toBeTrue();
});

it('filters organization reference tables and preserves focused results', function () {
    $company = Company::query()->firstOrFail();
    $branch = Branch::query()->create([
        'company_id' => $company->id,
        'name' => 'Filter Test Siem Reap',
        'code' => 'FILTER-SR',
        'city' => 'Siem Reap',
        'is_active' => false,
    ]);

    $this->actingAs($this->owner)
        ->get(route('branches.index', ['search' => 'FILTER-SR', 'status' => '0']))
        ->assertOk()
        ->assertSee($branch->name)
        ->assertSee('value="FILTER-SR"', false)
        ->assertSee('value="0" selected', false);

    $activeBranch = Branch::query()->where('is_active', true)->firstOrFail();
    $this->actingAs($this->owner)
        ->get(route('departments.index', ['branch_id' => $activeBranch->id]))
        ->assertOk()
        ->assertSee('value="'.$activeBranch->id.'" selected', false);
});

it('supports safe table page sizes and rejects oversized requests', function () {
    $company = Company::query()->firstOrFail();
    foreach (range(1, 25) as $number) {
        Branch::query()->create([
            'company_id' => $company->id,
            'name' => sprintf('Pagination Branch %02d', $number),
            'code' => sprintf('PAGE-%02d', $number),
            'is_active' => true,
        ]);
    }

    $this->actingAs($this->owner)
        ->get(route('branches.index', ['search' => 'Pagination Branch', 'per_page' => 10]))
        ->assertOk()
        ->assertViewHas('branches', fn ($branches) => $branches->perPage() === 10 && $branches->count() === 10)
        ->assertSee('page=2', false);

    $this->actingAs($this->owner)
        ->get(route('branches.index', ['search' => 'Pagination Branch', 'per_page' => 10000]))
        ->assertOk()
        ->assertViewHas('branches', fn ($branches) => $branches->perPage() === 20 && $branches->count() === 20);
});

it('supports save and new for repetitive employee task and announcement entry', function () {
    $branch = Branch::query()->firstOrFail();
    $department = $branch->departments()->firstOrFail();

    $this->actingAs($this->owner)->post(route('employees.store'), [
        'employee_code' => 'SAVE-NEW-EMP',
        'branch_id' => $branch->id,
        'department_id' => $department->id,
        'first_name' => 'Save',
        'last_name' => 'New',
        'hire_date' => today()->toDateString(),
        'employment_status' => 'Draft',
        'salary_currency' => 'USD',
        'is_active' => '1',
        'save_action' => 'new',
    ])->assertRedirect(route('employees.create'));
    expect(Employee::query()->where('employee_code', 'SAVE-NEW-EMP')->exists())->toBeTrue();

    $employee = Employee::query()->where('is_active', true)->firstOrFail();
    $this->actingAs($this->owner)->post(route('tasks.store'), [
        'assigned_to' => $employee->id,
        'title' => 'Save and new task',
        'priority' => 'medium',
        'start_date' => today()->toDateString(),
        'due_date' => today()->addDay()->toDateString(),
        'save_action' => 'new',
    ])->assertRedirect()->assertSessionHas('open_modal', 'createTask');
    expect(Task::query()->where('title', 'Save and new task')->exists())->toBeTrue();

    $this->actingAs($this->owner)->post(route('announcements.store'), [
        'title' => 'Save and new announcement',
        'content' => 'Created while testing repetitive entry actions.',
        'audience_type' => 'all',
        'save_action' => 'new',
    ])->assertRedirect()->assertSessionHas('open_modal', 'createAnnouncement');
    expect(Announcement::query()->where('title', 'Save and new announcement')->exists())->toBeTrue();
});

it('provides and imports the Excel template used by HR teams', function () {
    $this->actingAs($this->owner)
        ->get(route('imports.template', 'branches'))
        ->assertOk()
        ->assertHeader('content-disposition', 'attachment; filename=bizhr-branches-template.xlsx');

    $path = tempnam(sys_get_temp_dir(), 'bizhr-excel-test-').'.xlsx';
    $writer = SimpleExcelWriter::create($path);
    $writer->nameCurrentSheet('Data')->addRow([
        'code' => 'XLSX-01',
        'name' => 'Excel Import Branch',
        'manager_name' => 'Excel Manager',
        'email' => 'excel@example.com',
        'phone' => '012345678',
        'city' => 'Phnom Penh',
        'address' => 'Street 100',
        'is_head_office' => 'no',
        'is_active' => 'yes',
    ]);
    $writer->close();

    $file = new UploadedFile($path, 'branches.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    $response = $this->actingAs($this->owner)
        ->post(route('imports.preview', 'branches'), ['file' => $file])
        ->assertRedirect();

    preg_match('/\/imports\/branches\/preview\/([^?\"]+)/', $response->headers->get('Location'), $matches);
    expect($matches[1] ?? null)->not->toBeEmpty();
    $this->actingAs($this->owner)
        ->get(route('imports.preview.show', ['type' => 'branches', 'token' => $matches[1]]))
        ->assertOk()
        ->assertSee('Excel Import Branch');
    $this->actingAs($this->owner)
        ->post(route('imports.confirm', 'branches'), ['token' => $matches[1]])
        ->assertRedirect(route('imports.index'));

    expect(Branch::query()->where('code', 'XLSX-01')->where('name', 'Excel Import Branch')->exists())->toBeTrue();
    @unlink($path);
});

it('lets an authorized administrator schedule an employee once per day', function () {
    $employee = Employee::query()->where('is_active', true)->firstOrFail();
    $shift = WorkShift::query()->where('company_id', $employee->company_id)->where('is_active', true)->firstOrFail();
    $payload = [
        'employee_id' => $employee->id,
        'work_date' => today()->toDateString(),
        'work_shift_id' => $shift->id,
        'is_rest_day' => '0',
        'notes' => 'Published by HR',
    ];

    $this->actingAs($this->owner)->post(route('schedules.store'), $payload)->assertRedirect()->assertSessionHasNoErrors();

    $schedule = EmployeeSchedule::query()->where('employee_id', $employee->id)->whereDate('work_date', today())->firstOrFail();
    expect($schedule->work_shift_id)->toBe($shift->id)
        ->and($schedule->branch_id)->toBe($employee->branch_id);

    $this->actingAs($this->owner)->put(route('schedules.update', $schedule), [...$payload, 'is_rest_day' => '1', 'work_shift_id' => null, 'notes' => 'Company holiday'])->assertRedirect()->assertSessionHasNoErrors();
    expect($schedule->fresh()->is_rest_day)->toBeTrue()
        ->and($schedule->fresh()->work_shift_id)->toBeNull();

    $this->actingAs($this->owner)->delete(route('schedules.destroy', $schedule))->assertRedirect();
    expect(EmployeeSchedule::query()->whereKey($schedule->id)->exists())->toBeFalse();
});

it('records a reasoned leave-balance adjustment for HR audit', function () {
    app(LeaveBalanceService::class)->initializeYearForCompany(Company::query()->value('id'), now()->year);
    $balance = LeaveBalance::query()->firstOrFail();

    $this->actingAs($this->owner)->post(route('leave.balances.adjust', $balance), ['adjustment_days' => 1.5, 'reason' => 'Approved manual carry-forward correction.'])->assertRedirect()->assertSessionHasNoErrors();

    expect((float) $balance->fresh()->adjustment_days)->toBe(1.5)
        ->and(LeaveBalanceAdjustment::query()->where('leave_balance_id', $balance->id)->value('reason'))->toBe('Approved manual carry-forward correction.');
});

it('renders one leave type toolbar and a right aligned page-size selector', function () {
    $response = $this->actingAs($this->owner)
        ->get(route('leave.types.index', ['per_page' => 10]))
        ->assertOk()
        ->assertSee('Showing 1-', false)
        ->assertSee('name="per_page"', false)
        ->assertSee('value="20"', false)
        ->assertSee('pagination-controls', false);

    expect(substr_count($response->getContent(), 'class="reference-filter-form"'))->toBe(1)
        ->and(substr_count($response->getContent(), 'reference-list-toolbar'))->toBe(0);
});

it('ships the self hosted live search runtime on authenticated list pages', function () {
    $this->actingAs($this->owner)
        ->get(route('employees.index'))
        ->assertOk()
        ->assertSee('vendor/htmx/htmx.min.js', false)
        ->assertSee('hx-history="false"', false)
        ->assertSee('htmx.config.historyCacheSize = 0', false)
        ->assertSee('class="reference-filter-form"', false)
        ->assertSee('data-list-container', false);

    expect(public_path('vendor/htmx/htmx.min.js'))->toBeFile()
        ->and(filesize(public_path('vendor/htmx/htmx.min.js')))->toBeGreaterThan(40_000)
        ->and(file_get_contents(public_path('js/app.js')))->toContain('live-search-submit-fallback')
        ->and(file_get_contents(public_path('css/app.css')))->toContain('[data-live-search-ready="true"] .live-search-submit-fallback');
});
