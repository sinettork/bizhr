<x-layouts::app title="Assets & Inventory">
    <x-workspace-command-bar title="Company Assets & Inventory" icon="fa-boxes-stacked" context="Operations & IT">
        <x-slot:filters>
            <form class="reference-filter-form" method="GET" hx-get="{{ request()->url() }}" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="true">
                <div class="input-group reference-search">
                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input class="form-control" name="search" value="{{ request('search') }}" placeholder="Search code or asset name" hx-get="{{ request()->url() }}" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="false" hx-trigger="keyup changed delay:500ms">
                </div>
                <select class="form-select reference-status" name="status" hx-get="{{ request()->url() }}" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="true" hx-trigger="change">
                    <option value="">All statuses</option>
                    @foreach(['available','assigned','lost','retired'] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ str($status)->title() }}</option>
                    @endforeach
                </select>
                <button class="btn btn-primary reference-search-button">Filter</button>
            </form>
        </x-slot:filters>
        <x-slot:actions>
            <x-list-actions :add-modal="auth()->user()->can('asset.manage') ? 'createAsset' : null" add-label="Register new asset" />
        </x-slot:actions>
    </x-workspace-command-bar>

    @if(session('status'))
        <div class="alert alert-success d-flex align-items-center gap-2"><i class="fa-solid fa-circle-check"></i><span>{{ session('status') }}</span></div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger d-flex align-items-center gap-2"><i class="fa-solid fa-circle-exclamation"></i><span>{{ $errors->first() }}</span></div>
    @endif

    <div class="reference-list" data-list-container>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Asset Code</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Serial Number</th>
                        <th>Condition</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assets as $asset)
                        @php
                            $statusBadge = match($asset->status) {
                                'available' => 'success',
                                'assigned' => 'primary',
                                'lost' => 'danger',
                                default => 'secondary',
                            };
                        @endphp
                        <tr>
                            <td class="ps-3 fw-medium text-dark">{{ $asset->asset_code }}</td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $asset->name }}</div>
                            </td>
                            <td>{{ $asset->category }}</td>
                            <td>{{ $asset->serial_number ?: '—' }}</td>
                            <td><span class="badge text-bg-light border text-dark text-capitalize">{{ $asset->condition }}</span></td>
                            <td><span class="badge text-bg-{{ $statusBadge }}">{{ str($asset->status)->title() }}</span></td>
                            <td class="text-end pe-3 text-nowrap">
                                @can('asset.manage')
                                    @if($asset->status === 'available')
                                        <button class="btn btn-action-link btn-sm" data-bs-toggle="modal" data-bs-target="#assign{{ $asset->id }}">
                                            <i class="fa-solid fa-arrow-right-to-bracket"></i><span>Assign</span>
                                        </button>
                                    @endif
                                    <button class="btn btn-action-link btn-sm" data-bs-toggle="modal" data-bs-target="#editAsset{{ $asset->id }}">
                                        <i class="fa-solid fa-pen"></i><span>Edit</span>
                                    </button>
                                    @if($asset->status !== 'assigned')
                                        <form class="d-inline" method="POST" action="{{ route('assets.destroy', $asset) }}" data-confirm="Archive this asset from active inventory?">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-action-link btn-sm text-danger" type="submit">
                                                <i class="fa-solid fa-trash"></i><span>Delete</span>
                                            </button>
                                        </form>
                                    @endif
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="text-center text-body-secondary py-5" colspan="7">
                                <i class="fa-solid fa-boxes-stacked fa-xl d-block mb-3 text-primary"></i>No assets found in the inventory registry.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-pagination-footer :paginator="$assets" />
    </div>

    @can('asset.manage')
        <div class="modal fade" id="createAsset" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <form class="modal-content" method="POST" action="{{ route('assets.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h2 class="modal-title fs-5"><i class="fa-solid fa-boxes-stacked me-2 text-primary"></i>Register asset</h2>
                        <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <x-asset-fields />
                    </div>
                    <x-form-save-actions :allow-save-new="true" save-label="Save asset" />
                </form>
            </div>
        </div>

        @foreach($assets as $asset)
            <div class="modal fade" id="editAsset{{ $asset->id }}" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <form class="modal-content" method="POST" action="{{ route('assets.update', $asset) }}">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h2 class="modal-title fs-5"><i class="fa-solid fa-pen me-2 text-primary"></i>Edit asset · {{ $asset->name }}</h2>
                            <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <x-asset-fields :asset="$asset" />
                        </div>
                        <x-form-save-actions save-label="Save changes" />
                    </form>
                </div>
            </div>

            @if($asset->status === 'available')
                <div class="modal fade" id="assign{{ $asset->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <form class="modal-content" method="POST" action="{{ route('assets.assign', $asset) }}">
                            @csrf
                            <div class="modal-header">
                                <h2 class="modal-title fs-5"><i class="fa-solid fa-arrow-right-to-bracket me-2 text-primary"></i>Assign {{ $asset->name }}</h2>
                                <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <label class="form-label">Employee Assignee <span class="text-danger">*</span></label>
                                <select class="form-select" name="employee_id" required>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}">{{ $employee->getFullName() }}</option>
                                    @endforeach
                                </select>
                                <label class="form-label mt-3">Condition on handover</label>
                                <select class="form-select" name="condition_out">
                                    @foreach(['new','good','fair','poor'] as $condition)
                                        <option @selected($condition === 'good')>{{ $condition }}</option>
                                    @endforeach
                                </select>
                                <label class="form-label mt-3">Expected return date</label>
                                <input class="form-control" type="date" name="expected_return_date" min="{{ today()->toDateString() }}">
                            </div>
                            <x-form-save-actions save-label="Assign asset" />
                        </form>
                    </div>
                </div>
            @endif
        @endforeach
    @endcan
</x-layouts::app>
