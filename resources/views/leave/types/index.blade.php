<x-layouts::app title="Leave types">
    <x-workspace-command-bar title="Leave types" icon="fa-calendar-xmark" context="Leave management"><x-slot:filters><form class="reference-filter-form" method="GET" hx-get="{{ request()->url() }}" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="true"><div class="input-group reference-search"><span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span><input class="form-control" name="search" value="{{ request('search') }}" placeholder="Search name or code" hx-get="{{ request()->url() }}" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="false" hx-trigger="keyup changed delay:500ms"></div><select class="form-select reference-status" name="status" hx-get="{{ request()->url() }}" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="true" hx-trigger="change"><option value="">All statuses</option><option value="1" @selected(request('status') === '1')>Active</option><option value="0" @selected(request('status') === '0')>Inactive</option></select><button class="btn btn-primary">Search</button></form></x-slot:filters><x-slot:actions><x-list-actions :add-modal="auth()->user()->can('leave.manage') ? 'createLeaveType' : null" add-label="Add leave type" /></x-slot:actions></x-workspace-command-bar>

    @if (session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
    @if ($errors->any())<div class="alert alert-danger"><ul class="mb-0 ps-3">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <div class="reference-list" data-list-container>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th class="ps-3">Leave type</th><th>Entitlement</th><th>Policy</th><th>Usage</th><th>Status</th><th class="text-end pe-3">Actions</th></tr></thead>
                <tbody>
                    @forelse ($types as $type)
                        <tr>
                            <td class="ps-3"><div class="fw-semibold">{{ $type->name }}</div><small class="text-body-secondary">{{ $type->code }}</small></td>
                            <td>{{ number_format((float) $type->days_per_year, 1) }} days / year<br><small class="text-body-secondary">{{ $type->is_paid ? 'Paid leave' : 'Unpaid leave' }}</small></td>
                            <td><small>{{ $type->carry_forward_allowed ? 'Carry forward: up to '.number_format((float) $type->maximum_carry_forward_days, 1).' days' : 'No carry forward' }}</small><br>@if ($type->requires_attachment)<span class="badge text-bg-info">Document required</span>@endif</td>
                            <td>{{ $type->requests_count }} requests<br><small class="text-body-secondary">{{ $type->balances_count }} balances</small></td>
                            <td><span class="badge text-bg-{{ $type->is_active ? 'success' : 'secondary' }}">{{ $type->is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td class="text-end pe-3">
                                <button class="btn btn-action-link btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#editLeaveType{{ $type->id }}"><i class="fa-solid fa-pen"></i><span>Edit</span></button>
                                @if ($type->requests_count === 0 && $type->balances_count === 0)<form class="d-inline" method="POST" action="{{ route('leave.types.destroy', $type) }}" data-confirm="Delete this leave type?">@csrf @method('DELETE')<button class="btn btn-action-link btn-sm text-danger" type="submit"><i class="fa-solid fa-trash"></i><span>Delete</span></button></form>@endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-body-secondary py-5">No leave types found. Create a policy before employees submit requests.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-pagination-footer :paginator="$types" />
    </div>

    <div class="modal fade" id="createLeaveType" tabindex="-1" aria-labelledby="createLeaveTypeTitle" aria-hidden="true"><div class="modal-dialog modal-lg"><form class="modal-content" method="POST" action="{{ route('leave.types.store') }}">@csrf
        <div class="modal-header"><h2 class="modal-title fs-5" id="createLeaveTypeTitle">Add leave type</h2><button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button></div>
        <div class="modal-body">@include('leave.types._form', ['type' => null])</div>
        <x-form-save-actions :allow-save-new="true" save-label="Save & close" new-label="Save & new" />
    </form></div></div>

    @foreach ($types as $type)
        <div class="modal fade" id="editLeaveType{{ $type->id }}" tabindex="-1" aria-labelledby="editLeaveTypeTitle{{ $type->id }}" aria-hidden="true"><div class="modal-dialog modal-lg"><form class="modal-content" method="POST" action="{{ route('leave.types.update', $type) }}">@csrf @method('PUT')
            <div class="modal-header"><h2 class="modal-title fs-5" id="editLeaveTypeTitle{{ $type->id }}">Edit leave type</h2><button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <div class="modal-body">@include('leave.types._form', ['type' => $type])</div>
            <div class="modal-footer"><button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary" type="submit"><i class="fa-solid fa-floppy-disk me-1"></i>Save changes</button></div>
        </form></div></div>
    @endforeach

</x-layouts::app>