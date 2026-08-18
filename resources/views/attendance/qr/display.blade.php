<x-layouts::app title="Attendance QR"><x-workspace-command-bar title="Branch attendance QR" icon="fa-qrcode" />@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<div class="card border-0 shadow-sm"><form method="POST" action="{{ route('attendance.qr.sessions.store') }}">@csrf<div class="card-body row g-3 align-items-end"><div class="col-md-7"><label class="form-label">Branch <span class="text-danger">*</span></label><select class="form-select" name="branch_id" required><option value="">Choose branch</option>@foreach($branches as $branch)<option value="{{ $branch->id }}">{{ $branch->name }} ({{ $branch->code }})</option>@endforeach</select></div><div class="col-md-3"><label class="form-label">QR lifetime</label><select class="form-select" name="lifetime_seconds"><option value="45">45 seconds</option><option value="60">60 seconds</option><option value="90">90 seconds</option></select></div><div class="col-md-2"><button class="btn btn-primary w-100"><i class="fa-solid fa-qrcode me-1"></i>Generate</button></div></div></form></div>
@if(session('qr_session'))
    @php($qr = session('qr_session'))
    <div class="card border-0 shadow-sm mt-3"><div class="card-body text-center"><h2 class="h5">{{ $qr['branch'] }}</h2><p class="text-body-secondary">This QR expires at {{ \Carbon\Carbon::parse($qr['expires_at'])->timezone('Asia/Phnom_Penh')->format('H:i:s') }}. Display it only at the branch.</p><canvas id="attendanceQrCanvas" class="border rounded p-2 bg-white"></canvas><div class="mt-3"><a class="btn btn-outline-primary btn-sm" href="{{ $qr['url'] }}" target="_blank">Open attendance link</a></div></div></div>
    @push('scripts')
        @vite('resources/js/qrcode-display.js')
        <script nonce="{{ request()->attributes->get('csp_nonce') }}">window.addEventListener('qrcode:ready', () => QRCode.toCanvas(document.getElementById('attendanceQrCanvas'), @json($qr['url']), {width: 300, margin: 2, errorCorrectionLevel: 'M'}));</script>
    @endpush
@endif
</x-layouts::app>
