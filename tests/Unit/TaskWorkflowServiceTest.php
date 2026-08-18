<?php

use App\Models\Task;
use App\Models\User;
use App\Services\TaskWorkflowService;

it('reports task progress only within zero and one hundred percent', function () {
    expect(fn () => app(TaskWorkflowService::class)->updateProgress(new Task, new User, 101, null))->toThrow(DomainException::class);
});
