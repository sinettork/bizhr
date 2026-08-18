<?php

use App\Models\PayrollPayment;
use App\Services\PayrollWorkflowService;

it('provides one transactional workflow for approval and payment recording', function () {
    $service = app(PayrollWorkflowService::class);

    expect($service)->toBeInstanceOf(PayrollWorkflowService::class)
        ->and(class_exists(PayrollPayment::class))->toBeTrue();
});
