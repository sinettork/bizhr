<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetAssignment extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'assigned_date' => 'date',
            'expected_return_date' => 'date',
            'returned_date' => 'date',
            'transferred_at' => 'date',
            'is_lost' => 'boolean',
            'lost_at' => 'date',
            'is_retired' => 'boolean',
            'retired_at' => 'date',
        ];
    }

    /** @return BelongsTo<Asset, $this> */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    /** @return BelongsTo<Employee, $this> */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /** @return BelongsTo<Employee, $this> */
    public function transferredToEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'transferred_to_employee_id');
    }

    /** @return BelongsTo<User, $this> */
    public function transferredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'transferred_by');
    }

    /** @return BelongsTo<User, $this> */
    public function lostReportedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lost_reported_by');
    }

    /** @return BelongsTo<User, $this> */
    public function retiredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'retired_by');
    }
}
