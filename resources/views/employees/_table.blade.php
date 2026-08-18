<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead>
            <tr>
                <th class="ps-3 pe-2 text-center" style="width: 42px;">
                    <input class="form-check-input employee-select-all" type="checkbox" aria-label="Select all employees">
                </th>
                <th class="ps-0">Code</th>
                <th>Employee</th>
                <th>Department</th>
                <th>Contact</th>
                <th>Status</th>
                <th class="text-end pe-3">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($employees as $employee)
                <tr>
                    <td class="ps-3 pe-2 text-center">
                        <input class="form-check-input employee-row-select" type="checkbox" value="{{ $employee->id }}" aria-label="Select {{ $employee->full_name_en ?: trim($employee->first_name.' '.$employee->last_name) }}">
                    </td>
                    <td class="ps-0 fw-medium">{{ $employee->employee_code }}</td>
                    <td>
                        <a class="fw-semibold text-body" href="{{ route('employees.show', $employee) }}">{{ $employee->full_name_en ?: trim($employee->first_name.' '.$employee->last_name) }}</a>
                        @if($employee->position)<div class="small text-body-secondary">{{ $employee->position->title }}</div>@endif
                    </td>
                    <td>{{ $employee->department?->name ?? '—' }}<div class="small text-body-secondary">{{ $employee->branch?->name }}</div></td>
                    <td>{{ $employee->email ?: '—' }}<div class="small text-body-secondary">{{ $employee->phone }}</div></td>
                    <td><span class="badge text-bg-{{ $employee->is_active ? 'success' : 'secondary' }}">{{ $employee->employment_status }}</span></td>
                    <td class="text-end pe-3 text-nowrap">
                        <a class="btn btn-sm btn-link text-primary" href="{{ route('employees.show', $employee) }}" title="View employee"><i class="fa-solid fa-eye"></i><span class="visually-hidden">View</span></a>
                        @if(auth()->user()->can('employee.edit') || (auth()->user()->can('employee.edit-own') && $employee->user_id === auth()->id()))
                            <a class="btn btn-sm btn-link text-primary" href="{{ route('employees.edit', $employee) }}" title="Edit employee"><i class="fa-solid fa-pen"></i><span class="visually-hidden">Edit</span></a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="py-5 text-center text-body-secondary"><i class="fa-solid fa-users fa-xl d-block mb-3 text-primary"></i>No employees found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<x-pagination-footer :paginator="$employees" />
