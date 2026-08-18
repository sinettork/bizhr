<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobApplicant extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['applied_at' => 'datetime'];
    }

    /** @return BelongsTo<JobVacancy, $this> */
    public function vacancy(): BelongsTo
    {
        return $this->belongsTo(JobVacancy::class, 'job_vacancy_id');
    }

    /** @return HasMany<JobInterview, $this> */
    public function interviews(): HasMany
    {
        return $this->hasMany(JobInterview::class);
    }

    /** @return HasMany<JobOffer, $this> */
    public function offers(): HasMany
    {
        return $this->hasMany(JobOffer::class);
    }
}
