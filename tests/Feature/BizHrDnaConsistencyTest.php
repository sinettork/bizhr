<?php

it('keeps daily use workspaces on the compact BizHR surface language', function (): void {
    $views = [
        resource_path('views/attendance/index.blade.php'),
        resource_path('views/attendance/corrections/review.blade.php'),
        resource_path('views/leave/requests/index.blade.php'),
        resource_path('views/leave/requests/review.blade.php'),
        resource_path('views/tasks/index.blade.php'),
        resource_path('views/tasks/mine.blade.php'),
        resource_path('views/payroll/payslips/index.blade.php'),
        resource_path('views/payroll/review/index.blade.php'),
    ];

    foreach ($views as $view) {
        $content = file_get_contents($view);

        expect($content)->not->toBeFalse()
            ->and($content)->not->toContain('shadow-sm')
            ->and($content)->not->toContain('display-6');
    }
});

it('renders workspace context and uses one modal confirmation flow', function (): void {
    $commandBar = file_get_contents(resource_path('views/components/workspace-command-bar.blade.php'));
    $appJs = file_get_contents(public_path('js/app.js'));
    $confirmationJs = file_get_contents(public_path('js/confirmation-dialog.js'));

    expect($commandBar)->toContain('workspace-command-context')
        ->and($appJs)->not->toContain('window.confirm(')
        ->and($appJs)->not->toContain("querySelectorAll('form[data-confirm]')")
        ->and($confirmationJs)->toContain("form.matches('[data-confirm]')")
        ->and($confirmationJs)->not->toContain('nativeConfirm');
});

it('keeps employee profile navigation aware of self service context', function (): void {
    $profile = file_get_contents(resource_path('views/employees/show.blade.php'));

    expect($profile)->toContain('$isOwnProfile')
        ->and($profile)->toContain('Personal Workspace')
        ->and($profile)->toContain("route('dashboard')")
        ->and($profile)->toContain('People & Directory');
});

it('keeps the white utility topbar isolated from legacy navbar rules', function (): void {
    $shell = file_get_contents(public_path('css/app-shell.css'));

    expect($shell)->toContain('background-image: none !important;')
        ->and($shell)->toContain('.app-topbar.app-navbar .nav-link')
        ->and($shell)->toContain('.workspace-summary');
});
