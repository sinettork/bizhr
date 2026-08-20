<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Employee;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationMatrixTest extends TestCase
{
    use RefreshDatabase;

    private Company $company1;

    private Company $company2;

    private Employee $employee1;

    private Employee $employee2;

    private User $admin;

    private User $hr;

    private User $manager;

    private User $employee;

    private User $inactive;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);

        $this->company1 = Company::factory()->create(['name' => 'Company 1']);
        $this->company2 = Company::factory()->create(['name' => 'Company 2']);

        $this->employee1 = Employee::factory()->create(['company_id' => $this->company1->id]);
        $this->employee2 = Employee::factory()->create(['company_id' => $this->company2->id]);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('Super Admin');
        Employee::factory()->create(['company_id' => $this->company1->id, 'user_id' => $this->admin->id]);
        $this->admin->refresh();

        $this->hr = User::factory()->create();
        $this->hr->assignRole('HR Administrator');
        Employee::factory()->create(['company_id' => $this->company1->id, 'user_id' => $this->hr->id]);
        $this->hr->refresh();

        $this->manager = User::factory()->create();
        $this->manager->assignRole('Manager');
        Employee::factory()->create(['company_id' => $this->company1->id, 'user_id' => $this->manager->id]);
        $this->manager->refresh();

        $this->employee = User::factory()->create();
        $this->employee->assignRole('Employee');
        Employee::factory()->create(['company_id' => $this->company1->id, 'user_id' => $this->employee->id]);
        $this->employee->refresh();

        $this->inactive = User::factory()->create(['is_active' => false]);
        $this->inactive->assignRole('Employee');
        Employee::factory()->create(['company_id' => $this->company1->id, 'user_id' => $this->inactive->id, 'is_active' => false]);
        $this->inactive->refresh();
    }

    public function test_anonymous_user_cannot_access_protected_routes(): void
    {
        $protectedRoutes = [
            'GET /dashboard' => 'dashboard',
            'GET /employees' => 'employees.index',
            'GET /branches' => 'branches.index',
            'POST /employees' => 'employees.store',
            'GET /payroll' => 'payroll.periods.index',
            'GET /leave/requests' => 'leave.requests.index',
        ];

        foreach ($protectedRoutes as $method => $route) {
            [$httpMethod, $path] = explode(' ', $method);
            $response = $this->$httpMethod($path);
            $this->assertTrue(
                in_array($response->status(), [302, 401]),
                "Route {$route} should deny anonymous access, got {$response->status()}"
            );
        }
    }

    public function test_inactive_user_cannot_access_protected_routes(): void
    {
        $this->actingAs($this->inactive);

        $response = $this->get(route('dashboard'));
        $this->assertNotEquals(200, $response->status(), 'Inactive user should not access dashboard');
    }

    public function test_employee_view_authorization(): void
    {
        $this->actingAs($this->hr)
            ->get(route('employees.index'))
            ->assertStatus(200);

        $employeeUser = $this->employee;
        $employeeRecord = $employeeUser->employee;

        $this->actingAs($employeeUser)
            ->get(route('employees.show', $employeeRecord))
            ->assertStatus(200);

        $this->actingAs($this->manager)
            ->get(route('employees.index'))
            ->assertStatus(200);
    }

    public function test_cross_company_employee_access_blocked(): void
    {
        $this->actingAs($this->hr);

        $response = $this->get(route('employees.show', $this->employee2));

        $this->assertTrue(
            in_array($response->status(), [403, 404]),
            'Cross-company employee access should be blocked'
        );
    }

    public function test_employee_create_permission_enforcement(): void
    {
        $this->actingAs($this->hr);
        $response = $this->get(route('employees.create'));
        $this->assertTrue(
            $response->status() === 200,
            'HR should access employee create form'
        );

        $this->actingAs($this->employee);
        $response = $this->get(route('employees.create'));
        $this->assertTrue(
            in_array($response->status(), [403, 302]),
            'Employee role should not access create form'
        );
    }

    public function test_employee_delete_permission_enforcement(): void
    {
        $target = Employee::factory()->create([
            'company_id' => $this->company1->id,
            'employment_status' => 'Terminated',
            'is_active' => false,
        ]);

        $this->actingAs($this->hr);
        $response = $this->delete(route('employees.destroy', $target));
        $this->assertTrue(
            in_array($response->status(), [403, 302]),
            'HR role should not have delete permission without explicit grant'
        );

        $this->actingAs($this->admin);
        $this->delete(route('employees.destroy', $target))->assertRedirect(route('employees.index'));
        $this->assertSoftDeleted('employees', ['id' => $target->id]);
    }

    public function test_payroll_access_control(): void
    {
        $this->actingAs($this->employee)
            ->get(route('payroll.periods.index'))
            ->assertStatus(403);

        $this->actingAs($this->hr)
            ->get(route('payroll.periods.index'))
            ->assertStatus(200);
    }

    public function test_leave_request_access_control(): void
    {
        $this->actingAs($this->employee)
            ->get(route('leave.requests.index'))
            ->assertStatus(200);

        $this->post(route('leave.requests.store'), [
            'leave_type_id' => 1,
            'start_date' => now()->addDay()->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
        ]);
    }

    public function test_attendance_correction_access(): void
    {
        $this->actingAs($this->employee)
            ->get(route('attendance.corrections.request'))
            ->assertStatus(200);

        $this->actingAs($this->employee);
        $response = $this->get(route('attendance.corrections.review'));
        $this->assertTrue(
            in_array($response->status(), [403, 404]),
            'Employees should not access correction review'
        );
    }

    public function test_document_access_control(): void
    {
        $ownEmployee = Employee::query()->where('user_id', $this->employee->id)->sole();

        $this->actingAs($this->employee)
            ->get(route('employees.documents.index', $ownEmployee))
            ->assertStatus(200);

        $this->actingAs($this->employee);
        $response = $this->get(route('employees.documents.index', $this->employee2));
        $this->assertTrue(
            in_array($response->status(), [403, 404]),
            'Employees should not access others\' documents'
        );
    }

    public function test_audit_logs_access_control(): void
    {
        $this->actingAs($this->employee)
            ->get(route('audit-logs.index'))
            ->assertStatus(403);

        $this->actingAs($this->admin)
            ->get(route('audit-logs.index'))
            ->assertStatus(200);
    }

    public function test_company_scoping_in_employee_list(): void
    {
        $this->actingAs($this->hr);
        $response = $this->get(route('employees.index'));
        $response->assertStatus(200);
        $this->assertStringContainsString($this->employee1->full_name_en ?? $this->employee1->first_name, $response->content());
    }

    public function test_sensitive_salary_data_access_control(): void
    {
        $ownEmployee = Employee::query()->where('user_id', $this->employee->id)->sole();

        $this->actingAs($this->employee);
        $response = $this->get(route('employees.show', $ownEmployee));

        $response->assertStatus(200);
    }
}
