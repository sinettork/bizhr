<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class NotificationService
{
    /**
     * Create a notification for a user
     */
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
        ?\DateTime $expiresAt = null
    ): Notification {
        return Notification::create([
            'user_id' => $userId,
            'company_id' => $companyId ?? Auth::user()?->employee?->company_id,
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
     * Notify multiple users
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
        ?\DateTime $expiresAt = null
    ): void {
        foreach ($userIds as $userId) {
            $this->notify($userId, $type, $title, $message, $link, $icon, $level, $metadata, $companyId, $expiresAt);
        }
    }

    /**
     * Notify all users in a company
     */
    public function notifyCompany(
        int $companyId,
        string $type,
        string $title,
        string $message,
        ?string $link = null,
        ?string $icon = null,
        string $level = 'info',
        ?array $metadata = null,
        ?\DateTime $expiresAt = null
    ): void {
        $userIds = User::whereHas('employee', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })->pluck('id')->toArray();

        $this->notifyMany($userIds, $type, $title, $message, $link, $icon, $level, $metadata, $companyId, $expiresAt);
    }

    /**
     * Asset transfer notification
     */
    public function assetTransferred(int $toUserId, string $assetName, string $fromUserName): void
    {
        $this->notify(
            $toUserId,
            'asset_transfer',
            'Asset Transferred',
            "Asset '{$assetName}' has been transferred to you by {$fromUserName}.",
            '/assets',
            'box-arrow-right',
            'info'
        );
    }

    /**
     * Asset lost notification
     */
    public function assetLost(int $managerId, string $assetName, string $employeeName): void
    {
        $this->notify(
            $managerId,
            'asset_lost',
            'Asset Reported Lost',
            "Asset '{$assetName}' assigned to {$employeeName} has been reported as lost.",
            '/assets',
            'exclamation-triangle',
            'warning'
        );
    }

    /**
     * Asset retired notification
     */
    public function assetRetired(int $managerId, string $assetName): void
    {
        $this->notify(
            $managerId,
            'asset_retired',
            'Asset Retired',
            "Asset '{$assetName}' has been retired from service.",
            '/assets',
            'archive',
            'info'
        );
    }

    /**
     * Session revoked notification
     */
    public function sessionRevoked(int $userId, string $reason): void
    {
        $this->notify(
            $userId,
            'session_revoked',
            'Session Revoked',
            "Your session has been revoked. Reason: {$reason}",
            null,
            'shield-x',
            'warning'
        );
    }

    /**
     * Document verification notification
     */
    public function documentVerified(int $userId, string $documentType): void
    {
        $this->notify(
            $userId,
            'document_verified',
            'Document Verified',
            "Your {$documentType} document has been verified.",
            '/documents',
            'check-circle',
            'success'
        );
    }

    /**
     * Document revoked notification
     */
    public function documentRevoked(int $userId, string $documentType, string $reason): void
    {
        $this->notify(
            $userId,
            'document_revoked',
            'Document Revoked',
            "Your {$documentType} document has been revoked. Reason: {$reason}",
            '/documents',
            'x-circle',
            'error'
        );
    }

    /**
     * Contract approval notification
     */
    public function contractApproved(int $userId, string $contractNumber): void
    {
        $this->notify(
            $userId,
            'contract_approved',
            'Contract Approved',
            "Contract {$contractNumber} has been approved.",
            '/contracts',
            'file-check',
            'success'
        );
    }

    /**
     * Expense approval notification
     */
    public function expenseApproved(int $userId, string $category, float $amount): void
    {
        $this->notify(
            $userId,
            'expense_approved',
            'Expense Approved',
            "Your expense claim for {$category} ({$amount}) has been approved.",
            '/expenses',
            'check-circle',
            'success'
        );
    }

    /**
     * Expense rejection notification
     */
    public function expenseRejected(int $userId, string $category, string $reason): void
    {
        $this->notify(
            $userId,
            'expense_rejected',
            'Expense Rejected',
            "Your expense claim for {$category} has been rejected. Reason: {$reason}",
            '/expenses',
            'x-circle',
            'error'
        );
    }

    /**
     * Leave request notification
     */
    public function leaveRequestSubmitted(int $managerId, string $employeeName, string $leaveType, \DateTime $startDate): void
    {
        $this->notify(
            $managerId,
            'leave_request',
            'Leave Request Submitted',
            "{$employeeName} has requested {$leaveType} leave starting {$startDate->format('Y-m-d')}.",
            '/leave-requests',
            'calendar-check',
            'info'
        );
    }

    /**
     * Payroll payment notification
     */
    public function payrollPaid(int $userId, string $periodName, float $amount): void
    {
        $this->notify(
            $userId,
            'payroll_paid',
            'Payroll Paid',
            "Your payroll for {$periodName} ({$amount}) has been processed.",
            '/payroll',
            'cash-coin',
            'success'
        );
    }
}
