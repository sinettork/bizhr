@php
    $isSeparated = in_array($employee->employment_status, ['Resigned', 'Terminated', 'Retired'], true);
@endphp

<details class="reference-list mb-3" @if($isSeparated && ! $readiness['ready']) open @endif>
    <summary class="px-3 py-2 d-flex flex-wrap align-items-center justify-content-between gap-2" style="cursor:pointer;list-style:none;">
        <span>
            <span class="fw-semibold text-dark">Offboarding readiness</span>
            <span class="small text-body-secondary ms-2">Check handover before Resigned, Terminated, or Retired.</span>
        </span>
        <span class="status-text text-bg-{{ $readiness['ready'] ? 'success' : 'warning' }}">
            {{ $readiness['ready'] ? 'Ready' : $readiness['outstanding'].' area'.($readiness['outstanding'] === 1 ? '' : 's').' need attention' }}
        </span>
    </summary>

    <div class="table-responsive border-top">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th class="ps-3">Check</th>
                    <th>Current state</th>
                    <th class="pe-3">Guidance</th>
                </tr>
            </thead>
            <tbody>
                @foreach($readiness['items'] as $item)
                    <tr>
                        <td class="ps-3 fw-semibold text-dark">{{ $item['label'] }}</td>
                        <td>
                            <span class="status-text text-bg-{{ $item['blocking'] ? 'warning' : 'success' }}">
                                {{ $item['blocking'] ? number_format($item['count']).' outstanding' : 'Clear' }}
                            </span>
                        </td>
                        <td class="pe-3 small text-body-secondary">{{ $item['message'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($isSeparated && ! $readiness['ready'])
        <div class="px-3 py-2 border-top small text-warning-emphasis">
            <i class="fa-solid fa-triangle-exclamation me-1"></i>
            This employee is already separated, but operational handover still has outstanding items. Resolve them before archiving the employee record.
        </div>
    @endif
</details>
