<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/** @property Carbon $expires_at */
class JobOffer extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['salary_amount' => 'decimal:2', 'proposed_start_date' => 'date', 'expires_at' => 'date', 'approved_at' => 'datetime', 'responded_at' => 'datetime'];
    }

    /** @return BelongsTo<JobApplicant, $this> */
    public function applicant(): BelongsTo
    {
        return $this->belongsTo(JobApplicant::class);
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @return BelongsTo<User, $this> */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
