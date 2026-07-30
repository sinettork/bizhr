<?php

use App\Models\AttendanceQrSession;
use App\Models\Branch;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('QR វត្តមានប្រចាំសាខា')] class extends Component
{
    public int $branchId;
    public string $qrUrl = '';
    public string $expiresAt = '';
    public int $lifetimeSeconds = 45;

    public function mount(): void
    {
        abort_unless(
            auth()->user()?->can('attendance.approve')
            || auth()->user()?->can('attendance.report'),
            403
        );

        $this->lifetimeSeconds = max(
            30,
            min(120, (int) config('attendance.qr.session_lifetime_seconds', 45)),
        );

        $this->branchId = Branch::query()
            ->where('is_active', true)
            ->where('attendance_qr_enabled', true)
            ->value('id') ?? 0;

        abort_if($this->branchId === 0, 404, 'មិនមានសាខាដែលបានបើក QR Attendance។');

        $this->generateQr();
    }

    #[Computed]
    public function branches()
    {
        return Branch::query()
            ->where('is_active', true)
            ->where('attendance_qr_enabled', true)
            ->orderByDesc('is_head_office')
            ->orderBy('name')
            ->get(['id', 'name', 'code']);
    }

    #[Computed]
    public function branch(): Branch
    {
        return Branch::query()
            ->whereKey($this->branchId)
            ->where('is_active', true)
            ->where('attendance_qr_enabled', true)
            ->firstOrFail();
    }

    public function updatedBranchId(): void
    {
        unset($this->branch);
        $this->generateQr();
    }

    public function refreshQr(): void
    {
        if (
            $this->expiresAt === ''
            || now()->addSeconds(8)->gte(\Illuminate\Support\Carbon::parse($this->expiresAt))
        ) {
            $this->generateQr();
        }
    }

    public function generateQr(): void
    {
        $branch = $this->branch;

        if (! $branch->attendance_qr_token) {
            $branch->regenerateAttendanceQrToken();
        }

        AttendanceQrSession::query()
            ->where('expires_at', '<', now()->subHour())
            ->delete();

        $plainToken = Str::random(80);
        $expiresAt = now()->addSeconds($this->lifetimeSeconds);

        AttendanceQrSession::query()->create([
            'branch_id' => $branch->id,
            'token_hash' => hash('sha256', $plainToken),
            'expires_at' => $expiresAt,
            'created_by' => auth()->id(),
        ]);

        $this->qrUrl = route('attendance.qr.start', ['token' => $plainToken]);
        $this->expiresAt = $expiresAt->toIso8601String();
    }
};
?>

<div
    class="mx-auto w-full max-w-5xl space-y-6"
    wire:poll.5s="refreshQr"
    x-data="{
        remaining: 0,
        timer: null,
        async draw() {
            if (!window.QRCode || !$refs.canvas || !$wire.qrUrl) return;
            await window.QRCode.toCanvas($refs.canvas, $wire.qrUrl, {
                width: 380,
                margin: 2,
                errorCorrectionLevel: 'M',
                color: { dark: '#09090b', light: '#ffffff' }
            });
        },
        tick() {
            const expiry = new Date($wire.expiresAt).getTime();
            this.remaining = Math.max(0, Math.ceil((expiry - Date.now()) / 1000));
        },
        init() {
            this.draw();
            this.tick();
            this.timer = setInterval(() => {
                this.tick();
                this.draw();
            }, 1000);
        }
    }"
>
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <flux:heading size="xl">QR វត្តមានប្រចាំសាខា</flux:heading>
            <flux:subheading class="mt-1">
                បុគ្គលិកអាចប្រើ Camera ទូរស័ព្ទស្កេន ដើម្បីចុះម៉ោងដោយស្វ័យប្រវត្តិ
            </flux:subheading>
        </div>

        <div class="w-full lg:w-72">
            <flux:select wire:model.live="branchId" label="ជ្រើសរើសសាខា">
                @foreach ($this->branches as $branch)
                    <option value="{{ $branch->id }}">
                        {{ $branch->name }} ({{ $branch->code }})
                    </option>
                @endforeach
            </flux:select>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-[1fr_320px]">
        <div class="flex min-h-[560px] items-center justify-center rounded-3xl border border-zinc-200 bg-white p-8 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="text-center">
                <div class="mx-auto inline-flex rounded-3xl bg-white p-5 shadow-lg">
                    <canvas x-ref="canvas" wire:ignore></canvas>
                </div>

                <div class="mt-6 text-2xl font-semibold text-zinc-900 dark:text-white">
                    {{ $this->branch->name }}
                </div>

                <div class="mt-2 text-zinc-500 dark:text-zinc-400">
                    {{ now()->timezone('Asia/Phnom_Penh')->format('d/m/Y H:i:s') }}
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <div class="rounded-2xl border border-blue-200 bg-blue-50 p-6 text-center dark:border-blue-800 dark:bg-blue-950/30">
                <div class="text-sm text-blue-700 dark:text-blue-300">QR នឹងផ្លាស់ប្ដូរក្នុង</div>
                <div class="mt-2 text-5xl font-bold text-blue-700 dark:text-blue-300" x-text="remaining"></div>
                <div class="mt-1 text-sm text-blue-600 dark:text-blue-400">វិនាទី</div>
            </div>

            <div class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="lg">របៀបប្រើ</flux:heading>
                <ol class="mt-4 list-inside list-decimal space-y-3 text-sm text-zinc-600 dark:text-zinc-300">
                    <li>បើក Camera នៅលើទូរស័ព្ទ</li>
                    <li>ស្កេន QR Code លើអេក្រង់នេះ</li>
                    <li>បើកតំណ BizHR និងចូលគណនី</li>
                    <li>អនុញ្ញាត GPS Location</li>
                    <li>ប្រព័ន្ធកត់ត្រាម៉ោងដោយស្វ័យប្រវត្តិ</li>
                </ol>
            </div>

            @if (in_array(parse_url($qrUrl, PHP_URL_HOST), ['localhost', '127.0.0.1'], true))
                <div class="rounded-2xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-800 dark:border-amber-700 dark:bg-amber-950/30 dark:text-amber-200">
                    QR នេះប្រើ localhost ដូច្នេះទូរស័ព្ទផ្សេងមិនអាចបើកបានទេ។ សូមបើកទំព័រនេះតាម HTTPS domain ឬ tunnel URL។
                </div>
            @endif

            <flux:button class="w-full" variant="primary" icon="arrow-path" wire:click="generateQr">
                បង្កើត QR ថ្មីឥឡូវនេះ
            </flux:button>
        </div>
    </div>
</div>
