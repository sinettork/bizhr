<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Clear permission cache before seeding
        |--------------------------------------------------------------------------
        */

        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();

        /*
        |--------------------------------------------------------------------------
        | Permissions
        |--------------------------------------------------------------------------
        */

        $permissions = [
            // Company Management
            'company.view',
            'company.edit',

            // Branch Management
            'branch.view',
            'branch.create',
            'branch.edit',
            'branch.delete',

            // Department Management
            'department.view',
            'department.create',
            'department.edit',
            'department.delete',

            // Position Management
            'position.view',
            'position.create',
            'position.edit',
            'position.delete',

            // Employment Type Management
            'employment-type.view',
            'employment-type.create',
            'employment-type.edit',
            'employment-type.delete',

            // Employee Management
            'employee.view',
            'employee.view-own',
            'employee.create',
            'employee.edit',
            'employee.edit-own',
            'employee.delete',
            'employee.view-sensitive',

            // Attendance
            'attendance.view',
            'attendance.checkin',
            'attendance.checkout',
            'attendance.correction.request',
            'attendance.edit',
            'attendance.approve',
            'attendance.report',

            // Schedules
            'schedule.view',
            'schedule.create',
            'schedule.edit',
            'schedule.delete',

            // Shifts
            'shift.view',
            'shift.create',
            'shift.edit',
            'shift.delete',

            // Leave Management
            'leave.view',
            'leave.request',
            'leave.approve',
            'leave.manage',
            'leave.report',

            // Payroll
            'payroll.view',
            'payroll.edit',
            'payroll.approve',
            'payroll.process',
            'payroll.report',

            // Performance
            'performance.view',
            'performance.create',
            'performance.edit',
            'performance.manage-goals',

            // Tasks
            'task.view',
            'task.create',
            'task.edit',
            'task.delete',

            // Reports
            'report.view',
            'report.export',

            // Users and Roles
            'user.manage',
            'role.manage',

            // Audit Logs
            'audit.view',
        ];

        /*
        |--------------------------------------------------------------------------
        | Create permissions
        |--------------------------------------------------------------------------
        */

        foreach ($permissions as $permissionName) {
            Permission::findOrCreate(
                $permissionName,
                'web'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Employee Role
        |--------------------------------------------------------------------------
        |
        | An Employee can only access their own information and actions.
        |
        */

        $employeeRole = Role::findOrCreate(
            'Employee',
            'web'
        );

        $employeeRole->syncPermissions([
            'employee.view-own',
            'employee.edit-own',

            'attendance.view',
            'attendance.checkin',
            'attendance.checkout',
            'attendance.correction.request',

            'schedule.view',

            'leave.view',
            'leave.request',

            'payroll.view',

            'performance.view',

            'task.view',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Manager Role
        |--------------------------------------------------------------------------
        */

        $managerRole = Role::findOrCreate(
            'Manager',
            'web'
        );

        $managerRole->syncPermissions([
            'employee.view',

            'attendance.view',
            'attendance.checkin',
            'attendance.checkout',
            'attendance.correction.request',

            'schedule.view',

            'leave.view',
            'leave.request',
            'leave.approve',

            'performance.view',
            'performance.create',
            'performance.edit',
            'performance.manage-goals',

            'task.view',
            'task.create',
            'task.edit',
        ]);

        /*
        |--------------------------------------------------------------------------
        | HR Manager Role
        |--------------------------------------------------------------------------
        */

        $hrManagerRole = Role::findOrCreate(
            'HR Manager',
            'web'
        );

        $hrManagerRole->syncPermissions([
            'company.view',

            'branch.view',

            'department.view',

            'position.view',

            'employment-type.view',

            'employee.view',
            'employee.view-own',
            'employee.create',
            'employee.edit',
            'employee.edit-own',
            'employee.view-sensitive',

            'attendance.view',
            'attendance.checkin',
            'attendance.checkout',
            'attendance.correction.request',
            'attendance.edit',
            'attendance.approve',
            'attendance.report',

            'schedule.view',
            'schedule.create',
            'schedule.edit',
            'schedule.delete',

            'shift.view',
            'shift.create',
            'shift.edit',
            'shift.delete',

            'leave.view',
            'leave.request',
            'leave.approve',
            'leave.manage',
            'leave.report',

            'payroll.view',
            'payroll.report',

            'performance.view',
            'performance.create',
            'performance.edit',
            'performance.manage-goals',

            'task.view',
            'task.create',
            'task.edit',

            'report.view',
            'report.export',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Owner Role
        |--------------------------------------------------------------------------
        |
        | Owner has full business access, but cannot manage system roles.
        |
        */

        $ownerRole = Role::findOrCreate(
            'Owner',
            'web'
        );

        $ownerPermissions = Permission::query()
            ->where('guard_name', 'web')
            ->whereNotIn('name', [
                'role.manage',
                'audit.view',
            ])
            ->pluck('name')
            ->all();

        $ownerRole->syncPermissions(
            $ownerPermissions
        );

        /*
        |--------------------------------------------------------------------------
        | Super Admin Role
        |--------------------------------------------------------------------------
        |
        | Super Admin receives every permission currently in the database.
        |
        */

        $superAdminRole = Role::findOrCreate(
            'Super Admin',
            'web'
        );

        $superAdminRole->syncPermissions(
            Permission::query()
                ->where('guard_name', 'web')
                ->get()
        );

        /*
        |--------------------------------------------------------------------------
        | Clear cache after assigning permissions
        |--------------------------------------------------------------------------
        */

        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();
    }
}