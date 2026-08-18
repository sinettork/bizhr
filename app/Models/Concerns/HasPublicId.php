<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait HasPublicId
{
    public static function bootHasPublicId(): void
    {
        static::creating(function (Model $model): void {
            if (blank($model->getAttribute('public_id'))) {
                $model->setAttribute('public_id', (string) Str::uuid());
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    public function resolveRouteBindingQuery(
        mixed $query,
        mixed $value,
        mixed $field = null,
    ): mixed {
        $routeField = is_string($field) ? $field : $this->getRouteKeyName();

        if ($routeField === 'public_id' && ctype_digit((string) $value)) {
            $routeField = $this->getKeyName();
        }

        return parent::resolveRouteBindingQuery($query, $value, $routeField);
    }
}
