<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Add composite performance indexes for high-traffic enterprise queries.
 *
 * These indexes target the most frequent query patterns observed across:
 *  - Attendance daily summaries (dashboard, reports)
 *  - Leave request approval queues
 *  - Payroll period filtering by company + status
 *  - Audit log time-range queries per company
 */
return new class extends Migration
{
    public function up(): void
    {
        // ------------------------------------------------------------------
        // attendances — most queried table in the system
        // ------------------------------------------------------------------
        Schema::table('attendances', function (Blueprint $table): void {
            // Dashboard workforce metrics: "today's attendance for company X"
            if (! $this->indexExists('attendances', 'idx_att_employee_work_date')) {
                $table->index(['employee_id', 'work_date'], 'idx_att_employee_work_date');
            }
            // Reports: filter by work_date range (month/period reports)
            if (! $this->indexExists('attendances', 'idx_att_work_date_status')) {
                $table->index(['work_date', 'status'], 'idx_att_work_date_status');
            }
        });

        // ------------------------------------------------------------------
        // leave_requests — approval queues accessed by HR and managers
        // ------------------------------------------------------------------
        Schema::table('leave_requests', function (Blueprint $table): void {
            // Manager/HR approval queue: "pending leave requests for company X"
            if (! $this->indexExists('leave_requests', 'idx_lr_employee_status')) {
                $table->index(['employee_id', 'status'], 'idx_lr_employee_status');
            }
            // Overlap detection: "any approved leave covering this date range?"
            if (! $this->indexExists('leave_requests', 'idx_lr_status_dates')) {
                $table->index(['status', 'start_date', 'end_date'], 'idx_lr_status_dates');
            }
        });

        // ------------------------------------------------------------------
        // payroll_periods — filtered by company + status in every payroll view
        // ------------------------------------------------------------------
        Schema::table('payroll_periods', function (Blueprint $table): void {
            if (! $this->indexExists('payroll_periods', 'idx_pp_company_status')) {
                $table->index(['company_id', 'status'], 'idx_pp_company_status');
            }
            if (! $this->indexExists('payroll_periods', 'idx_pp_company_start_date')) {
                $table->index(['company_id', 'start_date'], 'idx_pp_company_start_date');
            }
        });

        // ------------------------------------------------------------------
        // audit_logs — time-range queries for compliance and security review
        // ------------------------------------------------------------------
        Schema::table('audit_logs', function (Blueprint $table): void {
            if (! $this->indexExists('audit_logs', 'idx_al_company_created_at')) {
                $table->index(['company_id', 'created_at'], 'idx_al_company_created_at');
            }
        });

        // ------------------------------------------------------------------
        // employee_schedules — daily workforce metrics (scheduled/present)
        // ------------------------------------------------------------------
        Schema::table('employee_schedules', function (Blueprint $table): void {
            if (! $this->indexExists('employee_schedules', 'idx_es_employee_work_date')) {
                $table->index(['employee_id', 'work_date'], 'idx_es_employee_work_date');
            }
        });

        // ------------------------------------------------------------------
        // tasks — open task counts per employee and department
        // ------------------------------------------------------------------
        Schema::table('tasks', function (Blueprint $table): void {
            if (! $this->indexExists('tasks', 'idx_tasks_company_assigned_status')) {
                $table->index(['company_id', 'assigned_to', 'status'], 'idx_tasks_company_assigned_status');
            }
        });
    }

    public function down(): void
    {
        $indexes = [
            'attendances'      => ['idx_att_employee_work_date', 'idx_att_work_date_status'],
            'leave_requests'   => ['idx_lr_employee_status', 'idx_lr_status_dates'],
            'payroll_periods'  => ['idx_pp_company_status', 'idx_pp_company_start_date'],
            'audit_logs'       => ['idx_al_company_created_at'],
            'employee_schedules' => ['idx_es_employee_work_date'],
            'tasks'            => ['idx_tasks_company_assigned_status'],
        ];

        foreach ($indexes as $table => $names) {
            Schema::table($table, function (Blueprint $table) use ($names): void {
                foreach ($names as $index) {
                    if ($this->indexExists($table->getTable(), $index)) {
                        $table->dropIndex($index);
                    }
                }
            });
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        if (DB::getDriverName() === 'pgsql') {
            return (bool) DB::selectOne(
                'SELECT 1 FROM pg_indexes WHERE tablename = ? AND indexname = ?',
                [$table, $indexName],
            );
        }

        if (DB::getDriverName() === 'mysql') {
            return (bool) DB::selectOne(
                'SELECT 1 FROM information_schema.statistics
                 WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ?
                 LIMIT 1',
                [$table, $indexName],
            );
        }

        // SQLite (local dev / CI) — check via pragma
        $indexes = DB::select("PRAGMA index_list(\"{$table}\")");

        foreach ($indexes as $index) {
            if ($index->name === $indexName) {
                return true;
            }
        }

        return false;
    }
};
