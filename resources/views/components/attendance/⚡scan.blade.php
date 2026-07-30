<?php

use App\Services\AttendanceQrService;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Title('ស្កេន QR វត្តមាន')]
class extends Component
{
    public string $qrPayload = '';
    public ?float $latitude = null;
    public ?float $longitude = null;
    public string $locationStatus = 'កំពុងរង់ចាំទីតាំង GPS...';
    public string $scannerStatus = 'កំពុងរង់ចាំការស្កេន QR Code...';

    public function setLocation(float $latitude, float $longitude): void
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->locationStatus = 'ទទួលបានទីតាំង GPS រួចរាល់។';
    }

    public function locationFailed(string $message): void
    {
        $this->locationStatus = $message;
    }

    public function setQrPayload(string $payload): void
    {
        $this->qrPayload = trim($payload);
        $this->scannerStatus = 'ស្កេន QR Code បានជោគជ័យ។';
    }

    public function submitAttendance(AttendanceQrService $service): void
    {
        $this->validate([
            'qrPayload' => ['required', 'string', 'max:2000'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ], [
            'qrPayload.required' => 'សូមស្កេន QR Code ជាមុនសិន។',
            'latitude.required' => 'មិនអាចទទួលបានទីតាំង GPS ទេ។',
            'longitude.required' => 'មិនអាចទទួលបានទីតាំង GPS ទេ។',
        ]);

        $employee = auth()->user()?->employee;

        if (! $employee) {
            throw ValidationException::withMessages([
                'employee' => 'គណនីអ្នកប្រើនេះមិនទាន់ភ្ជាប់ជាមួយបុគ្គលិកទេ។',
            ]);
        }

        $result = $service->process(
            employee: $employee,
            qrPayload: $this->qrPayload,
            latitude: (float) $this->latitude,
            longitude: (float) $this->longitude,
            ipAddress: request()->ip(),
            userAgent: request()->userAgent(),
        );

        session()->flash(
            'attendance_success',
            $result['message'].' ម៉ោង '.$result['time']->format('H:i:s')
            .' · ចម្ងាយ '.$result['distance'].' ម៉ែត្រ'
        );

        $this->reset('qrPayload');
        $this->scannerStatus = 'កត់ត្រាវត្តមានរួចរាល់។';
    }
};
?>

<div class="mx-auto w-full max-w-3xl space-y-6" x-data="attendanceQrScanner()" x-init="initialize()">
    <div>
        <flux:heading size="xl">ស្កេន QR វត្តមាន</flux:heading>
        <flux:subheading class="mt-2">
            ស្កេន QR Code របស់សាខា ដើម្បីចុះម៉ោងចូល ឬចុះម៉ោងចេញ
        </flux:subheading>
    </div>

    @if (session()->has('attendance_success'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800 dark:border-green-800 dark:bg-green-950 dark:text-green-200">
            {{ session('attendance_success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800 dark:border-red-800 dark:bg-red-950 dark:text-red-200">
            <div class="font-medium">មិនអាចកត់ត្រាវត្តមានបានទេ</div>
            <ul class="mt-2 list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="border-b border-zinc-200 px-5 py-4 dark:border-zinc-700">
            <flux:heading size="lg">កាមេរ៉ាស្កេន QR Code</flux:heading>
            <flux:subheading>សូមអនុញ្ញាត Camera និង Location ក្នុង Browser</flux:subheading>
        </div>

        <div class="p-5">
            <div class="relative aspect-square max-h-[480px] overflow-hidden rounded-2xl bg-black">
                <video x-ref="video" autoplay muted playsinline class="size-full object-cover"></video>

                <div class="pointer-events-none absolute inset-0 flex items-center justify-center">
                    <div class="relative size-64 max-h-[70%] max-w-[70%]">
                        <span class="absolute left-0 top-0 size-12 border-l-4 border-t-4 border-white"></span>
                        <span class="absolute right-0 top-0 size-12 border-r-4 border-t-4 border-white"></span>
                        <span class="absolute bottom-0 left-0 size-12 border-b-4 border-l-4 border-white"></span>
                        <span class="absolute bottom-0 right-0 size-12 border-b-4 border-r-4 border-white"></span>
                    </div>
                </div>

                <div x-show="cameraError" x-cloak class="absolute inset-0 flex items-center justify-center bg-black/90 p-6 text-center text-white">
                    <p x-text="cameraError"></p>
                </div>
            </div>

            <div class="mt-4 flex flex-wrap gap-3">
                <flux:button type="button" variant="primary" icon="camera" x-on:click="startCamera()">
                    បើកកាមេរ៉ា
                </flux:button>
                <flux:button type="button" variant="ghost" icon="stop" x-on:click="stopCamera()">
                    បិទកាមេរ៉ា
                </flux:button>
                <flux:button type="button" variant="ghost" icon="map-pin" x-on:click="requestLocation()">
                    ទទួលទីតាំងម្ដងទៀត
                </flux:button>
            </div>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="font-medium">ទីតាំង GPS</div>
            <div class="mt-1 text-sm text-zinc-500">{{ $locationStatus }}</div>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="font-medium">ស្ថានភាព QR</div>
            <div class="mt-1 text-sm text-zinc-500">{{ $scannerStatus }}</div>
        </div>
    </div>

    <form wire:submit="submitAttendance" class="space-y-4 rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:textarea
            wire:model="qrPayload"
            label="QR Payload"
            placeholder='{"type":"bizhr_attendance","version":1,"branch_id":1,"token":"..."}'
            rows="4"
        />
        <div class="flex justify-end">
            <flux:button type="submit" variant="primary" icon="check-circle" wire:loading.attr="disabled">
                <span wire:loading.remove>កត់ត្រាវត្តមាន</span>
                <span wire:loading>កំពុងដំណើរការ...</span>
            </flux:button>
        </div>
    </form>

    @script
        <script>
            Alpine.data('attendanceQrScanner', () => ({
                stream: null,
                timer: null,
                cameraError: '',

                initialize() {
                    this.requestLocation();
                    this.startCamera();
                },

                requestLocation() {
                    if (!navigator.geolocation) {
                        this.$wire.locationFailed('Browser នេះមិនគាំទ្រ GPS Location ទេ។');
                        return;
                    }

                    navigator.geolocation.getCurrentPosition(
                        position => this.$wire.setLocation(
                            position.coords.latitude,
                            position.coords.longitude
                        ),
                        () => this.$wire.locationFailed('មិនអាចទទួលបានទីតាំង GPS ទេ។'),
                        { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
                    );
                },

                async startCamera() {
                    this.stopCamera();
                    this.cameraError = '';

                    if (!navigator.mediaDevices?.getUserMedia) {
                        this.cameraError = 'Browser នេះមិនគាំទ្រការប្រើកាមេរ៉ាទេ។';
                        return;
                    }

                    if (!('BarcodeDetector' in window)) {
                        this.cameraError = 'Browser នេះមិនគាំទ្រ QR scanner ទេ។ សូមប្រើ Chrome ឬ Edge ជំនាន់ថ្មី។';
                        return;
                    }

                    try {
                        this.stream = await navigator.mediaDevices.getUserMedia({
                            video: { facingMode: { ideal: 'environment' } },
                            audio: false
                        });
                        this.$refs.video.srcObject = this.stream;
                        await this.$refs.video.play();
                        this.detect();
                    } catch (error) {
                        this.cameraError = 'មិនអាចបើកកាមេរ៉ាបានទេ។ សូមអនុញ្ញាត Camera ក្នុង Browser។';
                    }
                },

                async detect() {
                    if (!this.stream) return;

                    try {
                        const detector = new BarcodeDetector({ formats: ['qr_code'] });
                        const codes = await detector.detect(this.$refs.video);

                        if (codes.length && codes[0].rawValue) {
                            await this.$wire.setQrPayload(codes[0].rawValue);
                            this.stopCamera();
                            await this.$wire.submitAttendance();
                            return;
                        }
                    } catch (error) {
                        console.error(error);
                    }

                    this.timer = window.setTimeout(() => this.detect(), 400);
                },

                stopCamera() {
                    if (this.timer) window.clearTimeout(this.timer);
                    this.timer = null;
                    this.stream?.getTracks().forEach(track => track.stop());
                    this.stream = null;
                    if (this.$refs.video) this.$refs.video.srcObject = null;
                }
            }));
        </script>
    @endscript
</div>
