<div wire:poll.15s class="space-y-6">
        <div class="flex items-center justify-between"><div><flux:heading size="xl">ផ្ទាំងគ្រប់គ្រង</flux:heading><flux:text>{{ now()->format('d/m/Y H:i') }} · ធ្វើបច្ចុប្បន្នភាពដោយស្វ័យប្រវត្តិ</flux:text></div><flux:badge color="green">កំពុងដំណើរការ</flux:badge></div>
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            <flux:card><flux:text>បុគ្គលិកសកម្ម</flux:text><flux:heading size="xl">{{ $totalEmployees }}</flux:heading></flux:card>
            <flux:card><flux:text>វត្តមានថ្ងៃនេះ</flux:text><flux:heading size="xl">{{ $presentToday }}</flux:heading></flux:card>
            <flux:card><flux:text>មកយឺតថ្ងៃនេះ</flux:text><flux:heading size="xl">{{ $lateToday }}</flux:heading></flux:card>
            <flux:card><flux:text>កំពុងឈប់សម្រាក</flux:text><flux:heading size="xl">{{ $onLeaveToday }}</flux:heading></flux:card>
            <flux:card><flux:text>សំណើសុំច្បាប់រង់ចាំ</flux:text><flux:heading size="xl">{{ $pendingLeaveRequests }}</flux:heading></flux:card>
        </div>
        <flux:card><div class="flex items-center justify-between mb-4"><flux:heading size="lg">វត្តមានថ្ងៃនេះ</flux:heading>@can('attendance.report')<flux:button :href="route('attendance.reports.index')" size="sm" wire:navigate>មើលរបាយការណ៍</flux:button>@endcan</div><div class="divide-y divide-zinc-200 dark:divide-zinc-700">@forelse($recentAttendances as $attendance)<div class="flex items-center justify-between py-3"><div><div class="font-medium">{{ $attendance->employee?->getFullName() ?? 'មិនស្គាល់បុគ្គលិក' }}</div><div class="text-sm text-zinc-500">ចូលធ្វើការម៉ោង {{ $attendance->check_in_at?->format('H:i') ?? '—' }}</div></div><flux:badge>{{ $attendance->status }}</flux:badge></div>@empty<flux:text>មិនទាន់មានកំណត់ត្រាវត្តមានសម្រាប់ថ្ងៃនេះទេ។</flux:text>@endforelse</div></flux:card>
</div>
