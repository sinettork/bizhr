<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\AuditLog;
use App\Models\Employee;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

class AssetWorkflowService
{
    public function assign(Asset $asset, Employee $employee, User $actor, string $condition, ?string $dueDate): AssetAssignment
    {
        $this->assertActorCompany((int) $asset->company_id, $actor);

        return DB::transaction(function () use ($asset, $employee, $actor, $condition, $dueDate): AssetAssignment {
            $asset = Asset::query()->lockForUpdate()->findOrFail($asset->id);
            $employee = Employee::query()->lockForUpdate()->findOrFail($employee->id);
            $this->assertActorCompany((int) $asset->company_id, $actor);

            if ($asset->status !== 'available') {
                throw new DomainException('Only an available asset can be assigned.');
            }
            if ((int) $asset->company_id !== (int) $employee->company_id) {
                throw new DomainException('Asset and employee must belong to the same company.');
            }
            if (! $employee->is_active || in_array($employee->employment_status, ['Resigned', 'Terminated', 'Retired'], true)) {
                throw new DomainException('Assets can be assigned only to an active employee.');
            }

            $before = $asset->only(['status', 'condition']);
            $assignment = AssetAssignment::query()->create([
                'asset_id' => $asset->id,
                'employee_id' => $employee->id,
                'assigned_date' => today(),
                'expected_return_date' => $dueDate,
                'condition_out' => $condition,
                'status' => 'assigned',
                'assigned_by' => $actor->id,
            ]);
            $asset->update(['status' => 'assigned', 'condition' => $condition]);
            AuditLog::record(
                $asset,
                'assigned',
                $before,
                [
                    'status' => 'assigned',
                    'condition' => $condition,
                    'employee_id' => $employee->id,
                    'assignment_id' => $assignment->id,
                ],
                $actor,
            );

            return $assignment;
        });
    }

    public function receive(AssetAssignment $assignment, User $actor, string $condition, ?string $note): AssetAssignment
    {
        return DB::transaction(function () use ($assignment, $actor, $condition, $note): AssetAssignment {
            $assignment = AssetAssignment::query()->with('asset')->lockForUpdate()->findOrFail($assignment->id);
            $asset = $assignment->asset;
            if ($asset === null) {
                throw new DomainException('The assigned asset no longer exists.');
            }
            $this->assertActorCompany((int) $asset->company_id, $actor);
            if ($assignment->status !== 'assigned') {
                throw new DomainException('This assignment is already closed.');
            }

            $before = [
                'assignment_status' => $assignment->status,
                'asset_status' => $asset->status,
                'condition' => $asset->condition,
            ];
            $assignment->update([
                'status' => 'returned',
                'returned_date' => today(),
                'condition_in' => $condition,
                'notes' => trim((string) $note) ?: null,
                'received_by' => $actor->id,
            ]);
            $asset->update([
                'status' => in_array($condition, ['lost', 'retired'], true) ? $condition : 'available',
                'condition' => $condition,
            ]);
            AuditLog::record(
                $asset,
                'assignment_closed',
                $before,
                [
                    'assignment_status' => 'returned',
                    'asset_status' => $asset->status,
                    'condition' => $condition,
                    'employee_id' => $assignment->employee_id,
                    'assignment_id' => $assignment->id,
                    'received_by' => $actor->id,
                ],
                $actor,
            );

            return $assignment->refresh();
        });
    }

    private function assertActorCompany(int $companyId, User $actor): void
    {
        if ($actor->companyId() !== $companyId) {
            throw new DomainException('The asset does not belong to the actor company.');
        }
    }
}
