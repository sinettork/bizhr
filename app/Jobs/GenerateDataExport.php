<?php

namespace App\Jobs;

use App\Models\Attendance;
use App\Models\DataExport;
use App\Models\Employee;
use App\Models\PayrollItem;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Spatie\SimpleExcel\SimpleExcelWriter;
use Throwable;

class GenerateDataExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 300;

    public function __construct(public readonly int $dataExportId) {}

    public function handle(): void
    {
        $export = DB::transaction(function (): ?DataExport {
            $locked = DataExport::query()->lockForUpdate()->findOrFail($this->dataExportId);

            if ($locked->status === 'completed') {
                return null;
            }

            if ($locked->status === 'processing') {
                return null;
            }

            if (! in_array($locked->status, ['queued', 'failed'], true)) {
                throw new RuntimeException("Export cannot start from status [{$locked->status}].");
            }

            if (! in_array($locked->disk, ['local', 's3'], true)) {
                throw new RuntimeException('BizHR exports require a private local or S3 disk.');
            }

            $locked->update([
                'status' => 'processing',
                'progress' => 1,
                'started_at' => now(),
                'completed_at' => null,
                'error_message' => null,
            ]);

            return $locked->fresh();
        });

        if ($export === null) {
            return;
        }

        $disk = Storage::disk($export->disk);
        $path = "exports/{$export->company_id}/{$export->user_id}/{$export->id}.xlsx";
        $stream = null;
        $temporaryPath = null;

        try {
            $temporaryBase = tempnam(sys_get_temp_dir(), 'bizhr-export-');
            if ($temporaryBase === false) {
                throw new RuntimeException('Unable to create a temporary export file.');
            }
            $temporaryPath = $temporaryBase.'.xlsx';
            @unlink($temporaryBase);

            $writer = SimpleExcelWriter::create($temporaryPath);
            $writer->nameCurrentSheet('Data');
            $rowCount = match ($export->type) {
                'employees' => $this->writeEmployees($writer, $export),
                'attendance' => $this->writeAttendance($writer, $export),
                'payroll' => $this->writePayroll($writer, $export),
                default => throw new \InvalidArgumentException("Unsupported export type [{$export->type}]."),
            };
            $writer->close();

            $stream = fopen($temporaryPath, 'rb');
            if ($stream === false || ! $disk->put($path, $stream)) {
                throw new RuntimeException('Unable to store the private export file.');
            }
            fclose($stream);
            $stream = null;

            DataExport::query()
                ->whereKey($export->id)
                ->where('status', 'processing')
                ->update([
                    'status' => 'completed',
                    'progress' => 100,
                    'row_count' => $rowCount,
                    'file_path' => $path,
                    'completed_at' => now(),
                    'expires_at' => now()->addDays(7),
                ]);
        } catch (Throwable $exception) {
            if (is_resource($stream)) {
                fclose($stream);
            }
            $disk->delete($path);
            DataExport::query()
                ->whereKey($export->id)
                ->where('status', 'processing')
                ->update([
                    'status' => 'failed',
                    'error_message' => mb_substr($exception->getMessage(), 0, 2000),
                    'completed_at' => now(),
                ]);

            throw $exception;
        } finally {
            if ($temporaryPath && file_exists($temporaryPath)) {
                @unlink($temporaryPath);
            }
        }
    }

    public function failed(Throwable $exception): void
    {
        DataExport::query()
            ->whereKey($this->dataExportId)
            ->whereNotIn('status', ['completed'])
            ->update([
                'status' => 'failed',
                'error_message' => mb_substr($exception->getMessage(), 0, 2000),
                'completed_at' => now(),
            ]);
    }

    private function writeEmployees(SimpleExcelWriter $writer, DataExport $export): int
    {
        $writer->addHeader([
            'លេខកូដបុគ្គលិក', 'ឈ្មោះខ្មែរ', 'ឈ្មោះឡាតាំង', 'ភេទ', 'សាខា', 'ផ្នែក',
            'មុខតំណែង', 'ថ្ងៃចូលធ្វើការ', 'ស្ថានភាពការងារ', 'ប្រាក់បៀវត្សគោល',
            'រូបិយប័ណ្ណ', 'អ៊ីមែល', 'ទូរស័ព្ទ',
        ]);

        $query = Employee::query()
            ->with(['branch:id,name', 'department:id,name', 'position:id,title'])
            ->where('company_id', $export->company_id)
            ->when($export->filters['branch_id'] ?? null, fn (Builder $query, $branchId) => $query->where('branch_id', $branchId))
            ->when($export->filters['department_id'] ?? null, fn (Builder $query, $departmentId) => $query->where('department_id', $departmentId))
            ->when($export->filters['status'] ?? null, fn (Builder $query, $status) => $query->where('employment_status', $status))
            ->orderBy('id');

        return $this->writeQuery($query, $writer, function (Employee $employee): array {
            return [
                $this->safe($employee->employee_code),
                $this->safe($employee->full_name_km),
                $this->safe($employee->getFullName()),
                $employee->gender,
                $this->safe($employee->branch?->name),
                $this->safe($employee->department?->name),
                $this->safe($employee->position?->title),
                $employee->hire_date?->format('Y-m-d'),
                $employee->employment_status,
                $employee->base_salary,
                $employee->salary_currency,
                $this->safe($employee->email),
                $this->safe($employee->phone),
            ];
        });
    }

    private function writeAttendance(SimpleExcelWriter $writer, DataExport $export): int
    {
        $writer->addHeader([
            'ថ្ងៃ', 'លេខកូដបុគ្គលិក', 'ឈ្មោះ', 'សាខា', 'ស្ថានភាព', 'ម៉ោងចូល',
            'ម៉ោងចេញ', 'នាទីធ្វើការ', 'នាទីយឺត', 'នាទីចេញមុន', 'នាទីថែមម៉ោង',
        ]);

        $filters = $export->filters ?? [];
        $query = Attendance::query()
            ->with(['employee:id,employee_code,first_name,last_name,full_name_km,full_name_en', 'branch:id,name'])
            ->whereHas('employee', fn (Builder $query) => $query->where('company_id', $export->company_id))
            ->when($filters['branch_id'] ?? null, fn (Builder $query, $branchId) => $query->where('branch_id', $branchId))
            ->when($filters['employee_id'] ?? null, fn (Builder $query, $employeeId) => $query->where('employee_id', $employeeId))
            ->when($filters['status'] ?? null, fn (Builder $query, $status) => $query->where('status', $status))
            ->when($filters['date_from'] ?? null, fn (Builder $query, $date) => $query->whereDate('work_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn (Builder $query, $date) => $query->whereDate('work_date', '<=', $date))
            ->orderBy('id');

        return $this->writeQuery($query, $writer, function (Attendance $attendance): array {
            return [
                $attendance->work_date->format('Y-m-d'),
                $this->safe($attendance->employee?->employee_code),
                $this->safe($attendance->employee?->getFullNameKm()),
                $this->safe($attendance->branch?->name),
                $attendance->status,
                $attendance->check_in_at?->format('H:i:s'),
                $attendance->check_out_at?->format('H:i:s'),
                $attendance->worked_minutes,
                $attendance->late_minutes,
                $attendance->early_leave_minutes,
                $attendance->overtime_minutes,
            ];
        });
    }

    private function writePayroll(SimpleExcelWriter $writer, DataExport $export): int
    {
        $writer->addHeader([
            'វគ្គប្រាក់ខែ', 'លេខកូដបុគ្គលិក', 'ឈ្មោះ', 'រូបិយប័ណ្ណ', 'ប្រាក់ខែគោល',
            'ប្រាក់ខែសរុប', 'ពន្ធ', 'ប.ស.ស. បុគ្គលិក', 'ប្រាក់ខែសុទ្ធ', 'ស្ថានភាពបើកប្រាក់',
        ]);

        $query = PayrollItem::query()
            ->with(['employee:id,company_id,employee_code,first_name,last_name,full_name_km,full_name_en', 'period:id,name'])
            ->whereHas('employee', fn (Builder $query) => $query->where('company_id', $export->company_id))
            ->whereHas('period', fn (Builder $query) => $query->where('company_id', $export->company_id))
            ->when($export->filters['period_id'] ?? null, fn (Builder $query, $periodId) => $query->where('payroll_period_id', $periodId))
            ->orderBy('id');

        return $this->writeQuery($query, $writer, function (PayrollItem $item): array {
            return [
                $this->safe($item->period?->name),
                $this->safe($item->employee?->employee_code),
                $this->safe($item->employee?->getFullNameKm()),
                $item->currency,
                $item->base_salary,
                $item->gross_salary,
                $item->tax_amount,
                $item->nssf_employee_amount,
                $item->net_salary,
                $item->payment_status,
            ];
        });
    }

    /**
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  Builder<TModel>  $query
     * @param  callable(TModel): array<int, mixed>  $map
     */
    private function writeQuery(Builder $query, SimpleExcelWriter $writer, callable $map): int
    {
        $total = (clone $query)->count();
        $written = 0;

        foreach ($query->lazyById(250) as $row) {
            $writer->addRow($map($row));
            $written++;

            if ($written % 250 === 0) {
                DataExport::query()->whereKey($this->dataExportId)->where('status', 'processing')->update([
                    'progress' => $total > 0 ? min(99, 5 + (int) floor(($written / $total) * 90)) : 95,
                    'row_count' => $written,
                ]);
            }
        }

        DataExport::query()->whereKey($this->dataExportId)->where('status', 'processing')->update([
            'progress' => $total > 0 ? min(99, 5 + (int) floor(($written / $total) * 90)) : 95,
            'row_count' => $written,
        ]);

        return $written;
    }

    private function safe(?string $value): string
    {
        $value = (string) $value;

        return preg_match('/^[=+\-@]/', $value) ? "'{$value}" : $value;
    }
}
