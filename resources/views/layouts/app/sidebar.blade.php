@php
    use Illuminate\Support\Facades\Route;

    $user = auth()->user();
    $employee = $user?->employee;

    /*
    |--------------------------------------------------------------------------
    | Account display name
    |--------------------------------------------------------------------------
    |
    | Keep the original behavior: show the login account name first.
    |
    */

    $displayName =
        $user?->name
        ?: $employee?->full_name_km
        ?: $employee?->full_name_en
        ?: 'អ្នកប្រើប្រាស់';

    /*
    |--------------------------------------------------------------------------
    | Safe route finder
    |--------------------------------------------------------------------------
    |
    | The menu item remains hidden when its route does not exist.
    |
    */

    $findRoute = static function (
        array $routeNames
    ): ?string {
        foreach ($routeNames as $routeName) {
            if (Route::has($routeName)) {
                return $routeName;
            }
        }

        return null;
    };

    /*
    |--------------------------------------------------------------------------
    | Main routes
    |--------------------------------------------------------------------------
    */

    $dashboardRoute = $findRoute([
        'dashboard',
    ]);

    $companyRoute = $findRoute([
        'company.settings',
        'company-settings.index',
        'company.index',
    ]);

    $branchesRoute = $findRoute([
        'branches.index',
    ]);

    $departmentsRoute = $findRoute([
        'departments.index',
    ]);

    $positionsRoute = $findRoute([
        'positions.index',
    ]);

    $employmentTypesRoute = $findRoute([
        'employment-types.index',
        'employment-types',
    ]);

    /*
    |--------------------------------------------------------------------------
    | Employee and attendance routes
    |--------------------------------------------------------------------------
    */

    $employeesRoute = $findRoute([
        'employees.index',
    ]);

    $workShiftsRoute = $findRoute([
        'work-shifts.index',
        'shifts.index',
    ]);

    $schedulesRoute = $findRoute([
        'schedules.index',
    ]);

    $attendanceRoute = $findRoute([
        'attendance.checkinout',
    ]);

    $correctionRequestRoute = $findRoute([
        'attendance.corrections.request',
    ]);

    $correctionReviewRoute = $findRoute([
        'attendance.corrections.review',
    ]);

    $attendanceReportsRoute = $findRoute([
        'attendance.reports.index',
    ]);

    /*
    |--------------------------------------------------------------------------
    | Leave routes
    |--------------------------------------------------------------------------
    */

    $leaveTypesRoute = $findRoute([
        'leave.types.index',
        'leave-types.index',
        'leaves.types.index',
        'leaves.manage',
    ]);

    $leaveRequestsRoute = $findRoute([
        'leaves.index',
        'leave-requests.index',
        'leave.requests.index',
        'leaves.requests.index',
    ]);

    $leaveReviewRoute = $findRoute([
        'leave.requests.review',
        'leaves.requests.review',
        'leave-requests.review',
    ]);

    /*
    |--------------------------------------------------------------------------
    | User and role routes
    |--------------------------------------------------------------------------
    */

    $usersRoute = $findRoute([
        'users.index',
        'user.index',
    ]);

    $rolesRoute = $findRoute([
        'roles.index',
        'role.index',
    ]);

    /*
    |--------------------------------------------------------------------------
    | Profile route
    |--------------------------------------------------------------------------
    */

    $profileRoute = $findRoute([
        'settings.profile',
        'profile.edit',
    ]);

    $dashboardUrl = $dashboardRoute
        ? route($dashboardRoute)
        : url('/');
@endphp

<!DOCTYPE html>

<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    class="dark"
>
<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-zinc-100 dark:bg-zinc-950">
    <flux:sidebar
        sticky
        collapsible="mobile"
        class="border-e border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900"
    >
        {{-- Logo --}}
        <flux:sidebar.header>
            <x-app-logo
                :sidebar="true"
                href="{{ $dashboardUrl }}"
                wire:navigate
            />

            <flux:sidebar.collapse class="lg:hidden"/>
        </flux:sidebar.header>

        <flux:sidebar.nav>
            {{-- ============================================================
                 Dashboard
            ============================================================= --}}

            @if ($dashboardRoute)
                <flux:sidebar.group
                    heading="ការគ្រប់គ្រង"
                    class="grid"
                >
                    <flux:sidebar.item
                        icon="home"
                        :href="route($dashboardRoute)"
                        :current="request()->routeIs('dashboard')"
                        wire:navigate
                    >
                        ផ្ទាំងគ្រប់គ្រង
                    </flux:sidebar.item>
                </flux:sidebar.group>
            @endif

            {{-- ============================================================
                 Company structure
            ============================================================= --}}

            @canany([
                'company.view',
                'branch.view',
                'department.view',
                'position.view',
                'employment-type.view',
            ])
                <flux:sidebar.group
                    expandable
                    heading="រចនាសម្ព័ន្ធក្រុមហ៊ុន"
                    class="grid"
                >
                    @can('company.view')
                        @if ($companyRoute)
                            <flux:sidebar.item
                                icon="building-office"
                                :href="route($companyRoute)"
                                :current="request()->routeIs(
                                    $companyRoute
                                )"
                                wire:navigate
                            >
                                ព័ត៌មានក្រុមហ៊ុន
                            </flux:sidebar.item>
                        @endif
                    @endcan

                    @can('branch.view')
                        @if ($branchesRoute)
                            <flux:sidebar.item
                                icon="map-pin"
                                :href="route($branchesRoute)"
                                :current="request()->routeIs(
                                    'branches.*'
                                )"
                                wire:navigate
                            >
                                សាខា
                            </flux:sidebar.item>
                        @endif
                    @endcan

                    @can('department.view')
                        @if ($departmentsRoute)
                            <flux:sidebar.item
                                icon="squares-2x2"
                                :href="route($departmentsRoute)"
                                :current="request()->routeIs(
                                    'departments.*'
                                )"
                                wire:navigate
                            >
                                ផ្នែក
                            </flux:sidebar.item>
                        @endif
                    @endcan

                    @can('position.view')
                        @if ($positionsRoute)
                            <flux:sidebar.item
                                icon="briefcase"
                                :href="route($positionsRoute)"
                                :current="request()->routeIs(
                                    'positions.*'
                                )"
                                wire:navigate
                            >
                                មុខតំណែង
                            </flux:sidebar.item>
                        @endif
                    @endcan

                    @can('employment-type.view')
                        @if ($employmentTypesRoute)
                            <flux:sidebar.item
                                icon="identification"
                                :href="route(
                                    $employmentTypesRoute
                                )"
                                :current="request()->routeIs(
                                    'employment-types.*'
                                )"
                                wire:navigate
                            >
                                ប្រភេទការងារ
                            </flux:sidebar.item>
                        @endif
                    @endcan
                </flux:sidebar.group>
            @endcanany

            {{-- ============================================================
                 Employees and time
            ============================================================= --}}

            @canany([
                'employee.view',
                'employee.view-own',
                'shift.view',
                'schedule.view',
                'attendance.view',
                'attendance.checkin',
                'attendance.checkout',
                'attendance.correction.request',
                'attendance.approve',
                'attendance.report',
            ])
                <flux:sidebar.group
                    expandable
                    heading="បុគ្គលិក និង ពេលវេលា"
                    class="grid"
                >
                    {{-- Employee list --}}
                    @can('employee.view')
                        @if ($employeesRoute)
                            <flux:sidebar.item
                                icon="users"
                                :href="route($employeesRoute)"
                                :current="request()->routeIs(
                                    'employees.*'
                                )"
                                wire:navigate
                            >
                                បញ្ជីបុគ្គលិក
                            </flux:sidebar.item>
                        @endif
                    @endcan

                    {{-- Work shifts --}}
                    @can('shift.view')
                        @if ($workShiftsRoute)
                            <flux:sidebar.item
                                icon="clock"
                                :href="route($workShiftsRoute)"
                                :current="request()->routeIs(
                                    'work-shifts.*'
                                )"
                                wire:navigate
                            >
                                វេនការងារ
                            </flux:sidebar.item>
                        @endif
                    @endcan

                    {{-- Employee schedules --}}
                    @can('schedule.view')
                        @if ($schedulesRoute)
                            <flux:sidebar.item
                                icon="calendar-days"
                                :href="route($schedulesRoute)"
                                :current="request()->routeIs(
                                    'schedules.*'
                                )"
                                wire:navigate
                            >
                                កាលវិភាគ
                            </flux:sidebar.item>
                        @endif
                    @endcan

                    {{-- Personal attendance --}}
                    @canany([
                        'attendance.checkin',
                        'attendance.checkout',
                    ])
                        @if ($attendanceRoute)
                            <flux:sidebar.item
                                icon="check-circle"
                                :href="route($attendanceRoute)"
                                :current="request()->routeIs(
                                    'attendance.checkinout'
                                )"
                                wire:navigate
                            >
                                វត្តមាន
                            </flux:sidebar.item>
                        @endif
                    @endcanany

                    {{--
                        Approvers see the review page.

                        Ordinary employees see their personal correction
                        request page instead.
                    --}}

                    @can('attendance.approve')
                        @if ($correctionReviewRoute)
                            <flux:sidebar.item
                                icon="clipboard-document-check"
                                :href="route(
                                    $correctionReviewRoute
                                )"
                                :current="request()->routeIs(
                                    'attendance.corrections.review'
                                )"
                                wire:navigate
                            >
                                កែតម្រូវវត្តមាន
                            </flux:sidebar.item>
                        @endif
                    @elsecan('attendance.correction.request')
                        @if ($correctionRequestRoute)
                            <flux:sidebar.item
                                icon="clipboard-document"
                                :href="route(
                                    $correctionRequestRoute
                                )"
                                :current="request()->routeIs(
                                    'attendance.corrections.request'
                                )"
                                wire:navigate
                            >
                                កែតម្រូវវត្តមាន
                            </flux:sidebar.item>
                        @endif
                    @endcan

                    {{-- Attendance reports --}}
                    @can('attendance.report')
                        @if ($attendanceReportsRoute)
                            <flux:sidebar.item
                                icon="chart-bar"
                                :href="route(
                                    $attendanceReportsRoute
                                )"
                                :current="request()->routeIs(
                                    'attendance.reports.*'
                                )"
                                wire:navigate
                            >
                                របាយការណ៍វត្តមាន
                            </flux:sidebar.item>
                        @endif
                    @endcan
                </flux:sidebar.group>
            @endcanany

            {{-- ============================================================
                 Leave management
            ============================================================= --}}

            @canany([
                'leave.view',
                'leave.request',
                'leave.approve',
                'leave.manage',
                'leave.report',
            ])
                <flux:sidebar.group
                    expandable
                    heading="ការឈប់សម្រាក"
                    class="grid"
                >
                    @can('leave.manage')
                        @if ($leaveTypesRoute)
                            <flux:sidebar.item
                                icon="calendar-days"
                                :href="route($leaveTypesRoute)"
                                :current="request()->routeIs(
                                    'leave.types.*',
                                    'leave-types.*',
                                    'leaves.types.*'
                                )"
                                wire:navigate
                            >
                                ប្រភេទច្បាប់
                            </flux:sidebar.item>
                        @endif
                    @endcan

                    @can('leave.request')
                        @if ($leaveRequestsRoute)
                            <flux:sidebar.item
                                icon="document-text"
                                :href="route($leaveRequestsRoute)"
                                :current="request()->routeIs(
                                    'leave.requests.index'
                                )"
                            >
                                សំណើរបស់ខ្ញុំ
                            </flux:sidebar.item>
                        @endif
                    @endcan

                    @can('leave.approve')
                        @if ($leaveReviewRoute)
                            <flux:sidebar.item
                                icon="clipboard-document-check"
                                :href="route($leaveReviewRoute)"
                                :current="request()->routeIs(
                                    'leave.requests.review'
                                )"
                            >
                                ពិនិត្យសំណើច្បាប់
                            </flux:sidebar.item>
                        @endif
                    @endcan
                </flux:sidebar.group>
            @endcanany

            {{-- ============================================================
                 System settings
            ============================================================= --}}

            @canany([
                'user.manage',
                'role.manage',
            ])
                <flux:sidebar.group
                    expandable
                    heading="ការកំណត់"
                    class="grid"
                >
                    @can('user.manage')
                        @if ($usersRoute)
                            <flux:sidebar.item
                                icon="user-group"
                                :href="route($usersRoute)"
                                :current="request()->routeIs(
                                    'users.*'
                                )"
                                wire:navigate
                            >
                                អ្នកប្រើប្រាស់
                            </flux:sidebar.item>
                        @endif
                    @endcan

                    @can('role.manage')
                        @if ($rolesRoute)
                            <flux:sidebar.item
                                icon="shield-check"
                                :href="route($rolesRoute)"
                                :current="request()->routeIs(
                                    'roles.*'
                                )"
                                wire:navigate
                            >
                                តួនាទី និង សិទ្ធិ
                            </flux:sidebar.item>
                        @endif
                    @endcan
                </flux:sidebar.group>
            @endcanany
        </flux:sidebar.nav>

        <flux:sidebar.spacer/>

        {{-- Original desktop account dropdown --}}
        <x-desktop-user-menu
            class="hidden lg:block"
            :name="$displayName"
        />
    </flux:sidebar>

    {{-- Mobile header --}}
    <flux:header class="lg:hidden">
        <flux:sidebar.toggle
            class="lg:hidden"
            icon="bars-2"
            inset="left"
        />

        <flux:spacer/>

        <flux:dropdown
            position="top"
            align="end"
        >
            <flux:profile
                :initials="$user->initials()"
                icon-trailing="chevron-down"
            />

            <flux:menu>
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div
                            class="flex items-center gap-2 px-1 py-1.5 text-start text-sm"
                        >
                            <flux:avatar
                                :name="$displayName"
                                :initials="$user->initials()"
                            />

                            <div
                                class="grid flex-1 text-start text-sm leading-tight"
                            >
                                <span class="truncate font-semibold">
                                    {{ $displayName }}
                                </span>

                                <span class="truncate text-xs text-zinc-500">
                                    {{ $user->email }}
                                </span>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                @if ($profileRoute)
                    <flux:menu.separator/>

                    <flux:menu.radio.group>
                        <flux:menu.item
                            :href="route($profileRoute)"
                            icon="cog"
                            wire:navigate
                        >
                            ការកំណត់គណនី
                        </flux:menu.item>
                    </flux:menu.radio.group>
                @endif

                <flux:menu.separator/>

                <form
                    method="POST"
                    action="{{ route('logout') }}"
                    class="w-full"
                >
                    @csrf

                    <flux:menu.item
                        as="button"
                        type="submit"
                        icon="arrow-right-start-on-rectangle"
                        class="w-full cursor-pointer"
                    >
                        ចាកចេញ
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

    {{ $slot }}

    @persist('toast')
        <flux:toast.group>
            <flux:toast/>
        </flux:toast.group>
    @endpersist

    @fluxScripts
</body>
</html>