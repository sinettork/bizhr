<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead>
            <tr>
                <th class="ps-3 pe-2 text-center" style="width:42px;">
                    <input class="form-check-input employee-select-all" type="checkbox" aria-label="Select all employees">
                </th>
                <th class="ps-0">Employee</th>
                <th>Organization</th>
                <th>Role</th>
                <th>Contact</th>
                <th>Status</th>
                <th class="text-end pe-3">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($employees as $employee)
                @php
                    $displayName = $employee->full_name_en ?: trim($employee->first_name.' '.$employee->last_name);
                    $initials = collect(preg_split('/\s+/', trim($displayName)) ?: [])
                        ->filter()
                        ->take(2)
                        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
                        ->implode('');
                    $canEdit = auth()->user()->can('employee.edit') || (auth()->user()->can('employee.edit-own') && $employee->user_id === auth()->id());
                @endphp
                <tr>
                    <td class="ps-3 pe-2 text-center">
                        <input class="form-check-input employee-row-select" type="checkbox" value="{{ $employee->id }}" aria-label="Select {{ $displayName }}">
                    </td>
                    <td class="ps-0" style="min-width:250px;">
                        <div class="d-flex align-items-center gap-2">
                            <a href="{{ route('employees.show', $employee) }}" class="flex-shrink-0 text-decoration-none" aria-label="View {{ $displayName }}">
                                @if($employee->profile_photo)
                                    <img src="{{ route('employees.photo', $employee) }}" alt="{{ $displayName }}" class="rounded-3 border bg-body-tertiary" loading="lazy" style="width:42px;height:42px;object-fit:cover;">
                                @else
                                    <span class="rounded-3 border bg-body-tertiary text-primary fw-semibold d-inline-flex align-items-center justify-content-center" style="width:42px;height:42px;font-size:.72rem;">{{ $initials ?: '—' }}</span>
                                @endif
                            </a>
                            <div class="min-w-0">
                                <a class="fw-semibold text-body text-decoration-none d-block text-truncate" href="{{ route('employees.show', $employee) }}">{{ $displayName }}</a>
                                <div class="small text-body-secondary text-truncate">
                                    {{ $employee->employee_code }}
                                    @if($employee->full_name_km)
                                        <span class="mx-1">·</span><span>{{ $employee->full_name_km }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>
                    <td style="min-width:180px;">
                        <div class="fw-medium text-dark text-truncate">{{ $employee->department?->name ?? 'No department' }}</div>
                        <div class="small text-body-secondary text-truncate">{{ $employee->branch?->name ?? 'No branch' }}</div>
                    </td>
                    <td style="min-width:160px;">
                        <div class="fw-medium text-dark text-truncate">{{ $employee->position?->title ?? 'No position' }}</div>
                        <div class="small text-body-secondary">{{ $employee->hire_date?->format('d M Y') ? 'Joined '.$employee->hire_date->format('d M Y') : 'Hire date not set' }}</div>
                    </td>
                    <td style="min-width:180px;">
                        <div class="text-truncate">{{ $employee->phone ?: '—' }}</div>
                        <div class="small text-body-secondary text-truncate">{{ $employee->email ?: 'No email' }}</div>
                    </td>
                    <td class="text-nowrap">
                        <span class="badge text-bg-{{ $employee->is_active ? 'success' : 'secondary' }}">{{ $employee->employment_status }}</span>
                    </td>
                    <td class="text-end pe-3 text-nowrap">
                        <a class="btn btn-action-link btn-sm" href="{{ route('employees.show', $employee) }}">
                            <i class="fa-solid fa-user"></i><span>View</span>
                        </a>
                        <div class="dropdown d-inline-block">
                            <button class="btn btn-action-link btn-sm px-1" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="More actions for {{ $displayName }}">
                                <i class="fa-solid fa-ellipsis-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                <li><a class="dropdown-item" href="{{ route('employees.show', $employee) }}"><i class="fa-solid fa-address-card me-2"></i>Employee profile</a></li>
                                <li><a class="dropdown-item" href="{{ route('employees.id-card', $employee) }}"><i class="fa-solid fa-id-card me-2"></i>ID card</a></li>
                                @if($canEdit)
                                    <li><a class="dropdown-item" href="{{ route('employees.edit', $employee) }}"><i class="fa-solid fa-pen me-2"></i>Edit employee</a></li>
                                @endif
                            </ul>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="py-5 text-center text-body-secondary">
                        <i class="fa-solid fa-users fa-xl d-block mb-3 text-primary"></i>
                        No employees found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
<x-pagination-footer :paginator="$employees" />
