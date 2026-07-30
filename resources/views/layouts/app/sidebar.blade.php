@php
    use Illuminate\Support\Facades\Route;

    $user = auth()->user();
    $employee = $user?->employee;

    $displayName = $user?->name
        ?: $employee?->full_name_km
        ?: $employee?->full_name_en
        ?: 'អ្នកប្រើប្រាស់';

    $menuItem = static function (
        string $label,
        string $icon,
        string $routeName,
        string $activePattern,
        bool $allowed = true,
    ): ?array {
        if (! $allowed || ! Route::has($routeName)) {
            return null;
        }

        return [
            'label' => $label,
            'icon' => $icon,
            'route' => $routeName,
            'active' => $activePattern,
        ];
    };

    $companyItems = array_values(array_filter([
        $menuItem(
            'ព័ត៌មានក្រុមហ៊ុន',
            'building-office',
            'company.settings',
            'company.*',
            $user?->can('company.view') ?? false,
        ),
        $menuItem(
            'សាខា',
            'map-pin',
            'branches.index',
            'branches.*',
            $user?->can('branch.view') ?? false,
        ),
        $menuItem(
            'ផ្នែក',
            'squares-2x2',
            'departments.index',
            'departments.*',
            $user?->can('department.view') ?? false,
        ),
        $menuItem(
            'មុខតំណែង',
            'briefcase',
            'positions.index',
            'positions.*',
            $user?->can('position.view') ?? false,
        ),
        $menuItem(
            'ប្រភេទការងារ',
            'identification',
            'employment-types.index',
            'employment-types.*',
            $user?->can('employment-type.view') ?? false,
        ),
    ]));

    $attendanceAllowed = ($user?->can('attendance.checkin') ?? false)
        || ($user?->can('attendance.checkout') ?? false);

    $employeeItems = array_values(array_filter([
        $menuItem(
            'កិច្ចសន្យារបស់ខ្ញុំ',
            'document-text',
            'contracts.mine',
            'contracts.mine',
            $user?->can('contract.view-own') ?? false,
        ),
        $menuItem(
            'គ្រប់គ្រងកិច្ចសន្យា',
            'document-check',
            'contracts.index',
            'contracts.*',
            $user?->can('contract.view') ?? false,
        ),
        $menuItem(
            'បញ្ជីបុគ្គលិក',
            'users',
            'employees.index',
            'employees.*',
            $user?->can('employee.view') ?? false,
        ),
        $menuItem(
            'វេនការងារ',
            'clock',
            'work-shifts.index',
            'work-shifts.*',
            $user?->can('shift.view') ?? false,
        ),
        $menuItem(
            'កាលវិភាគការងារ',
            'calendar-days',
            'schedules.index',
            'schedules.*',
            $user?->can('schedule.view') ?? false,
        ),
        $menuItem(
            'ចុះវត្តមាន',
            'check-circle',
            'attendance.checkinout',
            'attendance.checkinout',
            $attendanceAllowed,
        ),
        $menuItem(
            'ស្កេន QR វត្តមាន',
            'qr-code',
            'attendance.scan',
            'attendance.scan',
            $attendanceAllowed,
        ),
        $menuItem(
            'បង្ហាញ QR វត្តមាន',
            'device-phone-mobile',
            'attendance.qr.display',
            'attendance.qr.display',
            ($user?->can('attendance.approve') ?? false)
                || ($user?->can('attendance.report') ?? false),
        ),
        $menuItem(
            'ស្នើកែតម្រូវវត្តមាន',
            'clipboard-document',
            'attendance.corrections.request',
            'attendance.corrections.request',
            $user?->can('attendance.correction.request') ?? false,
        ),
        $menuItem(
            'ពិនិត្យកែតម្រូវវត្តមាន',
            'clipboard-document-check',
            'attendance.corrections.review',
            'attendance.corrections.review',
            $user?->can('attendance.approve') ?? false,
        ),
        $menuItem(
            'របាយការណ៍វត្តមាន',
            'chart-bar',
            'attendance.reports.index',
            'attendance.reports.*',
            $user?->can('attendance.report') ?? false,
        ),
    ]));

    $leaveItems = array_values(array_filter([
        $menuItem(
            'ប្រភេទការឈប់សម្រាក',
            'calendar-days',
            'leave.types.index',
            'leave.types.*',
            $user?->can('leave.manage') ?? false,
        ),
        $menuItem(
            'សំណើរបស់ខ្ញុំ',
            'document-text',
            'leave.requests.index',
            'leave.requests.index',
            $user?->can('leave.request') ?? false,
        ),
        $menuItem(
            'ពិនិត្យសំណើឈប់សម្រាក',
            'clipboard-document-check',
            'leave.requests.review',
            'leave.requests.review',
            $user?->can('leave.approve') ?? false,
        ),
        $menuItem(
            'សមតុល្យការឈប់សម្រាក',
            'chart-bar',
            'leave.balances.index',
            'leave.balances.*',
            ($user?->can('leave.report') ?? false)
                || ($user?->can('leave.manage') ?? false),
        ),
    ]));

    $payrollItems = array_values(array_filter([
        $menuItem(
            'ប្រាក់ខែរបស់ខ្ញុំ',
            'wallet',
            'payroll.my-payslips',
            'payroll.my-payslips',
            $user?->can('payroll.view-own') ?? false,
        ),
        $menuItem(
            'គ្រប់គ្រងបញ្ជីប្រាក់ខែ',
            'banknotes',
            'payroll.periods.index',
            'payroll.periods.*',
            $user?->can('payroll.view') ?? false,
        ),
        $menuItem(
            'គោលការណ៍ប្រាក់ខែ',
            'adjustments-horizontal',
            'payroll.settings',
            'payroll.settings',
            $user?->can('payroll.view') ?? false,
        ),
        $menuItem(
            'ពិនិត្យប្រាក់ខែ និងម៉ោងបន្ថែម',
            'clipboard-document-check',
            'payroll.review',
            'payroll.review',
            $user?->can('payroll.approve') ?? false,
        ),
        $menuItem(
            'ពន្ធ និង ប.ស.ស. បុគ្គលិក',
            'identification',
            'payroll.statutory-profiles',
            'payroll.statutory-profiles',
            $user?->can('payroll.approve') ?? false,
        ),
        $menuItem(
            'របាយការណ៍ប្រាក់ខែ',
            'chart-bar-square',
            'payroll.reports',
            'payroll.reports',
            $user?->can('payroll.report') ?? false,
        ),
    ]));

    $performanceItems = array_values(array_filter([
        $menuItem(
            'ការវាយតម្លៃរបស់ខ្ញុំ',
            'star',
            'performance.my-reviews',
            'performance.my-reviews',
            $user?->can('performance.view-own') ?? false,
        ),
        $menuItem(
            'ការវាយតម្លៃការងារ',
            'clipboard-document-check',
            'performance.reviews',
            'performance.reviews',
            $user?->can('performance.view') ?? false,
        ),
        $menuItem(
            'គោលដៅរបស់ខ្ញុំ',
            'flag',
            'performance.my-goals',
            'performance.my-goals',
            $user?->can('performance.view-own') ?? false,
        ),
        $menuItem(
            'គោលដៅបុគ្គលិក',
            'trophy',
            'performance.goals',
            'performance.goals',
            $user?->can('performance.view') ?? false,
        ),
        $menuItem(
            'គំរូសូចនាករ KPI',
            'chart-bar-square',
            'performance.kpi-templates',
            'performance.kpi-templates',
            $user?->can('performance.manage-goals') ?? false,
        ),
    ]));

    $operationsItems = array_values(array_filter([
        $menuItem('ព័ត៌មានក្រុមហ៊ុន','megaphone','announcements.feed','announcements.feed',$user?->can('announcement.view') ?? false),
        $menuItem('កិច្ចការរបស់ខ្ញុំ','check-circle','tasks.mine','tasks.mine',$user?->can('task.view-own') ?? false),
        $menuItem('គ្រប់គ្រងកិច្ចការ','clipboard-document-list','tasks.index','tasks.index',$user?->can('task.view') ?? false),
        $menuItem('ការជ្រើសរើសបុគ្គលិក','user-plus','recruitment.pipeline','recruitment.*',$user?->can('recruitment.view') ?? false),
        $menuItem('វគ្គរបស់ខ្ញុំ','academic-cap','training.mine','training.mine',$user?->can('training.view-own') ?? false),
        $menuItem('គ្រប់គ្រងការបណ្តុះបណ្តាល','book-open','training.index','training.index',$user?->can('training.view') ?? false),
        $menuItem('ទ្រព្យរបស់ខ្ញុំ','computer-desktop','assets.mine','assets.mine',$user?->can('asset.view-own') ?? false),
        $menuItem('គ្រប់គ្រងទ្រព្យ','archive-box','assets.index','assets.index',$user?->can('asset.view') ?? false),
        $menuItem('ចំណាយរបស់ខ្ញុំ','receipt-percent','expenses.mine','expenses.mine',$user?->can('expense.view-own') ?? false),
        $menuItem('ពិនិត្យសំណងចំណាយ','currency-dollar','expenses.index','expenses.index',$user?->can('expense.view') ?? false),
        $menuItem('គ្រប់គ្រងសេចក្តីជូនដំណឹង','speaker-wave','announcements.index','announcements.index',$user?->can('announcement.manage') ?? false),
    ]));

    $settingsItems = array_values(array_filter([
        $menuItem(
            'អ្នកប្រើប្រាស់',
            'user-group',
            'users.index',
            'users.*',
            $user?->can('user.manage') ?? false,
        ),
        $menuItem(
            'តួនាទី និងសិទ្ធិ',
            'shield-check',
            'roles.index',
            'roles.*',
            $user?->can('role.manage') ?? false,
        ),
        $menuItem(
            'កំណត់ត្រាសកម្មភាព',
            'document-magnifying-glass',
            'audit-logs.index',
            'audit-logs.*',
            $user?->can('audit.view') ?? false,
        ),
    ]));

    $groups = array_values(array_filter([
        count($companyItems) > 0 ? [
            'key' => 'company',
            'heading' => 'រចនាសម្ព័ន្ធក្រុមហ៊ុន',
            'items' => $companyItems,
        ] : null,
        count($employeeItems) > 0 ? [
            'key' => 'employees',
            'heading' => 'បុគ្គលិក និងពេលវេលា',
            'items' => $employeeItems,
        ] : null,
        count($leaveItems) > 0 ? [
            'key' => 'leave',
            'heading' => 'ការឈប់សម្រាក',
            'items' => $leaveItems,
        ] : null,
        count($payrollItems) > 0 ? [
            'key' => 'payroll',
            'heading' => 'ប្រាក់បៀវត្ស',
            'items' => $payrollItems,
        ] : null,
        count($performanceItems) > 0 ? [
            'key' => 'performance',
            'heading' => 'ការអភិវឌ្ឍបុគ្គលិក',
            'items' => $performanceItems,
        ] : null,
        count($operationsItems) > 0 ? [
            'key' => 'operations',
            'heading' => 'ប្រតិបត្តិការ HR',
            'items' => $operationsItems,
        ] : null,
        count($settingsItems) > 0 ? [
            'key' => 'settings',
            'heading' => 'ការកំណត់',
            'items' => $settingsItems,
        ] : null,
    ]));

    $activeGroup = match (true) {
        request()->routeIs(
            'company.*',
            'branches.*',
            'departments.*',
            'positions.*',
            'employment-types.*',
        ) => 'company',

        request()->routeIs(
            'employees.*',
            'work-shifts.*',
            'schedules.*',
            'attendance.*',
            'contracts.*',
        ) => 'employees',

        request()->routeIs('leave.*') => 'leave',
        request()->routeIs('payroll.*') => 'payroll',
        request()->routeIs('performance.*') => 'performance',
        request()->routeIs('tasks.*', 'recruitment.*', 'training.*', 'assets.*', 'expenses.*', 'announcements.*') => 'operations',
        request()->routeIs('users.*', 'roles.*', 'audit-logs.*') => 'settings',
        default => null,
    };

    $openGroups = collect($groups)
        ->mapWithKeys(
            fn (array $group) => [
                $group['key'] => $group['key'] === $activeGroup,
            ]
        )
        ->all();

    $dashboardUrl = Route::has('dashboard')
        ? route('dashboard')
        : url('/');

    $profileRoute = Route::has('settings.profile')
        ? 'settings.profile'
        : (Route::has('profile.edit') ? 'profile.edit' : null);
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-zinc-100 dark:bg-zinc-950">
    <flux:sidebar
        sticky
        collapsible="mobile"
        class="border-e border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900"
    >
        <flux:sidebar.header>
            <x-app-logo
                :sidebar="true"
                href="{{ $dashboardUrl }}"
                wire:navigate
            />

            <flux:sidebar.collapse class="lg:hidden"/>
        </flux:sidebar.header>

        <flux:sidebar.nav
            x-data="{
                open: $persist(@js($openGroups))
                    .as('bizhr-sidebar-open-groups-v2')
            }"
            x-init="
                @if ($activeGroup)
                    open.{{ $activeGroup }} = true
                @endif
            "
        >
            @if (Route::has('dashboard'))
                <flux:sidebar.group heading="ការគ្រប់គ្រង" class="grid">
                    <flux:sidebar.item
                        icon="home"
                        :href="route('dashboard')"
                        :current="request()->routeIs('dashboard')"
                        wire:navigate
                    >
                        ផ្ទាំងគ្រប់គ្រង
                    </flux:sidebar.item>
                </flux:sidebar.group>
            @endif

            @foreach ($groups as $group)
                <div wire:key="sidebar-group-{{ $group['key'] }}">
                    <button
                        type="button"
                        x-on:click="
                            open.{{ $group['key'] }}
                                = !open.{{ $group['key'] }}
                        "
                        class="group mb-0.5 flex h-9 w-full items-center gap-2 rounded-lg px-2 text-xs font-medium tracking-wide text-zinc-500 uppercase hover:bg-zinc-800/5 hover:text-zinc-800 dark:text-white/60 dark:hover:bg-white/[7%] dark:hover:text-white"
                    >
                        <flux:icon.chevron-right
                            class="size-3! shrink-0 transition-transform duration-150"
                            x-bind:class="
                                open.{{ $group['key'] }}
                                    ? 'rotate-90'
                                    : ''
                            "
                        />

                        <span>{{ $group['heading'] }}</span>
                    </button>

                    <div
                        x-show="open.{{ $group['key'] }}"
                        x-cloak
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        class="relative grid gap-0.5 ps-5"
                    >
                        <div class="absolute inset-y-1 start-0 ms-2 w-px bg-zinc-200 dark:bg-white/10"></div>

                        @foreach ($group['items'] as $item)
                            <flux:sidebar.item
                                :icon="$item['icon']"
                                :href="route($item['route'])"
                                :current="request()->routeIs(
                                    $item['active']
                                )"
                                wire:navigate
                            >
                                {{ $item['label'] }}
                            </flux:sidebar.item>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </flux:sidebar.nav>

        <flux:sidebar.spacer/>

        <x-desktop-user-menu
            class="hidden lg:block"
            :name="$displayName"
        />
    </flux:sidebar>

    <flux:header class="lg:hidden">
        <flux:sidebar.toggle
            class="lg:hidden"
            icon="bars-2"
            inset="left"
        />

        <flux:spacer/>

        <flux:dropdown position="top" align="end">
            <flux:profile
                :initials="$user->initials()"
                icon-trailing="chevron-down"
            />

            <flux:menu>
                <div class="p-2">
                    <div class="flex items-center gap-2">
                        <flux:avatar
                            :name="$displayName"
                            :initials="$user->initials()"
                        />

                        <div class="min-w-0">
                            <div class="truncate text-sm font-semibold">
                                {{ $displayName }}
                            </div>

                            <div class="truncate text-xs text-zinc-500">
                                {{ $user->email }}
                            </div>
                        </div>
                    </div>
                </div>

                @if ($profileRoute)
                    <flux:menu.separator/>

                    <flux:menu.item
                        :href="route($profileRoute)"
                        icon="cog"
                        wire:navigate
                    >
                        ការកំណត់គណនី
                    </flux:menu.item>
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
