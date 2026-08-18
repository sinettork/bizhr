<x-layouts::app title="Employee Goals & OKRs">
    <x-workspace-command-bar title="Employee Goals & Objectives" icon="fa-trophy" context="Performance & OKRs">
        <x-slot:actions>
            <x-list-actions :add-modal="auth()->user()->can('performance.manage-goals') ? 'addGoal' : null" add-label="Assign goal" />
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
                        <th class="ps-3">Goal</th>
                        <th>Employee</th>
                        <th>Target</th>
                        <th>Current Reported</th>
                        <th>Weight</th>
                        <th>Due Date</th>
                        <th class="pe-3">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($goals as $goal)
                        @php
                            $statusBadge = match($goal->status) {
                                'achieved' => 'success',
                                'returned' => 'danger',
                                default => 'primary',
                            };
                        @endphp
                        <tr>
                            <td class="ps-3">
                                <div class="fw-medium text-dark">{{ $goal->title }}</div>
                                @if($goal->description)<small class="text-body-secondary">{{ \Illuminate\Support\Str::limit($goal->description, 60) }}</small>@endif
                            </td>
                            <td>{{ $goal->employee?->getFullName() }}</td>
                            <td>{{ $goal->target_value }} {{ $goal->measurement_unit }}</td>
                            <td class="fw-semibold text-primary">{{ $goal->current_value ?: 0 }} {{ $goal->measurement_unit }}</td>
                            <td>{{ $goal->weight }}%</td>
                            <td>{{ $goal->due_date?->format('d M Y') ?? 'Ongoing' }}</td>
                            <td class="pe-3">
                                <span class="badge text-bg-{{ $statusBadge }}">{{ str($goal->status)->title() }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="text-center text-body-secondary py-5" colspan="7">
                                <i class="fa-solid fa-trophy fa-xl d-block mb-3 text-primary"></i>No performance goals assigned.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-pagination-footer :paginator="$goals" />
    </div>

    @can('performance.manage-goals')
        <div class="modal fade" id="addGoal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <form class="modal-content" method="POST" action="{{ route('performance.goals.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h2 class="modal-title fs-5"><i class="fa-solid fa-trophy me-2 text-primary"></i>Assign goal</h2>
                        <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Employee <span class="text-danger">*</span></label>
                                <select class="form-select" name="employee_id" required>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}">{{ $employee->getFullName() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Goal Title <span class="text-danger">*</span></label>
                                <input class="form-control" name="title" required placeholder="e.g. Sales quota target">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Measurement Unit <span class="text-danger">*</span></label>
                                <input class="form-control" name="measurement_unit" required placeholder="e.g. USD, units, %">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Target Value <span class="text-danger">*</span></label>
                                <input class="form-control" type="number" step=".01" name="target_value" required placeholder="100">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Weight % <span class="text-danger">*</span></label>
                                <input class="form-control" type="number" step=".01" name="weight" value="100" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Start Date <span class="text-danger">*</span></label>
                                <input class="form-control" type="date" name="start_date" required value="{{ today()->toDateString() }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Due Date <span class="text-danger">*</span></label>
                                <input class="form-control" type="date" name="due_date" required value="{{ today()->addMonths(3)->toDateString() }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description & Success Criteria</label>
                                <textarea class="form-control" name="description" rows="2" placeholder="Key outcomes and milestone notes..."></textarea>
                            </div>
                        </div>
                    </div>
                    <x-form-save-actions save-label="Assign goal" />
                </form>
            </div>
        </div>
    @endcan
</x-layouts::app>
