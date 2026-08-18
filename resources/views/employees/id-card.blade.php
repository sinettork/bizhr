<x-layouts::app :title="$employee->getFullName().' ID card'">
    <x-workspace-command-bar title="Employee ID card" icon="fa-id-card">
        <x-slot:actions><a class="btn btn-action-link btn-sm" href="{{ route('employees.show', $employee) }}"><i class="fa-solid fa-arrow-left"></i><span>Employee profile</span></a><button class="btn btn-action-link btn-sm" type="button" data-action="print"><i class="fa-solid fa-print"></i><span>Print ID card</span></button></x-slot:actions>
    </x-workspace-command-bar>

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
                    <div class="employee-id-photo">@if($employee->profile_photo)<img src="{{ route('employees.photo',$employee) }}" alt="{{ $employee->getFullName() }}">@else<i class="fa-solid fa-user"></i>@endif</div>
                    <div class="employee-id-details"><h1>{{ $employee->getFullName() }}</h1>@if($employee->full_name_km)<h2>{{ $employee->full_name_km }}</h2>@endif<p>{{ $employee->position?->title ?? 'Employee' }}</p><dl><div><dt>ID</dt><dd>{{ $employee->employee_code }}</dd></div><div><dt>Department</dt><dd>{{ $employee->department?->name ?? '—' }}</dd></div><div><dt>Branch</dt><dd>{{ $employee->branch?->name ?? '—' }}</dd></div><div><dt>Joined</dt><dd>{{ $employee->hire_date?->format('d M Y') ?? '—' }}</dd></div></dl></div>
                </div>
                <footer><span>Expires: {{ $employee->id_card_expiry_date?->format('d M Y') ?? 'No expiry set' }}</span><small>Property of {{ $employee->company?->name ?? config('app.name') }}</small></footer>
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
                        <img src="{{ route('employees.id-card.qr', $employee) }}" alt="Verification QR code" class="employee-id-qr-code">
                        <div class="employee-id-verification-meta">
                            <span>Verify</span>
                            <small>Secure QR endpoint</small>
                        </div>
                    </div>
                </div>
                <footer><span>{{ $employee->employee_code }}</span><small>Verified via BizHR</small></footer>
            </article>
        </div>
    </div>
</x-layouts::app>
