<?php

namespace App\Services;

use App\Models\AssetAssignment;
use App\Models\Employee;
use App\Models\EmploymentContract;
use App\Models\ExpenseClaim;
use App\Models\LeaveRequest;
use App\Models\PayrollItem;
use App\Models\Task;

class EmployeeOffboardingReadinessService
{
    /**
     * @return array{
     *     ready: bool,
     *     outstanding: int,
     *     items: list<array{key: string, label: string, count: int, blocking: bool, message: string}>
     * }
     */
    public function forEmployee(Employee $employee): array
    {
        $companyId = (int) $employee->company_id;

        $assignedAssets = AssetAssignment::query()
            ->where('employee_id', $employee->id)
            ->whereNull('returned_date')
            ->whereNull('transferred_at')
            ->where('is_lost', false)
            ->where('is_retired', false)
            ->whereHas('asset', fn ($query) => $query->where('company_id', $companyId))
            ->count();

        $openTasks = Task::query()
            ->where('company_id', $companyId)
            ->where('assigned_to', $employee->id)
            ->whereNotIn('status', ['verified', 'cancelled'])
            ->count();

        $unsettledExpenses = ExpenseClaim::query()
            ->where('employee_id', $employee->id)
            ->whereIn('status', ['pending_manager', 'pending_accounting', 'approved'])
            ->count();

        $activeContracts = EmploymentContract::query()
            ->where('company_id', $companyId)
            ->where('employee_id', $employee->id)
            ->whereIn('status', ['active', 'expiring', 'pending_approval'])
            ->count();

        $openLeave = LeaveRequest::query()
            ->where('employee_id', $employee->id)
            ->whereIn('status', ['pending', 'manager_approved', 'approved'])
            ->whereDate('end_date', '>=', today())
            ->count();

        $unpaidPayroll = PayrollItem::query()
            ->where('employee_id', $employee->id)
            ->where(function ($query): void {
                $query->whereNull('payment_status')
                    ->orWhere('payment_status', '!=', 'paid');
            })
            ->whereHas('period', fn ($query) => $query
                ->where('company_id', $companyId)
                ->whereIn('status', ['approved', 'paid', 'closed']))
            ->count();

        $items = [
            [
                'key' => 'assets',
                'label' => 'Assigned assets',
                'count' => $assignedAssets,
                'blocking' => $assignedAssets > 0,
                'message' => $assignedAssets > 0 ? 'Return, transfer, retire, or record lost assets before final offboarding.' : 'No assets are waiting for return.',
            ],
            [
                'key' => 'tasks',
                'label' => 'Open tasks',
                'count' => $openTasks,
                'blocking' => $openTasks > 0,
                'message' => $openTasks > 0 ? 'Reassign, verify, or cancel remaining work.' : 'No open tasks remain.',
            ],
            [
                'key' => 'expenses',
                'label' => 'Unsettled expenses',
                'count' => $unsettledExpenses,
                'blocking' => $unsettledExpenses > 0,
                'message' => $unsettledExpenses > 0 ? 'Finish manager/accounting review and payment before closing the employee file.' : 'No expense claims require settlement.',
            ],
            [
                'key' => 'contracts',
                'label' => 'Active contracts',
                'count' => $activeContracts,
                'blocking' => $activeContracts > 0,
                'message' => $activeContracts > 0 ? 'Review or terminate active/pending employment contracts with the correct effective date.' : 'No active contract requires action.',
            ],
            [
                'key' => 'leave',
                'label' => 'Open leave',
                'count' => $openLeave,
                'blocking' => $openLeave > 0,
                'message' => $openLeave > 0 ? 'Resolve pending or future approved leave before completing separation.' : 'No open or future leave requires action.',
            ],
            [
                'key' => 'payroll',
                'label' => 'Unpaid payroll items',
                'count' => $unpaidPayroll,
                'blocking' => $unpaidPayroll > 0,
                'message' => $unpaidPayroll > 0 ? 'Confirm outstanding payroll payment or final-pay handling.' : 'No finalized payroll item is waiting for payment.',
            ],
        ];

        $outstanding = collect($items)->sum(fn (array $item): int => $item['blocking'] ? 1 : 0);

        return [
            'ready' => $outstanding === 0,
            'outstanding' => $outstanding,
            'items' => $items,
        ];
    }
}
