<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\User;
use App\Services\UploadedFileSecurityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmployeeDocumentController extends Controller
{
    public function index(Request $request, Employee $employee): View
    {
        $this->authorizeEmployee($employee);
        AuditLog::record($employee, 'viewed_documents', [], [
            'document_count' => $employee->documents()->count(),
        ]);

        return view('employees.documents', [
            'employee' => $employee,
            'documents' => $employee->documents()->latest()->paginate($this->perPage($request, 20))->withQueryString(),
        ]);
    }

    public function store(Request $request, Employee $employee, UploadedFileSecurityService $fileSecurity): RedirectResponse
    {
        $this->authorizeEmployee($employee, true);

        $data = $request->validate([
            'document_type' => ['required', 'string', 'max:100'],
            'document_number' => ['nullable', 'string', 'max:100'],
            'document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
            'issued_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:issued_date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $file = $request->file('document');
        abort_unless($file !== null, 422);
        $fileSecurity->assertSafe($file, 'document');
        $path = $file->store('employee-documents/'.$employee->id, 'local');

        $employee->documents()->create([
            ...array_diff_key($data, ['document' => true]),
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'version' => (int) $employee->documents()->where('document_type', $data['document_type'])->max('version') + 1,
        ]);

        return back()->with('status', 'បានផ្ទុកឯកសារឡើងដោយជោគជ័យ។');
    }

    public function download(Employee $employee, EmployeeDocument $document): StreamedResponse
    {
        $this->authorizeEmployee($employee);
        abort_unless($document->employee_id === $employee->id, 404);
        Gate::authorize('view', $document);
        abort_unless(Storage::disk('local')->exists($document->file_path), 404);
        AuditLog::record($document, 'downloaded', [], [
            'employee_id' => $employee->id,
            'document_type' => $document->document_type,
            'original_name' => $document->original_name,
        ]);

        return Storage::disk('local')->download($document->file_path, $document->original_name);
    }

    public function destroy(Employee $employee, EmployeeDocument $document): RedirectResponse
    {
        $this->authorizeEmployee($employee, true);
        abort_unless($document->employee_id === $employee->id, 404);
        Gate::authorize('update', $document);

        if ($document->status !== 'pending_verification') {
            return back()->withErrors(['document' => 'Verified document history must be revoked instead of deleted.']);
        }
        Storage::disk('local')->delete($document->file_path);
        $document->delete();

        return back()->with('status', 'បានលុបឯកសារដោយជោគជ័យ។');
    }

    public function verify(Employee $employee, EmployeeDocument $document, Request $request): RedirectResponse
    {
        $this->authorizeEmployee($employee, true);
        abort_unless($document->employee_id === $employee->id, 404);
        Gate::authorize('update', $document);
        abort_unless($document->status === 'pending_verification', 422);
        $document->update(['status' => 'verified', 'verified_by' => $request->user()->id, 'verified_at' => now()]);

        return back()->with('status', 'Document verified.');
    }

    public function revoke(Employee $employee, EmployeeDocument $document, Request $request): RedirectResponse
    {
        $this->authorizeEmployee($employee, true);
        abort_unless($document->employee_id === $employee->id, 404);
        Gate::authorize('update', $document);
        abort_unless($document->status === 'verified', 422);
        $data = $request->validate(['reason' => ['required', 'string', 'min:5', 'max:1000']]);
        $document->update(['status' => 'revoked', 'revoked_by' => $request->user()->id, 'revoked_at' => now(), 'revocation_reason' => trim($data['reason'])]);

        return back()->with('status', 'Document revoked; its audit history was retained.');
    }

    private function authorizeEmployee(Employee $employee, bool $editing = false): void
    {
        /** @var User|null $user */
        $user = auth()->user();
        abort_unless($user !== null, 403);
        abort_unless($employee->company_id === $user->companyId(), 404);

        $actor = $user->employee;
        $isOwnRecord = $actor?->id === $employee->id;

        if ($actor && $actor->company_id !== $employee->company_id) {
            abort(403);
        }

        if ($isOwnRecord) {
            $permission = $editing ? 'employee.edit-own' : 'employee.view-own';
            abort_unless($user->can($permission), 403);

            return;
        }

        if ($user->hasAnyRole(['Manager', 'manager'])) {
            abort(403);
        }

        if ($editing) {
            abort_unless($user->can('employee.edit') && $user->can('employee.view-sensitive'), 403);

            return;
        }

        abort_unless($user->can('employee.view-sensitive'), 403);
    }
}
