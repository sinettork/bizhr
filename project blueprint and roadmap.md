# BizHR — General Business Human Resource Management System

## 1. Project Overview

### Project name

**BizHR**

### Project type

A web-based Human Resource Management System for general businesses, including:

* Retail businesses
* Wholesale businesses
* Restaurants and cafés
* Offices and agencies
* Schools
* Clinics
* Hotels
* Factories
* Construction companies
* Logistics companies
* Service businesses
* Multi-branch companies

### Main objective

BizHR will help businesses manage:

* Companies
* Branches
* Departments
* Job positions
* Employees
* Attendance
* Work schedules
* Leave
* Payroll
* Employee performance
* Recruitment
* Training
* Company documents
* Assets
* Expenses
* Reports

The system will use Khmer as the main interface language and the **Siemreap** font.

---

# 2. Technology Stack

## Backend

```text
PHP
Laravel 13
Livewire 4
Laravel authentication
Laravel validation
Laravel queues
Laravel scheduled tasks
```

## Frontend

```text
Blade
Livewire single-file components
Flux UI
Tailwind CSS
Basic JavaScript
Siemreap Khmer font
```

## Database

### Development

```text
SQLite
```

### Production

```text
PostgreSQL
```

## Additional services

```text
Redis:
Cache, queues, sessions and background jobs

Cloud storage:
Employee documents, profile photos and payslips

Email service:
Notifications and password resets

Git and GitHub:
Source-code management

Linux and Nginx:
Production hosting
```

---

# 3. Project Language and Design

## Main language

```text
Khmer: Primary language
English: Fallback language
```

Laravel configuration:

```env
APP_LOCALE=km
APP_FALLBACK_LOCALE=en
APP_TIMEZONE=Asia/Phnom_Penh
```

## Font

```text
Siemreap
```

## Date format

```text
DD/MM/YYYY
```

## Default timezone

```text
Asia/Phnom_Penh
```

## Supported currencies

```text
USD
KHR
```

## General UI style

* Clean business dashboard
* Responsive on desktop, tablet and mobile
* Simple Khmer labels
* Clear icons
* Light and dark appearance
* Large readable forms
* Searchable tables
* Status badges
* Confirmation before deletion
* Toast notifications after successful actions
* Loading indicators during processing
* Icons for edit, status and delete actions

---

# 4. User Roles

## Owner

The owner can access all modules and reports.

Permissions include:

* Manage company settings
* Manage branches
* Manage employees
* View salaries
* Process payroll
* View financial HR reports
* Manage system settings
* Manage roles and permissions

## HR Administrator

The HR administrator can:

* Manage employee profiles
* Manage attendance
* Manage leave
* Manage schedules
* Prepare payroll
* Manage recruitment
* Manage employee documents
* Manage performance reviews

## Manager

A manager can:

* View their department
* View their employees
* Approve leave
* Approve attendance corrections
* Approve overtime
* Create work schedules
* Assign tasks
* Review employee performance

## Accountant

An accountant can:

* View approved payroll
* Process salary payments
* Manage salary advances
* Manage deductions
* Manage expense reimbursements
* Generate payroll reports

## Employee

An employee can:

* View personal profile
* Check in and check out
* View attendance
* Request leave
* View leave balances
* View schedule
* Download payslips
* Submit expenses
* View tasks
* Complete training
* View announcements

---

# 5. Core System Architecture

```text
BizHR
│
├── Authentication
├── Company Settings
├── Branch Management
├── Department Management
├── Job Position Management
├── Employee Management
├── Attendance Management
├── Shift and Schedule Management
├── Leave Management
├── Payroll Management
├── Performance Management
├── Task Management
├── Recruitment
├── Onboarding
├── Training
├── Document Management
├── Asset Management
├── Expense Reimbursement
├── Announcements
├── Reports
├── Roles and Permissions
├── Audit Logs
└── System Settings
```

---

# 6. Main Sidebar Structure

```text
ផ្ទាំងគ្រប់គ្រង

រចនាសម្ព័ន្ធក្រុមហ៊ុន
├── ព័ត៌មានក្រុមហ៊ុន
├── សាខា
├── ផ្នែក
└── មុខតំណែង

បុគ្គលិក
├── បញ្ជីបុគ្គលិក
├── កិច្ចសន្យាការងារ
├── ឯកសារបុគ្គលិក
├── ទ្រព្យសម្បត្តិប្រគល់ឱ្យ
└── ប្រវត្តិការងារ

ពេលវេលាការងារ
├── វត្តមាន
├── កាលវិភាគការងារ
├── វេនការងារ
├── ថ្ងៃឈប់សម្រាក
└── ម៉ោងបន្ថែម

ប្រាក់បៀវត្ស
├── បញ្ជីប្រាក់ខែ
├── ប្រាក់បន្ថែម
├── ប្រាក់កាត់
├── ប្រាក់កម្ចី
├── ប្រាក់បុរេប្រទាន
└── សន្លឹកប្រាក់ខែ

ការអភិវឌ្ឍបុគ្គលិក
├── ការវាយតម្លៃការងារ
├── គោលដៅការងារ
├── កិច្ចការ
├── ការបណ្តុះបណ្តាល
└── វិញ្ញាបនបត្រ

ការជ្រើសរើស
├── តំណែងកំពុងជ្រើសរើស
├── បេក្ខជន
├── ការសម្ភាសន៍
├── ការផ្តល់ការងារ
└── ការណែនាំបុគ្គលិកថ្មី

របាយការណ៍
├── របាយការណ៍បុគ្គលិក
├── របាយការណ៍វត្តមាន
├── របាយការណ៍ឈប់សម្រាក
├── របាយការណ៍ប្រាក់ខែ
├── របាយការណ៍ម៉ោងបន្ថែម
└── របាយការណ៍ការអនុវត្តការងារ

ការកំណត់
├── អ្នកប្រើប្រាស់
├── តួនាទី និងសិទ្ធិ
├── ប្រភេទការងារ
├── ប្រភេទថ្ងៃឈប់សម្រាក
├── ច្បាប់វត្តមាន
├── ច្បាប់ប្រាក់ខែ
└── កំណត់ត្រាសកម្មភាព
```

---

# 7. Database Blueprint

## 7.1 Companies

Table:

```text
companies
```

Important columns:

```text
id
name
legal_name
email
phone
website
registration_number
tax_id
address
city
country
currency
timezone
date_format
created_at
updated_at
```

Relationship:

```text
Company has many branches
Company has many departments
Company has many employees
Company has many users
```

---

## 7.2 Branches

Table:

```text
branches
```

Important columns:

```text
id
company_id
name
code
email
phone
address
city
manager_name
is_head_office
is_active
created_at
updated_at
```

Relationship:

```text
Branch belongs to company
Branch has many departments
Branch has many positions
Branch has many employees
Branch has many schedules
```

Business rules:

* Branch code must be unique inside a company.
* Only one branch should be the head office.
* The head office cannot be deleted directly.
* A branch can be active or inactive.

---

## 7.3 Departments

Table:

```text
departments
```

Important columns:

```text
id
company_id
branch_id
name
code
manager_name
phone
email
description
is_active
created_at
updated_at
```

Relationship:

```text
Department belongs to company
Department belongs to branch
Department has many positions
Department has many employees
```

Business rules:

* Department code must be unique within its branch.
* A department must belong to an existing branch.
* Inactive departments cannot receive new employees.

---

## 7.4 Job Positions

Table:

```text
positions
```

Important columns:

```text
id
company_id
branch_id
department_id
title
code
description
minimum_salary
maximum_salary
is_manager_position
is_active
created_at
updated_at
```

Examples:

```text
Owner
General Manager
HR Manager
Accountant
Sales Manager
Salesperson
Cashier
Warehouse Staff
Driver
Marketing Officer
Customer Service
```

---

## 7.5 Employment Types

Table:

```text
employment_types
```

Examples:

```text
Full-time
Part-time
Probation
Temporary
Internship
Contract
Freelance
Daily worker
```

Important columns:

```text
id
company_id
name
description
is_active
created_at
updated_at
```

---

## 7.6 Employees

Table:

```text
employees
```

Important columns:

```text
id
company_id
branch_id
department_id
position_id
employment_type_id
user_id
employee_code
first_name
last_name
full_name_km
full_name_en
gender
date_of_birth
phone
email
national_id
passport_number
address
city
profile_photo
hire_date
probation_end_date
contract_start_date
contract_end_date
base_salary
salary_currency
payment_method
bank_name
bank_account_name
bank_account_number
emergency_contact_name
emergency_contact_phone
employment_status
is_active
created_at
updated_at
```

Employee statuses:

```text
Draft
Active
On probation
On leave
Suspended
Resigned
Terminated
Retired
```

---

## 7.7 Employee Documents

Table:

```text
employee_documents
```

Important columns:

```text
id
employee_id
document_type
document_number
file_path
issued_date
expiry_date
notes
created_at
updated_at
```

Document examples:

```text
National ID
Passport
Employment contract
Driver’s licence
Certificate
Diploma
Warning letter
Promotion letter
Salary adjustment letter
Resignation letter
```

---

## 7.8 Work Shifts

Table:

```text
work_shifts
```

Important columns:

```text
id
company_id
name
code
start_time
end_time
break_minutes
late_grace_minutes
early_leave_grace_minutes
is_night_shift
is_active
created_at
updated_at
```

Examples:

```text
Morning shift
Afternoon shift
Night shift
Office shift
Weekend shift
```

---

## 7.9 Employee Schedules

Table:

```text
employee_schedules
```

Important columns:

```text
id
employee_id
branch_id
work_shift_id
work_date
is_rest_day
notes
created_at
updated_at
```

Business rules:

* One employee cannot have conflicting shifts.
* Approved leave should override the work schedule.
* Rest days should not calculate absence.

---

## 7.10 Attendance

Table:

```text
attendances
```

Important columns:

```text
id
employee_id
branch_id
work_date
scheduled_start
scheduled_end
check_in_at
check_out_at
check_in_method
check_out_method
check_in_location
check_out_location
late_minutes
early_leave_minutes
worked_minutes
overtime_minutes
status
notes
approved_by
created_at
updated_at
```

Attendance statuses:

```text
Present
Late
Absent
On leave
Half day
Holiday
Rest day
Remote work
Business trip
```

Check-in methods:

```text
Web
Mobile
QR code
GPS
Fingerprint
Face recognition
Manual entry
```

---

## 7.11 Attendance Corrections

Table:

```text
attendance_corrections
```

Important columns:

```text
id
attendance_id
employee_id
requested_check_in
requested_check_out
reason
status
reviewed_by
reviewed_at
review_note
created_at
updated_at
```

Statuses:

```text
Pending
Approved
Rejected
```

---

## 7.12 Leave Types

Table:

```text
leave_types
```

Examples:

```text
Annual leave
Sick leave
Maternity leave
Paternity leave
Emergency leave
Unpaid leave
Personal leave
Marriage leave
Bereavement leave
```

Important columns:

```text
id
company_id
name
code
days_per_year
is_paid
requires_attachment
carry_forward_allowed
maximum_carry_forward_days
is_active
created_at
updated_at
```

---

## 7.13 Leave Balances

Table:

```text
leave_balances
```

Important columns:

```text
id
employee_id
leave_type_id
year
opening_balance
earned_days
used_days
adjustment_days
remaining_days
created_at
updated_at
```

---

## 7.14 Leave Requests

Table:

```text
leave_requests
```

Important columns:

```text
id
employee_id
leave_type_id
start_date
end_date
total_days
reason
attachment
status
manager_id
manager_reviewed_at
manager_note
hr_id
hr_reviewed_at
hr_note
created_at
updated_at
```

Workflow:

```text
Employee submits
↓
Manager reviews
↓
HR reviews
↓
Approved leave updates schedule and attendance
```

---

## 7.15 Holidays

Table:

```text
holidays
```

Important columns:

```text
id
company_id
name
holiday_date
is_paid
applies_to_all_branches
notes
created_at
updated_at
```

---

## 7.16 Payroll Periods

Table:

```text
payroll_periods
```

Important columns:

```text
id
company_id
name
start_date
end_date
payment_date
status
processed_by
approved_by
created_at
updated_at
```

Statuses:

```text
Draft
Processing
Awaiting approval
Approved
Paid
Closed
```

---

## 7.17 Payroll Items

Table:

```text
payroll_items
```

Important columns:

```text
id
payroll_period_id
employee_id
base_salary
worked_days
absent_days
paid_leave_days
unpaid_leave_days
overtime_hours
overtime_amount
allowance_amount
bonus_amount
commission_amount
deduction_amount
loan_deduction
advance_deduction
tax_amount
gross_salary
net_salary
payment_status
created_at
updated_at
```

Payroll formula:

```text
Base salary
+ Overtime
+ Allowances
+ Bonuses
+ Commission
- Absence deductions
- Unpaid leave
- Salary advances
- Loan repayments
- Tax
- Other deductions
= Net salary
```

---

## 7.18 Payroll Adjustments

Table:

```text
payroll_adjustments
```

Types:

```text
Allowance
Bonus
Commission
Deduction
Loan
Advance
Penalty
Reimbursement
```

Important columns:

```text
id
employee_id
payroll_period_id
type
name
amount
is_recurring
effective_date
end_date
notes
created_at
updated_at
```

---

## 7.19 Payslips

Table:

```text
payslips
```

Important columns:

```text
id
payroll_item_id
employee_id
file_path
generated_at
viewed_at
downloaded_at
created_at
updated_at
```

---

## 7.20 Performance Reviews

Table:

```text
performance_reviews
```

Important columns:

```text
id
employee_id
reviewer_id
review_period_start
review_period_end
attendance_score
quality_score
productivity_score
teamwork_score
communication_score
customer_service_score
overall_score
strengths
areas_for_improvement
manager_comment
employee_comment
status
created_at
updated_at
```

---

## 7.21 Employee Goals and KPIs

Tables:

```text
employee_goals
kpi_templates
employee_kpi_results
```

Possible KPIs:

```text
Sales amount
Customer satisfaction
Attendance rate
Task completion
Order accuracy
Delivery success
Response time
Error rate
Project completion
Production quantity
```

---

## 7.22 Tasks

Table:

```text
tasks
```

Important columns:

```text
id
company_id
assigned_by
assigned_to
title
description
priority
start_date
due_date
completed_at
status
attachment
created_at
updated_at
```

Statuses:

```text
Not started
In progress
Waiting
Completed
Cancelled
Overdue
```

---

## 7.23 Recruitment

Tables:

```text
job_vacancies
job_applicants
interviews
job_offers
```

Recruitment workflow:

```text
Create vacancy
↓
Receive application
↓
Review candidate
↓
Schedule interview
↓
Record interview result
↓
Send job offer
↓
Hire candidate
↓
Convert candidate into employee
```

---

## 7.24 Onboarding

Tables:

```text
onboarding_templates
onboarding_tasks
employee_onboarding
```

Checklist examples:

```text
Submit identification documents
Sign employment contract
Create employee account
Assign equipment
Complete company introduction
Complete product training
Meet manager
Set probation goals
```

---

## 7.25 Training

Tables:

```text
training_courses
training_lessons
training_enrollments
training_results
training_certificates
```

Training content:

```text
Video
PDF
Image
Text lesson
Quiz
Practical assignment
Certificate
```

---

## 7.26 Company Assets

Tables:

```text
assets
asset_assignments
asset_returns
```

Asset examples:

```text
Laptop
Phone
SIM card
Vehicle
Motorbike
Uniform
Access card
Tools
Barcode scanner
```

---

## 7.27 Expense Reimbursements

Table:

```text
expense_claims
```

Important columns:

```text
id
employee_id
expense_date
category
amount
currency
business_purpose
receipt_path
status
manager_id
accountant_id
paid_at
created_at
updated_at
```

---

## 7.28 Announcements

Table:

```text
announcements
```

Important columns:

```text
id
company_id
title
content
audience_type
branch_id
department_id
published_at
expires_at
created_by
created_at
updated_at
```

---

## 7.29 Audit Logs

Table:

```text
audit_logs
```

Important columns:

```text
id
user_id
action
module
record_type
record_id
old_values
new_values
ip_address
user_agent
created_at
```

Examples:

```text
Employee created
Salary changed
Attendance edited
Leave approved
Payroll approved
Branch deleted
User permission changed
```

---

# 8. Development Roadmap

## Phase 0 — Project Foundation

### Objectives

* Install Laravel development environment
* Create Laravel project
* Configure Git
* Configure SQLite
* Install Livewire and Flux
* Configure Khmer language
* Configure Siemreap font

### Tasks

```text
1. Install PHP, Composer, Node.js and Git
2. Create Laravel project
3. Select Livewire starter kit
4. Configure SQLite
5. Run migrations
6. Test registration and login
7. Configure APP_LOCALE=km
8. Add lang/km.json
9. Add Siemreap font
10. Configure sidebar layout
11. Run automated tests
```

### Completion criteria

```text
[ ] Registration works
[ ] Login works
[ ] Logout works
[ ] Dashboard opens
[ ] Khmer text displays correctly
[ ] Siemreap font works
[ ] SQLite connects successfully
```

---

## Phase 1 — Company Structure

### Modules

```text
Company profile
Branches
Departments
Job positions
Employment types
```

### Development order

- [ ] Company model and migration
- [ ] Company Settings Livewire page
- [ ] Branch model and migration
- [ ] Branch relationships
- [ ] Branch CRUD page
- [ ] Department model and migration
- [ ] Department relationships
- [ ] Department CRUD page
- [ ] Position model and migration
- [ ] Position CRUD page
- [ ] Employment type CRUD page

### Current project status

- [x] Laravel project created
- [x] Authentication working
- [x] Khmer language configured
- [x] Siemreap font configured
- [x] Company Profile page
- [x] Branch database
- [x] Branch CRUD page
- [x] Branch search
- [x] Branch edit icon
- [x] Branch status icon
- [x] Branch delete icon
- [x] Department database
- [x] Department CRUD page (tested and working)
- [x] Job positions model, migration, and CRUD page
- [x] Employment types model, migration, and CRUD page
- [x] Phase 1 complete ✅
- [x] Phase 2 complete ✅ (Roles & Permissions)
- [x] Phase 3 Part 1 complete ✅ (Employee CRUD)
- [ ] Phase 3 Part 2 in progress (Employee Profile & Documents)
- [ ] Phase 4 in progress (Attendance)

### Completion criteria

- [ ] Company information can be saved
- [ ] Multiple branches can be created
- [ ] Only one head office exists
- [ ] Departments belong to branches
- [ ] Positions belong to departments
- [ ] Inactive records cannot be selected for new employees


---

## Phase 2 — Roles and Permissions

### Recommended package

```text
Spatie Laravel Permission
```

### Tasks

```text
✅ 1. Install permission package
✅ 2. Publish migrations
✅ 3. Create default roles
✅ 4. Create permission list
✅ 5. Create role management page
✅ 6. Assign roles to users
✅ 7. Protect routes
✅ 8. Protect Livewire actions
⏳ 9. Hide unauthorized sidebar items
⏳ 10. Create permission tests
```

### Initial roles

```text
Owner
HR Administrator
Manager
Accountant
Employee
```

### Completion criteria

```text
✅ Employees cannot access owner reports
✅ Managers see only their teams
✅ Accountants can access payroll
✅ HR can manage employees
✅ Owner has complete access
```

---

## Phase 3 — Employee Management

### Tasks

```text
✅ 1. Create employees migration
✅ 2. Create Employee model
✅ 3. Add employee relationships
✅ 4. Build employee list
✅ 5. Add search and filters
✅ 6. Build create employee form
✅ 7. Build edit employee form
⏳ 8. Build employee profile page
✅ 9. Upload profile photo
⏳ 10. Upload employee documents
⏳ 11. Add emergency contact
⏳ 12. Add salary information
⏳ 13. Add contract information
⏳ 14. Add employment history
⏳ 15. Add deactivate and resignation actions
```

### Phase 3 Status
**Part 1 (Employee CRUD)**: ✅ COMPLETE - Basic employee management operational
**Part 2 (Employee Profile & Details)**: ⏳ IN PROGRESS - Advanced profile features pending

### Employee tabs

```text
Overview
Employment
Contact
Documents
Attendance
Leave
Payroll
Performance
Assets
Activity
```

### Completion criteria

```text
✅ Employee code is unique
✅ Employee belongs to branch and department
✅ Employee profile photo uploads safely
✅ Sensitive salary data is permission-protected
✅ Basic employee CRUD operational
⏳ Employee can be activated or deactivated
⏳ Full employee profile page with all tabs
⏳ Document management system working
```

---

## Phase 4 — Attendance

### Status: READY TO START 🚀

Phase 4 can begin immediately as Phase 3 Part 1 foundation is complete. All employee records and permissions are in place for attendance tracking.

### First version

```text
Manual check-in and check-out
Attendance records
Late calculation
Early leave calculation
Worked-hour calculation
Daily and monthly reports
```

### Later versions

```text
QR attendance
GPS attendance
Fingerprint integration
Face recognition integration
Mobile attendance
```

### Tasks

```text
1. Create shift tables ✅ (migration + WorkShift model)
2. Create schedule tables ✅ (migration + EmployeeSchedule model)
3. Create attendance table ✅ (migration + Attendance model)
4. Build check-in page (pending)
5. Build check-out action (pending)
6. Calculate lateness (pending)
7. Calculate early leave (pending)
8. Calculate worked hours (pending)
9. Calculate overtime (pending)
10. Build daily attendance report (pending)
11. Build monthly attendance report (pending)
12. Add correction request (pending)
13. Add manager approval (pending)

Notes: `WorkShift` Livewire component created and route registered at `/work-shifts`.
Notes: `Schedule` Livewire component created and route registered at `/schedules`.
```

### Completion criteria

```text
[ ] Employee cannot check in twice
[ ] Check-out requires a check-in
[ ] Late minutes calculate correctly
[ ] Approved leave does not create absence
[ ] Rest days do not create absence
```

---

## Phase 5 — Leave Management

### Tasks

```text
1. Create leave types
2. Create leave balance table
3. Create leave request table
4. Build employee request form
5. Add date-range calculation
6. Exclude holidays and rest days
7. Add manager approval
8. Add HR approval
9. Update leave balance
10. Update attendance and schedule
11. Build leave calendar
12. Build leave reports
```

### Completion criteria

```text
[ ] Employee cannot request more than available balance
[ ] Overlapping leave requests are blocked
[ ] Approved leave updates the balance
[ ] Rejected leave does not deduct balance
[ ] Attachments can be required for sick leave
```

---

## Phase 6 — Shift Scheduling

### Tasks

```text
1. Create shift templates
2. Create employee schedules
3. Build monthly schedule calendar
4. Assign shifts to employees
5. Copy schedules between weeks
6. Add rest days
7. Detect schedule conflicts
8. Add shift replacement
9. Add shift-swap requests
10. Add schedule notifications
```

---

## Phase 7 — Payroll

### Development order

```text
1. Payroll settings
2. Payroll periods
3. Salary structures
4. Attendance integration
5. Overtime calculation
6. Allowances
7. Bonuses
8. Commissions
9. Deductions
10. Advances
11. Loans
12. Tax configuration
13. Payroll preview
14. Approval workflow
15. Salary payment status
16. Payslip generation
17. Payroll reports
```

### Payroll security

```text
Salary information must be encrypted or strictly permission-protected.
Only owners, authorized HR staff and accountants can access payroll.
Every salary change must be recorded in the audit log.
```

### Completion criteria

```text
[ ] Payroll calculations are repeatable
[ ] Approved payroll cannot be edited without reopening
[ ] Payslips show correct amounts
[ ] Payroll totals match employee totals
[ ] All adjustments are traceable
```

---

## Phase 8 — Performance and Tasks

### Tasks

```text
1. Create KPI templates
2. Assign KPIs by position
3. Create employee goals
4. Record monthly results
5. Create performance reviews
6. Add manager comments
7. Add employee acknowledgment
8. Build employee ranking
9. Build task management
10. Add overdue task notifications
```

---

## Phase 9 — Recruitment and Onboarding

### Tasks

```text
1. Create job vacancies
2. Build public job application page
3. Store candidate profiles
4. Upload CVs
5. Schedule interviews
6. Add interview scorecards
7. Send offers
8. Convert candidate to employee
9. Create onboarding templates
10. Assign onboarding checklist
11. Add probation review reminders
```

---

## Phase 10 — Training and SOP

### Tasks

```text
1. Create training courses
2. Upload lessons
3. Add quizzes
4. Enroll employees
5. Track progress
6. Generate certificates
7. Create SOP categories
8. Upload company procedures
9. Add version history
10. Require employee acknowledgment
```

---

## Phase 11 — Assets and Expenses

### Asset tasks

```text
1. Create asset categories
2. Create asset records
3. Assign assets to employees
4. Record condition
5. Return assets
6. Record damage or loss
7. Add asset reports
```

### Expense tasks

```text
1. Create expense categories
2. Submit reimbursement request
3. Upload receipt
4. Manager approval
5. Accounting approval
6. Payment status
7. Expense reports
```

---

## Phase 12 — Dashboard and Reports

### Owner dashboard

```text
Total employees
Present today
Absent today
Late today
On leave today
New employees
Employees on probation
Contracts expiring
Monthly payroll
Overtime cost
Open positions
Pending approvals
```

### Reports

```text
Employee report
Attendance report
Late report
Absence report
Overtime report
Leave report
Payroll report
Salary expense by department
Salary expense by branch
Employee turnover report
Recruitment report
Training report
Performance report
Asset report
Expense report
```

### Export formats

```text
PDF
Excel
CSV
Print
```

---

## Phase 13 — Notifications

### Notification channels

```text
In-app
Email
Telegram, optional
SMS, optional
Push notification, later
```

### Events

```text
Leave request submitted
Leave approved or rejected
Attendance correction submitted
Overtime approved
Contract expiring
Probation ending
Document expiring
Payroll approved
Payslip available
Task overdue
Training assigned
```

---

## Phase 14 — Security and Audit

### Required security controls

```text
Authentication
Email verification
Password reset
Role-based permissions
Rate limiting
CSRF protection
Server-side validation
Secure file uploads
Audit logs
Session security
Two-factor authentication
Database backups
HTTPS
```

### Sensitive actions requiring audit logs

```text
Salary changes
Payroll approval
Employee deletion
Attendance modification
Leave balance adjustment
Role changes
Permission changes
Company settings changes
Document downloads
```

---

## Phase 15 — Automated Testing

### Test categories

```text
Authentication tests
Authorization tests
Company tests
Branch tests
Department tests
Employee tests
Attendance tests
Leave tests
Payroll calculation tests
File upload tests
Report tests
Security tests
```

### Important test examples

```text
A user without permission cannot view payroll.
A branch code cannot be duplicated inside one company.
A department must belong to the selected company.
An employee cannot check in twice.
An employee cannot request overlapping leave.
Approved payroll cannot be modified directly.
The head office cannot be deleted.
```

Run regularly:

```text
php artisan test
```

---

## Phase 16 — Production Deployment

### Production preparation

```text
1. Change database from SQLite to PostgreSQL
2. Configure production environment
3. Set APP_ENV=production
4. Set APP_DEBUG=false
5. Configure HTTPS
6. Configure email service
7. Configure Redis
8. Configure queue workers
9. Configure scheduler
10. Configure cloud file storage
11. Configure automatic backups
12. Configure server monitoring
13. Optimize Laravel
14. Run database migrations
15. Create owner account
16. Test critical workflows
```

### Laravel optimization

```text
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### Required server services

```text
Nginx
PHP-FPM
PostgreSQL
Redis
Supervisor
Cron
SSL certificate
Backup service
```

---

# 9. Recommended Development Method

Build one module at a time.

For every module, follow this exact order:

```text
1. Define the business rules
2. Design the database table
3. Create the migration
4. Create the model
5. Add model relationships
6. Run the migration
7. Create the Livewire page
8. Add validation
9. Add create action
10. Add list and search
11. Add edit action
12. Add status action
13. Add delete protection
14. Add permissions
15. Add automated tests
16. Test manually
17. Commit to Git
18. Move to the next module
```

Do not start several unfinished modules simultaneously.

---

# 10. Git Development Workflow

## Branches

```text
main
develop
feature/company
feature/branches
feature/departments
feature/positions
feature/employees
feature/attendance
feature/leave
feature/payroll
```

## Example commit messages

```text
feat: add company profile settings
feat: add branch management
feat: add department management
fix: correct company migration columns
fix: prevent head office deletion
test: add branch validation tests
style: apply Khmer Siemreap font
```

Commit after every stable feature.

---

# 11. Coding Standards

## Laravel rules

* Use models for database data.
* Use migrations for every database change.
* Never modify the production database manually.
* Use validation for every form.
* Use relationships instead of manual joins when appropriate.
* Use database transactions for payroll and approvals.
* Use policies and permissions for authorization.
* Use queues for emails, reports and large imports.
* Use soft deletion where historical records must be preserved.
* Never permanently delete payroll or attendance history.

## Naming conventions

```text
Database tables: plural snake_case
Models: singular PascalCase
Properties: snake_case when matching database fields
Routes: module.action
Livewire pages: pages::module.page
Permissions: module.action
```

Examples:

```text
branches.index
departments.index
employees.create
employees.update
payroll.approve
reports.export
```

---

# 12. Data Protection Rules

Never expose these fields to unauthorized employees:

```text
Salary
Bank information
National identification
Passport information
Disciplinary records
Medical leave attachments
Performance review notes
Payroll adjustments
Personal documents
```

Important records should use soft deletion:

```text
Employees
Attendance records
Leave requests
Payroll periods
Payroll items
Performance reviews
Expense claims
```

---

# 13. Mobile Application Roadmap

Do not build the mobile application until the Laravel web application and API are stable.

## Mobile technology

```text
Flutter
Dart
Laravel REST API
```

## Employee mobile features

```text
Login
Check in and check out
GPS attendance
View schedule
Request leave
View leave balance
View payslips
Submit expenses
View tasks
View announcements
Complete training
Update profile
```

## Manager mobile features

```text
View team attendance
Approve leave
Approve overtime
Approve attendance corrections
Assign tasks
Review performance
View team schedule
```

---

# 14. SaaS Roadmap

After the single-company version works, convert BizHR into SaaS.

## SaaS additions

```text
Multiple companies
Tenant isolation
Subscription plans
Trial periods
Online payments
Feature limits
Employee limits
Storage limits
Company onboarding
Custom branding
Custom domains
Usage reports
Super administrator portal
```

## Suggested plans

```text
Starter:
Employees, attendance and leave

Growth:
Payroll, schedules, expenses and reports

Professional:
Recruitment, training and performance

Enterprise:
Multiple branches, API, custom permissions and integrations
```

---

# 15. Immediate Next Development Steps

The project should continue in this order:

- [x] ~~Finish and test Department CRUD~~
- [x] ~~Create Job Position model, migration and CRUD page~~
- [x] ~~Create Employment Type model, migration and CRUD page~~
- [x] ~~Create Employee model and database~~
- [x] ~~Build Employee list and create form~~
- [x] ~~Finish and test Employee CRUD page~~
- [x] ~~Install roles and permissions~~
- [ ] Build Employee profile page with tabs (Overview, Employment, Contact, Documents, Attendance, Leave, Payroll, Performance, Assets, Activity)
- [ ] Build Employee document management (upload/download)
- [ ] Add emergency contact fields
- [ ] Add salary information fields
- [ ] Add contract information
- [ ] Add employment history tracking
- [ ] Build work shifts management
- [ ] Build employee schedules
- [ ] Build attendance check-in/check-out
- [ ] Build attendance reports
- [ ] Build leave management system
- [ ] Build payroll

---

# 16. Minimum Viable Product

The first usable BizHR release should contain:

```text
Authentication
Company profile
Branches
Departments
Positions
Employment types
Roles and permissions
Employees
Employee documents
Attendance
Work shifts
Schedules
Leave requests
Leave approvals
Basic payroll
Payslips
Basic reports
Audit logs
Backups
```

Do not include advanced recruitment, training, mobile apps or AI before the MVP is stable.

---

# 17. Definition of Done

A module is complete only when:

- [ ] Database migration is correct
- [ ] Model and relationships work
- [ ] Create works
- [ ] List works
- [ ] Search works
- [ ] Edit works
- [ ] Status works
- [ ] Delete protection works
- [ ] Validation messages are in Khmer
- [ ] Permissions are enforced
- [ ] Mobile layout works
- [ ] Dark mode works
- [ ] Automated tests pass
- [ ] No errors appear in Laravel logs
- [ ] Git commit is created

---

# 18. Final Product Vision

BizHR should become a complete business employee-management platform that connects:

```text
Company structure
Employees
Time and attendance
Leave
Schedules
Payroll
Performance
Recruitment
Training
Assets
Expenses
Reports
Security
```

The system should first become a stable internal web application. After the core business logic is tested, it can expand into:

```text
Employee mobile application
Manager mobile application
Multi-company SaaS platform
AI HR assistant
Khmer chatbot
Automatic report summaries
Attendance hardware integrations
Accounting and POS integrations
```
