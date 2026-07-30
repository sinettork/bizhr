<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'company.view', 'company.edit',
            'branch.view', 'branch.create', 'branch.edit', 'branch.delete',
            'department.view', 'department.create', 'department.edit', 'department.delete',
            'position.view', 'position.create', 'position.edit', 'position.delete',
            'employment-type.view', 'employment-type.create', 'employment-type.edit', 'employment-type.delete',
            'employee.view', 'employee.view-own', 'employee.create', 'employee.edit',
            'employee.edit-own', 'employee.delete', 'employee.view-sensitive',
            'contract.view-own', 'contract.view', 'contract.create',
            'contract.edit', 'contract.approve', 'contract.terminate',
            'attendance.view', 'attendance.checkin', 'attendance.checkout',
            'attendance.correction.request', 'attendance.edit', 'attendance.approve', 'attendance.report',
            'schedule.view', 'schedule.create', 'schedule.edit', 'schedule.delete',
            'shift.view', 'shift.create', 'shift.edit', 'shift.delete',
            'leave.view', 'leave.request', 'leave.approve', 'leave.manage', 'leave.report',
            'payroll.view-own', 'payroll.view', 'payroll.edit', 'payroll.approve',
            'payroll.process', 'payroll.report',
            'performance.view-own', 'performance.view', 'performance.create', 'performance.edit',
            'performance.review', 'performance.approve', 'performance.reopen',
            'performance.manage-goals',
            'task.view-own', 'task.view', 'task.create', 'task.edit', 'task.delete',
            'task.assign', 'task.verify', 'task.cancel',
            'recruitment.view', 'recruitment.manage', 'recruitment.approve',
            'training.view-own', 'training.view', 'training.manage',
            'asset.view-own', 'asset.view', 'asset.manage',
            'expense.view-own', 'expense.view', 'expense.approve-manager',
            'expense.approve-accounting', 'expense.pay',
            'announcement.view', 'announcement.manage',
            'report.view', 'report.export',
            'user.manage', 'role.manage', 'audit.view',
        ];

        foreach ($permissions as $name) {
            Permission::findOrCreate($name, 'web');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
