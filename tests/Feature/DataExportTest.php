<?php

use App\Jobs\GenerateDataExport;
use App\Models\Branch;
use App\Models\Company;
use App\Models\DataExport;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\SimpleExcel\SimpleExcelReader;

function exportCompanyFixture(): array
{
    $company = Company::query()->create([
        'name' => 'Export Test Company',
        'currency' => 'USD',
        'timezone' => 'Asia/Phnom_Penh',
        'date_format' => 'd/m/Y',
    ]);
    $branch = Branch::query()->create([
        'company_id' => $company->id,
        'name' => 'Head Office',
        'code' => 'EXPORT-HQ',
        'is_active' => true,
    ]);
    $department = Department::query()->create([
        'company_id' => $company->id,
        'branch_id' => $branch->id,
        'name' => 'People Operations',
        'code' => 'EXPORT-HR',
        'is_active' => true,
    ]);

    return compact('company', 'branch', 'department');
}

it('queues an authorized employee export without doing the work in the request', function () {
    Queue::fake();
    $fixture = exportCompanyFixture();
    $user = User::factory()->create();
    Permission::findOrCreate('employee.view-sensitive', 'web');
    $user->givePermissionTo('employee.view-sensitive');

    $response = $this->actingAs($user)->post(route('exports.store', 'employees'), [
        'branch_id' => $fixture['branch']->id,
    ]);

    $response->assertRedirect(route('exports.index'));
    $export = DataExport::query()->sole();

    expect($export->status)->toBe('queued')
        ->and($export->company_id)->toBe($fixture['company']->id)
        ->and($export->filters)->toMatchArray(['branch_id' => $fixture['branch']->id]);
    Queue::assertPushed(GenerateDataExport::class, fn ($job) => $job->dataExportId === $export->id);
});

it('does not expose salary data to users with basic employee access', function () {
    exportCompanyFixture();
    $user = User::factory()->create();
    Permission::findOrCreate('employee.view', 'web');
    $user->givePermissionTo('employee.view');

    $this->actingAs($user)
        ->post(route('exports.store', 'employees'))
        ->assertForbidden();

    expect(DataExport::query()->exists())->toBeFalse();
});

it('rejects an export when the user lacks the matching report permission', function () {
    exportCompanyFixture();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('exports.store', 'payroll'))
        ->assertForbidden();

    expect(DataExport::query()->exists())->toBeFalse();
});

it('generates a private employee Excel file and neutralizes spreadsheet formulas', function () {
    Storage::fake('local');
    $fixture = exportCompanyFixture();
    $user = User::factory()->create();
    Employee::query()->create([
        'company_id' => $fixture['company']->id,
        'branch_id' => $fixture['branch']->id,
        'department_id' => $fixture['department']->id,
        'employee_code' => '=1+1',
        'first_name' => 'Dara',
        'last_name' => 'Sok',
        'hire_date' => '2026-01-01',
        'employment_status' => 'Active',
        'is_active' => true,
    ]);
    $export = DataExport::query()->create([
        'user_id' => $user->id,
        'company_id' => $fixture['company']->id,
        'type' => 'employees',
        'filters' => [],
        'status' => 'queued',
        'disk' => 'local',
        'file_name' => 'employees.xlsx',
    ]);

    (new GenerateDataExport($export->id))->handle();

    $export->refresh();
    expect($export->status)->toBe('completed')
        ->and($export->progress)->toBe(100)
        ->and($export->row_count)->toBe(1)
        ->and($export->expires_at)->not->toBeNull();

    Storage::disk('local')->assertExists($export->file_path);
    expect(Storage::disk('local')->get($export->file_path))->toStartWith('PK');
    $values = SimpleExcelReader::create(Storage::disk('local')->path($export->file_path), 'xlsx')
        ->noHeaderRow()
        ->getRows()
        ->skip(1)
        ->first();
    expect($values[0])->toBe("'=1+1");
});

it('only lets the export owner download a completed file', function () {
    Storage::fake('local');
    $fixture = exportCompanyFixture();
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    Storage::disk('local')->put('exports/test.xlsx', 'Excel file');
    $export = DataExport::query()->create([
        'user_id' => $owner->id,
        'company_id' => $fixture['company']->id,
        'type' => 'employees',
        'status' => 'completed',
        'progress' => 100,
        'disk' => 'local',
        'file_path' => 'exports/test.xlsx',
        'file_name' => 'employees.xlsx',
        'expires_at' => now()->addDay(),
    ]);

    $this->actingAs($otherUser)
        ->get(route('exports.download', $export))
        ->assertForbidden();

    $this->actingAs($owner)
        ->get(route('exports.download', $export))
        ->assertOk()
        ->assertDownload('employees.xlsx');
});
