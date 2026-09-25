@php
    $isOwnProfile = $employee->user_id === auth()->id();
@endphp

<x-layouts::app :title="$isOwnProfile ? 'My Profile' : ($employee->full_name_en ?: $employee->employee_code)">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div>
            <div class="small text-uppercase text-body-secondary fw-semibold">
                @if($isOwnProfile)
                    <a href="{{ route('dashboard') }}" class="text-decoration-none text-body-secondary">Personal Workspace</a>
                    <span class="mx-1">/</span>
                    <span>My Profile</span>
                @else
                    <a href="{{ route('employees.index') }}" class="text-decoration-none text-body-secondary">People & Directory</a>
                    <span class="mx-1">/</span>
                    <span>Employee Profile</span>
                @endif
            </div>
            <h1 class="h5 mb-0 fw-bold text-dark">
                {{ $employee->full_name_en ?: trim($employee->first_name.' '.$employee->last_name) }}
                @if($employee->full_name_km)
                    <span class="text-body-secondary fs-6 fw-normal ms-1">({{ $employee->full_name_km }})</span>
                @endif
            </h1>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <a class="btn btn-light btn-sm" href="{{ $isOwnProfile ? route('dashboard') : route('employees.index') }}">
                <i class="fa-solid fa-arrow-left me-1"></i>{{ $isOwnProfile ? 'Dashboard' : 'Employees' }}
            </a>
            @can('attendance.report')
                <a class="btn btn-action-link btn-sm" href="{{ route('attendance.reports.index', ['employee_id' => $employee->id]) }}" title="Attendance report">
                    <i class="fa-solid fa-clock-rotate-left"></i><span>Attendance</span>
                </a>
            @endcan
            @can('leave.request')
                <a class="btn btn-action-link btn-sm" href="{{ route('leave.requests.index', ['employee_id' => $employee->id]) }}" title="Leave requests">
                    <i class="fa-solid fa-calendar-minus"></i><span>Leave</span>
                </a>
            @endcan
            @can('payroll.view')
                <a class="btn btn-action-link btn-sm" href="{{ route('payroll.my-payslips') }}" title="Payslips">
                    <i class="fa-solid fa-receipt"></i><span>Payslip</span>
                </a>
            @endcan
            <a class="btn btn-action-link btn-sm" href="{{ route('employees.id-card', $employee) }}">
                <i class="fa-solid fa-id-card"></i><span>ID card</span>
            </a>
            @if(auth()->user()->can('employee.edit') || (auth()->user()->can('employee.edit-own') && $employee->user_id === auth()->id()))
                <a class="btn btn-primary btn-sm" href="{{ route('employees.edit', $employee) }}">
                    <i class="fa-solid fa-pen me-1"></i>{{ $isOwnProfile ? 'Edit my profile' : 'Edit profile' }}
                </a>
            @endif
        </div>
    </div>

    @if(session('success') || session('status'))
        <div class="alert alert-success d-flex align-items-center gap-2 mb-3">
            <i class="fa-solid fa-circle-check"></i><span>{{ session('success') ?: session('status') }}</span>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger d-flex align-items-center gap-2 mb-3">
            <i class="fa-solid fa-circle-exclamation"></i><span>{{ $errors->first() }}</span>
        </div>
    @endif

    <ul class="nav profile-nav-tabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="profile-nav-tab active" id="tab-personal-btn" data-bs-toggle="tab" data-bs-target="#tab-personal" type="button" role="tab" aria-controls="tab-personal" aria-selected="true">
                <i class="fa-solid fa-user-circle"></i><span>Personal info</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="profile-nav-tab" id="tab-employment-btn" data-bs-toggle="tab" data-bs-target="#tab-employment" type="button" role="tab" aria-controls="tab-employment" aria-selected="false">
                <i class="fa-solid fa-briefcase"></i><span>Employee details</span>
            </button>
        </li>
        @if($canViewSensitive)
            <li class="nav-item" role="presentation">
                <button class="profile-nav-tab" id="tab-payroll-btn" data-bs-toggle="tab" data-bs-target="#tab-payroll" type="button" role="tab" aria-controls="tab-payroll" aria-selected="false">
                    <i class="fa-solid fa-money-check-dollar"></i><span>Payroll details</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="profile-nav-tab" id="tab-documents-btn" data-bs-toggle="tab" data-bs-target="#tab-documents" type="button" role="tab" aria-controls="tab-documents" aria-selected="false">
                    <i class="fa-solid fa-file-invoice"></i><span>Documents</span>
                    <span class="status-counter ms-1">{{ $employee->documents->count() }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="profile-nav-tab" id="tab-leave-btn" data-bs-toggle="tab" data-bs-target="#tab-leave" type="button" role="tab" aria-controls="tab-leave" aria-selected="false">
                    <i class="fa-solid fa-calendar-minus"></i><span>Leave Balance</span>
                    <span class="status-counter ms-1">{{ $employee->leaveBalances?->count() ?? 0 }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="profile-nav-tab" id="tab-history-btn" data-bs-toggle="tab" data-bs-target="#tab-history" type="button" role="tab" aria-controls="tab-history" aria-selected="false">
                    <i class="fa-solid fa-clock-rotate-left"></i><span>History</span>
                    <span class="status-counter ms-1">{{ $employee->employmentHistories->count() }}</span>
                </button>
            </li>
        @endif
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="tab-personal" role="tabpanel" aria-labelledby="tab-personal-btn">
            <div class="profile-card">
                <div class="profile-card-header">
                    <h2 class="profile-card-title">
                        <i class="fa-solid fa-id-badge text-primary"></i><span>Basic information</span>
                    </h2>
                    @if(auth()->user()->can('employee.edit') || (auth()->user()->can('employee.edit-own') && $employee->user_id === auth()->id()))
                        <a href="{{ route('employees.edit', $employee) }}" class="btn btn-action-link btn-sm" title="Edit basic information">
                            <i class="fa-solid fa-pen"></i><span>Edit</span>
                        </a>
                    @endif
                </div>
                <div class="profile-card-body">
                    <div class="row g-4 align-items-center">
                        <div class="{{ $canViewSensitive ? 'col-lg-6' : 'col-12' }}">
                            <div class="profile-hero">
                                @if($employee->profile_photo)
                                    <img class="profile-hero-avatar" src="{{ route('employees.photo', $employee) }}" alt="{{ $employee->full_name_en }}">
                                @else
                                    <div class="profile-hero-avatar-placeholder"><i class="fa-solid fa-user"></i></div>
                                @endif
                                <div class="profile-hero-meta">
                                    <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                        <span class="profile-hero-name">{{ $employee->full_name_en ?: trim($employee->first_name.' '.$employee->last_name) }}</span>
                                        <span class="status-text"><span class="status-dot {{ $employee->is_active ? 'is-success' : 'is-muted' }}"></span>{{ str($employee->employment_status ?: ($employee->is_active ? 'Active' : 'Inactive'))->title() }}</span>
                                    </div>
                                    <div class="profile-hero-sub fw-semibold">
                                        <code>{{ $employee->employee_code }}</code>
                                        @if($employee->position)<span class="mx-1">·</span><span>{{ $employee->position->title }}</span>@endif
                                        @if($employee->department)<span class="mx-1">·</span><span>{{ $employee->department->name }}</span>@endif
                                    </div>
                                    <div class="profile-hero-contact">
                                        @if($employee->email)<a href="mailto:{{ $employee->email }}"><i class="fa-regular fa-envelope me-1 text-body-secondary"></i>{{ $employee->email }}</a>@endif
                                        @if($employee->phone)<a href="tel:{{ $employee->phone }}"><i class="fa-solid fa-phone me-1 text-body-secondary"></i>{{ $employee->phone }}</a>@endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @if($canViewSensitive)
                            <div class="col-lg-6 border-start-lg">
                                <div class="row g-2">
                                    <div class="col-sm-6">
                                        <div class="profile-kv-row"><span class="profile-kv-label">Date of birth</span><span class="profile-kv-value">{{ $employee->date_of_birth?->format('d M Y') ?? '—' }}</span></div>
                                        <div class="profile-kv-row"><span class="profile-kv-label">Age</span><span class="profile-kv-value">{{ $employee->date_of_birth ? $employee->date_of_birth->age.' years' : '—' }}</span></div>
                                        <div class="profile-kv-row"><span class="profile-kv-label">Gender</span><span class="profile-kv-value">{{ $employee->gender ? ucfirst($employee->gender) : '—' }}</span></div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="profile-kv-row"><span class="profile-kv-label">National ID</span><span class="profile-kv-value font-monospace">{{ $employee->national_id ?: '—' }}</span></div>
                                        <div class="profile-kv-row"><span class="profile-kv-label">Passport</span><span class="profile-kv-value font-monospace">{{ $employee->passport_number ?: '—' }}</span></div>
                                        <div class="profile-kv-row"><span class="profile-kv-label">City</span><span class="profile-kv-value">{{ $employee->city ?: '—' }}</span></div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            @if($canViewSensitive)
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="profile-card h-100 mb-0">
                            <div class="profile-card-header">
                                <h2 class="profile-card-title"><i class="fa-solid fa-location-dot text-primary"></i><span>Address & identification</span></h2>
                            </div>
                            <div class="profile-card-body">
                                <div class="profile-kv-row"><span class="profile-kv-label">Residential address</span><span class="profile-kv-value text-break">{{ $employee->address ?: 'Not specified' }}</span></div>
                                <div class="profile-kv-row"><span class="profile-kv-label">City / Province</span><span class="profile-kv-value">{{ $employee->city ?: '—' }}</span></div>
                                <div class="profile-kv-row"><span class="profile-kv-label">National ID</span><span class="profile-kv-value font-monospace">{{ $employee->national_id ?: '—' }}</span></div>
                                <div class="profile-kv-row"><span class="profile-kv-label">Passport number</span><span class="profile-kv-value font-monospace">{{ $employee->passport_number ?: '—' }}</span></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="profile-card h-100 mb-0">
                            <div class="profile-card-header">
                                <h2 class="profile-card-title"><i class="fa-solid fa-phone-volume text-primary"></i><span>Emergency contact</span></h2>
                            </div>
                            <div class="profile-card-body">
                                <div class="profile-kv-row"><span class="profile-kv-label">Contact person name</span><span class="profile-kv-value">{{ $employee->emergency_contact_name ?: 'Not recorded' }}</span></div>
                                <div class="profile-kv-row"><span class="profile-kv-label">Phone number</span><span class="profile-kv-value">{{ $employee->emergency_contact_phone ?: '—' }}</span></div>
                                <div class="profile-kv-row"><span class="profile-kv-label">Alternative phone</span><span class="profile-kv-value">{{ $employee->phone ?: '—' }}</span></div>
                                <div class="profile-kv-row"><span class="profile-kv-label">Primary email</span><span class="profile-kv-value">{{ $employee->email ?: '—' }}</span></div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="alert alert-light border d-flex align-items-center gap-2 mb-0">
                    <i class="fa-solid fa-shield-halved text-primary"></i>
                    <span>Personal identification, payroll, documents, and employment history are protected by sensitive-data permissions.</span>
                </div>
            @endif
        </div>

        <div class="tab-pane fade" id="tab-employment" role="tabpanel" aria-labelledby="tab-employment-btn">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="profile-card h-100 mb-0">
                        <div class="profile-card-header"><h2 class="profile-card-title"><i class="fa-solid fa-sitemap text-primary"></i><span>Organization & role</span></h2></div>
                        <div class="profile-card-body">
                            <div class="profile-kv-row"><span class="profile-kv-label">Branch</span><span class="profile-kv-value">{{ $employee->branch?->name ?? '—' }}</span></div>
                            <div class="profile-kv-row"><span class="profile-kv-label">Department</span><span class="profile-kv-value">{{ $employee->department?->name ?? '—' }}</span></div>
                            <div class="profile-kv-row"><span class="profile-kv-label">Job title / Position</span><span class="profile-kv-value">{{ $employee->position?->title ?? '—' }}</span></div>
                            <div class="profile-kv-row"><span class="profile-kv-label">Employment type</span><span class="profile-kv-value">{{ $employee->employmentType?->name ?? '—' }}</span></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="profile-card h-100 mb-0">
                        <div class="profile-card-header"><h2 class="profile-card-title"><i class="fa-solid fa-file-contract text-primary"></i><span>Contract & tenure</span></h2></div>
                        <div class="profile-card-body">
                            <div class="profile-kv-row"><span class="profile-kv-label">Hire date</span><span class="profile-kv-value">{{ $employee->hire_date?->format('d M Y') ?? '—' }}</span></div>
                            <div class="profile-kv-row"><span class="profile-kv-label">Probation end date</span><span class="profile-kv-value">{{ $employee->probation_end_date?->format('d M Y') ?? '—' }}</span></div>
                            <div class="profile-kv-row"><span class="profile-kv-label">Contract start</span><span class="profile-kv-value">{{ $employee->contract_start_date?->format('d M Y') ?? '—' }}</span></div>
                            <div class="profile-kv-row"><span class="profile-kv-label">Contract end</span><span class="profile-kv-value">{{ $employee->contract_end_date?->format('d M Y') ?? 'Permanent / Ongoing' }}</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($canViewSensitive)
            <div class="tab-pane fade" id="tab-payroll" role="tabpanel" aria-labelledby="tab-payroll-btn">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="profile-card h-100 mb-0">
                            <div class="profile-card-header"><h2 class="profile-card-title"><i class="fa-solid fa-wallet text-primary"></i><span>Salary & compensation</span></h2></div>
                            <div class="profile-card-body">
                                <div class="profile-kv-row"><span class="profile-kv-label">Base salary</span><span class="profile-kv-value fs-6 text-primary fw-bold">{{ $employee->base_salary ? number_format((float) $employee->base_salary, 2).' '.$employee->salary_currency : '—' }}</span></div>
                                <div class="profile-kv-row"><span class="profile-kv-label">Salary currency</span><span class="profile-kv-value font-monospace">{{ $employee->salary_currency ?: 'USD' }}</span></div>
                                <div class="profile-kv-row"><span class="profile-kv-label">Payment method</span><span class="profile-kv-value">{{ str($employee->payment_method ?: 'Bank Transfer')->replace('_', ' ')->title() }}</span></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="profile-card h-100 mb-0">
                            <div class="profile-card-header"><h2 class="profile-card-title"><i class="fa-solid fa-building-columns text-primary"></i><span>Bank account details</span></h2></div>
                            <div class="profile-card-body">
                                <div class="profile-kv-row"><span class="profile-kv-label">Bank name</span><span class="profile-kv-value">{{ $employee->bank_name ?: '—' }}</span></div>
                                <div class="profile-kv-row"><span class="profile-kv-label">Account holder name</span><span class="profile-kv-value">{{ $employee->bank_account_name ?: '—' }}</span></div>
                                <div class="profile-kv-row"><span class="profile-kv-label">Account number</span><span class="profile-kv-value font-monospace fw-bold">{{ $employee->bank_account_number ?: '—' }}</span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="tab-documents" role="tabpanel" aria-labelledby="tab-documents-btn">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h2 class="h6 fw-bold text-dark mb-0"><i class="fa-solid fa-file-invoice text-primary me-2"></i>Employee Document Archive</h2>
                    <a class="btn btn-action-link btn-sm" href="{{ route('employees.documents.index', $employee) }}"><i class="fa-solid fa-file-arrow-up"></i><span>Manage documents</span></a>
                </div>
                <div class="reference-list">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead><tr><th class="ps-3">Document Title</th><th>Category</th><th>File Name</th><th>Uploaded Date</th><th class="text-end pe-3">Action</th></tr></thead>
                            <tbody>
                                @forelse($employee->documents as $doc)
                                    <tr>
                                        <td class="ps-3 fw-semibold text-dark">{{ $doc->title ?: $doc->original_name }}</td>
                                        <td>{{ str($doc->document_type ?? 'General')->headline() }}</td>
                                        <td class="small text-body-secondary font-monospace">{{ $doc->original_name }}</td>
                                        <td class="small">{{ $doc->created_at->format('d M Y') }}</td>
                                        <td class="text-end pe-3"><a class="btn btn-action-link btn-sm" href="{{ route('employees.documents.download', [$employee, $doc]) }}"><i class="fa-solid fa-download"></i><span>Download</span></a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5"><x-empty-state icon="fa-file-arrow-up" title="No employee documents" message="No documents have been uploaded for this employee yet." compact /></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Leave Balance Tab --}}
            <div class="tab-pane fade" id="tab-leave" role="tabpanel" aria-labelledby="tab-leave-btn">
                <div class="profile-card mb-3">
                    <div class="profile-card-header">
                        <h2 class="profile-card-title"><i class="fa-solid fa-calendar-minus text-primary"></i><span>Leave Balance — {{ now()->year }}</span></h2>
                        @can('leave.request')
                            <a href="{{ route('leave.requests.index') }}" class="btn btn-action-link btn-sm">
                                <i class="fa-solid fa-plus"></i><span>Request Leave</span>
                            </a>
                        @endcan
                    </div>
                    <div class="profile-card-body">
                        @if(!empty($employee->leaveBalances) && $employee->leaveBalances->count() > 0)
                            <div class="row g-3">
                                @foreach($employee->leaveBalances as $balance)
                                    @php
                                        $used = (float) $balance->used_days;
                                        $total = (float) ($balance->opening_balance + $balance->earned_days + $balance->adjustment_days);
                                        $remaining = (float) $balance->remaining_days;
                                        $pct = $total > 0 ? min(100, round($used / $total * 100)) : 0;
                                        $barColor = $pct >= 90 ? 'danger' : ($pct >= 70 ? 'warning' : 'success');
                                    @endphp
                                    <div class="col-sm-6 col-md-4 col-lg-3">
                                        <div class="p-3 rounded" style="background:#f8fafc; border:1px solid #e8ecf4;">
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <span class="fw-semibold text-dark" style="font-size:.8rem;">{{ $balance->leaveType?->name ?? 'Unknown' }}</span>
                                                <span class="badge text-bg-light border" style="font-size:.62rem;">{{ now()->year }}</span>
                                            </div>
                                            <div class="d-flex align-items-baseline gap-1 mb-1">
                                                <span class="fw-bold text-dark" style="font-size:1.4rem; line-height:1;">{{ number_format($remaining, 1) }}</span>
                                                <span class="text-body-secondary" style="font-size:.72rem;">/ {{ number_format($total, 1) }} days</span>
                                            </div>
                                            <div class="progress mb-1" style="height:4px; border-radius:.2rem;">
                                                <div class="progress-bar bg-{{ $barColor }}" style="width:{{ $pct }}%; border-radius:.2rem;"></div>
                                            </div>
                                            <div class="d-flex justify-content-between" style="font-size:.65rem; color:#64748b;">
                                                <span>Used: {{ number_format($used, 1) }}d</span>
                                                <span>Remaining: {{ number_format($remaining, 1) }}d</span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <x-empty-state icon="fa-calendar-minus" title="No leave balances" message="No leave balances have been initialized for this employee yet." compact />
                        @endif
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="tab-history" role="tabpanel" aria-labelledby="tab-history-btn">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h2 class="h6 fw-bold text-dark mb-0"><i class="fa-solid fa-clock-rotate-left text-primary me-2"></i>Employment Lifecycle & Career Progression</h2>
                    <a class="btn btn-action-link btn-sm" href="{{ route('employees.history.index', $employee) }}"><i class="fa-solid fa-arrow-up-right-from-square"></i><span>View full history</span></a>
                </div>
                <div class="reference-list">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead><tr><th class="ps-3">Effective Date</th><th>Event Type</th><th>Department & Position</th><th>Change Details / Remarks</th><th class="text-end pe-3">Logged By</th></tr></thead>
                            <tbody>
                                @forelse($employee->employmentHistories as $history)
                                    <tr>
                                        <td class="ps-3 fw-semibold text-dark">{{ $history->effective_date?->format('d M Y') ?? '—' }}</td>
                                        <td>{{ str($history->event_type)->replace('_', ' ')->title() }}</td>
                                        <td><div>{{ $history->department?->name ?? '—' }}</div><small class="text-body-secondary">{{ $history->position?->title ?? '—' }}</small></td>
                                        <td class="small">{{ $history->notes ?: 'Regular organizational update' }}</td>
                                        <td class="text-end pe-3 small text-body-secondary">{{ $history->recordedBy?->name ?? 'System' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5"><x-empty-state icon="fa-clock-rotate-left" title="No employment history" message="No employment lifecycle events have been recorded yet." compact /></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @if(auth()->user()->can('employee.delete'))
        <div class="mt-4 pt-3 border-top text-end">
            <form method="POST" action="{{ route('employees.destroy', $employee) }}" class="d-inline" data-confirm="Archive this employee? Existing payroll and attendance records remain available.">
                @csrf
                @method('DELETE')
                <button class="btn btn-sm btn-outline-danger" type="submit"><i class="fa-solid fa-box-archive me-1"></i>Archive employee record</button>
            </form>
        </div>
    @endif
</x-layouts::app>
