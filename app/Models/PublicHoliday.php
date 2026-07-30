<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PublicHoliday extends Model
{
    protected $fillable = ['company_id', 'name', 'holiday_date', 'is_paid', 'notes'];

    protected function casts(): array
    {
        return ['holiday_date' => 'date', 'is_paid' => 'boolean'];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
