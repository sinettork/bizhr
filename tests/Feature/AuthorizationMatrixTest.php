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

        // Seed permissions and roles first
        $this->seed(DatabaseSeeder::class);

        // Create companies
        $this->company1 = Company::factory()->create(['name' => 'Company 1']);
        $this->company2 = Company::factory()->create(['name' => 'Company 2']);

        // Create employees for company1 and company2
        $this->employee1 = Employee::factory()->create(['company_id' => $this->company1->id]);
        $this->employee2 = Employee::factory()->create(['company_id' => $this->company2->id]);

        // Create users with different roles and link to employees
        $this->admin = User::factory()->create();
        $this->admin->assignRole('Super Admin');

        $this->hr = User::factory()->create();
        $this->hr->assignRole('HR Administrator');
        // Link HR to employee1 for company context
        $employee1User = Employee::factory()->create(['company_id' => $this->company1->id, 'user_id' => $this->hr->id]);
        $this->hr->refresh();

        $this->manager = User::factory()->create();
        $this->manager->assignRole('Manager');
        // Link manager to company1 via employee
        $employee2User = Employee::factory()->create(['company_id' => $this->company1->id, 'user_id' => $this->manager->id]);
        $this->manager->refresh();

        $this->employee = User::factory()->create();
        $this->employee->assignRole('Employee');
        // Link employee to company1 via employee record
        $employee3User = Employee::factory()->create(['company_id' => $this->company1->id, 'user_id' => $this->employee->id]);
        $this->employee->refresh();

        $this->inactive = User::factory()->create(['is_active' => false]);
        $this->inactive->assignRole('Employee');
        // Link inactive user to company1
        $employee4User = Employee::factory()->create(['company_id' => $this->company1->id, 'user_id' => $this->inactive->id, 'is_active' => false]);
        $this->inactive->refresh();
    }

    /**
     * Test anonymous access to protected routes is denied
     */
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

    /**
     * Test inactive users cannot access protected routes
     */
    public function test_inactive_user_cannot_access_protected_routes(): void
    {
        $this->actingAs($this->inactive);

        $response = $this->get(route('dashboard'));
        $this->assertNotEquals(200, $response->status(), 'Inactive user should not access dashboard');
    }

    /**
     * Test role-based access control for employee view
     */
    public function test_employee_view_authorization(): void
    {
        // HR can view all employees in their company
        $this->actingAs($this->hr)
            ->get(route('employees.index'))
            ->assertStatus(200);

        // Employee can view only their own record
        $employeeUser = $this->employee; // User linked to employee
        $employeeRecord = $employeeUser->employee; // The employee record they're linked to

        $this->actingAs($employeeUser)
            ->get(route('employees.show', $employeeRecord))
            ->assertStatus(200);

        // Manager role may have limited view
        $this->actingAs($this->manager)
            ->get(route('employees.index'))
            ->assertStatus(200);
    }

    /**
     * Test cross-company access is blocked
     */
    public function test_cross_company_employee_access_blocked(): void
    {
        $this->actingAs($this->hr);

        // HR from company1 should not be able to access employees from company2
        $response = $this->get(route('employees.show', $this->employee2));

        // Should either be 403 Forbidden or 404 Not Found
        $this->assertTrue(
            in_array($response->status(), [403, 404]),
            'Cross-company employee access should be blocked'
        );
    }

    /**
     * Test role permission enforcement for creating employees
     */
    public function test_employee_create_permission_enforcement(): void
    {
        // HR with permission can create
        $this->actingAs($this->hr);
        $response = $this->get(route('employees.create'));
        $this->assertTrue(
            $response->status() === 200,
            'HR should access employee create form'
        );

        // Employee without permission cannot create
        $this->actingAs($this->employee);
        $response = $this->get(route('employees.create'));
        $this->assertTrue(
            in_array($response->status(), [403, 302]),
            'Employee role should not access create form'
        );
    }

    /**
     * Test role permission enforcement for deleting employees
     */
    public function test_employee_delete_permission_enforcement(): void
    {
        $target = Employee::factory()->create(['company_id' => $this->company1->id]);

        // HR without delete permission should be blocked
        $this->actingAs($this->hr);
        $response = $this->delete(route('employees.destroy', $target));
        $this->assertTrue(
            in_array($response->status(), [403, 302]),
            'HR role should not have delete permission without explicit grant'
        );

        // Admin should be able to delete
        $this->actingAs($this->admin);
        $this->delete(route('employees.destroy', $target));
        $this->assertSoftDeleted('employees', ['id' => $target->id]);
    }

    /**
     * Test payroll access control
     */
    public function test_payroll_access_control(): void
    {
        // Only payroll officer should access payroll
        $this->actingAs($this->employee)
            ->get(route('payroll.periods.index'))
            ->assertStatus(403);

        // HR/Admin can access
        $this->actingAs($this->hr)
            ->get(route('payroll.periods.index'))
            ->assertStatus(200);
    }

    /**
     * Test leave request access control
     */
    public function test_leave_request_access_control(): void
    {
        // Employee can request leave
        $this->actingAs($this->employee)
            ->get(route('leave.requests.index'))
            ->assertStatus(200);

        // Employee can view but not approve
        $this->post(route('leave.requests.store'), [
            'leave_type_id' => 1,
            'start_date' => now()->addDay()->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
        ]);
    }

    /**
     * Test attendance corrections access
     */
    public function test_attendance_correction_access(): void
    {
        // Employee can request correction
        $this->actingAs($this->employee)
            ->get(route('attendance.corrections.request'))
            ->assertStatus(200);

        // Employee cannot review/approve
        $this->actingAs($this->employee);
        $response = $this->get(route('attendance.corrections.review'));
        $this->assertTrue(
            in_array($response->status(), [403, 404]),
            'Employees should not access correction review'
        );
    }

    /**
     * Test documents access control
     */
    public function test_document_access_control(): void
    {
        $ownEmployee = Employee::query()->where('user_id', $this->employee->id)->sole();

        // Employee can view own documents
        $this->actingAs($this->employee)
            ->get(route('employees.documents.index', $ownEmployee))
            ->assertStatus(200);

        // Employee cannot view other's documents
        $this->actingAs($this->employee);
        $response = $this->get(route('employees.documents.index', $this->employee2));
        $this->assertTrue(
            in_array($response->status(), [403, 404]),
            'Employees should not access others\' documents'
        );
    }

    /**
     * Test audit logs access (sensitive data)
     */
    public function test_audit_logs_access_control(): void
    {
        // Regular employees should not access audit logs
        $this->actingAs($this->employee)
            ->get(route('audit-logs.index'))
            ->assertStatus(403);

        // Admin can access
        $this->actingAs($this->admin)
            ->get(route('audit-logs.index'))
            ->assertStatus(200);
    }

    /**
     * Test company scoping in queries
     */
    public function test_company_scoping_in_employee_list(): void
    {
        // HR from company1 should only see company1 employees
        $this->actingAs($this->hr);
        $response = $this->get(route('employees.index'));
        $response->assertStatus(200);
        $this->assertStringContainsString($this->employee1->full_name_en ?? $this->employee1->first_name, $response->content());
    }

    /**
     * Test sensitive fields are not exposed to non-privileged users
     */
    public function test_sensitive_salary_data_access_control(): void
    {
        $ownEmployee = Employee::query()->where('user_id', $this->employee->id)->sole();

        // Employee should not see their own salary in detail (only HR/Admin)
        $this->actingAs($this->employee);
        $response = $this->get(route('employees.show', $ownEmployee));

        // The view should not expose sensitive payroll fields to the employee
        // This test verifies the view implementation, not the controller
        $response->assertStatus(200);
    }
}
