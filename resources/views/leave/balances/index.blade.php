<x-layouts::app title="Leave Balances">
    <x-workspace-command-bar title="Employee Leave Balances" icon="fa-chart-pie" context="Leave Management">
        <x-slot:filters>
            <form class="reference-filter-form" method="GET" hx-get="{{ request()->url() }}" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="true">
                <div class="input-group reference-search">
                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input class="form-control" name="search" value="{{ request('search') }}" placeholder="Search employee..." hx-get="{{ request()->url() }}" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="false" hx-trigger="keyup changed delay:500ms">
                </div>
                <select class="form-select reference-status" name="leave_type_id" hx-get="{{ request()->url() }}" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="true" hx-trigger="change">
                    <option value="">All leave types</option>
                    @foreach($leaveTypes as $type)
                        <option value="{{ $type->id }}" @selected(request('leave_type_id') == $type->id)>{{ $type->name }}</option>
                    @endforeach
                </select>
                <input class="form-control reference-status" type="number" min="2000" max="2100" name="year" value="{{ $year }}" aria-label="Year">
                <button class="btn btn-primary reference-search-button">Filter</button>
            </form>
        </x-slot:filters>
        <x-slot:actions>
            @can('leave.manage')
                <div class="d-flex gap-1">
                    <form method="POST" action="{{ route('leave.balances.synchronize') }}">
                        @csrf
                        <input type="hidden" name="year" value="{{ $year }}">
                        <button class="btn btn-action-link btn-sm" type="submit">
                            <i class="fa-solid fa-arrows-rotate"></i><span>Synchronize</span>
                        </button>
                    </form>
                    <form method="POST" action="{{ route('leave.balances.initialize') }}">
                        @csrf
                        <input type="hidden" name="year" value="{{ $year }}">
                        <button class="btn btn-action-link btn-sm" type="submit">
                            <i class="fa-solid fa-circle-plus"></i><span>Initialize {{ $year }}</span>
                        </button>
                    </form>
                </div>
            @endcan
        </x-slot:actions>
    </x-workspace-command-bar>

    @if(session('status'))
        <div class="alert alert-success d-flex align-items-center gap-2"><i class="fa-solid fa-circle-check"></i><span>{{ session('status') }}</span></div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger d-flex align-items-center gap-2"><i class="fa-solid fa-circle-exclamation"></i><span>{{ $errors->first() }}</span></div>
    @endif

    <div class="reference-list" data-list-container>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Employee</th>
                        <th>Leave Type</th>
                        <th>Year</th>
                        <th>Earned</th>
                        <th>Used</th>
                        <th>Adjustments</th>
                        <th>Remaining</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($balances as $balance)
                        <tr>
                            <td class="ps-3">
                                <div class="fw-medium text-dark">{{ $balance->employee?->getFullName() }}</div>
                                <small class="text-body-secondary">{{ $balance->employee?->employee_code }} · {{ $balance->employee?->department?->name }}</small>
                            </td>
                            <td>{{ $balance->leaveType?->name }} <span class="badge text-bg-light border text-dark">{{ $balance->leaveType?->code }}</span></td>
                            <td>{{ $balance->year }}</td>
                            <td>{{ number_format((float)$balance->earned_days, 1) }}</td>
                            <td>{{ number_format((float)$balance->used_days, 1) }}</td>
                            <td>{{ number_format((float)$balance->adjustment_days, 1) }}</td>
                            <td class="fw-bold text-primary">{{ number_format((float)$balance->remaining_days, 1) }} days</td>
                            <td class="text-end pe-3 text-nowrap">
                                @can('leave.manage')
                                    <button class="btn btn-action-link btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#adjust{{ $balance->id }}">
                                        <i class="fa-solid fa-pen"></i><span>Adjust</span>
                                    </button>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="text-center text-body-secondary py-5" colspan="8">
                                <i class="fa-solid fa-chart-pie fa-xl d-block mb-3 text-primary"></i>No leave balance records found. Click "Initialize {{ $year }}" to generate yearly balances.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-pagination-footer :paginator="$balances" />
    </div>

    @can('leave.manage')
        @foreach($balances as $balance)
            <div class="modal fade" id="adjust{{ $balance->id }}" tabindex="-1">
                <div class="modal-dialog">
                    <form class="modal-content" method="POST" action="{{ route('leave.balances.adjust', $balance) }}">
                        @csrf
                        <div class="modal-header">
                            <h2 class="modal-title fs-5"><i class="fa-solid fa-pen-to-square me-2 text-primary"></i>Adjust balance · {{ $balance->employee?->getFullName() }}</h2>
                            <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p class="small text-body-secondary mb-3">
                                <strong>{{ $balance->leaveType?->name }}</strong> · Year {{ $year }} · Current remaining: <strong>{{ number_format($balance->remaining_days, 1) }} days</strong>
                            </p>
                            <label class="form-label">Adjustment days (+ adds, - deducts) <span class="text-danger">*</span></label>
                            <input class="form-control" name="adjustment_days" type="number" min="-365" max="365" step="0.5" value="{{ $balance->adjustment_days }}" required>
                            <div class="form-text">Example: +2.0 to grant 2 days bonus, or -1.0 to deduct.</div>
                            <label class="form-label mt-3">Adjustment reason / note <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="reason" required minlength="5" maxlength="1000" rows="3" placeholder="Provide reason for balance adjustment..."></textarea>
                        </div>
                        <x-form-save-actions save-label="Save & close" />
                    </form>
                </div>
            </div>
        @endforeach
    @endcan
</x-layouts::app>