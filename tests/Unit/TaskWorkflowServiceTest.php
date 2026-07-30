<?php
use App\Services\TaskWorkflowService;
it('reports task progress only within zero and one hundred percent',function(){expect(fn()=>app(TaskWorkflowService::class)->updateProgress(new \App\Models\Task(),new \App\Models\User(),101,null))->toThrow(\DomainException::class);});
