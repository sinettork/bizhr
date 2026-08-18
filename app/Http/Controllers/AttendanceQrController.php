<?php

namespace App\Http\Controllers;

use App\Models\AttendanceQrScanEvent;
use App\Models\AttendanceQrSession;
use App\Models\Branch;
use App\Services\AttendanceQrService;
use BaconQrCode\Renderer\GDLibRenderer;
use BaconQrCode\Writer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AttendanceQrController extends Controller
{
    public function display(Request $request): View
    {
        $companyId = $request->user()->employee?->company_id;
        $branches = Branch::query()
            ->where('is_active', true)
            ->where('attendance_qr_enabled', true)
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->orderByDesc('is_head_office')
            ->orderBy('name')
            ->get();

        $qr = session('qr_session');
        $qr = is_array($qr) ? $qr : null;
        $qrImage = null;

        if ($qr && filled($qr['url'] ?? null)) {
            $renderer = new GDLibRenderer(420);
            $png = (new Writer($renderer))->writeString((string) $qr['url']);
            $qrImage = 'data:image/png;base64,'.base64_encode($png);
        }

        return view('attendance.qr.display', compact('branches', 'qr', 'qrImage'));
    }

    public function create(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'branch_id' => ['required', 'integer'],
            'lifetime_seconds' => ['nullable', 'integer', 'min:30', 'max:120'],
        ]);

        $branch = Branch::query()
            ->whereKey($data['branch_id'])
            ->where('is_active', true)
            ->where('attendance_qr_enabled', true)
            ->firstOrFail();

        $actorCompanyId = $request->user()->employee?->company_id;
        abort_unless(! $actorCompanyId || (int) $branch->company_id === (int) $actorCompanyId, 404);

        if (! $branch->attendance_qr_token) {
            $branch->regenerateAttendanceQrToken();
        }

        AttendanceQrSession::query()->where('expires_at', '<', now()->subHour())->delete();

        $token = Str::random(80);
        $lifetimeSeconds = (int) ($data['lifetime_seconds'] ?? config('attendance.qr.session_lifetime_seconds', 45));
        $session = AttendanceQrSession::query()->create([
            'branch_id' => $branch->id,
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addSeconds($lifetimeSeconds),
            'created_by' => $request->user()->id,
        ]);

        return back()->with('qr_session', [
            'url' => route('attendance.qr.start', $token),
            'expires_at' => $session->expires_at->toIso8601String(),
            'branch' => $branch->name,
        ]);
    }

    public function verify(string $token): View
    {
        $session = $this->session($token);

        return view('attendance.qr.verify', compact('token', 'session'));
    }

    public function record(Request $request, string $token, AttendanceQrService $service): JsonResponse
    {
        $data = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'accuracy' => ['required', 'numeric', 'gt:0', 'max:100'],
        ]);

        $employee = $request->user()->employee;
        abort_unless($employee !== null, 403, 'Your account is not linked to an employee.');

        $result = DB::transaction(function () use ($token, $data, $employee, $request, $service): array {
            $session = $this->session($token, true);
            abort_if(AttendanceQrScanEvent::query()->where('attendance_qr_session_id', $session->id)->where('employee_id', $employee->id)->exists(), 422, 'This QR code was already used by your account.');
            $result = $service->process($employee, $session->branch->attendanceQrPayload(), (float) $data['latitude'], (float) $data['longitude'], $request->ip(), $request->userAgent());

            AttendanceQrScanEvent::query()->create([
                'attendance_qr_session_id' => $session->id,
                'employee_id' => $employee->id,
                'attendance_id' => $result['attendance']->id,
                'branch_id' => $session->branch_id,
                'action' => $result['action'],
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
                'accuracy_meters' => $data['accuracy'],
                'distance_meters' => $result['distance'],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'recorded_at' => $result['time'],
            ]);

            $session->increment('scan_count');

            return $result;
        }, attempts: 3);

        return response()->json([
            'action' => $result['action'],
            'time' => $result['time']->timezone('Asia/Phnom_Penh')->format('d/m/Y H:i:s'),
            'branch' => $result['branch']->name,
            'distance' => $result['distance'],
        ]);
    }

    private function session(string $token, bool $lock = false): AttendanceQrSession
    {
        abort_unless(preg_match('/^[A-Za-z0-9]{80}$/', $token) === 1, 404);
        $query = AttendanceQrSession::query()->with('branch')->where('token_hash', hash('sha256', $token));

        if ($lock) {
            $query->lockForUpdate();
        }

        $session = $query->firstOrFail();
        $grant = session('attendance_qr_grant.'.hash('sha256', $token));
        $hasGrant = is_numeric($grant) && (int) $grant >= now()->timestamp;

        abort_unless(($session->isValid() || $hasGrant) && $session->branch->is_active && $session->branch->attendance_qr_enabled, 410, 'This QR code has expired.');

        return $session;
    }
}
