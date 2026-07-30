<?php

use App\Models\AttendanceQrSession;
use App\Models\AttendanceQrScanEvent;
use App\Services\AttendanceQrService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Title('កត់ត្រាវត្តមាន')]
class extends Component
{
    public string $token;
    public ?float $latitude = null;
    public ?float $longitude = null;
    public ?float $accuracy = null;
    public string $status = 'កំពុងរង់ចាំទីតាំង GPS...';
    public bool $completed = false;
    public ?string $action = null;
    public ?string $branchName = null;
    public ?string $recordedTime = null;
    public ?int $distance = null;

    public function mount(string $token): void
    {
        $this->token = $token;
        $this->findSession();
    }

    public function submit(
        float $latitude,
        float $longitude,
        float $accuracy,
        AttendanceQrService $service,
    ): void {
        if ($this->completed) {
            return;
        }

        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->accuracy = $accuracy;

        $employee = auth()->user()?->employee;

        if (! $employee) {
            throw ValidationException::withMessages([
                'employee' => 'គណនីនេះមិនទាន់ភ្ជាប់ជាមួយបុគ្គលិកទេ។',
            ]);
        }

        $maximumAccuracy = max(
            20,
            (float) config('attendance.qr.maximum_accuracy_meters', 100),
        );

        if ($accuracy <= 0 || $accuracy > $maximumAccuracy) {
            throw ValidationException::withMessages([
                'location' => "GPS មិនទាន់មានភាពត្រឹមត្រូវគ្រប់គ្រាន់ ({$accuracy} ម៉ែត្រ)។ សូមបើក GPS ហើយសាកល្បងម្ដងទៀតនៅទីតាំងបើកចំហ។",
            ]);
        }

        $result = DB::transaction(function () use (
            $employee,
            $latitude,
            $longitude,
            $accuracy,
            $service,
        ): array {
            $session = $this->findSession(lock: true);

            if (
                AttendanceQrScanEvent::query()
                    ->where('attendance_qr_session_id', $session->id)
                    ->where('employee_id', $employee->id)
                    ->exists()
            ) {
                throw ValidationException::withMessages([
                    'attendance' => 'QR នេះបានកត់ត្រាសម្រាប់គណនីរបស់អ្នករួចហើយ។ សូមកុំស្កេនដដែលម្ដងទៀត។',
                ]);
            }

            $result = $service->process(
                employee: $employee,
                qrPayload: $session->branch->attendanceQrPayload(),
                latitude: $latitude,
                longitude: $longitude,
                ipAddress: request()->ip(),
                userAgent: request()->userAgent(),
            );

            AttendanceQrScanEvent::query()->create([
                'attendance_qr_session_id' => $session->id,
                'employee_id' => $employee->id,
                'attendance_id' => $result['attendance']->id,
                'branch_id' => $session->branch_id,
                'action' => $result['action'],
                'latitude' => $latitude,
                'longitude' => $longitude,
                'accuracy_meters' => $accuracy,
                'distance_meters' => $result['distance'],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'recorded_at' => $result['time'],
            ]);

            $session->increment('scan_count');

            return $result;
        }, attempts: 3);

        $this->completed = true;
        $this->action = $result['action'];
        $this->branchName = $result['branch']->name;
        $this->recordedTime = $result['time']
            ->timezone('Asia/Phnom_Penh')
            ->format('d/m/Y H:i:s');
        $this->distance = $result['distance'];
        $this->status = $result['message'];
        session()->forget(
            'attendance_qr_grant.'.hash('sha256', $this->token),
        );
    }

    protected function findSession(bool $lock = false): AttendanceQrSession
    {
        $query = AttendanceQrSession::query()
            ->with('branch')
            ->where('token_hash', hash('sha256', $this->token));

        if ($lock) {
            $query->lockForUpdate();
        }

        $session = $query->first();
        $tokenHash = hash('sha256', $this->token);
        $grantExpiresAt = session("attendance_qr_grant.{$tokenHash}");
        $hasValidGrant = is_numeric($grantExpiresAt)
            && (int) $grantExpiresAt >= now()->timestamp;

        if (
            ! $session
            || (! $session->isValid() && ! $hasValidGrant)
            || ! $session->branch
            || ! $session->branch->is_active
            || ! $session->branch->attendance_qr_enabled
        ) {
            throw ValidationException::withMessages([
                'token' => 'QR Code នេះអស់សុពលភាព។ សូមស្កេន QR ថ្មី។',
            ]);
        }

        return $session;
    }
};
?>

<div
    class="mx-auto flex min-h-[70vh] w-full max-w-lg items-center"
    x-data="{
        loading: true,
        init() {
            if (!navigator.geolocation) {
                this.loading = false;
                $wire.set('status', 'ទូរស័ព្ទនេះមិនគាំទ្រ GPS។');
                return;
            }

            navigator.geolocation.getCurrentPosition(
                async position => {
                    try {
                        await $wire.submit(
                            position.coords.latitude,
                            position.coords.longitude,
                            position.coords.accuracy
                        );
                    } finally {
                        this.loading = false;
                    }
                },
                error => {
                    this.loading = false;
                    const messages = {
                        1: 'សូមអនុញ្ញាត Location ដើម្បីកត់ត្រាវត្តមាន។',
                        2: 'មិនអាចរកទីតាំង GPS បានទេ។ សូមបើក GPS ហើយសាកល្បងម្ដងទៀត។',
                        3: 'ការស្វែងរក GPS ចំណាយពេលយូរ។ សូមសាកល្បងម្ដងទៀត។'
                    };
                    $wire.set('status', messages[error.code] || 'មិនអាចទាញយកទីតាំង GPS បានទេ។');
                },
                { enableHighAccuracy: true, timeout: 20000, maximumAge: 0 }
            );
        }
    }"
>
    <div class="w-full rounded-3xl border border-zinc-200 bg-white p-8 text-center shadow-lg dark:border-zinc-700 dark:bg-zinc-900">
        <div class="mx-auto flex size-20 items-center justify-center rounded-full"
             @class([
                 'bg-green-100 text-green-600' => $completed,
                 'bg-blue-100 text-blue-600' => ! $completed,
             ])>
            @if ($completed)
                <flux:icon.check-circle class="size-10"/>
            @else
                <flux:icon.map-pin class="size-10"/>
            @endif
        </div>

        <flux:heading size="xl" class="mt-6">
            {{ $completed ? 'កត់ត្រារួចរាល់' : 'កំពុងកត់ត្រាវត្តមាន' }}
        </flux:heading>

        <p class="mt-3 text-zinc-600 dark:text-zinc-300">{{ $status }}</p>

        @if ($completed)
            <dl class="mt-6 grid grid-cols-2 gap-3 text-left text-sm">
                <div class="rounded-xl bg-zinc-50 p-3 dark:bg-zinc-800">
                    <dt class="text-zinc-500">សកម្មភាព</dt>
                    <dd class="mt-1 font-medium">
                        {{ $action === 'check_in' ? 'ចុះម៉ោងចូល' : 'ចុះម៉ោងចេញ' }}
                    </dd>
                </div>
                <div class="rounded-xl bg-zinc-50 p-3 dark:bg-zinc-800">
                    <dt class="text-zinc-500">ចម្ងាយ</dt>
                    <dd class="mt-1 font-medium">{{ $distance }} ម៉ែត្រ</dd>
                </div>
                <div class="col-span-2 rounded-xl bg-zinc-50 p-3 dark:bg-zinc-800">
                    <dt class="text-zinc-500">សាខា</dt>
                    <dd class="mt-1 font-medium">{{ $branchName }}</dd>
                </div>
                <div class="col-span-2 rounded-xl bg-zinc-50 p-3 dark:bg-zinc-800">
                    <dt class="text-zinc-500">ម៉ោងម៉ាស៊ីនមេ</dt>
                    <dd class="mt-1 font-medium">{{ $recordedTime }}</dd>
                </div>
            </dl>
        @endif

        @if ($errors->any())
            <div class="mt-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <div x-show="loading" class="mt-6 text-sm text-zinc-500">
            សូមរង់ចាំ...
        </div>

        @if (! $completed)
            <flux:button
                class="mt-6 w-full"
                variant="primary"
                icon="arrow-path"
                x-on:click="loading = true; init()"
            >
                សាកល្បង GPS ម្ដងទៀត
            </flux:button>
        @endif
    </div>
</div>
