# Phase 2 — Testing Guide

## Quick Start

After seeding, you have 5 test users available:

| Email | Password | Role |
|-------|----------|------|
| owner@example.com | password | Owner (all permissions) |
| hr@example.com | password | HR Administrator |
| manager@example.com | password | Manager |
| accountant@example.com | password | Accountant |
| employee@example.com | password | Employee |

## Test Scenarios

### Scenario 1: Role-Based Route Access

**Test**: Verify employees cannot access employee list
1. Login as: `employee@example.com` / `password`
2. Try to navigate to: `/employees`
3. **Expected**: 403 Forbidden error (no `employee.view` permission)
4. **Success**: ✅ Permission middleware blocks unauthorized access

**Test**: Verify HR Admin can access employee list
1. Logout
2. Login as: `hr@example.com` / `password`
3. Navigate to: `/employees`
4. **Expected**: Employee list loads successfully
5. **Success**: ✅ HR Admin has `employee.view` permission

### Scenario 2: Role Management Access

**Test**: Verify only owner can access role management
1. Login as: `manager@example.com` / `password`
2. Try to navigate to: `/roles`
3. **Expected**: 403 Forbidden error (no `role.manage` permission)
4. **Success**: ✅ Only owner has role management access

**Test**: Verify owner can access role management
1. Logout
2. Login as: `owner@example.com` / `password`
3. Navigate to: `/roles`
4. **Expected**: Role management page loads with all 5 roles visible
5. **Success**: ✅ Owner has full access

### Scenario 3: User Management Access

**Test**: Verify HR Admin cannot manage users
1. Login as: `hr@example.com` / `password`
2. Try to navigate to: `/users`
3. **Expected**: 403 Forbidden error (no `user.manage` permission)
4. **Success**: ✅ Only owner/admin can manage users

**Test**: Verify owner can manage users
1. Logout
2. Login as: `owner@example.com` / `password`
3. Navigate to: `/users`
4. **Expected**: User list loads with all 5 test users
5. **Success**: ✅ Owner can manage users

### Scenario 4: Permission Verification in UI

**Test**: Check role permissions from `/roles`
1. Login as: `owner@example.com` / `password`
2. Go to: `/roles`
3. Select "Employee" role
4. **Expected**: See only these permission groups:
   - employee (view-own, edit-own)
   - attendance (checkin, checkout)
   - leave (request)
   - schedule (view)
5. **Success**: ✅ Permissions match expected employee capabilities

**Test**: Check role permissions for Manager
1. Still logged in as owner
2. Still on `/roles`
3. Select "Manager" role
4. **Expected**: See permission groups for:
   - employee (view)
   - attendance (view, approve)
   - leave (view, approve)
   - schedule (view, create, edit)
   - performance (view, create, edit)
   - task (create, edit, view)
5. **Success**: ✅ Manager has appropriate limited permissions

### Scenario 5: User Role Assignment

**Test**: Assign additional role to user
1. Login as: `owner@example.com` / `password`
2. Go to: `/users`
3. Click on user: `manager@example.com`
4. Check the "HR Administrator" checkbox
5. **Expected**: User now has both "Manager" and "HR Administrator" roles
6. **Success**: ✅ Multiple role assignment works

### Scenario 6: Permission Caching

**Test**: Verify permission changes take effect
1. Login as: `owner@example.com` / `password`
2. Go to: `/roles`
3. Select "Employee" role
4. Add permission: `employee.view` to the employee role
5. In another tab/incognito, login as: `employee@example.com` / `password`
6. Try to access: `/employees`
7. **Expected**: After page refresh, employee should now see employee list (permission added)
8. **Success**: ✅ Permissions updated in real-time

**Note**: Cache invalidation is automatic in the role/user management components.

## Command-Line Verification

### Check if permissions were seeded
```bash
php artisan tinker
# Then in tinker:
>>> Spatie\Permission\Models\Permission::count()
# Should return: 40
```

### Check if roles were created
```bash
php artisan tinker
# Then in tinker:
>>> Spatie\Permission\Models\Role::pluck('name')
# Should return: Collection with 5 roles
```

### Check test users and their roles
```bash
php artisan tinker
# Then in tinker:
>>> App\Models\User::first()->roles  # Check owner user
>>> App\Models\User::where('email', 'employee@example.com')->first()->roles
>>> App\Models\User::where('email', 'manager@example.com')->first()->roles
```

### Check if middleware is registered
```bash
php artisan tinker
# Then in tinker:
>>> app('router')->getMiddleware()['permission']
# Should return: App\Http\Middleware\CheckPermission
```

## Expected Behavior Summary

### Owner Account
- ✅ Access all routes: `/employees`, `/roles`, `/users`
- ✅ Manage all employees
- ✅ Manage all roles and permissions
- ✅ Manage all users
- ✅ See all sensitive data

### HR Administrator
- ✅ Access `/employees` (view, create, edit, delete)
- ❌ Cannot access `/roles` (403)
- ❌ Cannot access `/users` (403)
- ✅ See non-sensitive employee data

### Manager
- ✅ Access `/employees` but see limited data
- ❌ Cannot access `/roles` (403)
- ❌ Cannot access `/users` (403)
- ✅ Can approve leave and attendance
- ❌ Cannot see sensitive salary data

### Accountant
- ✅ Access `/employees` (for payroll data only)
- ❌ Cannot access `/roles` (403)
- ❌ Cannot access `/users` (403)
- ⏳ (Payroll routes not yet created in Phase 4)

### Employee
- ❌ Cannot access `/employees` (403)
- ❌ Cannot access `/roles` (403)
- ❌ Cannot access `/users` (403)
- ⏳ Can check in/out (Phase 4)
- ⏳ Can request leave (Phase 5)

## Troubleshooting

### Getting "403 Forbidden" unexpectedly
**Solution**: Clear cache and verify user has role:
```bash
php artisan cache:clear
php artisan tinker
# Check user's roles:
>>> App\Models\User::where('email', 'hr@example.com')->first()->roles
```

### New role not appearing
**Solution**: Ensure role was saved and cache is cleared:
```bash
php artisan cache:clear
# Then verify in tinker:
>>> Spatie\Permission\Models\Role::where('name', 'your-role')->first()
```

### Permission changes not taking effect
**Solution**: The management UIs auto-invalidate cache, but you can manually clear:
```bash
php artisan cache:clear
```

### Users can see pages they shouldn't
**Solution**: Check that middleware is applied to the route:
```bash
# In routes/web.php, verify:
->middleware('permission:the.permission')
# is added after the route definition
```

## Next Steps After Testing

Once all scenarios pass:

1. **Create Navigation Component** (remaining Phase 2 task)
   - Add permission checks to sidebar
   - Show/hide menu items based on user role

2. **Proceed to Phase 3** (Attendance Management)
   - Implement checkin/checkout with permission checks
   - Use `attendance.checkin`, `attendance.checkout` permissions

3. **Implement Phase 4** (Leave Management)
   - Implement leave requests with `leave.request` permission
   - Implement leave approval with `leave.approve` permission
   - Show leave balance with permission checks

## Contact Database for Verification

If you need to manually verify in SQLite:

```sql
-- Check permissions exist
SELECT COUNT(*) FROM permissions;
-- Expected: 40

-- Check roles exist
SELECT * FROM roles;
-- Expected: 5 roles

-- Check user roles
SELECT u.email, r.name FROM model_has_roles mr
JOIN users u ON u.id = mr.model_id
JOIN roles r ON r.id = mr.role_id;

-- Check role permissions
SELECT r.name, p.name FROM role_has_permissions rp
JOIN roles r ON r.id = rp.role_id
JOIN permissions p ON p.id = rp.permission_id
WHERE r.name = 'owner'
LIMIT 10;
```

## Performance Notes

- Permission caching is automatic (Spatie handles it)
- Role/permission checks have minimal overhead (< 1ms)
- First request creates cache, subsequent requests use cache
- Cache invalidates automatically on management page saves
- For high-traffic scenarios, consider Redis caching
