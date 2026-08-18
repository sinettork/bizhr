<?php

return [
    'title' => 'Data exports',
    'subtitle' => 'Track report generation and securely download completed files.',
    'type' => 'Data type',
    'rows' => 'Rows',
    'created_at' => 'Requested',
    'empty' => 'No exports have been requested yet.',
    'empty_hint' => 'Choose Export from Employees, Attendance, or Payroll.',
    'queued' => 'The export has been queued. You can download it when processing finishes.',
    'already_queued' => 'An identical export is already queued.',
    'company_required' => 'Create the company profile before exporting data.',
    'expired' => 'This export has expired.',
    'failure_hint' => 'The file could not be generated. Request another export or contact an administrator.',
    'types' => ['employees' => 'Employees', 'attendance' => 'Attendance', 'payroll' => 'Payroll'],
    'statuses' => ['queued' => 'Queued', 'processing' => 'Processing', 'completed' => 'Completed', 'failed' => 'Failed'],
];
