<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\Employee;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

class AssetWorkflowService
{
    public function assign(Asset $asset, Employee $employee, User $actor, string $condition, ?string $dueDate): AssetAssignment
    {
        if ($asset->status !== 'available') {
            throw new DomainException('Only an available asset can be assigned.');
        }

        return DB::transaction(function () use ($asset, $employee, $actor, $condition, $dueDate) {
            $asset = Asset::query()->lockForUpdate()->findOrFail($asset->id);
            if ($asset->status !== 'available') throw new DomainException('Only an available asset can be assigned.');
            if ($asset->company_id !== $employee->company_id) throw new DomainException('Asset and employee must belong to the same company.');
            $assignment = AssetAssignment::create([
                'asset_id' => $asset->id, 'employee_id' => $employee->id,
                'assigned_date' => today(), 'expected_return_date' => $dueDate,
                'condition_out' => $condition, 'status' => 'assigned', 'assigned_by' => $actor->id,
            ]);
            $asset->update(['status' => 'assigned', 'condition' => $condition]);
            return $assignment;
        });
    }

    public function receive(AssetAssignment $assignment, User $actor, string $condition, ?string $note): AssetAssignment
    {
        return DB::transaction(function () use ($assignment, $actor, $condition, $note) {
            $assignment = AssetAssignment::query()->lockForUpdate()->findOrFail($assignment->id);
            if ($assignment->status !== 'assigned') throw new DomainException('This assignment is already closed.');
            $assignment->update(['status' => 'returned', 'returned_date' => today(), 'condition_in' => $condition, 'notes' => trim((string) $note) ?: null, 'received_by' => $actor->id]);
            $assignment->asset->update(['status' => in_array($condition, ['lost', 'retired'], true) ? $condition : 'available', 'condition' => $condition]);
            return $assignment->refresh();
        });
    }
}
