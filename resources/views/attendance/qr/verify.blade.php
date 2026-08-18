<x-layouts::app title="Record attendance">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6 col-xl-5">
            <section class="profile-card mb-0">
                <div class="profile-card-body p-4 p-md-5 text-center">
                    <span class="metric-icon bg-primary-subtle text-primary mb-3" aria-hidden="true">
                        <i class="fa-solid fa-qrcode"></i>
                    </span>

                    <div class="small text-uppercase text-body-secondary fw-semibold">{{ $session->branch?->name }}</div>
                    <h1 class="h4 fw-bold mt-1 mb-2">Recording attendance</h1>
                    <p class="text-body-secondary mb-4">Keep this page open and allow precise location. Your attendance will be recorded automatically.</p>

                    <div id="attendanceQrProgress" class="d-flex flex-column align-items-center gap-3 py-3" aria-live="polite">
                        <div id="attendanceQrSpinner" class="spinner-border text-primary" role="status" aria-label="Recording attendance"></div>
                        <div>
                            <div id="attendanceQrTitle" class="fw-semibold text-dark">Checking your location…</div>
                            <div id="attendanceQrMessage" class="small text-body-secondary mt-1">This normally takes only a few seconds.</div>
                        </div>
                    </div>

                    <div id="attendanceQrResult" class="alert d-none text-start mt-3" role="status"></div>

                    <button id="attendanceQrRetry" class="btn btn-primary d-none mt-2" type="button">
                        <i class="fa-solid fa-rotate me-1"></i>Try again
                    </button>
                </div>
            </section>
        </div>
    </div>

    @push('scripts')
        <script nonce="{{ request()->attributes->get('csp_nonce') }}">
            (() => {
                const progress = document.getElementById('attendanceQrProgress');
                const spinner = document.getElementById('attendanceQrSpinner');
                const title = document.getElementById('attendanceQrTitle');
                const message = document.getElementById('attendanceQrMessage');
                const result = document.getElementById('attendanceQrResult');
                const retry = document.getElementById('attendanceQrRetry');
                const endpoint = @json(route('attendance.qr.record', $token));
                const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
                let recording = false;
                let completed = false;

                const setProgress = (heading, detail) => {
                    progress.classList.remove('d-none');
                    spinner.classList.remove('d-none');
                    title.textContent = heading;
                    message.textContent = detail;
                    result.classList.add('d-none');
                    retry.classList.add('d-none');
                };

                const setFailure = (detail) => {
                    completed = false;
                    progress.classList.remove('d-none');
                    spinner.classList.add('d-none');
                    title.textContent = 'Attendance was not recorded';
                    message.textContent = detail;
                    retry.classList.remove('d-none');
                };

                const setSuccess = (data) => {
                    completed = true;
                    progress.classList.add('d-none');
                    retry.classList.add('d-none');
                    result.className = 'alert alert-success mt-3';
                    const action = data.action === 'check_in' ? 'Checked in' : 'Checked out';
                    result.innerHTML = `<div class="d-flex gap-3 align-items-start"><i class="fa-solid fa-circle-check fa-lg mt-1"></i><div><div class="fw-semibold">${action} successfully</div><div class="small mt-1">${data.time} · ${data.branch}</div><div class="small mt-1">Location verified ${Math.round(Number(data.distance) || 0)} m from the attendance point.</div></div></div>`;
                };

                const errorMessage = async (response) => {
                    try {
                        const data = await response.json();
                        return data.message || Object.values(data.errors || {}).flat().join(' ') || 'Unable to record attendance.';
                    } catch (_) {
                        return 'Unable to record attendance. Please try again.';
                    }
                };

                const submitLocation = async (position) => {
                    setProgress('Recording attendance…', 'Your location is verified. Saving your attendance now.');

                    const response = await fetch(endpoint, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                        },
                        body: JSON.stringify({
                            latitude: position.coords.latitude,
                            longitude: position.coords.longitude,
                            accuracy: position.coords.accuracy,
                        }),
                    });

                    if (! response.ok) {
                        throw new Error(await errorMessage(response));
                    }

                    setSuccess(await response.json());
                };

                const recordAttendance = () => {
                    if (recording || completed) return;
                    recording = true;

                    if (! window.isSecureContext) {
                        recording = false;
                        setFailure('Your browser requires HTTPS to use precise location. Open the QR through a secure HTTPS address and try again.');
                        return;
                    }

                    if (! navigator.geolocation) {
                        recording = false;
                        setFailure('This phone or browser does not support GPS location.');
                        return;
                    }

                    setProgress('Checking your location…', 'Allow precise location when your phone asks for permission.');

                    navigator.geolocation.getCurrentPosition(
                        async (position) => {
                            try {
                                await submitLocation(position);
                            } catch (error) {
                                setFailure(error.message || 'Unable to record attendance.');
                            } finally {
                                recording = false;
                            }
                        },
                        (error) => {
                            recording = false;
                            const detail = error.code === error.PERMISSION_DENIED
                                ? 'Precise location permission is required. Enable location for this site, then try again.'
                                : error.code === error.TIMEOUT
                                    ? 'Your phone could not get a precise location in time. Move to an open area and try again.'
                                    : 'Your phone could not determine a precise location. Check GPS and try again.';
                            setFailure(detail);
                        },
                        {
                            enableHighAccuracy: true,
                            timeout: 20000,
                            maximumAge: 0,
                        }
                    );
                };

                retry.addEventListener('click', recordAttendance);
                recordAttendance();
            })();
        </script>
    @endpush
</x-layouts::app>
