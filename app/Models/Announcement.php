<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Announcement extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['published_at' => 'datetime', 'expires_at' => 'datetime', 'is_pinned' => 'boolean', 'is_urgent' => 'boolean', 'requires_acknowledgement' => 'boolean'];
    }

    /** @return BelongsToMany<User, $this> */
    public function acknowledgements(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'announcement_acknowledgements')->withPivot(['acknowledged_at', 'ip_address'])->withTimestamps();
    }
}
