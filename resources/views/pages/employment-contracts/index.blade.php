<x-layouts::app title="Employment contracts">
    <x-workspace-command-bar title="Employment contracts" icon="fa-file-signature" context="People & Contracts">
        <x-slot:filters>
            <form class="reference-filter-form" method="GET" hx-get="{{ request()->url() }}" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="true">
                <div class="input-group reference-search">
                    <span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input class="form-control" name="search" value="{{ request('search') }}" placeholder="Search contract or employee" hx-get="{{ request()->url() }}" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="false" hx-trigger="keyup changed delay:500ms">
                </div>
                <select class="form-select reference-status" name="status" hx-get="{{ request()->url() }}" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="true" hx-trigger="change">
                    <option value="">All statuses</option>
                    @foreach (['draft', 'pending_approval', 'active', 'expiring', 'expired', 'terminated', 'superseded'] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucwords(str_replace('_', ' ', $status)) }}</option>
                    @endforeach
                </select>
                <button class="btn btn-primary reference-search-button" type="submit">Search</button>
            </form>
        </x-slot:filters>
        <x-slot:actions>
            @can('contract.create')
                <a class="btn btn-action-link btn-sm" href="{{ route('contracts.create') }}"><i class="fa-solid fa-circle-plus"></i><span>Create contract</span></a>
            @endcan
        </x-slot:actions>
    </x-workspace-command-bar>

    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    <div class="reference-list" data-list-container>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th class="ps-3">Contract / employee</th><th>Type</th><th>Period</th><th>Salary</th><th>Status</th><th class="text-end pe-3">Actions</th></tr></thead>
                <tbody>
                    @forelse ($contracts as $contract)
                        <tr>
                            <td class="ps-3"><div class="fw-semibold">{{ $contract->contract_number }}</div><div class="small text-body-secondary">{{ $contract->employee->full_name_km ?: $contract->employee->full_name_en }} · {{ $contract->employee->employee_code }}</div></td>
                            <td><span class="small fw-semibold text-body-secondary">{{ strtoupper($contract->type) }}</span></td>
                            <td class="text-nowrap">{{ $contract->start_date->format('d/m/Y') }} – {{ $contract->end_date?->format('d/m/Y') ?? 'Open-ended' }}</td>
                            <td class="text-nowrap fw-medium">{{ number_format($contract->salary_amount, $contract->salary_currency === 'KHR' ? 0 : 2) }} {{ $contract->salary_currency }}</td>
                            <td><span class="badge text-bg-{{ in_array($contract->status, ['active'], true) ? 'success' : (in_array($contract->status, ['terminated', 'expired', 'superseded'], true) ? 'secondary' : 'warning') }}">{{ ucwords(str_replace('_', ' ', $contract->status)) }}</span></td>
                            <td class="text-end pe-3 text-nowrap">
                                @if ($contract->document_path)
                                    <a class="btn btn-action-link btn-sm" href="{{ route('contracts.download', $contract) }}" title="Download PDF"><i class="fa-solid fa-download"></i><span class="visually-hidden">Download PDF</span></a>
                                @endif
                                @can('contract.approve')
                                    @if ($contract->status === 'pending_approval')
                                        <form method="POST" action="{{ route('contracts.approve', $contract) }}" class="d-inline" data-confirm="Approve this employment contract? The approved contract becomes active and may supersede a previous renewal contract." data-confirm-title="Approve employment contract" data-confirm-action="Approve contract" data-confirm-tone="primary">
                                            @csrf
                                            <button class="btn btn-action-link btn-sm" type="submit"><i class="fa-solid fa-check"></i><span>Approve</span></button>
                                        </form>
                                    @endif
                                @endcan
                                @can('contract.create')
                                    @if ($contract->type === 'fdc' && in_array($contract->status, ['active', 'expiring'], true))
                                        <a class="btn btn-action-link btn-sm" href="{{ route('contracts.renew', $contract) }}"><i class="fa-solid fa-rotate"></i><span>Renew</span></a>
                                    @endif
                                @endcan
                                @can('contract.terminate')
                                    @if (in_array($contract->status, ['active', 'expiring'], true))
                                        <button class="btn btn-action-link btn-sm text-danger" type="button" data-bs-toggle="modal" data-bs-target="#terminateContract{{ $contract->id }}"><i class="fa-solid fa-ban"></i><span>Terminate</span></button>
                                    @endif
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><x-empty-state class="py-5 px-3" icon="fa-file-signature" title="No employment contracts" message="No employment contracts match this view." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-pagination-footer :paginator="$contracts" />
    </div>

    @can('contract.terminate')
        @foreach($contracts as $contract)
            @if(in_array($contract->status, ['active', 'expiring'], true))
                <div class="modal fade" id="terminateContract{{ $contract->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <form class="modal-content" method="POST" action="{{ route('contracts.terminate', $contract) }}">
                            @csrf
                            <div class="modal-header">
                                <div>
                                    <div class="small text-body-secondary">{{ $contract->contract_number }}</div>
                                    <h2 class="modal-title fs-5">Terminate employment contract</h2>
                                </div>
                                <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p class="small text-body-secondary">This ends the active contract for <strong>{{ $contract->employee->full_name_km ?: $contract->employee->full_name_en }}</strong>. The termination date and reason are kept in the contract audit trail.</p>
                                <div class="mb-3">
                                    <label class="form-label" for="terminationDate{{ $contract->id }}">Termination date <span class="text-danger">*</span></label>
                                    <input class="form-control" id="terminationDate{{ $contract->id }}" type="date" name="termination_date" value="{{ today()->toDateString() }}" min="{{ $contract->start_date->toDateString() }}" required>
                                </div>
                                <div>
                                    <label class="form-label" for="terminationReason{{ $contract->id }}">Reason <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="terminationReason{{ $contract->id }}" name="termination_reason" rows="3" minlength="10" maxlength="2000" required placeholder="Explain why this contract is being terminated..."></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-light" type="button" data-bs-dismiss="modal">Keep contract</button>
                                <button class="btn btn-danger" type="submit"><i class="fa-solid fa-ban me-1"></i>Terminate contract</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        @endforeach
    @endcan
</x-layouts::app>