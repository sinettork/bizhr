<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobInterview extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['scheduled_at' => 'datetime', 'completed_at' => 'datetime'];
    }

    /** @return BelongsTo<JobApplicant, $this> */
    public function applicant(): BelongsTo
    {
        return $this->belongsTo(JobApplicant::class);
    }

    /** @return BelongsTo<User, $this> */
    public function interviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'interviewer_id');
    }
}
