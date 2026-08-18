<x-layouts::app title="My Assets & Equipment">
    <x-workspace-command-bar title="My Assigned Equipment" icon="fa-laptop" context="Personal Workspace" />

    @if(session('status'))
        <div class="alert alert-success d-flex align-items-center gap-2"><i class="fa-solid fa-circle-check"></i><span>{{ session('status') }}</span></div>
    @endif

    <div class="reference-list" data-list-container>
        @if($assignments->count())
            <div class="px-3 py-2 border-bottom bg-body-tertiary d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div>
                    <div class="fw-semibold text-dark">Equipment assigned to me</div>
                    <div class="small text-body-secondary">Current company equipment, return expectations and handover condition.</div>
                </div>
                <span class="badge status-counter">{{ number_format($assignments->total()) }} item(s)</span>
            </div>

            <div class="row g-2 p-3">
                @foreach($assignments as $assignment)
                    @php
                        $isAssigned = $assignment->status === 'assigned';
                        $asset = $assignment->asset;
                        $assetIcon = match(strtolower((string) $asset?->category)) {
                            'laptop', 'computer' => 'fa-laptop',
                            'phone', 'mobile' => 'fa-mobile-screen-button',
                            'monitor', 'display' => 'fa-desktop',
                            'vehicle' => 'fa-car',
                            default => 'fa-box',
                        };
                    @endphp
                    <div class="col-12 col-md-6 col-xl-4 col-xxl-3">
                        <article class="card h-100 shadow-none">
                            <div class="card-body p-3 d-flex flex-column">
                                <div class="d-flex align-items-start justify-content-between gap-2">
                                    <div class="d-flex align-items-start gap-2 min-w-0">
                                        <span class="page-icon flex-shrink-0" style="width:38px;height:38px;font-size:.82rem;"><i class="fa-solid {{ $assetIcon }}"></i></span>
                                        <div class="min-w-0">
                                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                                <h2 class="h6 mb-0 text-dark text-truncate">{{ $asset?->name }}</h2>
                                                <span class="badge text-bg-{{ $isAssigned ? 'primary' : 'secondary' }}">{{ ucfirst($assignment->status) }}</span>
                                            </div>
                                            <div class="small text-body-secondary mt-1 text-truncate">{{ $asset?->asset_code }} · {{ $asset?->category ?? 'General' }}</div>
                                            @if($asset?->serial_number)<div class="small text-body-secondary text-truncate" title="{{ $asset->serial_number }}">S/N {{ $asset->serial_number }}</div>@endif
                                        </div>
                                    </div>
                                </div>

                                <hr class="my-3">

                                <div class="row g-0 text-center mt-auto">
                                    <div class="col-4 px-1">
                                        <div class="fw-semibold text-dark">{{ $assignment->assigned_date->format('d M') }}</div>
                                        <div class="small text-body-secondary">Assigned</div>
                                    </div>
                                    <div class="col-4 px-1 border-start border-end">
                                        <div class="fw-semibold text-dark text-capitalize">{{ $assignment->condition_out }}</div>
                                        <div class="small text-body-secondary">Condition</div>
                                    </div>
                                    <div class="col-4 px-1">
                                        <div class="fw-semibold text-dark">{{ $assignment->expected_return_date?->format('d M') ?? 'Permanent' }}</div>
                                        <div class="small text-body-secondary">Return</div>
                                    </div>
                                </div>

                                <div class="pt-3 mt-3 border-top d-flex justify-content-end">
                                    @if($isAssigned)
                                        <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#return{{ $assignment->id }}"><i class="fa-solid fa-rotate-left me-1"></i>Initiate return</button>
                                    @else
                                        <span class="small text-body-secondary"><i class="fa-solid fa-check me-1"></i>Returned</span>
                                    @endif
                                </div>
                            </div>
                        </article>
                    </div>
                @endforeach
            </div>
            <x-pagination-footer :paginator="$assignments" />
        @else
            <div class="empty-state py-5 px-3">
                <i class="fa-solid fa-laptop fa-2xl d-block mb-3 text-primary"></i>
                <h2 class="h6 mb-1">No equipment assigned</h2>
                <p class="text-body-secondary mb-0">Company equipment assigned to you will appear here.</p>
            </div>
        @endif
    </div>

    @foreach($assignments as $assignment)
        @if($assignment->status === 'assigned')
            <div class="modal fade" id="return{{ $assignment->id }}" tabindex="-1">
                <div class="modal-dialog">
                    <form class="modal-content" method="POST" action="{{ route('assets.receive', $assignment) }}">
                        @csrf
                        <div class="modal-header"><h2 class="modal-title fs-5"><i class="fa-solid fa-rotate-left me-2 text-primary"></i>Return {{ $assignment->asset?->name }}</h2><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div>
                        <div class="modal-body">
                            <label class="form-label">Condition on return <span class="text-danger">*</span></label>
                            <select class="form-select mb-3" name="condition_in">
                                <option value="new">New</option><option value="good" selected>Good</option><option value="fair">Fair</option><option value="poor">Poor</option><option value="lost">Lost</option><option value="retired">Retired</option>
                            </select>
                            <label class="form-label">Handover & return notes</label>
                            <textarea class="form-control" name="notes" placeholder="Condition details or accessories returned..."></textarea>
                        </div>
                        <x-form-save-actions save-label="Record return" />
                    </form>
                </div>
            </div>
        @endif
    @endforeach
</x-layouts::app>
