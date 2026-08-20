<?php

namespace App\View\Components;

use App\Models\Employee;
use App\Services\EmployeeOffboardingReadinessService;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class EmployeeOffboardingReadiness extends Component
{
    /** @var array{ready: bool, outstanding: int, items: list<array{key: string, label: string, count: int, blocking: bool, message: string}>} */
    public array $readiness;

    public function __construct(
        public Employee $employee,
        EmployeeOffboardingReadinessService $service,
    ) {
        $this->readiness = $service->forEmployee($employee);
    }

    public function render(): View
    {
        return view('components.employee-offboarding-readiness');
    }
}
