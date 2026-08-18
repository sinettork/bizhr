<x-layouts::app title="Attendance Corrections">
    <x-workspace-command-bar title="Attendance Corrections" icon="fa-clipboard-check" context="Attendance Requests" />

    @if(session('status'))
        <div class="alert alert-success d-flex align-items-center gap-2"><i class="fa-solid fa-circle-check"></i><span>{{ session('status') }}</span></div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger d-flex align-items-center gap-2"><i class="fa-solid fa-circle-exclamation"></i><span>{{ $errors->first() }}</span></div>
    @endif

    @foreach($requests->where('status', 'rejected') as $correction)
        <div class="alert alert-warning d-flex flex-column flex-md-row gap-2 align-items-md-center">
            <span><strong>Correction rejected:</strong> {{ $correction->review_note }}</span>
            <form method="POST" action="{{ route('attendance.corrections.reopen', $correction) }}" class="ms-md-auto d-flex gap-2">
                @csrf
                <label class="visually-hidden" for="reopen-reason-{{ $correction->id }}">Reason for reopening</label>
                <input id="reopen-reason-{{ $correction->id }}" class="form-control form-control-sm" name="reason" required minlength="5" maxlength="2000" placeholder="Reason for reopening">
                <button class="btn btn-sm btn-outline-primary text-nowrap" type="submit">Reopen</button>
            </form>
        </div>
    @endforeach

    <div class="card mb-3">
        <div class="card-header">
            <h2 class="h6 mb-0 text-dark fw-bold"><i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i>Submit Correction Request</h2>
        </div>
        <form method="POST" action="{{ route('attendance.corrections.store') }}">
            @csrf
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label class="form-label">Attendance record <span class="text-danger">*</span></label>
                    <select class="form-select" name="attendance_id" required>
                        <option value="">Choose a record</option>
                        @foreach($attendances as $attendance)
                            <option value="{{ $attendance->id }}">{{ $attendance->work_date->format('d M Y') }} · In {{ $attendance->check_in_at?->format('H:i') ?? '—' }} · Out {{ $attendance->check_out_at?->format('H:i') ?? '—' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Correct check-in</label>
                    <input class="form-control" type="datetime-local" name="requested_check_in">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Correct check-out</label>
                    <input class="form-control" type="datetime-local" name="requested_check_out">
                </div>
                <div class="col-12">
                    <label class="form-label">Reason <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="reason" rows="3" required minlength="5" maxlength="2000" placeholder="Explain the reason for time adjustment..."></textarea>
                </div>
            </div>
            <div class="card-footer text-end">
                <button class="btn btn-primary" type="submit"><i class="fa-solid fa-paper-plane me-1"></i>Submit request</button>
            </div>
        </form>
    </div>

    <div class="reference-list" data-list-container>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Date</th>
                        <th>Requested Change</th>
                        <th>Reason</th>
                        <th>Review Note</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $correction)
                        <tr>
                            <td class="ps-3 fw-medium text-dark">{{ $correction->attendance?->work_date?->format('d M Y') }}</td>
                            <td>
                                <div>In: {{ $correction->requested_check_in?->format('d M H:i') ?? '—' }}</div>
                                <div>Out: {{ $correction->requested_check_out?->format('d M H:i') ?? '—' }}</div>
                            </td>
                            <td>{{ $correction->reason }}</td>
                            <td>{{ $correction->review_note ?: '—' }}</td>
                            <td>
                                <span class="badge text-bg-{{ $correction->status === 'approved' ? 'success' : ($correction->status === 'rejected' ? 'danger' : 'warning') }}">
                                    {{ ucfirst($correction->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-5 text-center text-body-secondary">
                                <i class="fa-solid fa-clipboard-check fa-xl d-block mb-3 text-primary"></i>No correction requests submitted.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-pagination-footer :paginator="$requests" />
    </div>
</x-layouts::app>
