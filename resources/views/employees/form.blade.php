<x-layouts::app :title="$title">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div><div class="small text-uppercase text-body-secondary fw-semibold">People</div><h1 class="h5 mb-0">{{ $title }}</h1></div>
        <a class="btn btn-light" href="{{ route('employees.index') }}"><i class="fa-solid fa-arrow-left me-1"></i>Back to employees</a>
    </div>

    <form method="POST" enctype="multipart/form-data" action="{{ $employee->exists ? route('employees.update', $employee) : route('employees.store') }}">
        @csrf
        @if($employee->exists) @method('PUT') @endif
        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card mb-3"><div class="card-header">Work assignment</div><div class="card-body row g-3">
                    <div class="col-md-4"><label class="form-label" for="employee_code">Employee code</label><input class="form-control @error('employee_code') is-invalid @enderror" id="employee_code" name="employee_code" value="{{ old('employee_code', $employee->employee_code) }}" maxlength="50">@error('employee_code')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
                    <div class="col-md-4"><label class="form-label" for="branch_id">Branch <span class="text-danger">*</span></label><select class="form-select @error('branch_id') is-invalid @enderror" id="branch_id" name="branch_id" required>@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected(old('branch_id', $employee->branch_id) == $branch->id)>{{ $branch->name }}</option>@endforeach</select>@error('branch_id')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-md-4"><label class="form-label" for="department_id">Department <span class="text-danger">*</span></label><select class="form-select @error('department_id') is-invalid @enderror" id="department_id" name="department_id" required>@foreach($departments as $department)<option value="{{ $department->id }}" @selected(old('department_id', $employee->department_id) == $department->id) data-branch="{{ $department->branch_id }}">{{ $department->name }}</option>@endforeach</select>@error('department_id')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-md-4"><label class="form-label" for="position_id">Position</label><select class="form-select" id="position_id" name="position_id"><option value="">No position</option>@foreach($positions as $position)<option value="{{ $position->id }}" @selected(old('position_id', $employee->position_id) == $position->id) data-branch="{{ $position->branch_id }}" data-department="{{ $position->department_id }}">{{ $position->title }}</option>@endforeach</select></div>
                    <div class="col-md-4"><label class="form-label" for="employment_type_id">Employment type</label><select class="form-select" id="employment_type_id" name="employment_type_id"><option value="">Not set</option>@foreach($employmentTypes as $type)<option value="{{ $type->id }}" @selected(old('employment_type_id', $employee->employment_type_id) == $type->id)>{{ $type->name }}</option>@endforeach</select></div>
                    <div class="col-md-4"><label class="form-label" for="employment_status">Status</label><select class="form-select" id="employment_status" name="employment_status">@foreach($statuses as $option)<option value="{{ $option }}" @selected(old('employment_status', $employee->employment_status) === $option)>{{ $option }}</option>@endforeach</select></div>
                </div></div>
                <div class="card mb-3"><div class="card-header">Personal information</div><div class="card-body row g-3">
                    <div class="col-md-6"><label class="form-label" for="first_name">First name</label><input class="form-control @error('first_name') is-invalid @enderror" id="first_name" name="first_name" value="{{ old('first_name', $employee->first_name) }}" required>@error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-md-6"><label class="form-label" for="last_name">Last name</label><input class="form-control @error('last_name') is-invalid @enderror" id="last_name" name="last_name" value="{{ old('last_name', $employee->last_name) }}" required>@error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-md-6"><label class="form-label" for="full_name_en">Full name (English)</label><input class="form-control" id="full_name_en" name="full_name_en" value="{{ old('full_name_en', $employee->full_name_en) }}"></div>
                    <div class="col-md-6"><label class="form-label" for="full_name_km">Full name (Khmer)</label><input class="form-control" id="full_name_km" name="full_name_km" value="{{ old('full_name_km', $employee->full_name_km) }}"></div>
                    <div class="col-md-4"><label class="form-label" for="gender">Gender</label><select class="form-select" id="gender" name="gender"><option value="">Not set</option>@foreach(['male' => 'Male', 'female' => 'Female', 'other' => 'Other'] as $value => $label)<option value="{{ $value }}" @selected(old('gender', $employee->gender) === $value)>{{ $label }}</option>@endforeach</select></div>
                    <div class="col-md-4"><label class="form-label" for="date_of_birth">Date of birth</label><input class="form-control" type="date" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth', $employee->date_of_birth?->format('Y-m-d')) }}"></div>
                    <div class="col-12"><label class="form-label">Profile photo</label><div class="row g-3 align-items-center"><div class="col-sm-auto"><label class="image-upload-zone employee-photo-zone" for="profile_photo">@if($employee->profile_photo)<img id="employee-photo-preview" src="{{ route('employees.photo',$employee) }}" alt="{{ $employee->getFullName() }}">@else<img id="employee-photo-preview" src="" alt="" hidden><span class="image-upload-placeholder"><i class="fa-solid fa-camera"></i><strong>Choose employee photo</strong><small>Square portrait · max 2 MB</small></span>@endif</label><input class="visually-hidden" type="file" id="profile_photo" name="profile_photo" accept="image/jpeg,image/png,image/webp" data-image-preview="employee-photo-preview"></div><div class="col"><p class="small text-body-secondary mb-2">Used on the employee profile, directory and printed ID card.</p>@if($employee->profile_photo)<label class="form-check"><input class="form-check-input" type="checkbox" name="remove_profile_photo" value="1"><span class="form-check-label">Remove current photo</span></label>@endif</div></div></div>
                    <div class="col-md-6"><label class="form-label" for="national_id">National ID</label><input class="form-control" id="national_id" name="national_id" value="{{ old('national_id', $employee->national_id) }}"></div>
                    <div class="col-md-6"><label class="form-label" for="passport_number">Passport number</label><input class="form-control" id="passport_number" name="passport_number" value="{{ old('passport_number', $employee->passport_number) }}"></div>
                </div></div>
                <div class="card"><div class="card-header">Contact and emergency contact</div><div class="card-body row g-3">
                    <div class="col-md-6"><label class="form-label" for="email">Email</label><input class="form-control" type="email" id="email" name="email" value="{{ old('email', $employee->email) }}"></div><div class="col-md-6"><label class="form-label" for="phone">Phone</label><input class="form-control" id="phone" name="phone" value="{{ old('phone', $employee->phone) }}"></div>
                    <div class="col-md-8"><label class="form-label" for="address">Address</label><input class="form-control" id="address" name="address" value="{{ old('address', $employee->address) }}"></div><div class="col-md-4"><label class="form-label" for="city">City</label><input class="form-control" id="city" name="city" value="{{ old('city', $employee->city) }}"></div>
                    <div class="col-md-6"><label class="form-label" for="emergency_contact_name">Emergency contact</label><input class="form-control" id="emergency_contact_name" name="emergency_contact_name" value="{{ old('emergency_contact_name', $employee->emergency_contact_name) }}"></div><div class="col-md-6"><label class="form-label" for="emergency_contact_phone">Emergency phone</label><input class="form-control" id="emergency_contact_phone" name="emergency_contact_phone" value="{{ old('emergency_contact_phone', $employee->emergency_contact_phone) }}"></div>
                </div></div>
            </div>
            <div class="col-lg-4">
                <div class="card mb-3"><div class="card-header">Employment dates</div><div class="card-body vstack gap-3">
                    @foreach(['hire_date' => 'Hire date', 'probation_end_date' => 'Probation end', 'contract_start_date' => 'Contract start', 'contract_end_date' => 'Contract end', 'id_card_expiry_date' => 'ID card expiry date'] as $field => $label)<div><label class="form-label" for="{{ $field }}">{{ $label }}</label><input class="form-control @error($field) is-invalid @enderror" type="date" id="{{ $field }}" name="{{ $field }}" value="{{ old($field, $employee->{$field}?->format('Y-m-d')) }}" @if($field === 'hire_date') required @endif>@error($field)<div class="invalid-feedback">{{ $message }}</div>@enderror</div>@endforeach
                </div></div>
                <div class="card mb-3"><div class="card-header">Payroll and payment</div><div class="card-body vstack gap-3">
                    <div><label class="form-label" for="base_salary">Base salary</label><input class="form-control" type="number" min="0" step="0.01" id="base_salary" name="base_salary" value="{{ old('base_salary', $employee->base_salary) }}"></div><div><label class="form-label" for="salary_currency">Currency</label><select class="form-select" id="salary_currency" name="salary_currency"><option value="USD" @selected(old('salary_currency', $employee->salary_currency) === 'USD')>USD</option><option value="KHR" @selected(old('salary_currency', $employee->salary_currency) === 'KHR')>KHR</option></select></div>
                    <div><label class="form-label" for="payment_method">Payment method</label><input class="form-control" id="payment_method" name="payment_method" value="{{ old('payment_method', $employee->payment_method) }}"></div><div><label class="form-label" for="bank_name">Bank name</label><input class="form-control" id="bank_name" name="bank_name" value="{{ old('bank_name', $employee->bank_name) }}"></div><div><label class="form-label" for="bank_account_name">Account name</label><input class="form-control" id="bank_account_name" name="bank_account_name" value="{{ old('bank_account_name', $employee->bank_account_name) }}"></div><div><label class="form-label" for="bank_account_number">Account number</label><input class="form-control" id="bank_account_number" name="bank_account_number" value="{{ old('bank_account_number', $employee->bank_account_number) }}"></div>
                </div></div>
                <div class="card"><div class="card-body"><div class="form-check"><input class="form-check-input" type="checkbox" value="1" id="is_active" name="is_active" @checked(old('is_active', $employee->is_active))><label class="form-check-label" for="is_active">Employee account is active</label></div></div></div>
                @if($employee->exists)<div class="card mt-3"><div class="card-header">Change control</div><div class="card-body vstack gap-3"><div><label class="form-label" for="effective_date">Effective date</label><input class="form-control" type="date" id="effective_date" name="effective_date" value="{{ old('effective_date', today()->format('Y-m-d')) }}"></div><div><label class="form-label" for="change_reason">Reason for employment change</label><textarea class="form-control" id="change_reason" name="change_reason" maxlength="1000" placeholder="Required when assignment, salary or status changes">{{ old('change_reason') }}</textarea><div class="form-text">Creates a permanent employment-history entry when controlled fields change.</div></div></div></div>@endif
            </div>
        </div>
        <div class="d-flex flex-wrap justify-content-end gap-2 mt-3">
            <a class="btn btn-light" href="{{ route('employees.index') }}">Close</a>
            @unless($employee->exists)<button class="btn btn-outline-primary" type="submit" name="save_action" value="new"><i class="fa-solid fa-plus me-1"></i>Save & new</button>@endunless
            <button class="btn btn-primary" type="submit" name="save_action" value="close"><i class="fa-solid fa-floppy-disk me-1"></i>{{ $employee->exists ? 'Save changes' : 'Save & close' }}</button>
        </div>
    </form>
</x-layouts::app>

@push('scripts')
<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    document.addEventListener('DOMContentLoaded', () => {
        const branch = document.getElementById('branch_id'), department = document.getElementById('department_id'), position = document.getElementById('position_id');
        const filter = () => {
            [...department.options].forEach(option => { option.hidden = option.dataset.branch && option.dataset.branch !== branch.value; });
            if (department.selectedOptions[0]?.hidden) department.value = '';
            [...position.options].forEach(option => { option.hidden = option.dataset.branch && (option.dataset.branch !== branch.value || option.dataset.department !== department.value); });
            if (position.selectedOptions[0]?.hidden) position.value = '';
        };
        branch.addEventListener('change', filter); department.addEventListener('change', filter); filter();
    });
</script>
@endpush
