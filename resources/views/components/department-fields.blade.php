@props(['branches', 'department' => null])
<label class="form-label">Name</label><input class="form-control mb-3" name="name" value="{{ old('name', $department?->name) }}" required>
<label class="form-label">Code</label><input class="form-control mb-3" name="code" value="{{ old('code', $department?->code) }}" placeholder="auto-generate">
<label class="form-label">Branch</label><select class="form-select mb-3" name="branch_id"><option value="">Company-wide</option>@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected(old('branch_id', $department?->branch_id) == $branch->id)>{{ $branch->name }}</option>@endforeach</select>
<label class="form-label">Manager</label><input class="form-control mb-3" name="manager_name" value="{{ old('manager_name', $department?->manager_name) }}">
<label class="form-label">Phone</label><input class="form-control mb-3" name="phone" value="{{ old('phone', $department?->phone) }}">
<label class="form-label">Email</label><input class="form-control mb-3" type="email" name="email" value="{{ old('email', $department?->email) }}">
<label class="form-label">Description</label><textarea class="form-control mb-3" name="description">{{ old('description', $department?->description) }}</textarea>
