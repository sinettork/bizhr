@props(['branches', 'department' => null])

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="dept-name-{{ $department?->id ?? 'new' }}">
            Department name (EN) <span class="text-danger">*</span>
        </label>
        <div class="input-group">
            <span class="input-group-text"><i class="fa-solid fa-sitemap text-muted"></i></span>
            <input 
                id="dept-name-{{ $department?->id ?? 'new' }}"
                class="form-control" 
                name="name" 
                value="{{ old('name', $department?->name) }}" 
                placeholder="e.g. Human Resources, Engineering" 
                required
            >
        </div>
    </div>

    <div class="col-md-6">
        <label class="form-label" for="dept-local-name-{{ $department?->id ?? 'new' }}">
            ឈ្មោះភាសាខ្មែរ (Khmer Name)
        </label>
        <div class="input-group">
            <span class="input-group-text"><i class="fa-solid fa-language text-muted"></i></span>
            <input 
                id="dept-local-name-{{ $department?->id ?? 'new' }}"
                class="form-control" 
                name="local_name" 
                value="{{ old('local_name', $department?->local_name) }}" 
                placeholder="ឧ. នាយកដ្ឋានធនធានមនុស្ស"
            >
        </div>
    </div>

    <div class="col-md-6">
        <label class="form-label" for="dept-code-{{ $department?->id ?? 'new' }}">Department code</label>
        <div class="input-group">
            <span class="input-group-text"><i class="fa-solid fa-code text-muted"></i></span>
            <input 
                id="dept-code-{{ $department?->id ?? 'new' }}"
                class="form-control" 
                name="code" 
                value="{{ old('code', $department?->code) }}" 
                placeholder="auto-generate (e.g. HR, IT)"
            >
        </div>
    </div>

    <div class="col-md-6">
        <label class="form-label" for="dept-branch-{{ $department?->id ?? 'new' }}">Branch allocation</label>
        <div class="input-group">
            <span class="input-group-text"><i class="fa-solid fa-building text-muted"></i></span>
            <select id="dept-branch-{{ $department?->id ?? 'new' }}" class="form-select" name="branch_id">
                <option value="">Company-wide</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" @selected(old('branch_id', $department?->branch_id) == $branch->id)>
                        {{ $branch->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="col-md-6">
        <label class="form-label" for="dept-manager-{{ $department?->id ?? 'new' }}">Department manager</label>
        <div class="input-group">
            <span class="input-group-text"><i class="fa-solid fa-user-tie text-muted"></i></span>
            <input 
                id="dept-manager-{{ $department?->id ?? 'new' }}"
                class="form-control" 
                name="manager_name" 
                value="{{ old('manager_name', $department?->manager_name) }}" 
                placeholder="e.g. Sok Sovan"
            >
        </div>
    </div>

    <div class="col-md-6">
        <label class="form-label" for="dept-capacity-{{ $department?->id ?? 'new' }}">
            Headcount quota / capacity
        </label>
        <div class="input-group">
            <span class="input-group-text"><i class="fa-solid fa-users-viewfinder text-muted"></i></span>
            <input 
                id="dept-capacity-{{ $department?->id ?? 'new' }}"
                class="form-control" 
                type="number"
                min="1"
                max="100000"
                name="headcount_capacity" 
                value="{{ old('headcount_capacity', $department?->headcount_capacity) }}" 
                placeholder="Approved staff quota (e.g. 25)"
            >
        </div>
    </div>

    <div class="col-md-6">
        <label class="form-label" for="dept-phone-{{ $department?->id ?? 'new' }}">Phone & Extension</label>
        <div class="input-group">
            <span class="input-group-text"><i class="fa-solid fa-phone text-muted"></i></span>
            <input 
                id="dept-phone-{{ $department?->id ?? 'new' }}"
                class="form-control" 
                type="tel" 
                name="phone" 
                value="{{ old('phone', $department?->phone) }}" 
                placeholder="Primary phone"
            >
            <input 
                class="form-control" 
                style="max-width: 110px;"
                name="phone_extension" 
                value="{{ old('phone_extension', $department?->phone_extension) }}" 
                placeholder="Ext (e.g. 102)"
                title="Internal phone extension"
            >
        </div>
    </div>

    <div class="col-md-6">
        <label class="form-label" for="dept-telegram-{{ $department?->id ?? 'new' }}">Telegram contact / group</label>
        <div class="input-group">
            <span class="input-group-text text-info"><i class="fa-brands fa-telegram"></i></span>
            <input 
                id="dept-telegram-{{ $department?->id ?? 'new' }}"
                class="form-control" 
                name="telegram_username" 
                value="{{ old('telegram_username', $department?->telegram_username) }}" 
                placeholder="Username or t.me link (e.g. hr_dept)"
            >
        </div>
    </div>

    <div class="col-12">
        <label class="form-label" for="dept-email-{{ $department?->id ?? 'new' }}">Official email address</label>
        <div class="input-group">
            <span class="input-group-text"><i class="fa-solid fa-envelope text-muted"></i></span>
            <input 
                id="dept-email-{{ $department?->id ?? 'new' }}"
                class="form-control" 
                type="email" 
                name="email" 
                value="{{ old('email', $department?->email) }}" 
                placeholder="e.g. hr@company.com"
            >
        </div>
    </div>

    <div class="col-12">
        <label class="form-label" for="dept-desc-{{ $department?->id ?? 'new' }}">Description & mandate</label>
        <textarea 
            id="dept-desc-{{ $department?->id ?? 'new' }}"
            class="form-control" 
            name="description" 
            rows="3" 
            placeholder="Brief overview of department's operational mandate and core duties..."
        >{{ old('description', $department?->description) }}</textarea>
    </div>
</div>

