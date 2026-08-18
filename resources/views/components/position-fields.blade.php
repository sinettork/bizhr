@props(['branches', 'departments', 'position' => null])
<div class="row g-3">
    <div class="col-md-6"><label class="form-label">Title</label><input class="form-control" name="title" value="{{ old('title', $position?->title) }}" required></div>
    <div class="col-md-6"><label class="form-label">Code</label><input class="form-control" name="code" value="{{ old('code', $position?->code) }}" placeholder="auto-generate"></div>
    <div class="col-md-6"><label class="form-label">Branch</label><select class="form-select" name="branch_id"><option value="">All branches</option>@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected(old('branch_id', $position?->branch_id) == $branch->id)>{{ $branch->name }}</option>@endforeach</select></div>
    <div class="col-md-6"><label class="form-label">Department</label><select class="form-select" name="department_id"><option value="">All departments</option>@foreach($departments as $department)<option value="{{ $department->id }}" @selected(old('department_id', $position?->department_id) == $department->id)>{{ $department->name }}</option>@endforeach</select></div>
    <div class="col-md-4"><label class="form-label">Minimum salary</label><input class="form-control" type="number" step="0.01" name="minimum_salary" value="{{ old('minimum_salary', $position?->minimum_salary) }}"></div>
    <div class="col-md-4"><label class="form-label">Maximum salary</label><input class="form-control" type="number" step="0.01" name="maximum_salary" value="{{ old('maximum_salary', $position?->maximum_salary) }}"></div>
    <div class="col-md-4"><label class="form-label">Sort order</label><input class="form-control" type="number" name="sort_order" value="{{ old('sort_order', $position?->sort_order ?? 0) }}"></div>
    <div class="col-12"><label class="form-label">Description</label><textarea class="form-control" name="description">{{ old('description', $position?->description) }}</textarea></div>
</div>
