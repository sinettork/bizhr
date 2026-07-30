<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class AuditLogObserver
{
    private const IGNORED = ['created_at', 'updated_at', 'remember_token'];

    public function created(Model $model): void
    {
        $this->record($model, 'created', [], $this->clean($model->getAttributes()));
    }

    public function updated(Model $model): void
    {
        $changes = $this->clean($model->getChanges());

        if ($changes === []) {
            return;
        }

        $old = [];
        foreach (array_keys($changes) as $key) {
            $old[$key] = $model->getRawOriginal($key);
        }

        $this->record($model, 'updated', $old, $changes);
    }

    public function deleted(Model $model): void
    {
        $this->record($model, 'deleted', $this->clean($model->getAttributes()), []);
    }

    private function record(Model $model, string $action, array $old, array $new): void
    {
        if (app()->runningInConsole() && ! auth()->check()) {
            return;
        }

        AuditLog::record($model, $action, $old, $new);
    }

    private function clean(array $values): array
    {
        return collect($values)
            ->except(self::IGNORED)
            ->map(fn ($value) => is_scalar($value) || $value === null ? $value : (string) $value)
            ->all();
    }
}
