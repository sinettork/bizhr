<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AccessAdministrationController extends Controller
{
    private const PROTECTED_ROLES = ['Super Admin', 'Owner'];

    public function users(Request $request): View
    {
        $companyId = (int) Company::query()->value('id');
        $users = User::query()->with(['roles', 'employee'])
            ->where(fn ($query) => $query->whereDoesntHave('employee')->orWhereHas('employee', fn ($employee) => $employee->where('company_id', $companyId)))
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = '%'.str_replace(['%', '_'], ['\\%', '\\_'], trim((string) $request->input('search'))).'%';
                $query->where(fn ($inner) => $inner->where('name', 'like', $search)->orWhere('email', 'like', $search)->orWhereHas('employee', fn ($employee) => $employee->where('employee_code', 'like', $search)));
            })
            ->when($request->filled('status'), fn ($query) => $query->where('is_active', $request->boolean('status')))
            ->orderBy('name')->paginate($this->perPage($request, 20))->withQueryString();

        return view('access.users', [
            'users' => $users,
            'roles' => Role::query()->where('guard_name', 'web')->orderBy('name')->get(),
            'employees' => Employee::query()->where('company_id', $companyId)->whereNull('user_id')->orderBy('first_name')->orderBy('last_name')->get(),
            'accessReview' => $this->accessReview($companyId),
        ]);
    }

    public function storeUser(Request $request): RedirectResponse
    {
        $companyId = (int) Company::query()->value('id');
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'employee_id' => ['nullable', Rule::exists('employees', 'id')->where(fn ($query) => $query->where('company_id', $companyId)->whereNull('user_id'))],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['string', Rule::exists('roles', 'name')->where('guard_name', 'web')],
        ]);
        $this->guardPrivilegedAssignment($request, $data['roles']);

        $user = DB::transaction(function () use ($data): User {
            $user = User::query()->create(['name' => trim($data['name']), 'email' => Str::lower(trim($data['email'])), 'password' => Hash::make(Str::random(48)), 'is_active' => true]);
            $user->syncRoles($data['roles']);
            if (! empty($data['employee_id'])) {
                Employee::query()->whereKey($data['employee_id'])->update(['user_id' => $user->id]);
            }
            AuditLog::record($user, 'provisioned', [], ['email' => $user->email, 'roles' => $data['roles'], 'employee_id' => $data['employee_id'] ?? null]);

            return $user;
        });

        Password::sendResetLink(['email' => $user->email]);

        return back()->with('status', 'User provisioned and password setup instructions sent.');
    }

    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $this->authorizeManagedUser($user);
        $companyId = (int) Company::query()->value('id');
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'employee_id' => ['nullable', Rule::exists('employees', 'id')->where(fn ($query) => $query->where('company_id', $companyId)->where(fn ($employee) => $employee->whereNull('user_id')->orWhere('user_id', $user->id)))],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['string', Rule::exists('roles', 'name')->where('guard_name', 'web')],
        ]);
        $this->guardPrivilegedAssignment($request, $data['roles']);
        $old = ['name' => $user->name, 'email' => $user->email, 'roles' => $user->getRoleNames()->all(), 'employee_id' => $user->employee?->id];

        DB::transaction(function () use ($user, $data, $old): void {
            Employee::query()->where('user_id', $user->id)->when(empty($data['employee_id']), fn ($query) => $query, fn ($query) => $query->whereKeyNot($data['employee_id']))->update(['user_id' => null]);
            if (! empty($data['employee_id'])) {
                Employee::query()->whereKey($data['employee_id'])->update(['user_id' => $user->id]);
            }
            $user->update(['name' => trim($data['name']), 'email' => Str::lower(trim($data['email']))]);
            $user->syncRoles($data['roles']);
            DB::table('sessions')->where('user_id', $user->id)->delete();
            AuditLog::record($user, 'access_updated', $old, ['name' => $user->name, 'email' => $user->email, 'roles' => $data['roles'], 'employee_id' => $data['employee_id'] ?? null]);
        });

        return back()->with('status', 'User access updated and existing sessions revoked.');
    }

    public function status(Request $request, User $user): RedirectResponse
    {
        $this->authorizeManagedUser($user);
        abort_if($user->is($request->user()), 422, 'You cannot deactivate your own account.');
        $active = $request->boolean('is_active');
        $old = $user->is_active;
        $user->update(['is_active' => $active]);
        if (! $active) {
            DB::table('sessions')->where('user_id', $user->id)->delete();
        }
        AuditLog::record($user, $active ? 'reactivated' : 'deprovisioned', ['is_active' => $old], ['is_active' => $active]);

        return back()->with('status', $active ? 'User reactivated.' : 'User deactivated and sessions revoked.');
    }

    public function resetPassword(User $user): RedirectResponse
    {
        $this->authorizeManagedUser($user);
        Password::sendResetLink(['email' => $user->email]);
        AuditLog::record($user, 'password_reset_requested', [], ['email' => $user->email]);

        return back()->with('status', 'Password reset instructions sent.');
    }

    public function roles(Request $request): View
    {
        return view('access.roles', [
            'roles' => Role::query()->with('permissions')->withCount(['users', 'permissions'])->where('guard_name', 'web')->orderBy('name')->paginate($this->perPage($request, 20))->withQueryString(),
            'permissions' => Permission::query()->where('guard_name', 'web')->orderBy('name')->get()->groupBy(fn (Permission $permission) => Str::before($permission->name, '.')),
            'protectedRoles' => self::PROTECTED_ROLES,
        ]);
    }

    public function storeRole(Request $request): RedirectResponse
    {
        $data = $this->validateRole($request);
        $role = Role::query()->create(['name' => trim($data['name']), 'guard_name' => 'web']);
        $role->syncPermissions($data['permissions']);
        AuditLog::record($role, 'created', [], $data);

        return back()->with('status', 'Role created.');
    }

    public function updateRole(Request $request, Role $role): RedirectResponse
    {
        abort_if(in_array($role->name, self::PROTECTED_ROLES, true), 422, 'Protected system roles cannot be modified.');
        $data = $this->validateRole($request, $role);
        $old = ['name' => $role->name, 'permissions' => $role->permissions()->pluck('name')->all()];
        $role->update(['name' => trim($data['name'])]);
        $role->syncPermissions($data['permissions']);
        DB::table('sessions')->whereIn('user_id', DB::table('model_has_roles')->where('role_id', $role->id)->pluck('model_id'))->delete();
        AuditLog::record($role, 'updated', $old, $data);

        return back()->with('status', 'Role updated and affected sessions revoked.');
    }

    public function destroyRole(Role $role): RedirectResponse
    {
        abort_if(in_array($role->name, self::PROTECTED_ROLES, true), 422, 'Protected system roles cannot be deleted.');
        if ($role->users()->exists()) {
            return back()->withErrors(['role' => 'Reassign all users before deleting this role.']);
        }
        AuditLog::record($role, 'deleted', ['name' => $role->name, 'permissions' => $role->permissions()->pluck('name')->all()], []);
        $role->delete();

        return back()->with('status', 'Role deleted.');
    }

    /** @return array{name: string, permissions: array<int, string>} */
    private function validateRole(Request $request, ?Role $role = null): array
    {
        return $request->validate(['name' => ['required', 'string', 'max:100', Rule::unique('roles', 'name')->where('guard_name', 'web')->ignore($role)], 'permissions' => ['required', 'array', 'min:1'], 'permissions.*' => ['string', Rule::exists('permissions', 'name')->where('guard_name', 'web')]]);
    }

    /** @param array<int, string> $roles */
    private function guardPrivilegedAssignment(Request $request, array $roles): void
    {
        if (array_intersect(self::PROTECTED_ROLES, $roles)) {
            abort_unless($request->user()->hasRole('Super Admin'), 403);
        }
    }

    private function authorizeManagedUser(User $user): void
    {
        $companyId = (int) Company::query()->value('id');
        abort_unless($user->employee === null || $user->employee->company_id === $companyId, 404);
        if ($user->hasAnyRole(self::PROTECTED_ROLES)) {
            abort_unless(request()->user()?->hasRole('Super Admin'), 403);
        }
    }

    /** @return array<string, int> */
    private function accessReview(int $companyId): array
    {
        $companyUsers = User::query()->where(fn ($query) => $query->whereDoesntHave('employee')->orWhereHas('employee', fn ($employee) => $employee->where('company_id', $companyId)));
        $recentSessionUsers = DB::table('sessions')->where('last_activity', '>=', now()->subDays(90)->timestamp)->whereNotNull('user_id')->select('user_id');

        return [
            'privileged' => (clone $companyUsers)->whereHas('roles', fn ($roles) => $roles->whereIn('name', self::PROTECTED_ROLES))->count(),
            'dormant' => (clone $companyUsers)->where('is_active', true)->whereNotIn('id', $recentSessionUsers)->count(),
            'employees_without_accounts' => Employee::query()->where('company_id', $companyId)->where('is_active', true)->whereNull('user_id')->count(),
            'separated_with_access' => Employee::query()->where('company_id', $companyId)->whereIn('employment_status', ['Resigned', 'Terminated', 'Retired'])->whereHas('user', fn ($users) => $users->where('is_active', true))->count(),
        ];
    }
}
