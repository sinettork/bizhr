<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * @var list<string>
     */
    private array $tables = [
        'employees',
        'employee_documents',
        'employment_histories',
        'leave_requests',
        'employment_contracts',
        'data_exports',
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->uuid('public_id')->nullable()->unique();
            });

            /**
             * @param  Collection<int, object{id: int|string}>  $rows
             */
            $backfill = function (Collection $rows) use ($tableName): void {
                foreach ($rows as $row) {
                    DB::table($tableName)
                        ->where('id', $row->id)
                        ->update(['public_id' => (string) Str::uuid()]);
                }
            };

            DB::table($tableName)
                ->select('id')
                ->orderBy('id')
                ->chunkById(500, $backfill);
        }
    }

    public function down(): void
    {
        foreach (array_reverse($this->tables) as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->dropUnique(['public_id']);
                $table->dropColumn('public_id');
            });
        }
    }
};
