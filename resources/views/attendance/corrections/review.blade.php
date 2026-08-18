<x-layouts::app title="Review attendance corrections">
    <x-workspace-command-bar title="Review attendance corrections" icon="fa-list-check" context="Time and attendance" />

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

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
        <div>
            <h2 class="h6 fw-bold mb-1">Pending correction decisions</h2>
            <p class="small text-body-secondary mb-0">Compare the recorded attendance with the employee's requested change before approving.</p>
        </div>
        <span class="status-text text-bg-primary">{{ $corrections->total() }} pending</span>
    </div>

    <div class="vstack gap-3 reference-list" data-list-container>
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

            <article class="card border-0 shadow-sm">
                <div class="card-body p-3 p-lg-4">
                    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                        <div class="d-flex gap-3 align-items-start">
                            <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width:42px;height:42px">
                                <i class="fa-solid fa-user-clock"></i>
                            </div>
                            <div>
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                    <h3 class="h6 fw-bold mb-0">{{ $employee?->getFullName() }}</h3>
                                    <span class="status-text text-bg-warning">Pending</span>
                                </div>
                                <div class="small text-body-secondary">
                                    {{ $employee?->employee_code ?: '—' }}
                                    <span class="mx-1">•</span>
                                    {{ $employee?->department?->name ?: 'No department' }}
                                    @if($employee?->branch?->name)
                                        <span class="mx-1">•</span>{{ $employee->branch->name }}
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="text-lg-end">
                            <div class="small text-body-secondary">Attendance date</div>
                            <div class="fw-semibold">{{ $attendance?->work_date?->format('d M Y') ?: '—' }}</div>
                        </div>
                    </div>

                    <div class="row g-3 mt-1">
                        <div class="col-12 col-xl-7">
                            <div class="border rounded-3 overflow-hidden h-100">
                                <div class="row g-0">
                                    <div class="col-12 col-md-6 p-3 border-end-md">
                                        <div class="small text-uppercase text-body-secondary fw-semibold mb-2">Recorded attendance</div>
                                        <div class="d-flex gap-4">
                                            <div>
                                                <div class="small text-body-secondary">Check in</div>
                                                <div class="fs-5 fw-semibold">{{ $originalIn ?: '—' }}</div>
                                            </div>
                                            <div>
                                                <div class="small text-body-secondary">Check out</div>
                                                <div class="fs-5 fw-semibold">{{ $originalOut ?: '—' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6 p-3 bg-body-tertiary">
                                        <div class="small text-uppercase text-body-secondary fw-semibold mb-2">Requested correction</div>
                                        <div class="d-flex gap-4">
                                            <div>
                                                <div class="small text-body-secondary">Check in</div>
                                                <div class="fs-5 fw-semibold {{ $changesIn ? 'text-primary' : '' }}">{{ $requestedIn ?: $originalIn ?: '—' }}</div>
                                                @if($changesIn)<small class="text-primary"><i class="fa-solid fa-arrow-up-right-dots me-1"></i>Changed</small>@endif
                                            </div>
                                            <div>
                                                <div class="small text-body-secondary">Check out</div>
                                                <div class="fs-5 fw-semibold {{ $changesOut ? 'text-primary' : '' }}">{{ $requestedOut ?: $originalOut ?: '—' }}</div>
                                                @if($changesOut)<small class="text-primary"><i class="fa-solid fa-arrow-up-right-dots me-1"></i>Changed</small>@endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-xl-5">
                            <div class="border rounded-3 p-3 h-100">
                                <div class="small text-uppercase text-body-secondary fw-semibold mb-2">Employee reason</div>
                                <p class="mb-0">{{ $correction->reason }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 pt-3 border-top">
                        <x-decision-actions
                            class="justify-content-end"
                            :compact="false"
                            :approve-url="route('attendance.corrections.approve', $correction)"
                            reject-target="#rejectCorrection{{ $correction->id }}"
                            approve-label="Approve correction"
                        />
                    </div>
                </div>
            </article>

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
                            <textarea class="form-control" id="rejectNote{{ $correction->id }}" name="note" required minlength="3" maxlength="1000" rows="4" placeholder="Explain why this correction cannot be approved."></textarea>
                            <div class="form-text">This note will be visible in the correction history.</div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancel</button>
                            <button class="btn btn-danger" type="submit"><i class="fa-solid fa-xmark me-1"></i>Confirm rejection</button>
                        </div>
                    </form>
                </div>
            </div>
        @empty
            <div class="card border-0 shadow-sm">
                <x-empty-state class="py-5 px-3" icon="fa-check-double" tone="success" title="No corrections waiting for review" message="The attendance correction queue is clear." />
            </div>
        @endforelse

        @if($corrections->hasPages())
            <div class="reference-list-footer bg-transparent border-0 px-0">{{ $corrections->links() }}</div>
        @endif
    </div>
</x-layouts::app>
