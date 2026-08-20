@php
    $isSeparated = in_array($employee->employment_status, ['Resigned', 'Terminated', 'Retired'], true);
@endphp

<section class="reference-list mb-3" aria-labelledby="offboarding-readiness-title">
    <div class="px-3 py-2 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div>
            <h2 class="h6 fw-bold text-dark mb-0" id="offboarding-readiness-title">Offboarding readiness</h2>
            <div class="small text-body-secondary">Review operational handover before setting this employee to Resigned, Terminated, or Retired.</div>
        </div>
        <span class="status-text text-bg-{{ $readiness['ready'] ? 'success' : 'warning' }}">
            {{ $readiness['ready'] ? 'Ready' : $readiness['outstanding'].' area'.($readiness['outstanding'] === 1 ? '' : 's').' need attention' }}
        </span>
    </div>

    <div class="table-responsive">
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
</section>
