@props(['course' => null])
<div class="row g-3">
    <div class="col-md-8"><label class="form-label">Course title</label><input class="form-control" name="title" value="{{ old('title', $course?->title) }}" required></div>
    <div class="col-md-4"><label class="form-label">Duration (minutes)</label><input class="form-control" type="number" min="0" max="100000" name="duration_minutes" value="{{ old('duration_minutes', $course?->duration_minutes ?? 60) }}" required></div>
    <div class="col-12"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="3">{{ old('description', $course?->description) }}</textarea></div>
    <div class="col-md-6"><label class="form-check"><input class="form-check-input" type="checkbox" name="is_mandatory" value="1" @checked(old('is_mandatory', $course?->is_mandatory))><span class="form-check-label">Mandatory training</span></label></div>
    @if($course)<div class="col-md-6"><label class="form-check"><input class="form-check-input" type="checkbox" name="is_active" value="1" @checked(old('is_active', $course->is_active))><span class="form-check-label">Active course</span></label></div>@endif
</div>
