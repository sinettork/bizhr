<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use DateTimeInterface;
use Illuminate\Support\Facades\Auth;

class NotificationService
{
    /** @param array<string, mixed>|null $metadata */
    public function notify(
        int $userId,
        string $type,
        string $title,
        string $message,
        ?string $link = null,
        ?string $icon = null,
        string $level = 'info',
        ?array $metadata = null,
        ?int $companyId = null,
        ?DateTimeInterface $expiresAt = null,
    ): Notification {
        /** @var User|null $actor */
        $actor = Auth::user();

        return Notification::create([
            'user_id' => $userId,
            'company_id' => $companyId ?? $actor?->companyId(),
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'link' => $link,
            'icon' => $icon,
            'level' => $level,
            'metadata' => $metadata,
            'expires_at' => $expiresAt,
        ]);
    }

    /**
     * @param list<int> $userIds
     * @param array<string, mixed>|null $metadata
     */
    public function notifyMany(
        array $userIds,
        string $type,
        string $title,
        string $message,
        ?string $link = null,
        ?string $icon = null,
        string $level = 'info',
        ?array $metadata = null,
        ?int $companyId = null,
        ?DateTimeInterface $expiresAt = null,
    ): void {
        foreach ($userIds as $userId) {
            $this->notify($userId, $type, $title, $message, $link, $icon, $level, $metadata, $companyId, $expiresAt);
        }
    }

    /** @param array<string, mixed>|null $metadata */
    public function notifyCompany(
        int $companyId,
        string $type,
        string $title,
        string $message,
        ?string $link = null,
        ?string $icon = null,
        string $level = 'info',
        ?array $metadata = null,
        ?DateTimeInterface $expiresAt = null,
    ): void {
        $userIds = User::query()
            ->whereHas('employee', fn ($query) => $query->where('company_id', $companyId))
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->all();

        $this->notifyMany($userIds, $type, $title, $message, $link, $icon, $level, $metadata, $companyId, $expiresAt);
    }

    public function assetTransferred(int $toUserId, string $assetName, string $fromUserName): void
    {
        $this->notify($toUserId, 'asset_transfer', 'Asset Transferred', "Asset '{$assetName}' has been transferred to you by {$fromUserName}.", '/assets', 'box-arrow-right', 'info');
    }

    public function assetLost(int $managerId, string $assetName, string $employeeName): void
    {
        $this->notify($managerId, 'asset_lost', 'Asset Reported Lost', "Asset '{$assetName}' assigned to {$employeeName} has been reported as lost.", '/assets', 'exclamation-triangle', 'warning');
    }

    public function assetRetired(int $managerId, string $assetName): void
    {
        $this->notify($managerId, 'asset_retired', 'Asset Retired', "Asset '{$assetName}' has been retired from service.", '/assets', 'archive', 'info');
    }

    public function sessionRevoked(int $userId, string $reason): void
    {
        $this->notify($userId, 'session_revoked', 'Session Revoked', "Your session has been revoked. Reason: {$reason}", null, 'shield-x', 'warning');
    }

    public function documentVerified(int $userId, string $documentType): void
    {
        $this->notify($userId, 'document_verified', 'Document Verified', "Your {$documentType} document has been verified.", '/documents', 'check-circle', 'success');
    }

    public function documentRevoked(int $userId, string $documentType, string $reason): void
    {
        $this->notify($userId, 'document_revoked', 'Document Revoked', "Your {$documentType} document has been revoked. Reason: {$reason}", '/documents', 'x-circle', 'error');
    }

    public function contractApproved(int $userId, string $contractNumber): void
    {
        $this->notify($userId, 'contract_approved', 'Contract Approved', "Contract {$contractNumber} has been approved.", '/contracts', 'file-check', 'success');
    }

    public function expenseApproved(int $userId, string $category, float $amount): void
    {
        $this->notify($userId, 'expense_approved', 'Expense Approved', "Your expense claim for {$category} ({$amount}) has been approved.", '/expenses', 'check-circle', 'success');
    }

    public function expenseRejected(int $userId, string $category, string $reason): void
    {
        $this->notify($userId, 'expense_rejected', 'Expense Rejected', "Your expense claim for {$category} has been rejected. Reason: {$reason}", '/expenses', 'x-circle', 'error');
    }

    public function leaveRequestSubmitted(int $managerId, string $employeeName, string $leaveType, DateTimeInterface $startDate): void
    {
        $this->notify($managerId, 'leave_request', 'Leave Request Submitted', "{$employeeName} has requested {$leaveType} leave starting {$startDate->format('Y-m-d')}.", '/leave-requests', 'calendar-check', 'info');
    }

    public function payrollPaid(int $userId, string $periodName, float $amount): void
    {
        $this->notify($userId, 'payroll_paid', 'Payroll Paid', "Your payroll for {$periodName} ({$amount}) has been processed.", '/payroll', 'cash-coin', 'success');
    }
}
