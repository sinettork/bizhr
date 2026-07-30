<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class KpiTemplate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id', 'position_id', 'name', 'description',
        'review_frequency', 'is_active', 'created_by',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function position(): BelongsTo { return $this->belongsTo(Position::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function items(): HasMany { return $this->hasMany(KpiTemplateItem::class)->orderBy('sort_order'); }
}
