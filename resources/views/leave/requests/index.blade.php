<x-layouts::app title="Leave Requests">
    <x-workspace-command-bar title="My Leave & Time Off" icon="fa-calendar-day" context="Personal Workspace">
        <x-slot:actions>
            @can('attendance.checkin')
                <a class="btn btn-action-link btn-sm" href="{{ route('attendance.checkinout') }}"><i class="fa-solid fa-user-clock"></i><span>Attendance</span></a>
            @endcan
            <x-list-actions :add-modal="'newLeaveRequest'" add-label="New leave request" />
        </x-slot:actions>
    </x-workspace-command-bar>

    @if (session('status'))
        <div class="alert alert-success d-flex align-items-center gap-2"><i class="fa-solid fa-circle-check"></i><span>{{ session('status') }}</span></div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger"><ul class="mb-0 ps-3">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <div class="reference-list" data-list-container>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Leave Type</th>
                        <th>Dates</th>
                        <th>Duration</th>
                        <th>Reason</th>
                        <th>Submitted</th>
                        <th class="pe-3">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($requests as $leaveRequest)
                        @php
                            $statusBadge = match($leaveRequest->status) {
                                'approved' => 'success',
                                'rejected' => 'danger',
                                default => 'warning',
                            };
                        @endphp
                        <tr>
                            <td class="ps-3 fw-medium text-dark">{{ $leaveRequest->leaveType?->name }}</td>
                            <td>{{ $leaveRequest->start_date->format('d M Y') }} – {{ $leaveRequest->end_date->format('d M Y') }}</td>
                            <td>{{ number_format($leaveRequest->total_days ?? 0, 1) }} day{{ ($leaveRequest->total_days ?? 0) == 1 ? '' : 's' }}</td>
                            <td><span class="small text-body-secondary">{{ \Illuminate\Support\Str::limit($leaveRequest->reason, 70) ?: '—' }}</span></td>
                            <td><span class="small text-body-secondary">{{ $leaveRequest->created_at?->format('d M Y') ?? '—' }}</span></td>
                            <td class="pe-3">
                                <span class="badge text-bg-{{ $statusBadge }}">{{ ucfirst(str_replace('_', ' ', $leaveRequest->status)) }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="text-center text-body-secondary py-5" colspan="6">
                                <i class="fa-solid fa-calendar-xmark fa-xl d-block mb-3 text-primary"></i>You haven't requested any time off yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-pagination-footer :paginator="$requests" />
    </div>

    <div class="modal fade" id="newLeaveRequest" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form class="modal-content" method="POST" action="{{ route('leave.requests.store') }}">
                @csrf
                <div class="modal-header">
                    <h2 class="modal-title fs-5"><i class="fa-solid fa-calendar-plus me-2 text-primary"></i>Request time off</h2>
                    <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Leave type <span class="text-danger">*</span></label>
                            <select class="form-select" name="leave_type_id" required>
                                <option value="">Select type...</option>
                                @foreach ($types as $type)
                                    <option value="{{ $type->id }}" @selected(old('leave_type_id') == $type->id)>{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Start date <span class="text-danger">*</span></label>
                            <input class="form-control" name="start_date" type="date" value="{{ old('start_date', today()->toDateString()) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">End date <span class="text-danger">*</span></label>
                            <input class="form-control" name="end_date" type="date" value="{{ old('end_date', today()->toDateString()) }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Reason / Justification</label>
                            <textarea class="form-control" name="reason" rows="3" placeholder="Provide notes or reason for this leave request...">{{ old('reason') }}</textarea>
                        </div>
                    </div>
                </div>
                <x-form-save-actions save-label="Submit leave request" />
            </form>
        </div>
    </div>
</x-layouts::app>
