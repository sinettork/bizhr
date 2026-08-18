<x-layouts::app title="My Assets & Equipment">
    <x-workspace-command-bar title="My Assigned Equipment" icon="fa-laptop" context="Personal Workspace" />

    @if(session('status'))
        <div class="alert alert-success d-flex align-items-center gap-2"><i class="fa-solid fa-circle-check"></i><span>{{ session('status') }}</span></div>
    @endif

    <div class="reference-list" data-list-container>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Asset Code</th>
                        <th>Asset Name</th>
                        <th>Category</th>
                        <th>Assigned Date</th>
                        <th>Expected Return</th>
                        <th>Condition Out</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assignments as $assignment)
                        @php
                            $isAssigned = $assignment->status === 'assigned';
                        @endphp
                        <tr>
                            <td class="ps-3 fw-medium text-dark">{{ $assignment->asset?->asset_code }}</td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $assignment->asset?->name }}</div>
                                @if($assignment->asset?->serial_number)<small class="text-body-secondary">S/N: {{ $assignment->asset->serial_number }}</small>@endif
                            </td>
                            <td>{{ $assignment->asset?->category ?? 'General' }}</td>
                            <td>{{ $assignment->assigned_date->format('d M Y') }}</td>
                            <td>{{ $assignment->expected_return_date?->format('d M Y') ?? 'Permanent' }}</td>
                            <td><span class="badge text-bg-light border text-dark text-capitalize">{{ $assignment->condition_out }}</span></td>
                            <td>
                                <span class="badge text-bg-{{ $isAssigned ? 'primary' : 'secondary' }}">{{ ucfirst($assignment->status) }}</span>
                            </td>
                            <td class="text-end pe-3 text-nowrap">
                                @if($isAssigned)
                                    <button class="btn btn-action-link btn-sm" data-bs-toggle="modal" data-bs-target="#return{{ $assignment->id }}">
                                        <i class="fa-solid fa-rotate-left"></i><span>Initiate return</span>
                                    </button>
                                @else
                                    <span class="small text-body-secondary"><i class="fa-solid fa-check me-1"></i>Returned</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="text-center text-body-secondary py-5" colspan="8">
                                <i class="fa-solid fa-laptop fa-xl d-block mb-3 text-primary"></i>You have no company assets assigned to you.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-pagination-footer :paginator="$assignments" />
    </div>

    @foreach($assignments as $assignment)
        @if($assignment->status === 'assigned')
            <div class="modal fade" id="return{{ $assignment->id }}" tabindex="-1">
                <div class="modal-dialog">
                    <form class="modal-content" method="POST" action="{{ route('assets.receive', $assignment) }}">
                        @csrf
                        <div class="modal-header">
                            <h2 class="modal-title fs-5"><i class="fa-solid fa-rotate-left me-2 text-primary"></i>Return {{ $assignment->asset?->name }}</h2>
                            <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <label class="form-label">Condition on return <span class="text-danger">*</span></label>
                            <select class="form-select mb-3" name="condition_in">
                                <option value="new">New</option>
                                <option value="good" selected>Good</option>
                                <option value="fair">Fair</option>
                                <option value="poor">Poor</option>
                                <option value="lost">Lost</option>
                                <option value="retired">Retired</option>
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
