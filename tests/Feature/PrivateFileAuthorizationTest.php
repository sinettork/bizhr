<?php

use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\EmploymentContract;
use App\Models\ExpenseClaim;
use App\Models\JobApplicant;
use App\Models\JobVacancy;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Storage::fake('local');
    $this->seed([DatabaseSeeder::class, DemoDataSeeder::class]);
    $this->owner = User::query()->where('email', 'demo.owner@bizhr.local')->firstOrFail();
    $this->primaryCompany = Company::query()->firstOrFail();
    $this->otherCompany = Company::query()->create(['name' => 'Private other company']);
    $this->otherBranch = Branch::query()->create(['company_id' => $this->otherCompany->id, 'name' => 'Private branch', 'code' => 'PRIVATE-BR', 'is_active' => true]);
    $this->otherDepartment = Department::query()->create(['company_id' => $this->otherCompany->id, 'branch_id' => $this->otherBranch->id, 'name' => 'Private department', 'code' => 'PRIVATE-DEPT', 'is_active' => true]);
});

it('denies a privileged account access to another company employee document', function () {
    $employee = Employee::query()->create(['company_id' => $this->otherCompany->id, 'branch_id' => $this->otherBranch->id, 'department_id' => $this->otherDepartment->id, 'employee_code' => 'PRIVATE-DOC', 'first_name' => 'Private', 'last_name' => 'Employee', 'hire_date' => today(), 'employment_status' => 'Active', 'salary_currency' => 'USD', 'is_active' => true]);
    $document = EmployeeDocument::query()->create(['employee_id' => $employee->id, 'document_type' => 'identity', 'original_name' => 'identity.pdf', 'file_path' => 'private/other/identity.pdf']);
    Storage::disk('local')->put($document->file_path, 'private document');

    $this->actingAs($this->owner)->get(route('employees.documents.download', [$employee, $document]))->assertNotFound();
});

it('denies cross-company expense receipt access', function () {
    $employee = Employee::query()->create(['company_id' => $this->otherCompany->id, 'branch_id' => $this->otherBranch->id, 'department_id' => $this->otherDepartment->id, 'employee_code' => 'PRIVATE-EXP', 'first_name' => 'Private', 'last_name' => 'Expense', 'hire_date' => today(), 'employment_status' => 'Active', 'salary_currency' => 'USD', 'is_active' => true]);
    $claim = ExpenseClaim::query()->create(['company_id' => $this->otherCompany->id, 'employee_id' => $employee->id, 'expense_date' => today(), 'category' => 'Travel', 'amount' => 20, 'currency' => 'USD', 'business_purpose' => 'Private company business travel.', 'receipt_path' => 'private/other/receipt.pdf', 'status' => 'pending_manager']);
    Storage::disk('local')->put($claim->receipt_path, 'private receipt');

    $this->actingAs($this->owner)->get(route('expenses.receipt', $claim))->assertNotFound();
});

it('denies cross-company candidate CV access', function () {
    $vacancy = JobVacancy::query()->create(['company_id' => $this->otherCompany->id, 'title' => 'Private vacancy', 'description' => 'A confidential vacancy for another company.', 'openings' => 1, 'open_date' => today(), 'status' => 'open', 'created_by' => $this->owner->id]);
    $applicant = JobApplicant::query()->create(['job_vacancy_id' => $vacancy->id, 'full_name' => 'Private Candidate', 'email' => 'private.candidate@example.test', 'phone' => '012345678', 'cv_path' => 'private/other/candidate.pdf', 'status' => 'applied', 'applied_at' => now()]);
    Storage::disk('local')->put($applicant->cv_path, 'private CV');

    $this->actingAs($this->owner)->get(route('recruitment.applicants.cv', $applicant))->assertNotFound();
});

it('denies cross-company employment contract document access', function () {
    $employee = Employee::query()->create(['company_id' => $this->otherCompany->id, 'branch_id' => $this->otherBranch->id, 'department_id' => $this->otherDepartment->id, 'employee_code' => 'PRIVATE-CONTRACT', 'first_name' => 'Private', 'last_name' => 'Contract', 'hire_date' => today(), 'employment_status' => 'Active', 'salary_currency' => 'USD', 'is_active' => true]);
    $contract = EmploymentContract::query()->create([
        'company_id' => $this->otherCompany->id,
        'employee_id' => $employee->id,
        'contract_number' => 'PRIVATE-CONTRACT-001',
        'type' => 'udc',
        'status' => 'active',
        'start_date' => today(),
        'salary_amount' => 500,
        'salary_currency' => 'USD',
        'pay_type' => 'monthly',
        'work_hours_per_day' => 8,
        'work_days_per_week' => 6,
        'document_path' => 'private/other/contract.pdf',
        'original_name' => 'contract.pdf',
    ]);
    Storage::disk('local')->put($contract->document_path, 'private contract');

    $this->actingAs($this->owner)->get(route('contracts.download', $contract))->assertNotFound();
});

it('requires authentication for private image endpoints', function () {
    $employee = Employee::query()->where('company_id', $this->primaryCompany->id)->firstOrFail();
    $employee->update(['profile_photo' => "private/companies/{$employee->company_id}/employees/{$employee->id}/photo.jpg"]);
    Storage::disk('local')->put($employee->profile_photo, 'photo');

    $this->get(route('employees.photo', $employee))->assertRedirect(route('login'));
    $this->get(route('profile.avatar'))->assertRedirect(route('login'));
});
