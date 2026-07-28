<div class="w-44 p-2 rounded-sm bg-zinc-900 text-zinc-200">
    <div class="flex items-center justify-between mb-2">
        <h3 class="text-sm font-semibold">Preview</h3>
        <button wire:click="$toggle('showEmployees')" class="text-xs text-zinc-400">Toggle</button>
    </div>

    <div class="space-y-1 text-xs">
        <label class="flex items-center gap-2">
            <input type="checkbox" wire:model="showEmployees" class="h-4 w-4 text-blue-500 bg-zinc-800 rounded" />
            <span class="truncate">បញ្ជីបុគ្គលិក</span>
        </label>

        <label class="flex items-center gap-2">
            <input type="checkbox" wire:model="showWorkShifts" class="h-4 w-4 text-blue-500 bg-zinc-800 rounded" />
            <span class="truncate">វេនការងារ</span>
        </label>

        <label class="flex items-center gap-2">
            <input type="checkbox" wire:model="showSchedules" class="h-4 w-4 text-blue-500 bg-zinc-800 rounded" />
            <span class="truncate">កាលវិភាគ</span>
        </label>

        <label class="flex items-center gap-2">
            <input type="checkbox" wire:model="showAttendance" class="h-4 w-4 text-blue-500 bg-zinc-800 rounded" />
            <span class="truncate">វត្តមាន</span>
        </label>

        <label class="flex items-center gap-2">
            <input type="checkbox" wire:model="showCorrections" class="h-4 w-4 text-blue-500 bg-zinc-800 rounded" />
            <span class="truncate">កែសម្រួល (Manager)</span>
        </label>
    </div>
</div>
