<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table): void {
            if (! Schema::hasColumn('attendances', 'check_in_method')) {
                $table->string('check_in_method', 30)
                    ->nullable()
                    ->after('check_in_at');
            }

            if (! Schema::hasColumn('attendances', 'check_out_method')) {
                $table->string('check_out_method', 30)
                    ->nullable()
                    ->after('check_out_at');
            }

            if (! Schema::hasColumn('attendances', 'check_in_latitude')) {
                $table->decimal('check_in_latitude', 10, 7)
                    ->nullable();
            }

            if (! Schema::hasColumn('attendances', 'check_in_longitude')) {
                $table->decimal('check_in_longitude', 10, 7)
                    ->nullable();
            }

            if (! Schema::hasColumn('attendances', 'check_out_latitude')) {
                $table->decimal('check_out_latitude', 10, 7)
                    ->nullable();
            }

            if (! Schema::hasColumn('attendances', 'check_out_longitude')) {
                $table->decimal('check_out_longitude', 10, 7)
                    ->nullable();
            }

            if (! Schema::hasColumn('attendances', 'check_in_distance')) {
                $table->unsignedInteger('check_in_distance')
                    ->nullable()
                    ->comment('Distance from branch in meters');
            }

            if (! Schema::hasColumn('attendances', 'check_out_distance')) {
                $table->unsignedInteger('check_out_distance')
                    ->nullable()
                    ->comment('Distance from branch in meters');
            }

            if (! Schema::hasColumn('attendances', 'check_in_ip')) {
                $table->string('check_in_ip', 45)
                    ->nullable();
            }

            if (! Schema::hasColumn('attendances', 'check_out_ip')) {
                $table->string('check_out_ip', 45)
                    ->nullable();
            }

            if (! Schema::hasColumn('attendances', 'check_in_user_agent')) {
                $table->text('check_in_user_agent')
                    ->nullable();
            }

            if (! Schema::hasColumn('attendances', 'check_out_user_agent')) {
                $table->text('check_out_user_agent')
                    ->nullable();
            }

            if (! Schema::hasColumn('attendances', 'check_in_qr_token')) {
                $table->string('check_in_qr_token', 100)
                    ->nullable();
            }

            if (! Schema::hasColumn('attendances', 'check_out_qr_token')) {
                $table->string('check_out_qr_token', 100)
                    ->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table): void {
            $columns = [
                'check_in_method',
                'check_out_method',
                'check_in_latitude',
                'check_in_longitude',
                'check_out_latitude',
                'check_out_longitude',
                'check_in_distance',
                'check_out_distance',
                'check_in_ip',
                'check_out_ip',
                'check_in_user_agent',
                'check_out_user_agent',
                'check_in_qr_token',
                'check_out_qr_token',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('attendances', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};