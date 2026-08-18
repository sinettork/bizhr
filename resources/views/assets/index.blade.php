<x-layouts::app title="Assets & Inventory">
    <x-workspace-command-bar title="Company Assets & Inventory" icon="fa-boxes-stacked" context="Operations & IT">
        <x-slot:filters>
            <form class="reference-filter-form" method="GET" hx-get="{{ request()->url() }}" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="true">
                <div class="input-group reference-search">
                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input class="form-control" name="search" value="{{ request('search') }}" placeholder="Search name, code, serial or category" hx-get="{{ request()->url() }}" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="false" hx-trigger="keyup changed delay:500ms">
                </div>
                <select class="form-select reference-status" name="category" hx-get="{{ request()->url() }}" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="true" hx-trigger="change">
                    <option value="">All categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category }}" @selected(request('category') === $category)>{{ $category }}</option>
                    @endforeach
                </select>
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
        @if($assets->count())
            <div class="px-3 py-2 border-bottom bg-body-tertiary d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div>
                    <div class="fw-semibold text-dark">Inventory overview</div>
                    <div class="small text-body-secondary">Photos make large inventories easier to scan and identify.</div>
                </div>
                <span class="badge status-counter">{{ number_format($assets->total()) }} total</span>
            </div>

            <div class="row g-2 p-3">
                @foreach($assets as $asset)
                    @php
                        $statusTone = match($asset->status) {
                            'available' => 'success',
                            'assigned' => 'primary',
                            'lost' => 'danger',
                            default => 'secondary',
                        };
                        $assetIcon = match(strtolower((string) $asset->category)) {
                            'laptop', 'computer' => 'fa-laptop',
                            'phone', 'mobile' => 'fa-mobile-screen-button',
                            'monitor', 'display' => 'fa-desktop',
                            'vehicle' => 'fa-car',
                            default => 'fa-box',
                        };
                    @endphp
                    <div class="col-12 col-md-6 col-xl-4 col-xxl-3">
                        <article class="card h-100 shadow-none overflow-hidden">
                            <div class="card-body p-3 d-flex flex-column">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="flex-shrink-0 rounded-3 overflow-hidden border bg-body-tertiary d-flex align-items-center justify-content-center" style="width:76px;height:76px;">
                                        @if($asset->image_path)
                                            <img src="{{ asset('storage/'.$asset->image_path) }}" alt="{{ $asset->name }}" class="w-100 h-100" loading="lazy" style="object-fit:cover;">
                                        @else
                                            <i class="fa-solid {{ $assetIcon }} text-primary fs-4"></i>
                                        @endif
                                    </div>

                                    <div class="min-w-0 flex-grow-1">
                                        <div class="d-flex align-items-start justify-content-between gap-2">
                                            <div class="min-w-0">
                                                <h2 class="h6 mb-1 text-dark text-truncate">{{ $asset->name }}</h2>
                                                <div class="small text-body-secondary text-truncate">{{ $asset->asset_code }}</div>
                                            </div>
                                            <div class="dropdown flex-shrink-0">
                                                <button class="btn btn-action-link btn-sm px-1" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Asset actions"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                                    @can('asset.manage')
                                                        @if($asset->status === 'available')
                                                            <li><button class="dropdown-item" type="button" data-bs-toggle="modal" data-bs-target="#assign{{ $asset->id }}"><i class="fa-solid fa-user-plus me-2"></i>Assign asset</button></li>
                                                        @endif
                                                        <li><button class="dropdown-item" type="button" data-bs-toggle="modal" data-bs-target="#editAsset{{ $asset->id }}"><i class="fa-solid fa-pen me-2"></i>Edit asset</button></li>
                                                        @if($asset->status !== 'assigned')
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li><form method="POST" action="{{ route('assets.destroy',$asset) }}" data-confirm="Archive this asset from active inventory?">@csrf @method('DELETE')<button class="dropdown-item text-danger" type="submit"><i class="fa-solid fa-box-archive me-2"></i>Archive asset</button></form></li>
                                                        @endif
                                                    @endcan
                                                </ul>
                                            </div>
                                        </div>

                                        <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
                                            <span class="badge text-bg-{{ $statusTone }}">{{ str($asset->status)->title() }}</span>
                                            <span class="small text-body-secondary">{{ $asset->category }}</span>
                                        </div>
                                    </div>
                                </div>

                                @if($asset->serial_number)
                                    <div class="small text-body-secondary text-truncate mt-3" title="{{ $asset->serial_number }}"><i class="fa-solid fa-barcode me-1"></i>S/N {{ $asset->serial_number }}</div>
                                @endif

                                <hr class="my-3">

                                <div class="row g-0 text-center mt-auto">
                                    <div class="col-4 px-1">
                                        <div class="fw-semibold text-dark text-capitalize text-truncate">{{ $asset->condition ?: '—' }}</div>
                                        <div class="small text-body-secondary">Condition</div>
                                    </div>
                                    <div class="col-4 px-1 border-start border-end">
                                        <div class="fw-semibold text-dark text-truncate">{{ $asset->category ?: '—' }}</div>
                                        <div class="small text-body-secondary">Category</div>
                                    </div>
                                    <div class="col-4 px-1">
                                        <div class="fw-semibold text-dark">{{ $asset->status === 'assigned' ? 'In use' : ($asset->status === 'available' ? 'Ready' : '—') }}</div>
                                        <div class="small text-body-secondary">Availability</div>
                                    </div>
                                </div>
                            </div>
                        </article>
                    </div>
                @endforeach
            </div>
            <x-pagination-footer :paginator="$assets" />
        @else
            <div class="empty-state py-5 px-3">
                <i class="fa-solid fa-boxes-stacked fa-2xl d-block mb-3 text-primary"></i>
                <h2 class="h6 mb-1">No assets found</h2>
                <p class="text-body-secondary mb-0">Adjust the search, category or status filter, or register the first asset.</p>
            </div>
        @endif
    </div>

    @can('asset.manage')
        <div class="modal fade" id="createAsset" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <form class="modal-content" method="POST" action="{{ route('assets.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header"><h2 class="modal-title fs-5"><i class="fa-solid fa-boxes-stacked me-2 text-primary"></i>Register asset</h2><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body"><x-asset-fields /></div>
                    <x-form-save-actions :allow-save-new="true" save-label="Save asset" />
                </form>
            </div>
        </div>

        @foreach($assets as $asset)
            <div class="modal fade" id="editAsset{{ $asset->id }}" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <form class="modal-content" method="POST" action="{{ route('assets.update', $asset) }}" enctype="multipart/form-data">
                        @csrf @method('PUT')
                        <div class="modal-header"><h2 class="modal-title fs-5"><i class="fa-solid fa-pen me-2 text-primary"></i>Edit asset · {{ $asset->name }}</h2><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div>
                        <div class="modal-body"><x-asset-fields :asset="$asset" /></div>
                        <x-form-save-actions save-label="Save changes" />
                    </form>
                </div>
            </div>

            @if($asset->status === 'available')
                <div class="modal fade" id="assign{{ $asset->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <form class="modal-content" method="POST" action="{{ route('assets.assign', $asset) }}">
                            @csrf
                            <div class="modal-header"><h2 class="modal-title fs-5"><i class="fa-solid fa-arrow-right-to-bracket me-2 text-primary"></i>Assign {{ $asset->name }}</h2><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div>
                            <div class="modal-body">
                                <label class="form-label">Employee Assignee <span class="text-danger">*</span></label>
                                <select class="form-select" name="employee_id" required>@foreach($employees as $employee)<option value="{{ $employee->id }}">{{ $employee->getFullName() }}</option>@endforeach</select>
                                <label class="form-label mt-3">Condition on handover</label>
                                <select class="form-select" name="condition_out">@foreach(['new','good','fair','poor'] as $condition)<option @selected($condition === 'good')>{{ $condition }}</option>@endforeach</select>
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
