<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Employee;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class AttendanceQrService
{
    public function process(
        Employee $employee,
        string $qrPayload,
        float $latitude,
        float $longitude,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): array {
        $branch = $this->validateQrPayload($qrPayload);
        $this->validateEmployeeBranch($employee, $branch);

        $distance = $this->validateLocation(
            $branch,
            $latitude,
            $longitude,
        );

        try {
            return DB::transaction(function () use (
                $employee,
                $branch,
                $latitude,
                $longitude,
                $distance,
                $ipAddress,
                $userAgent,
            ): array {
                $attendance = Attendance::query()
                    ->where('employee_id', $employee->getKey())
                    ->whereDate('work_date', today())
                    ->lockForUpdate()
                    ->first();

                if (! $attendance) {
                    return $this->checkIn(
                        $employee,
                        $branch,
                        $latitude,
                        $longitude,
                        $distance,
                        $ipAddress,
                        $userAgent,
                    );
                }

                if ($attendance->check_out_at) {
                    throw ValidationException::withMessages([
                        'qrPayload' => 'អ្នកបានចុះម៉ោងចូល និងចេញរួចហើយសម្រាប់ថ្ងៃនេះ។',
                    ]);
                }

                if (! $attendance->check_in_at) {
                    return $this->updateMissingCheckIn(
                        $attendance,
                        $branch,
                        $latitude,
                        $longitude,
                        $distance,
                        $ipAddress,
                        $userAgent,
                    );
                }

                return $this->checkOut(
                    $attendance,
                    $branch,
                    $latitude,
                    $longitude,
                    $distance,
                    $ipAddress,
                    $userAgent,
                );
            });
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            throw ValidationException::withMessages([
                'qrPayload' => 'មិនអាចកត់ត្រាវត្តមានបានទេ។ សូមសាកល្បងម្ដងទៀត។',
            ]);
        }
    }

    public function validateQrPayload(string $qrPayload): Branch
    {
        $data = json_decode(trim($qrPayload), true);

        if (
            ! is_array($data)
            || ($data['type'] ?? null) !== 'bizhr_attendance'
            || empty($data['branch_id'])
            || ! is_string($data['token'] ?? null)
        ) {
            throw ValidationException::withMessages([
                'qrPayload' => 'QR Code នេះមិនត្រឹមត្រូវ ឬមិនមែនជារបស់ BizHR។',
            ]);
        }

        $branch = Branch::query()->find($data['branch_id']);

        if (! $branch) {
            throw ValidationException::withMessages([
                'qrPayload' => 'រកមិនឃើញសាខារបស់ QR Code នេះទេ។',
            ]);
        }

        if (! $branch->attendance_qr_enabled) {
            throw ValidationException::withMessages([
                'qrPayload' => 'QR Attendance របស់សាខានេះត្រូវបានបិទ។',
            ]);
        }

        if (
            ! $branch->attendance_qr_token
            || ! hash_equals(
                (string) $branch->attendance_qr_token,
                (string) $data['token'],
            )
        ) {
            throw ValidationException::withMessages([
                'qrPayload' => 'QR Code នេះអស់សុពលភាព ឬត្រូវបានប្ដូររួចហើយ។',
            ]);
        }

        return $branch;
    }

    public function validateEmployeeBranch(
        Employee $employee,
        Branch $branch,
    ): void {
        if (! $employee->is_active) {
            throw ValidationException::withMessages([
                'employee' => 'គណនីបុគ្គលិកនេះមិនសកម្មទេ។',
            ]);
        }

        if ((int) $employee->branch_id !== (int) $branch->getKey()) {
            throw ValidationException::withMessages([
                'employee' => 'អ្នកមិនអាចចុះវត្តមាននៅសាខានេះបានទេ។',
            ]);
        }
    }

    public function validateLocation(
        Branch $branch,
        float $employeeLatitude,
        float $employeeLongitude,
    ): int {
        if (
            $employeeLatitude < -90
            || $employeeLatitude > 90
            || $employeeLongitude < -180
            || $employeeLongitude > 180
        ) {
            throw ValidationException::withMessages([
                'location' => 'ទិន្នន័យទីតាំង GPS មិនត្រឹមត្រូវ។',
            ]);
        }

        if ($branch->latitude === null || $branch->longitude === null) {
            throw ValidationException::withMessages([
                'location' => 'សាខានេះមិនទាន់បានកំណត់ទីតាំង GPS ទេ។',
            ]);
        }

        $distance = $this->calculateDistanceInMeters(
            $employeeLatitude,
            $employeeLongitude,
            (float) $branch->latitude,
            (float) $branch->longitude,
        );

        $allowedRadius = max(1, (int) ($branch->attendance_radius ?? 100));

        if ($distance > $allowedRadius) {
            throw ValidationException::withMessages([
                'location' => "អ្នកស្ថិតនៅចម្ងាយ {$distance} ម៉ែត្រពីសាខា។ ចម្ងាយអនុញ្ញាតគឺ {$allowedRadius} ម៉ែត្រ។",
            ]);
        }

        return $distance;
    }

    protected function checkIn(
        Employee $employee,
        Branch $branch,
        float $latitude,
        float $longitude,
        int $distance,
        ?string $ipAddress,
        ?string $userAgent,
    ): array {
        $now = now();

        $attendance = Attendance::query()->create([
            'employee_id' => $employee->getKey(),
            'branch_id' => $branch->getKey(),
            'work_date' => $now->toDateString(),
            'check_in_at' => $now,
            'check_in_method' => 'qr',
            'check_in_location' => "{$latitude},{$longitude}",
            'check_in_latitude' => $latitude,
            'check_in_longitude' => $longitude,
            'check_in_distance' => $distance,
            'check_in_ip' => $ipAddress,
            'check_in_user_agent' => $userAgent,
            'check_in_qr_token' => $branch->attendance_qr_token,
        ]);

        return $this->result('check_in', 'ចុះម៉ោងចូលបានជោគជ័យ។', $attendance, $branch, $distance, $now);
    }

    protected function updateMissingCheckIn(
        Attendance $attendance,
        Branch $branch,
        float $latitude,
        float $longitude,
        int $distance,
        ?string $ipAddress,
        ?string $userAgent,
    ): array {
        $now = now();

        $attendance->forceFill([
            'branch_id' => $branch->getKey(),
            'check_in_at' => $now,
            'check_in_method' => 'qr',
            'check_in_location' => "{$latitude},{$longitude}",
            'check_in_latitude' => $latitude,
            'check_in_longitude' => $longitude,
            'check_in_distance' => $distance,
            'check_in_ip' => $ipAddress,
            'check_in_user_agent' => $userAgent,
            'check_in_qr_token' => $branch->attendance_qr_token,
        ])->save();

        return $this->result('check_in', 'ចុះម៉ោងចូលបានជោគជ័យ។', $attendance, $branch, $distance, $now);
    }

    protected function checkOut(
        Attendance $attendance,
        Branch $branch,
        float $latitude,
        float $longitude,
        int $distance,
        ?string $ipAddress,
        ?string $userAgent,
    ): array {
        $now = now();

        if (
            $attendance->check_in_at
            && Carbon::parse($attendance->check_in_at)->greaterThan($now)
        ) {
            throw ValidationException::withMessages([
                'attendance' => 'ម៉ោងចូលមិនត្រឹមត្រូវ។',
            ]);
        }

        $minimumCheckoutMinutes = max(
            1,
            (int) config('attendance.qr.minimum_checkout_minutes', 5),
        );

        if (
            $attendance->check_in_at
            && Carbon::parse($attendance->check_in_at)
                ->addMinutes($minimumCheckoutMinutes)
                ->isFuture()
        ) {
            throw ValidationException::withMessages([
                'attendance' => "មិនអាចចុះម៉ោងចេញភ្លាមៗបានទេ។ សូមរង់ចាំយ៉ាងតិច {$minimumCheckoutMinutes} នាទីបន្ទាប់ពីចុះម៉ោងចូល។",
            ]);
        }

        $attendance->forceFill([
            'check_out_at' => $now,
            'check_out_method' => 'qr',
            'check_out_location' => "{$latitude},{$longitude}",
            'check_out_latitude' => $latitude,
            'check_out_longitude' => $longitude,
            'check_out_distance' => $distance,
            'check_out_ip' => $ipAddress,
            'check_out_user_agent' => $userAgent,
            'check_out_qr_token' => $branch->attendance_qr_token,
        ])->save();

        return $this->result('check_out', 'ចុះម៉ោងចេញបានជោគជ័យ។', $attendance, $branch, $distance, $now);
    }

    protected function result(
        string $action,
        string $message,
        Attendance $attendance,
        Branch $branch,
        int $distance,
        Carbon $time,
    ): array {
        return compact(
            'action',
            'message',
            'attendance',
            'branch',
            'distance',
            'time',
        );
    }

    public function calculateDistanceInMeters(
        float $latitude1,
        float $longitude1,
        float $latitude2,
        float $longitude2,
    ): int {
        $earthRadius = 6_371_000;
        $latitudeDifference = deg2rad($latitude2 - $latitude1);
        $longitudeDifference = deg2rad($longitude2 - $longitude1);

        $a = sin($latitudeDifference / 2) ** 2
            + cos(deg2rad($latitude1))
            * cos(deg2rad($latitude2))
            * sin($longitudeDifference / 2) ** 2;

        return (int) round(
            $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a))
        );
    }
}
