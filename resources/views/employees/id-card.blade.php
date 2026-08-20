<x-layouts::app :title="$employee->getFullName().' ID card'">
    <x-workspace-command-bar title="Employee ID card" icon="fa-id-card">
        <x-slot:actions>
            <a class="btn btn-action-link btn-sm" href="{{ route('employees.show', $employee) }}">
                <i class="fa-solid fa-arrow-left"></i><span>Employee profile</span>
            </a>
            <button class="btn btn-action-link btn-sm" type="button" data-action="print">
                <i class="fa-solid fa-print"></i><span>Print ID card</span>
            </button>
        </x-slot:actions>
    </x-workspace-command-bar>

    @if(session('success'))
        <div class="alert alert-success d-flex align-items-center gap-2 mb-3">
            <i class="fa-solid fa-circle-check"></i><span>{{ session('success') }}</span>
        </div>
    @endif

    @php
        $verificationRevoked = $employee->id_card_verification_revoked_at !== null;
        $verificationExpired = $employee->id_card_verification_expires_at?->isPast() ?? false;
        $canManageVerification = auth()->user()->can('employee.edit') || (auth()->user()->can('employee.edit-own') && $employee->user_id === auth()->id());
    @endphp

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3 py-3">
            <div class="d-flex align-items-start gap-2">
                <i class="fa-solid fa-shield-halved text-primary mt-1"></i>
                <div>
                    <div class="fw-semibold text-dark">Public verification</div>
                    @if($verificationRevoked)
                        <div class="small text-danger">Revoked — printed QR codes cannot verify this employee until a new token is generated.</div>
                    @elseif($verificationExpired)
                        <div class="small text-warning">Expired — update the card expiry date before issuing a new card.</div>
                    @else
                        <div class="small text-body-secondary">Active. Reloading or printing this page keeps the same verification token.</div>
                    @endif
                </div>
            </div>

            <div class="d-flex align-items-center gap-2">
                <span class="status-text">
                    <span class="status-dot {{ $verificationRevoked || $verificationExpired ? 'is-danger' : 'is-success' }}"></span>
                    {{ $verificationRevoked ? 'Revoked' : ($verificationExpired ? 'Expired' : 'Active') }}
                </span>
                @if($canManageVerification)
                    <form method="POST" action="{{ route('employees.id-card.verify.revoke', $employee) }}" class="d-inline"
                          data-confirm="{{ $verificationRevoked ? 'Generate a new verification token? All previously printed QR codes will remain invalid.' : 'Revoke this verification token? Printed QR codes will stop verifying this employee.' }}">
                        @csrf
                        <button class="btn btn-sm {{ $verificationRevoked ? 'btn-outline-primary' : 'btn-outline-danger' }}" type="submit">
                            <i class="fa-solid {{ $verificationRevoked ? 'fa-rotate' : 'fa-ban' }} me-1"></i>
                            {{ $verificationRevoked ? 'Generate new token' : 'Revoke verification' }}
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <div class="id-card-workspace">
        <div class="employee-id-card-stack">
            <article class="employee-id-card" aria-label="Employee identification card front face">
                <header>
                    <div class="employee-id-brand">
                        @if($employee->company?->logo_path)
                            <img src="{{ Storage::disk('public')->url($employee->company->logo_path) }}" alt="{{ $employee->company->name }} logo" class="employee-id-company-logo">
                        @else
                            <strong>{{ $employee->company?->name ?? config('app.name') }}</strong>
                        @endif
                        <small>EMPLOYEE IDENTIFICATION</small>
                    </div>
                    <i class="fa-solid fa-shield-check"></i>
                </header>
                <div class="employee-id-card-body">
                    <div class="employee-id-photo">
                        @if($employee->profile_photo)
                            <img src="{{ route('employees.photo', $employee) }}" alt="{{ $employee->getFullName() }}">
                        @else
                            <i class="fa-solid fa-user"></i>
                        @endif
                    </div>
                    <div class="employee-id-details">
                        <h1>{{ $employee->getFullName() }}</h1>
                        @if($employee->full_name_km)<h2>{{ $employee->full_name_km }}</h2>@endif
                        <p>{{ $employee->position?->title ?? 'Employee' }}</p>
                        <dl>
                            <div><dt>ID</dt><dd>{{ $employee->employee_code }}</dd></div>
                            <div><dt>Department</dt><dd>{{ $employee->department?->name ?? '—' }}</dd></div>
                            <div><dt>Branch</dt><dd>{{ $employee->branch?->name ?? '—' }}</dd></div>
                            <div><dt>Joined</dt><dd>{{ $employee->hire_date?->format('d M Y') ?? '—' }}</dd></div>
                        </dl>
                    </div>
                </div>
                <footer>
                    <span>Expires: {{ $employee->id_card_expiry_date?->format('d M Y') ?? 'No expiry set' }}</span>
                    <small>Property of {{ $employee->company?->name ?? config('app.name') }}</small>
                </footer>
            </article>

            <article class="employee-id-card employee-id-card-back" aria-label="Employee identification card back face">
                <header>
                    <div><strong>Emergency contact</strong><small>VALIDATION BACK</small></div>
                    <i class="fa-solid fa-id-card-clip"></i>
                </header>
                <div class="employee-id-card-back-body">
                    <div class="employee-id-back-section">
                        <div class="employee-id-back-label">Emergency contact</div>
                        <div class="employee-id-back-value">{{ $employee->emergency_contact_name ?: 'Not provided' }}</div>
                    </div>
                    <div class="employee-id-back-section">
                        <div class="employee-id-back-label">Phone</div>
                        <div class="employee-id-back-value">{{ $employee->emergency_contact_phone ?: 'Not provided' }}</div>
                    </div>
                    <div class="employee-id-back-section">
                        <div class="employee-id-back-label">Expiry date</div>
                        <div class="employee-id-back-value">{{ $employee->id_card_expiry_date?->format('d M Y') ?? 'Not set' }}</div>
                    </div>
                    <div class="employee-id-verification-box">
                        @if($verificationRevoked)
                            <div class="employee-id-qr-code d-flex align-items-center justify-content-center border rounded bg-body-tertiary" aria-label="Verification revoked">
                                <i class="fa-solid fa-ban text-danger fs-2"></i>
                            </div>
                        @else
                            <img src="{{ route('employees.id-card.qr', $employee) }}" alt="Verification QR code" class="employee-id-qr-code">
                        @endif
                        <div class="employee-id-verification-meta">
                            <span>{{ $verificationRevoked ? 'Revoked' : 'Verify' }}</span>
                            <small>{{ $verificationRevoked ? 'Generate a new token to issue another card' : 'Secure QR endpoint' }}</small>
                        </div>
                    </div>
                </div>
                <footer><span>{{ $employee->employee_code }}</span><small>Verified via BizHR</small></footer>
            </article>
        </div>
    </div>
</x-layouts::app>
