<x-layouts::app title="Review attendance corrections">
    <x-workspace-command-bar title="Review attendance corrections" icon="fa-list-check" context="Time and attendance">
        <x-slot:actions>
            <span class="status-text text-bg-warning">{{ number_format($corrections->total()) }} pending</span>
        </x-slot:actions>
    </x-workspace-command-bar>

    @if(session('status'))
        <div class="alert alert-success d-flex align-items-center gap-2">
            <i class="fa-solid fa-circle-check"></i><span>{{ session('status') }}</span>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger d-flex align-items-center gap-2">
            <i class="fa-solid fa-circle-exclamation"></i><span>{{ $errors->first() }}</span>
        </div>
    @endif

    <div class="reference-list" data-list-container>
        <div class="reference-list-toolbar">
            <div>
                <div class="fw-semibold text-dark">Pending correction decisions</div>
                <div class="small text-body-secondary">Compare recorded attendance with the requested change before deciding.</div>
            </div>
        </div>

        @forelse($corrections as $correction)
            @php
                $attendance = $correction->attendance;
                $employee = $correction->employee;
                $originalIn = $attendance?->check_in_at?->format('H:i');
                $originalOut = $attendance?->check_out_at?->format('H:i');
                $requestedIn = $correction->requested_check_in?->format('H:i');
                $requestedOut = $correction->requested_check_out?->format('H:i');
                $changesIn = filled($requestedIn) && $requestedIn !== $originalIn;
                $changesOut = filled($requestedOut) && $requestedOut !== $originalOut;
            @endphp

            <div class="px-3 py-3 border-bottom">
                <div class="row g-3 align-items-start">
                    <div class="col-12 col-xl-3">
                        <div class="fw-semibold text-dark">{{ $employee?->getFullName() }}</div>
                        <div class="small text-body-secondary mt-1">
                            {{ $employee?->employee_code ?: '—' }}
                            @if($employee?->department?->name)
                                · {{ $employee->department->name }}
                            @endif
                            @if($employee?->branch?->name)
                                · {{ $employee->branch->name }}
                            @endif
                        </div>
                        <div class="small text-body-secondary mt-2">{{ $attendance?->work_date?->format('d M Y') ?: '—' }}</div>
                    </div>

                    <div class="col-12 col-md-6 col-xl-3">
                        <div class="small text-body-secondary mb-1">Recorded attendance</div>
                        <div class="d-flex gap-4">
                            <div>
                                <span class="small text-body-secondary d-block">In</span>
                                <strong>{{ $originalIn ?: '—' }}</strong>
                            </div>
                            <div>
                                <span class="small text-body-secondary d-block">Out</span>
                                <strong>{{ $originalOut ?: '—' }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-xl-3">
                        <div class="small text-body-secondary mb-1">Requested correction</div>
                        <div class="d-flex gap-4">
                            <div>
                                <span class="small text-body-secondary d-block">In</span>
                                <strong class="{{ $changesIn ? 'text-primary' : '' }}">{{ $requestedIn ?: $originalIn ?: '—' }}</strong>
                                @if($changesIn)<span class="status-text text-bg-primary d-block mt-1">Changed</span>@endif
                            </div>
                            <div>
                                <span class="small text-body-secondary d-block">Out</span>
                                <strong class="{{ $changesOut ? 'text-primary' : '' }}">{{ $requestedOut ?: $originalOut ?: '—' }}</strong>
                                @if($changesOut)<span class="status-text text-bg-primary d-block mt-1">Changed</span>@endif
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-xl-3">
                        <div class="small text-body-secondary mb-1">Employee reason</div>
                        <div class="small text-dark mb-3">{{ $correction->reason }}</div>
                        <x-decision-actions
                            class="justify-content-xl-end"
                            :approve-url="route('attendance.corrections.approve', $correction)"
                            reject-target="#rejectCorrection{{ $correction->id }}"
                            approve-label="Approve correction"
                            approve-confirm="Approve this attendance correction? The attendance record will be updated and recalculated."
                            approve-confirm-title="Confirm attendance correction"
                        />
                    </div>
                </div>
            </div>

            <div class="modal fade" id="rejectCorrection{{ $correction->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <form class="modal-content" method="POST" action="{{ route('attendance.corrections.reject', $correction) }}">
                        @csrf
                        <div class="modal-header">
                            <div>
                                <h2 class="modal-title fs-5">Reject correction request</h2>
                                <div class="small text-body-secondary mt-1">{{ $employee?->getFullName() }} · {{ $attendance?->work_date?->format('d M Y') }}</div>
                            </div>
                            <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <label class="form-label" for="rejectNote{{ $correction->id }}">Reason for rejection <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="rejectNote{{ $correction->id }}" name="note" required minlength="3" maxlength="1000" rows="3" placeholder="Explain why this correction cannot be approved."></textarea>
                            <div class="form-text">This note remains in the correction history.</div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancel</button>
                            <button class="btn btn-danger" type="submit"><i class="fa-solid fa-xmark me-1"></i>Reject correction</button>
                        </div>
                    </form>
                </div>
            </div>
        @empty
            <x-empty-state class="py-5 px-3" icon="fa-check-double" tone="success" title="No corrections waiting for review" message="The attendance correction queue is clear." />
        @endforelse

        @if($corrections->count())
            <x-pagination-footer :paginator="$corrections" />
        @endif
    </div>
</x-layouts::app>
