<?php

use App\Models\Asset;
use App\Models\Employee;
use App\Models\ExpenseClaim;
use App\Models\JobApplicant;
use App\Models\User;
use App\Services\AssetWorkflowService;
use App\Services\ExpenseWorkflowService;
use App\Services\RecruitmentWorkflowService;

it('rejects an invalid recruitment stage jump', function () {
    $applicant = new JobApplicant(['status' => 'applied']);
    expect(fn () => app(RecruitmentWorkflowService::class)->moveApplicant($applicant, 'hired'))
        ->toThrow(DomainException::class);
});

it('rejects assignment of an unavailable asset before persistence', function () {
    $asset = new Asset(['status' => 'assigned']);
    expect(fn () => app(AssetWorkflowService::class)->assign($asset, new Employee, new User, 'good', null))
        ->toThrow(DomainException::class);
});

it('rejects paying an expense that has not completed approvals', function () {
    $claim = new ExpenseClaim(['status' => 'pending_manager']);
    expect(fn () => app(ExpenseWorkflowService::class)->markPaid($claim, new User, 'PAY-001'))
        ->toThrow(DomainException::class);
});
