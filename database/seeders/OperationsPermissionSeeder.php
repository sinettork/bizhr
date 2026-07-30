<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class OperationsPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $permissions = [
            'task.view-own','task.view','task.assign','task.verify','task.cancel',
            'recruitment.view','recruitment.manage','recruitment.approve',
            'training.view-own','training.view','training.manage',
            'asset.view-own','asset.view','asset.manage',
            'expense.view-own','expense.view','expense.approve-manager','expense.approve-accounting','expense.pay',
            'announcement.view','announcement.manage',
        ];
        foreach ($permissions as $name) Permission::findOrCreate($name, 'web');

        $grant = function (string $role, array $items): void {
            if ($model = Role::where('name', $role)->where('guard_name', 'web')->first()) $model->givePermissionTo($items);
        };
        $grant('Employee', ['task.view-own','training.view-own','asset.view-own','expense.view-own','announcement.view']);
        $grant('Manager', ['task.view-own','task.view','task.assign','task.verify','task.cancel','training.view-own','training.view','asset.view-own','expense.view-own','expense.view','expense.approve-manager','announcement.view']);
        $grant('HR Administrator', ['task.view-own','task.view','task.assign','task.verify','task.cancel','recruitment.view','recruitment.manage','recruitment.approve','training.view-own','training.view','training.manage','asset.view-own','asset.view','asset.manage','expense.view-own','expense.view','announcement.view','announcement.manage']);
        $grant('Accountant', ['expense.view-own','expense.view','expense.approve-accounting','expense.pay','announcement.view']);
        foreach (['Owner', 'Super Admin'] as $role) $grant($role, $permissions);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
