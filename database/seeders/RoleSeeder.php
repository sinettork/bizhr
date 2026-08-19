<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    private const ALIASES = [
        'Owner' => ['owner'],
        'HR Administrator' => ['HR Manager', 'hr-administrator'],
        'Manager' => ['manager'],
        'Accountant' => ['accountant'],
        'Employee' => ['employee'],
        'Super Admin' => [],
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (array_keys(self::ALIASES) as $name) {
            Role::findOrCreate($name, 'web');
        }

        $this->transferAliases();

        $all = Permission::query()
            ->where('guard_name', 'web')
            ->pluck('name')
            ->map(fn ($name): string => (string) $name)
            ->all();

        $this->sync('Super Admin', $all);
        $this->sync('Owner', $all);

        $this->sync('HR Administrator', [
            'company.view',
            'branch.view', 'branch.create', 'branch.edit',
            'department.view', 'department.create', 'department.edit',
            'position.view', 'position.create', 'position.edit',
            'employment-type.view', 'employment-type.create', 'employment-type.edit',

            'employee.view', 'employee.view-own', 'employee.create', 'employee.edit',
            'employee.edit-own', 'employee.view-sensitive', 'employee.approve',

            'contract.view-own', 'contract.view', 'contract.create', 'contract.edit',
            'contract.approve', 'contract.terminate',

            'attendance.view', 'attendance.checkin', 'attendance.checkout',
            'attendance.correction.request', 'attendance.edit', 'attendance.approve', 'attendance.report',
            'schedule.view', 'schedule.create', 'schedule.edit', 'schedule.delete',
            'shift.view', 'shift.create', 'shift.edit', 'shift.delete',

            'leave.view', 'leave.request', 'leave.approve', 'leave.manage', 'leave.report',

            'payroll.view-own', 'payroll.view', 'payroll.approve', 'payroll.report',

            'performance.view-own', 'performance.view', 'performance.create', 'performance.edit',
            'performance.review', 'performance.approve', 'performance.reopen', 'performance.manage-goals',

            'task.view-own', 'task.view', 'task.create', 'task.edit', 'task.assign', 'task.verify', 'task.cancel',

            'recruitment.view', 'recruitment.manage', 'recruitment.approve',
            'training.view-own', 'training.view', 'training.manage',
            'asset.view-own', 'asset.view', 'asset.manage',

            'expense.view-own', 'expense.view',
            'announcement.view', 'announcement.manage',

            'report.view', 'report.export', 'audit.view',
        ]);

        $this->sync('Manager', [
            'employee.view', 'employee.view-own', 'employee.edit-own',
            'contract.view-own',

            'attendance.view', 'attendance.checkin', 'attendance.checkout',
            'attendance.correction.request', 'attendance.approve', 'attendance.report',
            'schedule.view', 'schedule.create', 'schedule.edit',

            'leave.view', 'leave.request', 'leave.approve', 'leave.report',
            'payroll.view-own',

            'performance.view-own', 'performance.view', 'performance.create', 'performance.edit',
            'performance.review', 'performance.manage-goals',

            'task.view-own', 'task.view', 'task.create', 'task.edit', 'task.assign', 'task.verify', 'task.cancel',
            'training.view-own', 'training.view',
            'asset.view-own',
            'expense.view-own', 'expense.view', 'expense.approve-manager',
            'announcement.view',
            'report.view',
        ]);

        $this->sync('Accountant', [
            'employee.view',
            'contract.view-own',
            'attendance.view', 'attendance.report',
            'leave.view', 'leave.report',

            'payroll.view-own', 'payroll.view', 'payroll.edit', 'payroll.process', 'payroll.report',

            'performance.view-own',
            'task.view-own',
            'training.view-own',
            'asset.view-own',

            'expense.view-own', 'expense.view', 'expense.approve-accounting', 'expense.pay',
            'announcement.view',
            'report.view', 'report.export',
        ]);

        $this->sync('Employee', [
            'employee.view-own', 'employee.edit-own',
            'contract.view-own',

            'attendance.view', 'attendance.checkin', 'attendance.checkout', 'attendance.correction.request',
            'schedule.view',
            'leave.view', 'leave.request',
            'payroll.view-own',

            'performance.view-own',
            'task.view-own',
            'training.view-own',
            'asset.view-own',
            'expense.view-own',
            'announcement.view',
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function transferAliases(): void
    {
        foreach (self::ALIASES as $canonicalName => $aliases) {
            $canonical = Role::findByName($canonicalName, 'web');

            Role::query()
                ->where('guard_name', 'web')
                ->whereIn('name', $aliases)
                ->get()
                ->each(function (Role $alias) use ($canonical): void {
                    DB::table('model_has_roles')
                        ->where('role_id', $alias->id)
                        ->get()
                        ->each(fn ($assignment) => DB::table('model_has_roles')->insertOrIgnore([
                            'role_id' => $canonical->id,
                            'model_type' => $assignment->model_type,
                            'model_id' => $assignment->model_id,
                        ]));

                    $alias->delete();
                });
        }
    }

    /** @param list<string> $permissions */
    private function sync(string $roleName, array $permissions): void
    {
        Role::findByName($roleName, 'web')->syncPermissions(
            Permission::query()
                ->where('guard_name', 'web')
                ->whereIn('name', $permissions)
                ->get()
        );
    }
}
