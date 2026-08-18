<x-layouts::app :title="$title">
    <x-workspace-command-bar :title="$title" :icon="$icon" :context="$workspaceContext">
        <x-slot:filters>
            <form class="reference-filter-form" method="GET">
                <div class="input-group reference-search"><span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass"></i></span><input class="form-control" name="search" value="{{ $search }}" placeholder="Search {{ strtolower($title) }}" aria-label="Search {{ strtolower($title) }}"></div>
                @if($hasStatus)<select class="form-select reference-status" name="status"><option value="">All statuses</option>@foreach($statusOptions as $option)<option value="{{ $option }}" @selected($status === $option)>{{ ucfirst(str_replace('_', ' ', $option)) }}</option>@endforeach</select>@endif
                <button class="btn btn-primary reference-search-button" type="submit">Search</button>
            </form>
        </x-slot:filters>
    </x-workspace-command-bar>
    <x-placeholder-alert :isPlaceholder="$isPlaceholder ?? false" />
    <div class="reference-list" data-list-container>
        <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr>@foreach($columns as $column)<th class="{{ $loop->first ? 'ps-3' : '' }}">{{ \Illuminate\Support\Str::headline($column) }}</th>@endforeach</tr></thead><tbody>
            @forelse($records as $record)<tr>@foreach($columns as $column)@php($value = data_get($record, $column))<td class="{{ $loop->first ? 'ps-3 fw-medium' : '' }}">
                @if($column === $statusColumn)<span class="badge text-bg-{{ in_array($value, ['active','approved','paid','completed','verified','present'], true) ? 'success' : (in_array($value, ['rejected','cancelled','failed'], true) ? 'danger' : 'warning') }}">{{ ucfirst(str_replace('_', ' ', $value ?? '—')) }}</span>
                @elseif(is_bool($value))<span class="badge text-bg-{{ $value ? 'success' : 'secondary' }}">{{ $value ? 'Yes' : 'No' }}</span>
                @elseif($value instanceof \DateTimeInterface){{ $value->format('d M Y H:i') }}
                @elseif(in_array($column, ['amount','gross_salary','net_salary','base_salary','minimum_salary','maximum_salary','earned_days','used_days','remaining_days'], true) && $value !== null){{ number_format((float) $value, 2) }}
                @else{{ filled($value) ? $value : '—' }}@endif
            </td>@endforeach</tr>
            @empty<tr><td class="py-5 text-center text-body-secondary" colspan="{{ count($columns) }}"><i class="fa-solid {{ $icon }} fa-xl d-block mb-3 text-primary"></i>No {{ strtolower($title) }} records found.</td></tr>@endforelse
        </tbody></table></div>
        <x-pagination-footer :paginator="$records" />
    </div>
</x-layouts::app>
