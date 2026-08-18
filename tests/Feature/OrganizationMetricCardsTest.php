<?php

use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\QueryException;
use Spatie\Permission\Models\Permission;

test('organization pages render the shared application shell', function (
    string $routeName,
    string $permissionName,
) {
    Company::query()->create([
        'name' => 'BizHR Organization',
        'currency' => 'USD',
        'timezone' => 'Asia/Phnom_Penh',
        'date_format' => 'd/m/Y',
    ]);

    $user = User::factory()->create();
    Permission::findOrCreate($permissionName, 'web');
    $user->givePermissionTo($permissionName);

    $this->actingAs($user)
        ->get(route($routeName))
        ->assertOk()
        ->assertSee('app-navbar', false);
})->with([
    'branches' => ['branches.index', 'branch.view'],
    'departments' => ['departments.index', 'department.view'],
    'positions' => ['positions.index', 'position.view'],
    'employment types' => [
        'employment-types.index',
        'employment-type.view',
    ],
]);

test('department names are unique within a branch', function () {
    $company = Company::query()->create([
        'name' => 'BizHR Organization',
        'currency' => 'USD',
        'timezone' => 'Asia/Phnom_Penh',
        'date_format' => 'd/m/Y',
    ]);

    $firstBranch = Branch::query()->create([
        'company_id' => $company->id,
        'name' => 'Phnom Penh',
        'code' => 'PP',
    ]);
    $secondBranch = Branch::query()->create([
        'company_id' => $company->id,
        'name' => 'Battambang',
        'code' => 'BB',
    ]);

    Department::query()->create([
        'company_id' => $company->id,
        'branch_id' => $firstBranch->id,
        'name' => 'Human Resources',
        'code' => 'PP-HR',
    ]);
    Department::query()->create([
        'company_id' => $company->id,
        'branch_id' => $secondBranch->id,
        'name' => 'Human Resources',
        'code' => 'BB-HR',
    ]);

    expect(Department::query()->count())->toBe(2)
        ->and(fn () => Department::query()->create([
            'company_id' => $company->id,
            'branch_id' => $firstBranch->id,
            'name' => 'Human Resources',
            'code' => 'PP-HR-2',
        ]))->toThrow(QueryException::class);
});
