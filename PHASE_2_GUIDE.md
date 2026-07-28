# Phase 2 — Roles & Permissions Implementation Guide

## Overview

Phase 2 implements a comprehensive role-based access control (RBAC) system using Spatie Laravel Permission package version 8.3.0. This system allows granular permission management across 15+ modules with 5 predefined roles.

## Implementation Details

### Installed Package
- **Package**: `spatie/laravel-permission`
- **Version**: `^8.3.0`
- **Purpose**: Production-ready RBAC system with role and permission management

### Database Setup

The migration `2026_07_25_024524_create_permission_tables.php` creates the following tables:

```
permissions
├── id
├── name (e.g., 'employee.view')
├── guard_name (e.g., 'web')
└── timestamps

roles
├── id
├── name (e.g., 'owner')
├── guard_name
└── timestamps

model_has_permissions (pivot)
├── permission_id
├── model_id
├── model_type

model_has_roles (pivot)
├── role_id
├── model_id
├── model_type

role_has_permissions (pivot)
├── permission_id
├── role_id
```

### Permissions Structure (40+ total)

Permissions are organized by module using dot notation:

**Company Management**
- `company.view` — View company settings
- `company.edit` — Edit company settings

**Branch Management**
- `branch.view`, `branch.create`, `branch.edit`, `branch.delete`

**Department Management**
- `department.view`, `department.create`, `department.edit`, `department.delete`

**Position Management**
- `position.view`, `position.create`, `position.edit`, `position.delete`

**Employment Type Management**
- `employment-type.view`, `employment-type.create`, `employment-type.edit`, `employment-type.delete`

**Employee Management** (7 permissions for granular control)
- `employee.view` — View all employees
- `employee.view-own` — View own employee profile
- `employee.create` — Create new employees
- `employee.edit` — Edit any employee
- `employee.edit-own` — Edit own profile
- `employee.delete` — Delete employees
- `employee.view-sensitive` — View salary, bank info (permission-protected)

**Attendance**
- `attendance.view`, `attendance.checkin`, `attendance.checkout`, `attendance.edit`, `attendance.approve`, `attendance.report`

**Schedules**
- `schedule.view`, `schedule.create`, `schedule.edit`, `schedule.delete`

**Shifts**
- `shift.view`, `shift.create`, `shift.edit`, `shift.delete`

**Leave Management**
- `leave.view`, `leave.request`, `leave.approve`, `leave.manage`, `leave.report`

**Payroll** (5 permissions for financial operations)
- `payroll.view`, `payroll.edit`, `payroll.approve`, `payroll.process`, `payroll.report`

**Performance**
- `performance.view`, `performance.create`, `performance.edit`, `performance.manage-goals`

**Tasks**
- `task.view`, `task.create`, `task.edit`, `task.delete`

**Reports**
- `report.view`, `report.export`

**User & Role Management**
- `user.manage` — Manage users and their roles
- `role.manage` — Manage roles and permissions

**Audit**
- `audit.view` — View system audit logs

### System Roles (5 tiers)

#### 1. Owner
**Permissions**: All 40+ permissions
- Complete system access
- Manage all employees and sensitive data
- Full payroll access
- Can manage roles and permissions
- Can view all reports

#### 2. HR Administrator
**Key Permissions**: 
- All employee management (view, create, edit)
- Attendance management
- Leave management
- Schedule creation
- Performance reviews
- Task management
- All reports
- **Cannot**: Process payroll, manage roles

#### 3. Manager
**Key Permissions**:
- Limited employee viewing
- Attendance approval
- Leave approval
- Team scheduling
- Performance reviews
- Task creation
- **Cannot**: Create employees, process payroll, manage roles

#### 4. Accountant
**Key Permissions**:
- Employee viewing (for payroll purposes)
- Attendance viewing (for payroll)
- Leave viewing (for payroll)
- Full payroll module access
- Payroll reports
- **Cannot**: Create/edit employees, manage roles

#### 5. Employee
**Key Permissions**:
- View own profile (`employee.view-own`)
- Check in/out (`attendance.checkin`, `attendance.checkout`)
- Request leave (`leave.request`)
- View own schedule
- **Cannot**: Manage anyone else, access sensitive data

### Test Users

Seed `TestUsersSeeder` to create test users with all roles:

```php
// Credentials format: email / password "password"
owner@example.com           // Role: owner
hr@example.com             // Role: hr-administrator
manager@example.com         // Role: manager
accountant@example.com      // Role: accountant
employee@example.com        // Role: employee
```

### Route Protection

Routes are protected using the `permission` middleware:

```php
// In routes/web.php
Route::livewire('/employees', 'pages::employees.index')
    ->middleware('permission:employee.view');

Route::livewire('/roles', 'pages::roles.index')
    ->middleware('permission:role.manage');

Route::livewire('/users', 'pages::users.index')
    ->middleware('permission:user.manage');
```

Usage pattern:
```php
// Single permission required
->middleware('permission:employee.view')

// Any one of multiple permissions required
->middleware('permission:payroll.edit,payroll.approve')
```

### Middleware Registration

The `CheckPermission` middleware is registered in `bootstrap/app.php`:

```php
$middleware->alias([
    'permission' => \App\Http\Middleware\CheckPermission::class,
]);
```

Implementation:
```php
public function handle(Request $request, Closure $next, ...$permissions): Response
{
    if ($permissions && !$request->user()?->hasAnyPermission($permissions)) {
        abort(403, 'You do not have permission to access this resource.');
    }
    return $next($request);
}
```

### Management Interfaces

#### Role Management (`/roles`)
- View all system roles with permission counts
- Create new custom roles
- Edit role permissions
- Permissions grouped by module
- Bulk selection by module group
- Individual permission toggle
- System roles (owner, hr-administrator) protected from deletion

#### User Management (`/users`)
- Search users by name or email
- View user details
- Assign/revoke roles
- Pagination (15 users per page)
- Shows current assigned roles

### Checking Permissions in Code

#### In Controllers/Livewire Components:
```php
// Check if user has permission
if (auth()->user()->hasPermission('employee.edit')) {
    // Allow action
}

// Check if user has any of multiple permissions
if (auth()->user()->hasAnyPermission(['employee.edit', 'employee.create'])) {
    // Allow action
}

// Check if user has all permissions
if (auth()->user()->hasAllPermissions(['employee.view', 'employee.edit'])) {
    // Allow action
}

// Check via role
if (auth()->user()->hasRole('owner')) {
    // Owner-only functionality
}
```

#### In Blade Templates:
```php
@can('employee.edit')
    <!-- Show edit button -->
    <a href="{{ route('edit', $employee) }}">Edit</a>
@endcan

@canany(['employee.view', 'employee.view-own'])
    <!-- Show employee info -->
@endcanany
```

#### In Livewire Components:
```php
// In component properties
#[Computed]
public function canEditEmployee()
{
    return auth()->user()->hasPermission('employee.edit');
}

// In component methods
public function updateEmployee()
{
    $this->authorize('employee.edit');
    
    // Update logic
}
```

### Assigning Permissions and Roles

#### In Seeders:
```php
// Assign permission to user
$user->givePermissionTo('employee.view');

// Assign multiple permissions
$user->givePermissionTo(['employee.view', 'employee.edit']);

// Assign role to user
$user->assignRole('manager');

// Assign multiple roles
$user->assignRole(['manager', 'accountant']);

// Revoke permission
$user->revokePermissionTo('employee.delete');

// Remove role
$user->removeRole('manager');

// Sync permissions (replaces existing)
$user->syncPermissions(['employee.view', 'attendance.view']);

// Sync roles (replaces existing)
$user->syncRoles(['manager']);
```

#### Via Management UI:
1. Go to `/roles` to manage roles and their permissions
2. Go to `/users` to assign roles to users
3. Changes take effect immediately (cached)

### Caching

Spatie Permission caches roles and permissions automatically:

```php
// Clear cache after making changes
app()['cache']->forget('spatie.permission.cache');

// Or use artisan command
php artisan cache:clear
```

The management interfaces automatically handle cache invalidation.

### Next Steps (Remaining Phase 2 Tasks)

1. **Hide Unauthorized Sidebar Items** (⏳)
   - Create sidebar component with permission checks
   - Show/hide menu items based on user permissions
   - Use computed properties for performance

2. **Add Permission Tests** (⏳)
   - Create Feature tests for permission middleware
   - Test role assignment workflows
   - Test component-level authorization
   - Verify sensitive data is hidden from unauthorized users

### Debugging

#### Check User Permissions:
```bash
# Via tinker
php artisan tinker
>>> $user = App\Models\User::find(1)
>>> $user->roles    // See assigned roles
>>> $user->permissions  // See direct permissions
>>> $user->hasPermission('employee.view')  // Check specific permission
>>> $user->hasRole('owner')  // Check role
```

#### Check Permission Exists:
```bash
>>> \Spatie\Permission\Models\Permission::all()  // List all permissions
>>> \Spatie\Permission\Models\Role::all()  // List all roles
```

### Troubleshooting

**"You do not have permission to access this resource" error when should have access**
- Issue: Cache not invalidated
- Solution: `php artisan cache:clear`

**Permission not found error**
- Issue: Permission hasn't been seeded
- Solution: Run `php artisan db:seed --class=PermissionSeeder`

**Role not assigned to user**
- Issue: Need to use User management page or assign directly
- Solution: Via UI: `/users` → Select user → Assign role
- Or: `$user->assignRole('manager')` then `php artisan cache:clear`

**Middleware blocking valid access**
- Issue: User doesn't have required permission through role or direct assignment
- Solution: Check user roles via `/users`, verify role has permission via `/roles`

## Summary

Phase 2 provides a complete, production-ready permission system that:
- ✅ Protects sensitive routes with middleware
- ✅ Enables granular permission control (40+ permissions)
- ✅ Provides 5 role tiers matching organizational hierarchy
- ✅ Includes management UI for both roles and user assignment
- ✅ Supports permission caching for performance
- ✅ Integrates seamlessly with Livewire components
- ✅ Works with Blade authorization directives
- ⏳ Remaining: UI element hiding, automated tests

The system is now ready to protect sensitive operations in Phases 3-7 (Attendance, Leave, Payroll, etc.) by enforcing access control at both route and component levels.
