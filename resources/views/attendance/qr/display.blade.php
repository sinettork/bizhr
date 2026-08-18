<x-layouts::app title="Attendance QR">
    <x-workspace-command-bar title="Branch attendance QR" icon="fa-qrcode" context="Time & Attendance" />

    @if($errors->any())
        <div class="alert alert-danger d-flex align-items-center gap-2">
            <i class="fa-solid fa-circle-exclamation"></i><span>{{ $errors->first() }}</span>
        </div>
    @endif

    <div class="row g-3 align-items-stretch">
        <div class="col-12 col-xl-5">
            <section class="profile-card h-100 mb-0">
                <div class="profile-card-header">
                    <h2 class="profile-card-title"><i class="fa-solid fa-sliders text-primary"></i><span>Generate attendance QR</span></h2>
                </div>
                <div class="profile-card-body">
                    @if($branches->isEmpty())
                        <x-empty-state
                            icon="fa-building-circle-xmark"
                            title="No QR-enabled branch"
                            message="Enable attendance QR for at least one active branch before generating a code."
                            class="py-5"
                        />
                    @else
                        <form method="POST" action="{{ route('attendance.qr.sessions.store') }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Branch <span class="text-danger">*</span></label>
                                <select class="form-select" name="branch_id" required>
                                    <option value="">Choose branch</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" @selected((string) old('branch_id') === (string) $branch->id)>
                                            {{ $branch->name }} · {{ $branch->code }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">The QR can only be used for the selected branch.</div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">QR lifetime</label>
                                <select class="form-select" name="lifetime_seconds">
                                    @foreach([45 => '45 seconds', 60 => '60 seconds', 90 => '90 seconds'] as $seconds => $label)
                                        <option value="{{ $seconds }}" @selected((int) old('lifetime_seconds', 45) === $seconds)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text">Short-lived codes reduce screenshot sharing and replay risk.</div>
                            </div>

                            <button class="btn btn-primary w-100" type="submit">
                                <i class="fa-solid fa-qrcode me-1"></i>Generate secure QR
                            </button>
                        </form>
                    @endif
                </div>
            </section>
        </div>

        <div class="col-12 col-xl-7">
            <section class="profile-card h-100 mb-0">
                <div class="profile-card-header">
                    <h2 class="profile-card-title"><i class="fa-solid fa-mobile-screen-button text-primary"></i><span>Display screen</span></h2>
                    @if($qr)
                        <span class="status-text text-bg-success">Active session</span>
                    @endif
                </div>
                <div class="profile-card-body d-flex align-items-center justify-content-center">
                    @if($qr && $qrImage)
                        <div class="text-center w-100 py-3">
                            <div class="small text-uppercase text-body-secondary fw-semibold">{{ $qr['branch'] }}</div>
                            <h2 class="h5 mt-1 mb-3 text-dark">Scan to record attendance</h2>

                            <div class="d-inline-flex align-items-center justify-content-center bg-white border rounded-3 p-3 shadow-sm">
                                <img src="{{ $qrImage }}" alt="Attendance QR for {{ $qr['branch'] }}" width="300" height="300" class="img-fluid" style="max-width:300px;">
                            </div>

                            <div class="mt-3 small text-body-secondary">
                                Expires at
                                <strong class="text-dark">{{ \Carbon\Carbon::parse($qr['expires_at'])->timezone('Asia/Phnom_Penh')->format('H:i:s') }}</strong>
                            </div>
                            <div class="small text-body-secondary mt-1">Keep this screen visible only at the branch attendance point.</div>

                            <div class="d-flex flex-wrap justify-content-center gap-2 mt-3">
                                <a class="btn btn-outline-primary btn-sm" href="{{ $qr['url'] }}" target="_blank" rel="noopener">
                                    <i class="fa-solid fa-arrow-up-right-from-square me-1"></i>Open attendance link
                                </a>
                                <a class="btn btn-light btn-sm" href="{{ route('attendance.qr.display') }}">
                                    <i class="fa-solid fa-rotate me-1"></i>Clear display
                                </a>
                            </div>
                        </div>
                    @else
                        <x-empty-state
                            icon="fa-qrcode"
                            title="Ready to generate"
                            message="Choose a branch and generate a short-lived QR code. It will appear here for employees to scan."
                            class="py-5"
                        />
                    @endif
                </div>
            </section>
        </div>
    </div>
</x-layouts::app>
