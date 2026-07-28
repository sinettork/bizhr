<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()['cache']->forget('spatie.permission.cache');

        // Create Owner role (super admin)
        $owner = Role::firstOrCreate(
            ['name' => 'owner'],
            ['guard_name' => 'web']
        );

        // Owner gets all permissions
        $allPermissions = Permission::all();
        $owner->syncPermissions($allPermissions);

        // Create HR Administrator role
        $hrAdmin = Role::firstOrCreate(
            ['name' => 'hr-administrator'],
            ['guard_name' => 'web']
        );

        $hrAdminPermissions = [
            // Employee Management
            'employee.view',
            'employee.create',
            'employee.edit',
            'employee.delete',
            'employee.view-sensitive',

            // Attendance
            'attendance.view',
            'attendance.edit',
            'attendance.approve',
            'attendance.report',

            // Leave
            'leave.view',
            'leave.approve',
            'leave.manage',
            'leave.report',

            // Schedules & Shifts
            'schedule.view',
            'schedule.create',
            'schedule.edit',
            'schedule.delete',
            'shift.view',
            'shift.create',
            'shift.edit',
            'shift.delete',

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
        ];

        $hrAdmin->syncPermissions(
            Permission::whereIn('name', $hrAdminPermissions)->get()
        );

        // Create Manager role
        $manager = Role::firstOrCreate(
            ['name' => 'manager'],
            ['guard_name' => 'web']
        );

        $managerPermissions = [
            // Limited employee viewing
            'employee.view',
            'employee.view-own',
            'employee.edit-own',

            // Attendance
            'attendance.view',
            'attendance.report',
            'attendance.approve',

            // Leave
            'leave.view',
            'leave.approve',
            'leave.report',

            // Schedules
            'schedule.view',
            'schedule.create',
            'schedule.edit',

            // Performance - for their team
            'performance.view',
            'performance.create',
            'performance.edit',

            // Tasks
            'task.view',
            'task.create',
            'task.edit',

            // Reports
            'report.view',
            'report.export',
        ];

        $manager->syncPermissions(
            Permission::whereIn('name', $managerPermissions)->get()
        );

        // Create Accountant role
        $accountant = Role::firstOrCreate(
            ['name' => 'accountant'],
            ['guard_name' => 'web']
        );

        $accountantPermissions = [
            // Employee basic info only
            'employee.view',

            // Payroll access
            'payroll.view',
            'payroll.edit',
            'payroll.approve',
            'payroll.process',
            'payroll.report',

            // Attendance (for payroll)
            'attendance.view',
            'attendance.report',

            // Leave (for payroll)
            'leave.view',
            'leave.report',

            // Reports
            'report.view',
            'report.export',
        ];

        $accountant->syncPermissions(
            Permission::whereIn('name', $accountantPermissions)->get()
        );

        // Create Employee role (default for all employees)
        $employee = Role::firstOrCreate(
            ['name' => 'employee'],
            ['guard_name' => 'web']
        );

        $employeePermissions = [
            // View own profile
            'employee.view-own',
            'employee.edit-own',

            // Attendance
            'attendance.checkin',
            'attendance.checkout',

            // Leave
            'leave.view',
            'leave.request',

            // View own schedule
            'schedule.view',

            // View own tasks
            'task.view',
        ];

        $employee->syncPermissions(
            Permission::whereIn('name', $employeePermissions)->get()
        );
    }
}
