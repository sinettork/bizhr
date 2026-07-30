<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table): void {
            if (! Schema::hasColumn('branches', 'attendance_qr_token')) {
                $table->string('attendance_qr_token', 100)
                    ->nullable()
                    ->unique()
                    ->after('id');
            }

            if (! Schema::hasColumn('branches', 'attendance_qr_enabled')) {
                $table->boolean('attendance_qr_enabled')
                    ->default(true)
                    ->after('attendance_qr_token');
            }

            if (! Schema::hasColumn('branches', 'latitude')) {
                $table->decimal('latitude', 10, 7)
                    ->nullable()
                    ->after('attendance_qr_enabled');
            }

            if (! Schema::hasColumn('branches', 'longitude')) {
                $table->decimal('longitude', 10, 7)
                    ->nullable()
                    ->after('latitude');
            }

            if (! Schema::hasColumn('branches', 'attendance_radius')) {
                $table->unsignedInteger('attendance_radius')
                    ->default(100)
                    ->comment('Allowed attendance radius in meters')
                    ->after('longitude');
            }

            if (! Schema::hasColumn('branches', 'qr_last_regenerated_at')) {
                $table->timestamp('qr_last_regenerated_at')
                    ->nullable()
                    ->after('attendance_radius');
            }
        });
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table): void {
            $columns = [
                'attendance_qr_token',
                'attendance_qr_enabled',
                'latitude',
                'longitude',
                'attendance_radius',
                'qr_last_regenerated_at',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('branches', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};