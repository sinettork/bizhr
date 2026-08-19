<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

abstract class Controller
{
    protected function perPage(Request $request, int $default = 20): int
    {
        $value = $request->integer('per_page', $default);

        return in_array($value, [10, 20, 30, 50, 100], true) ? $value : $default;
    }

    protected function currentCompanyId(Request $request): int
    {
        $companyId = $request->user()?->companyId();
        abort_unless($companyId !== null, 403, 'Your account is not linked to a company context.');

        return $companyId;
    }

    /**
     * @param  array<string>  $sourceFields
     */
    protected function prepareGeneratedCode(Request $request, string $field, string $table, string $column, string $defaultPrefix, array $sourceFields, string $companyColumn = 'company_id', ?int $companyId = null, ?int $ignoreId = null, int $maxLength = 30): void
    {
        $value = trim((string) $request->input($field, ''));
        if ($value !== '') {
            $request->merge([$field => strtoupper($value)]);

            return;
        }

        $seedSource = collect($sourceFields)
            ->map(function (string $sourceField) use ($request): string {
                return trim((string) $request->input($sourceField, ''));
            })
            ->filter()
            ->implode(' ');

        $base = trim((string) preg_replace('/[^A-Za-z0-9]+/', '-', strtoupper($seedSource)), '-');
        $base = $base !== '' ? $base : $defaultPrefix;
        $base = substr($base, 0, max(1, $maxLength - 6));

        $candidate = $base;
        $counter = 1;

        while ($this->codeExists($table, $column, $candidate, $companyColumn, $companyId, $ignoreId)) {
            $candidate = sprintf('%s-%d', $base, $counter);
            $counter++;
            if (strlen($candidate) > $maxLength) {
                $base = substr($base, 0, max(1, $maxLength - strlen((string) $counter) - 1));
                $candidate = sprintf('%s-%d', $base, $counter - 1);
            }
        }

        $request->merge([$field => $candidate]);
    }

    protected function codeExists(string $table, string $column, string $candidate, string $companyColumn = 'company_id', ?int $companyId = null, ?int $ignoreId = null): bool
    {
        $query = DB::table($table)->where($column, $candidate);
        if ($companyId !== null) {
            $query->where($companyColumn, $companyId);
        }
        if ($ignoreId !== null) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }
}
