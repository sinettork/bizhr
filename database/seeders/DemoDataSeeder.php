<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class DemoDataSeeder extends Seeder
{
    private array $columns = [];

    public function run(): void
    {
        if (app()->isProduction() && ! (bool) env('ALLOW_DEMO_DATA', false)) {
            throw new RuntimeException(
                'Demo data is blocked in production. Use a non-production database or explicitly set ALLOW_DEMO_DATA=true.'
            );
        }

        DB::transaction(function (): void {
            $companyId = $this->seedCompany();
            $branches = $this->seedBranches($companyId);
            $departments = $this->seedDepartments($companyId, $branches);
            $employmentTypeId = $this->seedEmploymentType($companyId);
            $positions = $this->seedPositions($companyId, $branches, $departments);
            $users = $this->seedUsers();
            $employees = $this->seedEmployees(
                $companyId,
                $branches,
                $departments,
                $positions,
                $employmentTypeId,
                $users,
            );

            $shiftId = $this->seedShift($companyId);
            $this->seedSchedulesAndAttendance($shiftId, $employees);
            $this->seedPayroll($companyId, $employees, $users);
            $this->seedLeave($companyId, $employees, $users);
            $this->seedContracts($companyId, $employees, $users);
            $this->seedTasks($companyId, $employees, $users);
            $this->seedPerformance($companyId, $positions, $employees, $users);
            $this->seedRecruitment($companyId, $branches, $positions, $users);
            $this->seedTraining($companyId, $employees, $users);
            $this->seedAssets($companyId, $employees, $users);
            $this->seedExpenses($companyId, $employees, $users);
            $this->seedAnnouncements($companyId, $branches, $departments, $users);
        });

        $this->command?->info('BizHR realistic demo data seeded successfully.');
        $this->command?->warn('Existing passwords were preserved. New @bizhr.local password: Demo@12345');
    }

    private function seedCompany(): int
    {
        // The current BizHR UI is single-company and resolves Company::first().
        // Reuse that company so seeded records are visible throughout the project.
        $existingCompanyId = DB::table('companies')->orderBy('id')->value('id');
        if ($existingCompanyId) {
            return (int) $existingCompanyId;
        }

        return $this->upsertId('companies', ['name' => 'BizHR Demo Cambodia'], [
            'legal_name' => 'BizHR Demo Cambodia Co., Ltd.',
            'email' => 'info@bizhr-demo.test',
            'phone' => '023 900 888',
            'website' => 'https://bizhr-demo.test',
            'registration_number' => 'DEMO-MOC-2026-001',
            'tax_id' => 'DEMO-TIN-001',
            'address' => 'Russian Federation Boulevard, Sen Sok',
            'city' => 'Phnom Penh',
            'country' => 'Cambodia',
            'currency' => 'USD',
            'timezone' => 'Asia/Phnom_Penh',
            'date_format' => 'd/m/Y',
        ]);
    }

    private function seedBranches(int $companyId): array
    {
        $items = [
            'PP-HQ' => ['ការិយាល័យកណ្ដាល ភ្នំពេញ', 'Phnom Penh', 'Russian Federation Boulevard, Sen Sok', '023 900 889', 11.5564, 104.9282, true],
            'SR-BR' => ['សាខាសៀមរាប', 'Siem Reap', 'Sivutha Boulevard, Svay Dangkum', '063 900 889', 13.3618, 103.8606, false],
            'BB-BR' => ['សាខាបាត់ដំបង', 'Battambang', 'Street 1, Svay Por', '053 900 889', 13.0957, 103.2022, false],
            'SHV-BR' => ['សាខាព្រះសីហនុ', 'Preah Sihanouk', 'Ekareach Street, Sangkat 4', '034 900 889', 10.6253, 103.5234, false],
        ];
        $ids = [];
        foreach ($items as $code => [$name, $city, $address, $phone, $latitude, $longitude, $headOffice]) {
            $ids[$code] = $this->upsertId('branches', [
                'company_id' => $companyId,
                'code' => $code,
            ], [
                'name' => $name,
                'email' => strtolower($code).'@bizhr-demo.test',
                'phone' => $phone,
                'address' => $address,
                'city' => $city,
                'manager_name' => $headOffice ? 'សុខ វិសាល' : 'អ្នកគ្រប់គ្រងសាខា',
                'is_head_office' => $headOffice,
                'is_active' => true,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'attendance_qr_enabled' => true,
            ]);
        }
        return $ids;
    }

    private function seedDepartments(int $companyId, array $branches): array
    {
        $items = [
            'HR' => ['ធនធានមនុស្ស', 'hr@bizhr-demo.test'],
            'FIN' => ['ហិរញ្ញវត្ថុ និងគណនេយ្យ', 'finance@bizhr-demo.test'],
            'SAL' => ['ផ្នែកលក់', 'sales@bizhr-demo.test'],
            'OPS' => ['ប្រតិបត្តិការ', 'operations@bizhr-demo.test'],
        ];
        $ids = [];
        foreach ($branches as $branchCode => $branchId) {
            foreach ($items as $code => [$name, $email]) {
                $ids[$branchCode][$code] = $this->upsertId('departments', [
                    'company_id' => $companyId,
                    'branch_id' => $branchId,
                    'code' => $branchCode.'-'.$code,
                ], [
                    'name' => $name,
                    'email' => strtolower($branchCode).'-'.$email,
                    'phone' => '023 900 890',
                    'description' => "ផ្នែក {$name} — {$branchCode}",
                    'is_active' => true,
                ]);
            }
        }
        // Head-office aliases keep company-wide KPI and announcement setup simple.
        foreach (array_keys($items) as $code) $ids[$code] = $ids['PP-HQ'][$code];
        return $ids;
    }

    private function seedEmploymentType(int $companyId): int
    {
        return $this->upsertId('employment_types', [
            'company_id' => $companyId,
            'code' => 'FULL-TIME',
        ], [
            'name' => 'បុគ្គលិកពេញម៉ោង',
            'description' => 'កិច្ចសន្យាការងារពេញម៉ោង',
            'is_active' => true,
            'sort_order' => 1,
        ]);
    }

    private function seedPositions(int $companyId, array $branches, array $departments): array
    {
        $items = [
            'HR-MGR' => ['HR', 'ប្រធានធនធានមនុស្ស', 900, 1800, true],
            'ACCOUNTANT' => ['FIN', 'គណនេយ្យករ', 550, 1100, false],
            'SALES-MGR' => ['SAL', 'ប្រធានផ្នែកលក់', 800, 1600, true],
            'SALES' => ['SAL', 'បុគ្គលិកលក់', 350, 800, false],
            'OPS' => ['OPS', 'បុគ្គលិកប្រតិបត្តិការ', 350, 750, false],
        ];
        $ids = [];
        $sort = 1;
        foreach ($branches as $branchCode => $branchId) {
            foreach ($items as $code => [$department, $title, $min, $max, $manager]) {
                $ids[$branchCode][$code] = $this->upsertId('positions', [
                    'company_id' => $companyId,
                    'branch_id' => $branchId,
                    'department_id' => $departments[$branchCode][$department],
                    'code' => $branchCode.'-'.$code,
                ], [
                    'title' => $title,
                    'description' => "មុខតំណែង {$title} — {$branchCode}",
                    'minimum_salary' => $min,
                    'maximum_salary' => $max,
                    'is_manager_position' => $manager,
                    'is_active' => true,
                    'sort_order' => $sort++,
                ]);
            }
        }
        foreach (array_keys($items) as $code) $ids[$code] = $ids['PP-HQ'][$code];
        return $ids;
    }

    private function seedUsers(): array
    {
        $items = [
            // Demo accounts are intentionally isolated from TestUsersSeeder and
            // existing client accounts. Never attach generated HR records to
            // owner@example.com, hr@example.com, or other pre-existing users.
            'owner' => ['BizHR Demo Owner', 'demo.owner@bizhr.local', 'Owner'],
            'hr' => ['ចាន់ ស្រីពៅ', 'sreypov@bizhr.local', 'HR Administrator'],
            'manager' => ['សុខ វិសាល', 'visal@bizhr.local', 'Manager'],
            'accountant' => ['ហេង ដារ៉ា', 'dara@bizhr.local', 'Accountant'],
            'employee1' => ['សេង ពិសិដ្ឋ', 'piseth@bizhr.local', 'Employee'],
            'employee2' => ['លី សុភា', 'sophea@bizhr.local', 'Employee'],
            'employee3' => ['គឹម ស្រីនាង', 'sreynang@bizhr.local', 'Employee'],
            'employee4' => ['វ៉ាន់ រតនា', 'rathana@bizhr.local', 'Employee'],
            'employee5' => ['ពៅ មករា', 'makara@bizhr.local', 'Employee'],
            'employee6' => ['នី សុជាតា', 'socheata@bizhr.local', 'Employee'],
            'employee7' => ['ឈុន វណ្ណៈ', 'vannak@bizhr.local', 'Employee'],
        ];
        $ids = [];
        foreach ($items as $key => [$name, $email, $role]) {
            $existingId = DB::table('users')->where('email', $email)->value('id');
            $ids[$key] = $existingId
                ? (int) $existingId
                : $this->upsertId('users', ['email' => $email], [
                    'name' => $name,
                    'email_verified_at' => now(),
                    'password' => Hash::make('Demo@12345'),
                ]);
            $this->assignRole($ids[$key], $role);
        }
        return $ids;
    }

    private function seedEmployees(
        int $companyId,
        array $branches,
        array $departments,
        array $positions,
        int $employmentTypeId,
        array $users,
    ): array {
        $items = [
            ['key'=>'hr','code'=>'EMP-001','branch'=>'PP-HQ','first'=>'Sreypov','last'=>'Chan','km'=>'ចាន់ ស្រីពៅ','gender'=>'female','dept'=>'HR','position'=>'HR-MGR','salary'=>1200,'currency'=>'USD','hire'=>'2022-03-01'],
            ['key'=>'manager','code'=>'EMP-002','branch'=>'PP-HQ','first'=>'Visal','last'=>'Sok','km'=>'សុខ វិសាល','gender'=>'male','dept'=>'SAL','position'=>'SALES-MGR','salary'=>1400,'currency'=>'USD','hire'=>'2021-06-15'],
            ['key'=>'accountant','code'=>'EMP-003','branch'=>'PP-HQ','first'=>'Dara','last'=>'Heng','km'=>'ហេង ដារ៉ា','gender'=>'male','dept'=>'FIN','position'=>'ACCOUNTANT','salary'=>850,'currency'=>'USD','hire'=>'2023-01-10'],
            ['key'=>'employee1','code'=>'EMP-004','branch'=>'PP-HQ','first'=>'Piseth','last'=>'Seng','km'=>'សេង ពិសិដ្ឋ','gender'=>'male','dept'=>'OPS','position'=>'OPS','salary'=>500,'currency'=>'USD','hire'=>'2023-11-01'],
            ['key'=>'employee2','code'=>'EMP-005','branch'=>'SR-BR','first'=>'Sophea','last'=>'Ly','km'=>'លី សុភា','gender'=>'male','dept'=>'SAL','position'=>'SALES','salary'=>600,'currency'=>'USD','hire'=>'2024-02-01'],
            ['key'=>'employee3','code'=>'EMP-006','branch'=>'SR-BR','first'=>'Sreynang','last'=>'Kim','km'=>'គឹម ស្រីនាង','gender'=>'female','dept'=>'SAL','position'=>'SALES','salary'=>2400000,'currency'=>'KHR','hire'=>'2024-05-20'],
            ['key'=>'employee4','code'=>'EMP-007','branch'=>'BB-BR','first'=>'Rathana','last'=>'Van','km'=>'វ៉ាន់ រតនា','gender'=>'male','dept'=>'OPS','position'=>'OPS','salary'=>500,'currency'=>'USD','hire'=>'2025-01-06'],
            ['key'=>'employee5','code'=>'EMP-008','branch'=>'BB-BR','first'=>'Makara','last'=>'Pov','km'=>'ពៅ មករា','gender'=>'male','dept'=>'OPS','position'=>'OPS','salary'=>2000000,'currency'=>'KHR','hire'=>'2025-03-03'],
            ['key'=>'employee6','code'=>'EMP-009','branch'=>'SHV-BR','first'=>'Socheata','last'=>'Ny','km'=>'នី សុជាតា','gender'=>'female','dept'=>'FIN','position'=>'ACCOUNTANT','salary'=>650,'currency'=>'USD','hire'=>'2025-05-12'],
            ['key'=>'employee7','code'=>'EMP-010','branch'=>'SHV-BR','first'=>'Vannak','last'=>'Chhun','km'=>'ឈុន វណ្ណៈ','gender'=>'male','dept'=>'SAL','position'=>'SALES','salary'=>2200000,'currency'=>'KHR','hire'=>'2025-08-01'],
        ];
        $ids = [];
        foreach ($items as $index => $item) {
            $ids[$item['key']] = $this->upsertId('employees', ['employee_code' => $item['code']], [
                'company_id' => $companyId,
                'branch_id' => $branches[$item['branch']],
                'department_id' => $departments[$item['branch']][$item['dept']],
                'position_id' => $positions[$item['branch']][$item['position']],
                'employment_type_id' => $employmentTypeId,
                'user_id' => $users[$item['key']],
                'first_name' => $item['first'],
                'last_name' => $item['last'],
                'full_name_km' => $item['km'],
                'full_name_en' => "{$item['first']} {$item['last']}",
                'date_of_birth' => now()->subYears(24 + $index)->subMonths($index)->toDateString(),
                'phone' => '010 900 '.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                'email' => DB::table('users')->where('id', $users[$item['key']])->value('email'),
                'address' => 'Phnom Penh, Cambodia',
                'city' => 'Phnom Penh',
                'hire_date' => $item['hire'],
                'probation_end_date' => date('Y-m-d', strtotime($item['hire'].' +3 months')),
                'contract_start_date' => $item['hire'],
                'contract_end_date' => date('Y-m-d', strtotime($item['hire'].' +2 years')),
                'base_salary' => $item['salary'],
                'salary_currency' => $item['currency'],
                'payment_method' => 'bank_transfer',
                'bank_name' => 'ABA Bank',
                'bank_account_name' => $item['first'].' '.$item['last'],
                'bank_account_number' => 'DEMO-'.str_pad((string) ($index + 1), 6, '0', STR_PAD_LEFT),
                'emergency_contact_name' => 'Demo Contact '.($index + 1),
                'emergency_contact_phone' => '012 800 '.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                'employment_status' => 'Active',
                'is_active' => true,
            ]);
        }
        return $ids;
    }

    private function seedShift(int $companyId): int
    {
        return $this->upsertId('work_shifts', ['code' => 'OFFICE-0800'], [
            'company_id' => $companyId,
            'name' => 'វេនការិយាល័យ 08:00–17:00',
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
            'break_minutes' => 60,
            'late_grace_minutes' => 10,
            'early_leave_grace_minutes' => 5,
            'is_night_shift' => false,
            'is_active' => true,
        ]);
    }

    private function seedSchedulesAndAttendance(int $shiftId, array $employees): void
    {
        if (! Schema::hasTable('employee_schedules') || ! Schema::hasTable('attendances')) return;
        foreach (array_values($employees) as $employeeIndex => $employeeId) {
            $branchId = (int) DB::table('employees')->where('id', $employeeId)->value('branch_id');
            for ($offset = 28; $offset >= 1; $offset--) {
                $date = today()->subDays($offset);
                $isRest = $date->isSunday();
                $this->upsertId('employee_schedules', [
                    'employee_id' => $employeeId,
                    'work_date' => $date->toDateString(),
                ], [
                    'branch_id' => $branchId,
                    'work_shift_id' => $isRest ? null : $shiftId,
                    'is_rest_day' => $isRest,
                    'notes' => $isRest ? 'ថ្ងៃសម្រាកប្រចាំសប្ដាហ៍' : null,
                ]);
                if ($isRest) continue;

                $late = (($offset + $employeeIndex) % 7 === 0) ? 18 : 0;
                $absent = (($offset + $employeeIndex * 3) % 29 === 0);
                $checkIn = $absent ? null : $date->copy()->setTime(8, $late);
                $checkOut = $absent ? null : $date->copy()->setTime(17, ($offset % 4) * 5);
                $worked = $absent ? 0 : max(0, $checkIn->diffInMinutes($checkOut) - 60);

                $this->upsertId('attendances', [
                    'employee_id' => $employeeId,
                    'work_date' => $date->toDateString(),
                ], [
                    'branch_id' => $branchId,
                    'scheduled_start' => '08:00:00',
                    'scheduled_end' => '17:00:00',
                    'check_in_at' => $checkIn,
                    'check_out_at' => $checkOut,
                    'check_in_method' => $absent ? 'manual' : (($offset % 3 === 0) ? 'qr' : 'web'),
                    'check_out_method' => $absent ? null : 'web',
                    'check_in_location' => $absent ? null : '11.5564,104.9282',
                    'check_out_location' => $absent ? null : '11.5564,104.9282',
                    'late_minutes' => $late,
                    'early_leave_minutes' => 0,
                    'worked_minutes' => $worked,
                    'overtime_minutes' => max(0, $worked - 480),
                    'status' => $absent ? 'absent' : ($late > 0 ? 'late' : 'present'),
                    'notes' => $absent ? 'Demo absence for report testing' : null,
                ]);
            }
        }
    }

    private function seedLeave(int $companyId, array $employees, array $users): void
    {
        if (! Schema::hasTable('leave_types')) return;
        $annual = $this->upsertId('leave_types', ['company_id'=>$companyId,'code'=>'ANNUAL'], [
            'name'=>'ឈប់សម្រាកប្រចាំឆ្នាំ','days_per_year'=>18,'is_paid'=>true,
            'requires_attachment'=>false,'carry_forward_allowed'=>true,
            'maximum_carry_forward_days'=>5,'is_active'=>true,
        ]);
        $sick = $this->upsertId('leave_types', ['company_id'=>$companyId,'code'=>'SICK'], [
            'name'=>'ឈប់សម្រាកព្យាបាលជំងឺ','days_per_year'=>7,'is_paid'=>true,
            'requires_attachment'=>true,'carry_forward_allowed'=>false,'is_active'=>true,
        ]);

        if (Schema::hasTable('leave_requests')) {
            foreach (array_values($employees) as $index=>$employeeId) {
                $approved = $index % 3 !== 0;
                $start = $approved ? today()->subDays(5 + $index * 2) : today()->addDays(4 + $index);
                $days = $index % 4 === 0 ? 2 : 1;
                $this->upsertId('leave_requests', [
                    'employee_id'=>$employeeId,
                    'start_date'=>$start->toDateString(),
                ], [
                    'leave_type_id'=>$index % 3 === 0 ? $sick : $annual,
                    'end_date'=>$start->copy()->addDays($days - 1)->toDateString(),
                    'total_days'=>$days,
                    'reason'=>$index % 3 === 0 ? 'ពិនិត្យសុខភាព' : 'សម្រាកកិច្ចការគ្រួសារ',
                    'status'=>$approved ? 'approved' : 'pending',
                    'reviewed_by'=>$approved ? $users['hr'] : null,
                    'reviewed_at'=>$approved ? $start->copy()->subDay() : null,
                ]);
            }
        }
    }

    private function seedPayroll(int $companyId, array $employees, array $users): void
    {
        if (! Schema::hasTable('payroll_periods') || ! Schema::hasTable('payroll_items')) return;
        $start = today()->subMonth()->startOfMonth();
        $end = today()->subMonth()->endOfMonth();
        $periodId = $this->upsertId('payroll_periods', [
            'company_id'=>$companyId,'start_date'=>$start->toDateString(),'end_date'=>$end->toDateString(),
        ], [
            'name'=>'បញ្ជីប្រាក់ខែ '.$start->format('m/Y'),'payment_date'=>$end->copy()->addDays(5),
            'status'=>'draft','processed_by'=>null,'processed_at'=>null,
            'approved_by'=>null,'approved_at'=>null,
            'notes'=>'Demo payroll preview with separate USD and KHR totals; recalculate before approval',
        ]);
        foreach (array_values($employees) as $index=>$employeeId) {
            $employee = DB::table('employees')->where('id',$employeeId)->first();
            $base = (float)$employee->base_salary;
            $allowance = $employee->salary_currency==='KHR' ? 80000 : 20;
            $bonus = $index % 3 === 0 ? ($employee->salary_currency==='KHR'?120000:30) : 0;
            $deduction = $index % 4 === 0 ? ($employee->salary_currency==='KHR'?40000:10) : 0;
            $tax = 0;
            $gross = $base+$allowance+$bonus;
            $net = $gross-$deduction-$tax;
            $this->upsertId('payroll_items', [
                'payroll_period_id'=>$periodId,'employee_id'=>$employeeId,
            ], [
                'currency'=>$employee->salary_currency,'base_salary'=>$base,'pay_type'=>'monthly',
                'worked_days'=>24,'absent_days'=>$index%5===0?1:0,'paid_leave_days'=>$index%4===0?1:0,
                'unpaid_leave_days'=>0,'overtime_hours'=>$index%3,'overtime_amount'=>0,
                'allowance_amount'=>$allowance,'bonus_amount'=>$bonus,'commission_amount'=>0,
                'deduction_amount'=>$deduction,'loan_deduction'=>0,'advance_deduction'=>0,
                'tax_amount'=>$tax,'gross_salary'=>$gross,'net_salary'=>$net,
                'scheduled_minutes'=>11520,'worked_minutes'=>11040-($index*15),
                'paid_leave_minutes'=>$index%4===0?480:0,'absent_minutes'=>$index%5===0?480:0,
                'late_minutes'=>$index*3,'early_leave_minutes'=>0,'approved_overtime_minutes'=>$index*30,
                'payable_base_amount'=>$base,'exception_count'=>$index%5===0?1:0,
                'payment_status'=>'unpaid','notes'=>'Demo calculation; validate before real payroll use',
                'calculation_details'=>json_encode(['demo'=>true,'currency'=>$employee->salary_currency],JSON_THROW_ON_ERROR),
            ]);
        }
    }

    private function seedTasks(int $companyId, array $employees, array $users): void
    {
        if (! Schema::hasTable('tasks')) return;
        $titles = [
            'រៀបចំរបាយការណ៍អតិថិជន','តាមដានគោលដៅប្រចាំសប្ដាហ៍',
            'ពិនិត្យស្តុកសម្ភារៈ','ធ្វើបច្ចុប្បន្នភាពអ្នកផ្គត់ផ្គង់',
            'ទាក់ទងអតិថិជនសកម្ម','រៀបចំឯកសារការងារ',
            'ពិនិត្យទិន្នន័យប្រចាំខែ','រៀបចំផែនការប្រចាំសប្ដាហ៍',
            'ធ្វើបច្ចុប្បន្នភាព CRM','សង្ខេបលទ្ធផលការងារ',
        ];
        foreach (array_values($employees) as $index=>$employeeId) {
            $progress = [0,25,40,60,75,90,100,100,35,80][$index];
            $status = $progress===100 ? ($index % 2 ? 'verified' : 'waiting_verification') : ($progress ? 'in_progress' : 'not_started');
            $due = $index===8 ? today()->subDay() : today()->addDays($index + 1);
            $this->upsertId('tasks', ['assigned_to'=>$employeeId,'title'=>$titles[$index]], [
                'company_id'=>$companyId,'assigned_by'=>$users['manager'],
                'description'=>'កិច្ចការសាកល្បងសម្រាប់បង្ហាញ workflow និង dashboard',
                'priority'=>['low','medium','high','urgent'][$index % 4],
                'start_date'=>today()->subDays(4),'due_date'=>$due,
                'status'=>$status,'progress'=>$progress,
                'submitted_at'=>$status==='waiting_verification'?now()->subHour():null,
                'completed_at'=>$status==='verified'?now()->subDays(2):null,
                'verified_by'=>$status==='verified'?$users['manager']:null,
                'verified_at'=>$status==='verified'?now()->subDays(2):null,
            ]);
        }
    }

    private function seedContracts(
        int $companyId,
        array $employees,
        array $users,
    ): void {
        if (! Schema::hasTable('employment_contracts')) return;

        foreach (array_keys($employees) as $index=>$employeeKey) {
            $employee = DB::table('employees')->where('id',$employees[$employeeKey])->first();
            $branchName = DB::table('branches')->where('id', $employee->branch_id)->value('name');
            // Use UDC for demo records so no fake signed FDC document is represented as genuine.
            $type = 'udc';
            $number = 'DEMO-'.strtoupper($type).'-'.str_pad((string)($index+1),3,'0',STR_PAD_LEFT);
            $start = today()->subYear();
            $end = $type === 'fdc' ? $start->copy()->addYears(2)->subDay() : null;
            $this->upsertId('employment_contracts', ['contract_number'=>$number], [
                'company_id'=>$companyId,
                'employee_id'=>$employees[$employeeKey],
                'type'=>$type,
                'status'=>'active',
                'start_date'=>$start,
                'end_date'=>$end,
                'signed_at'=>$start->copy()->subDays(3),
                'probation_category'=>'regular',
                'probation_end_date'=>$start->copy()->addMonths(3),
                'position_title'=>DB::table('positions')->where('id',$employee->position_id)->value('title'),
                'department_name'=>DB::table('departments')->where('id',$employee->department_id)->value('name'),
                'branch_name'=>$branchName,
                'salary_amount'=>$employee->base_salary,
                'salary_currency'=>$employee->salary_currency,
                'pay_type'=>'monthly',
                'work_hours_per_day'=>8,
                'work_days_per_week'=>6,
                'renewal_notice_date'=>$end?->copy()->subMonth(),
                'submitted_by'=>$users['hr'],
                'submitted_at'=>$start->copy()->subDays(5),
                'approved_by'=>$users['owner'],
                'approved_at'=>$start->copy()->subDays(4),
                'terms'=>json_encode(['demo'=>true,'notice_days'=>30], JSON_THROW_ON_ERROR),
                'checksum'=>hash('sha256',$number.'|'.$employees[$employeeKey].'|'.$employee->base_salary.'|'.$employee->salary_currency),
            ]);
        }
    }

    private function seedPerformance(int $companyId, array $positions, array $employees, array $users): void
    {
        if (! Schema::hasTable('kpi_templates')) return;
        $template = $this->upsertId('kpi_templates', [
            'company_id'=>$companyId,'position_id'=>$positions['SALES'],'name'=>'KPI ផ្នែកលក់ប្រចាំខែ',
        ], [
            'description'=>'គំរូវាយតម្លៃការលក់សម្រាប់ demo','review_frequency'=>'monthly',
            'is_active'=>true,'created_by'=>$users['hr'],
        ]);
        $item = $this->upsertId('kpi_template_items', [
            'kpi_template_id'=>$template,'name'=>'ចំណូលលក់',
        ], [
            'description'=>'ចំណូលលក់សរុបប្រចាំខែ','measurement_unit'=>'USD',
            'target_value'=>10000,'weight'=>60,'scoring_direction'=>'higher_is_better','sort_order'=>1,
        ]);
        if (Schema::hasTable('employee_goals')) {
            foreach (array_values($employees) as $index=>$employeeId) {
                $target = 5000 + ($index * 750);
                $this->upsertId('employee_goals', [
                    'employee_id'=>$employeeId,'title'=>'គោលដៅការងារ '.str_pad((string)($index+1),2,'0',STR_PAD_LEFT),
                ], [
                    'company_id'=>$companyId,'kpi_template_item_id'=>$item,
                    'description'=>'គោលដៅការងារប្រចាំខែសម្រាប់បុគ្គលិក',
                    'measurement_unit'=>'USD','target_value'=>$target,
                    'current_value'=>round($target * (0.45 + $index * 0.04),2),'weight'=>60,
                    'scoring_direction'=>'higher_is_better','start_date'=>today()->startOfMonth(),
                    'due_date'=>today()->endOfMonth(),'status'=>'active','assigned_by'=>$users['manager'],
                    'activated_at'=>now()->startOfMonth(),
                ]);
            }
        }
        if (Schema::hasTable('performance_reviews')) {
            foreach (array_values($employees) as $index=>$employeeId) {
                $status = ['draft','manager_submitted','hr_approved','employee_acknowledged','closed'][$index % 5];
                $this->upsertId('performance_reviews', [
                    'employee_id'=>$employeeId,
                    'period_start'=>today()->subMonth()->startOfMonth()->toDateString(),
                    'period_end'=>today()->subMonth()->endOfMonth()->toDateString(),
                    'version'=>1,
                ], [
                    'company_id'=>$companyId,'reviewer_id'=>$users['manager'],'status'=>$status,
                    'overall_score'=>$status==='draft'?null:75+$index,
                    'strengths'=>'ការទទួលខុសត្រូវ និងការគោរពពេលវេលាល្អ',
                    'areas_for_improvement'=>'បង្កើនការរៀបចំផែនការ និងការរាយការណ៍',
                    'manager_comment'=>'លទ្ធផលការងារសមស្របតាមគោលដៅ',
                    'manager_submitted_at'=>$status==='draft'?null:now()->subDays(5),
                    'hr_approved_by'=>in_array($status,['hr_approved','employee_acknowledged','closed'],true)?$users['hr']:null,
                    'hr_approved_at'=>in_array($status,['hr_approved','employee_acknowledged','closed'],true)?now()->subDays(3):null,
                    'employee_acknowledged_at'=>in_array($status,['employee_acknowledged','closed'],true)?now()->subDays(2):null,
                    'closed_by'=>$status==='closed'?$users['hr']:null,
                    'closed_at'=>$status==='closed'?now()->subDay():null,
                ]);
            }
        }
    }

    private function seedRecruitment(int $companyId, array $branches, array $positions, array $users): void
    {
        if (! Schema::hasTable('job_vacancies')) return;
        if (Schema::hasTable('job_applicants')) {
            $candidates = [
                ['សាន មុនីរ័ត្ន','moniroth.candidate@example.test','010 111 201','screening'],
                ['ជា សុវណ្ណា','sovanna.candidate@example.test','010 111 202','shortlisted'],
                ['នួន ដាលីន','dalin.candidate@example.test','010 111 203','interview'],
                ['សុខ ស្រីមុំ','sreymom.candidate@example.test','010 111 204','applied'],
                ['ហេង វិចិត្រ','vichet.candidate@example.test','010 111 205','offer_pending'],
                ['លឹម សុភ័ក្ត្រ','sopheak.candidate@example.test','010 111 206','screening'],
                ['គង់ ម៉ាលី','maly.candidate@example.test','010 111 207','shortlisted'],
                ['យ៉ែម រដ្ឋា','rotha.candidate@example.test','010 111 208','rejected'],
                ['ឌី ស្រីលក្ខណ៍','sreyleak.candidate@example.test','010 111 209','interview'],
                ['អ៊ុំ វាសនា','veasna.candidate@example.test','010 111 210','accepted'],
            ];
            $titles = [
                'បុគ្គលិកលក់','មន្ត្រីធនធានមនុស្ស','គណនេយ្យករជំនួយ',
                'បុគ្គលិកប្រតិបត្តិការ','ប្រធានក្រុមលក់','អ្នកថែទាំអតិថិជន',
                'មន្ត្រីរដ្ឋបាល','បុគ្គលិកស្តុក','មន្ត្រីរបាយការណ៍','អ្នកសម្របសម្រួលការងារ',
            ];
            $positionKeys = ['SALES','HR-MGR','ACCOUNTANT','OPS','SALES-MGR','SALES','OPS','OPS','ACCOUNTANT','OPS'];
            $branchKeys = ['PP-HQ','SR-BR','BB-BR','SHV-BR','PP-HQ','SR-BR','BB-BR','SHV-BR','PP-HQ','SR-BR'];
            foreach ($candidates as $index=>[$name,$email,$phone,$status]) {
                $branchCode = $branchKeys[$index];
                $vacancy = $this->upsertId('job_vacancies', ['company_id'=>$companyId,'title'=>$titles[$index]], [
                    'position_id'=>$positions[$branchCode][$positionKeys[$index]],'branch_id'=>$branches[$branchCode],
                    'description'=>'ស្វែងរកបេក្ខជនដែលមានជំនាញ និងការទទួលខុសត្រូវល្អ',
                    'openings'=>1+($index%2),'open_date'=>today()->subDays(10+$index),
                    'close_date'=>today()->addDays(20-$index),'status'=>$index===7?'closed':'open',
                    'created_by'=>$users['hr'],
                ]);
                $this->upsertId('job_applicants', ['job_vacancy_id'=>$vacancy,'email'=>$email], [
                    'full_name'=>$name,'phone'=>$phone,'status'=>$status,
                    'hr_note'=>'ទិន្នន័យបេក្ខជនសម្រាប់បង្ហាញ workflow','applied_at'=>now()->subDays($index+1),
                ]);
            }
        }
    }

    private function seedTraining(int $companyId, array $employees, array $users): void
    {
        if (! Schema::hasTable('training_courses')) return;
        $courseNames = [
            'សុវត្ថិភាព និងសីលធម៌ការងារ','ការប្រើប្រាស់ BizHR',
            'សេវាកម្មអតិថិជន','ជំនាញលក់មូលដ្ឋាន','ការគ្រប់គ្រងពេលវេលា',
            'សុវត្ថិភាពទិន្នន័យ','Excel សម្រាប់ការងារ','ទំនាក់ទំនងក្នុងក្រុម',
            'ការដោះស្រាយបញ្ហា','ការរៀបចំរបាយការណ៍',
        ];
        foreach ($courseNames as $index=>$courseName) {
            $course = $this->upsertId('training_courses', ['company_id'=>$companyId,'title'=>$courseName], [
                'description'=>'វគ្គបណ្តុះបណ្តាលសម្រាប់ការអភិវឌ្ឍបុគ្គលិក',
                'duration_minutes'=>60+($index*15),'is_mandatory'=>$index<2,
                'is_active'=>true,'created_by'=>$users['hr'],
            ]);
            if (Schema::hasTable('training_enrollments')) {
                $employeeId = array_values($employees)[$index];
                $progress = [100,70,25,0,50,80,100,10,40,90][$index];
                $this->upsertId('training_enrollments', [
                    'training_course_id'=>$course,'employee_id'=>$employeeId,
                ], [
                    'status'=>$progress===100?'completed':($progress>0?'in_progress':'assigned'),
                    'progress'=>$progress,'score'=>$progress===100?88:null,
                    'due_date'=>today()->addDays(14),'started_at'=>$progress>0?now()->subDays(3):null,
                    'completed_at'=>$progress===100?now()->subDay():null,'assigned_by'=>$users['hr'],
                ]);
            }
        }
    }

    private function seedAssets(int $companyId, array $employees, array $users): void
    {
        if (! Schema::hasTable('assets')) return;
        $assets = [
            ['LAP-001','Dell Latitude 5440','Laptop',850],
            ['LAP-002','Lenovo ThinkPad E14','Laptop',780],
            ['PHN-001','Samsung Galaxy A55','Mobile phone',420],
            ['PHN-002','iPhone 15','Mobile phone',850],
            ['MON-001','Dell 24-inch Monitor','Monitor',210],
            ['TAB-001','Samsung Galaxy Tab','Tablet',390],
            ['PRN-001','HP LaserJet Printer','Printer',320],
            ['CHR-001','Ergonomic Office Chair','Furniture',180],
            ['CAM-001','Logitech Conference Camera','Camera',250],
            ['RTR-001','TP-Link Business Router','Network',160],
        ];
        foreach ($assets as $index=>[$code,$name,$category,$cost]) {
            $assigned = $index < 7;
            $assetId = $this->upsertId('assets', ['company_id'=>$companyId,'asset_code'=>$code], [
                'name'=>$name,'category'=>$category,'serial_number'=>'DEMO-ASSET-'.str_pad((string)($index+1),4,'0',STR_PAD_LEFT),
                'purchase_date'=>today()->subMonths(12-$index),'purchase_cost'=>$cost,'currency'=>'USD',
                'condition'=>$index===8?'fair':'good','status'=>$assigned?'assigned':'available','notes'=>'Demo asset '.($index+1),
            ]);
            if ($assigned && Schema::hasTable('asset_assignments')) {
                $employeeId = array_values($employees)[$index];
                $this->upsertId('asset_assignments', ['asset_id'=>$assetId,'employee_id'=>$employeeId,'status'=>'assigned'], [
                    'assigned_date'=>today()->subMonths(3),'expected_return_date'=>today()->addYear(),
                    'condition_out'=>'good','assigned_by'=>$users['hr'],'notes'=>'Assigned for business use',
                ]);
            }
        }
    }

    private function seedExpenses(int $companyId, array $employees, array $users): void
    {
        if (! Schema::hasTable('expense_claims')) return;
        $items = [
            ['employee1',today()->subDays(3),'Client transport',18.50,'USD','ជួបអតិថិជននៅភ្នំពេញ','pending_manager'],
            ['employee2',today()->subDays(6),'Customer meeting',80000,'KHR','អាហារជាមួយអតិថិជនសំខាន់','pending_accounting'],
            ['employee3',today()->subDays(9),'Office supplies',42.25,'USD','ទិញសម្ភារៈការិយាល័យ','paid'],
            ['employee4',today()->subDays(4),'Fuel',120000,'KHR','ថ្លៃប្រេងសម្រាប់ចុះជួបអតិថិជន','approved'],
            ['employee5',today()->subDays(7),'Delivery',15,'USD','ថ្លៃដឹកឯកសារទៅសាខា','rejected'],
            ['employee6',today()->subDays(2),'Internet',25,'USD','ទិញកញ្ចប់អ៊ីនធឺណិតការងារ','pending_manager'],
            ['employee7',today()->subDays(5),'Printing',60000,'KHR','បោះពុម្ពឯកសារផ្សព្វផ្សាយ','pending_accounting'],
            ['hr',today()->subDays(8),'Recruitment',35,'USD','ថ្លៃផ្សព្វផ្សាយការជ្រើសរើសបុគ្គលិក','paid'],
            ['manager',today()->subDays(10),'Team meeting',160000,'KHR','ចំណាយសម្រាប់កិច្ចប្រជុំក្រុម','approved'],
            ['accountant',today()->subDays(11),'Bank fee',12,'USD','សេវាធនាគារសម្រាប់ប្រតិបត្តិការ','paid'],
        ];
        foreach ($items as [$key,$date,$category,$amount,$currency,$purpose,$status]) {
            $this->upsertId('expense_claims', [
                'employee_id'=>$employees[$key],'expense_date'=>$date->toDateString(),'category'=>$category,
            ], [
                'company_id'=>$companyId,'amount'=>$amount,'currency'=>$currency,
                'business_purpose'=>$purpose,'receipt_path'=>'private/demo/receipt-'.$key.'.pdf',
                'receipt_original_name'=>'demo-receipt.pdf','status'=>$status,
                'manager_id'=>$status!=='pending_manager'?$users['manager']:null,
                'manager_reviewed_at'=>$status!=='pending_manager'?now()->subDays(2):null,
                'accountant_id'=>in_array($status,['approved','paid'],true)?$users['accountant']:null,
                'accountant_reviewed_at'=>in_array($status,['approved','paid'],true)?now()->subDay():null,
                'review_note'=>in_array($status,['approved','paid'],true)?'បានពិនិត្យឯកសារត្រឹមត្រូវ':null,
                'paid_at'=>$status==='paid'?now():null,
                'payment_reference'=>$status==='paid'?'DEMO-PAY-'.strtoupper($key):null,
            ]);
        }
    }

    private function seedAnnouncements(int $companyId, array $branches, array $departments, array $users): void
    {
        if (! Schema::hasTable('announcements')) return;
        $items = [
            ['សូមស្វាគមន៍មកកាន់ BizHR','នេះជាទិន្នន័យសម្រាប់ការបង្ហាញប្រព័ន្ធ BizHR ដល់អតិថិជន។','all',null,true],
            ['កិច្ចប្រជុំផ្នែកលក់','កិច្ចប្រជុំផ្នែកលក់នៅថ្ងៃសុក្រ ម៉ោង ៣ រសៀល។','department','SAL',false],
            ['ថ្ងៃឈប់សម្រាកជាតិ','ការិយាល័យនឹងបិទតាមប្រតិទិនថ្ងៃឈប់សម្រាកផ្លូវការ។','all',null,true],
            ['វគ្គបណ្តុះបណ្តាលថ្មី','សូមបុគ្គលិកចូលពិនិត្យវគ្គបណ្តុះបណ្តាលដែលបានផ្តល់។','all',null,false],
            ['ការបិទបញ្ជីប្រាក់ខែ','សូមពិនិត្យវត្តមាន និងសំណើកែតម្រូវមុនថ្ងៃបិទបញ្ជី។','all',null,false],
            ['រំលឹកការចុះវត្តមានសាខាសៀមរាប','សូមចុះវត្តមានតាម QR នៅទីតាំងសាខាសៀមរាប។','branch','SR-BR',false],
            ['ការត្រួតពិនិត្យទ្រព្យ','សូមបញ្ជាក់សភាពទ្រព្យដែលបានប្រគល់ឱ្យអ្នក។','all',null,false],
            ['គោលដៅប្រចាំខែ','សូមធ្វើបច្ចុប្បន្នភាពវឌ្ឍនភាពគោលដៅមុនចុងខែ។','department','SAL',false],
            ['ដាក់សំណើចំណាយ','សំណើចំណាយត្រូវមានបង្កាន់ដៃ និងគោលបំណងអាជីវកម្ម។','all',null,false],
            ['សុវត្ថិភាពគណនី','កុំចែករំលែកពាក្យសម្ងាត់ និងសូមចាកចេញក្រោយប្រើប្រាស់។','all',null,true],
        ];
        foreach ($items as $index=>[$title,$content,$audience,$department,$pinned]) {
            $branchCode = $audience === 'branch' ? $department : null;
            $this->upsertId('announcements', ['company_id'=>$companyId,'title'=>$title], [
                'content'=>$content,'audience_type'=>$audience,
                'branch_id'=>$branchCode ? $branches[$branchCode] : null,
                'department_id'=>$audience === 'department' ? $departments[$department] : null,
                'published_at'=>now()->subHours($index+1),'expires_at'=>now()->addDays(30-$index),
                'created_by'=>$index===1?$users['manager']:$users['hr'],'is_pinned'=>$pinned,
            ]);
        }
    }

    private function assignRole(int $userId, string $roleName): void
    {
        if (! Schema::hasTable('roles') || ! Schema::hasTable('model_has_roles')) return;
        $roleId = DB::table('roles')->where('name', $roleName)->where('guard_name', 'web')->value('id');
        if (! $roleId) return;
        DB::table('model_has_roles')->updateOrInsert([
            'role_id' => $roleId,
            'model_type' => 'App\\Models\\User',
            'model_id' => $userId,
        ], []);
    }

    private function upsertId(string $table, array $unique, array $values): int
    {
        if (! Schema::hasTable($table)) return 0;
        $columns = $this->columns[$table] ??= Schema::getColumnListing($table);
        $unique = array_intersect_key($unique, array_flip($columns));
        $values = array_intersect_key($values, array_flip($columns));
        $timestamps = [];
        if (in_array('updated_at', $columns, true)) $timestamps['updated_at'] = now();
        if (in_array('created_at', $columns, true)) $timestamps['created_at'] = now();

        $existing = DB::table($table)->where($unique)->first();
        if ($existing) {
            DB::table($table)->where('id', $existing->id)->update($values + array_intersect_key($timestamps, ['updated_at'=>true]));
            return (int) $existing->id;
        }
        return (int) DB::table($table)->insertGetId($unique + $values + $timestamps);
    }
}
