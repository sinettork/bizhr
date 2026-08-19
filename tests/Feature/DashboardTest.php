<?php

use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;

it('redirects guests and renders the Bootstrap dashboard for a company-linked user', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));

    $company = Company::factory()->create();
    $branch = Branch::factory()->create(['company_id' => $company->id]);
    $department = Department::factory()->create(['company_id' => $company->id, 'branch_id' => $branch->id]);
    $user = User::factory()->create();
    Employee::factory()->create([
        'company_id' => $company->id,
        'branch_id' => $branch->id,
        'department_id' => $department->id,
        'user_id' => $user->id,
    ]);

    $this->actingAs($user)->get(route('dashboard'))->assertOk()->assertSee('Dashboard')->assertSee('app-navbar', false);
});
