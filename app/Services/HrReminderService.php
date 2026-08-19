<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\EmploymentContract;
use App\Models\Notification;
use App\Models\User;
use Carbon\CarbonImmutable;

class HrReminderService
{
    private const MILESTONES = [30, 7, 1];

    public function __construct(private readonly NotificationService $notifications) {}

    public function run(): int
    {
        $sent = 0;

        foreach (self::MILESTONES as $days) {
            $date = CarbonImmutable::today()->addDays($days);
            $sent += $this->remindContractExpiries($date, $days);
            $sent += $this->remindDocumentExpiries($date, $days);
            $sent += $this->remindProbationExpiries($date, $days);
        }

        return $sent;
    }

    private function remindContractExpiries(CarbonImmutable $date, int $days): int
    {
        $sent = 0;

        EmploymentContract::query()
            ->with('employee:id,company_id,user_id,employee_code,first_name,last_name')
            ->whereIn('status', ['active', 'expiring'])
            ->whereDate('end_date', $date)
            ->chunkById(100, function ($contracts) use ($days, &$sent): void {
                foreach ($contracts as $contract) {
                    $employee = $contract->employee;
                    if ($employee === null) {
                        continue;
                    }

                    $name = $this->employeeName($employee);
                    $message = "Contract {$contract->contract_number} for {$name} expires in {$days} day".($days === 1 ? '' : 's').'.';
                    $link = '/employment-contracts';

                    if ($employee->user_id !== null && $this->notifyOnceToday(
                        (int) $employee->user_id,
                        (int) $employee->company_id,
                        'contract_expiry',
                        'Contract expiry reminder',
                        $message,
                        $link,
                        'file-contract',
                        'warning',
                    )) {
                        $sent++;
                    }

                    foreach ($this->hrUserIds((int) $employee->company_id) as $userId) {
                        if ($this->notifyOnceToday(
                            $userId,
                            (int) $employee->company_id,
                            'contract_expiry',
                            'Contract expiry reminder',
                            $message,
                            $link,
                            'file-contract',
                            'warning',
                        )) {
                            $sent++;
                        }
                    }
                }
            });

        return $sent;
    }

    private function remindDocumentExpiries(CarbonImmutable $date, int $days): int
    {
        $sent = 0;

        EmployeeDocument::query()
            ->with('employee:id,company_id,user_id,employee_code,first_name,last_name')
            ->where('status', '!=', 'revoked')
            ->whereDate('expiry_date', $date)
            ->chunkById(100, function ($documents) use ($days, &$sent): void {
                foreach ($documents as $document) {
                    $employee = $document->employee;
                    if ($employee === null) {
                        continue;
                    }

                    $documentName = str_replace('_', ' ', $document->document_type ?: 'employee document');
                    $message = ucfirst($documentName).' for '.$this->employeeName($employee)." expires in {$days} day".($days === 1 ? '' : 's').'.';
                    $link = route('employees.documents.index', $employee, false);

                    if ($employee->user_id !== null && $this->notifyOnceToday(
                        (int) $employee->user_id,
                        (int) $employee->company_id,
                        'document_expiry',
                        'Document expiry reminder',
                        $message,
                        $link,
                        'file-circle-exclamation',
                        'warning',
                    )) {
                        $sent++;
                    }

                    foreach ($this->hrUserIds((int) $employee->company_id) as $userId) {
                        if ($this->notifyOnceToday(
                            $userId,
                            (int) $employee->company_id,
                            'document_expiry',
                            'Document expiry reminder',
                            $message,
                            $link,
                            'file-circle-exclamation',
                            'warning',
                        )) {
                            $sent++;
                        }
                    }
                }
            });

        return $sent;
    }

    private function remindProbationExpiries(CarbonImmutable $date, int $days): int
    {
        $sent = 0;

        Employee::query()
            ->where('is_active', true)
            ->where('employment_status', 'On probation')
            ->whereDate('probation_end_date', $date)
            ->chunkById(100, function ($employees) use ($days, &$sent): void {
                foreach ($employees as $employee) {
                    $message = 'Probation for '.$this->employeeName($employee)." ends in {$days} day".($days === 1 ? '' : 's').'. Review confirmation or extension before the deadline.';
                    $link = route('employees.show', $employee, false);

                    if ($employee->user_id !== null && $this->notifyOnceToday(
                        (int) $employee->user_id,
                        (int) $employee->company_id,
                        'probation_expiry',
                        'Probation ending soon',
                        $message,
                        $link,
                        'user-clock',
                        'warning',
                    )) {
                        $sent++;
                    }

                    foreach ($this->hrUserIds((int) $employee->company_id) as $userId) {
                        if ($this->notifyOnceToday(
                            $userId,
                            (int) $employee->company_id,
                            'probation_expiry',
                            'Probation ending soon',
                            $message,
                            $link,
                            'user-clock',
                            'warning',
                        )) {
                            $sent++;
                        }
                    }
                }
            });

        return $sent;
    }

    /** @return list<int> */
    private function hrUserIds(int $companyId): array
    {
        return User::query()
            ->where('is_active', true)
            ->whereHas('employee', fn ($query) => $query->where('company_id', $companyId))
            ->whereHas('roles', fn ($query) => $query->whereIn('name', ['HR Administrator', 'Owner']))
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->values()
            ->all();
    }

    private function notifyOnceToday(
        int $userId,
        int $companyId,
        string $type,
        string $title,
        string $message,
        string $link,
        string $icon,
        string $level,
    ): bool {
        $alreadySent = Notification::query()
            ->where('user_id', $userId)
            ->where('company_id', $companyId)
            ->where('type', $type)
            ->where('message', $message)
            ->whereDate('created_at', today())
            ->exists();

        if ($alreadySent) {
            return false;
        }

        $this->notifications->notify(
            $userId,
            $type,
            $title,
            $message,
            $link,
            $icon,
            $level,
            ['source' => 'hr_reminder'],
            $companyId,
        );

        return true;
    }

    private function employeeName(Employee $employee): string
    {
        $name = trim($employee->first_name.' '.$employee->last_name);

        return $name !== '' ? $name : $employee->employee_code;
    }
}
