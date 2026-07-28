<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class EmployeeDocumentController extends Controller
{
    public function index(Employee $employee): View
    {
        $this->authorizeEmployee($employee);

        return view('employees.documents', [
            'employee' => $employee,
            'documents' => $employee->documents()->latest()->get(),
        ]);
    }

    public function store(Request $request, Employee $employee): RedirectResponse
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

        $file = $data['document'];
        $path = $file->store('employee-documents/'.$employee->id, 'local');

        $employee->documents()->create([
            ...collect($data)->except('document')->all(),
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $path,
        ]);

        return back()->with('status', 'Document uploaded successfully.');
    }

    public function download(Employee $employee, EmployeeDocument $document)
    {
        $this->authorizeEmployee($employee);
        abort_unless($document->employee_id === $employee->id, 404);

        return Storage::disk('local')->download($document->file_path, $document->original_name);
    }

    public function destroy(Employee $employee, EmployeeDocument $document): RedirectResponse
    {
        $this->authorizeEmployee($employee, true);
        abort_unless($document->employee_id === $employee->id, 404);

        Storage::disk('local')->delete($document->file_path);
        $document->delete();

        return back()->with('status', 'Document deleted successfully.');
    }

    private function authorizeEmployee(Employee $employee, bool $editing = false): void
    {
        $user = auth()->user();
        abort_unless($user && ($editing ? $user->can('employee.edit') : $user->can('employee.view')), 403);
    }
}
