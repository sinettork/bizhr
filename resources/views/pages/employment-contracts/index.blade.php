<x-layouts::app title="Employment contracts">
    <x-workspace-command-bar title="Employment contracts" icon="fa-file-signature">
        <x-slot:filters><form class="reference-filter-form" method="GET" hx-get="{{ request()->url() }}" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="true"><div class="input-group reference-search"><span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass"></i></span><input class="form-control" name="search" value="{{ request('search') }}" placeholder="Search contract or employee" hx-get="{{ request()->url() }}" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="false" hx-trigger="keyup changed delay:500ms"></div><select class="form-select reference-status" name="status" hx-get="{{ request()->url() }}" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="true" hx-trigger="change"><option value="">All statuses</option>@foreach (['draft', 'pending_approval', 'active', 'expiring', 'expired', 'terminated', 'superseded'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ ucwords(str_replace('_', ' ', $status)) }}</option>@endforeach</select><button class="btn btn-primary reference-search-button" type="submit">Search</button></form></x-slot:filters>
        <x-slot:actions>@can('contract.create')<a class="btn btn-action-link btn-sm" href="{{ route('contracts.create') }}"><i class="fa-solid fa-circle-plus"></i><span>Create contract</span></a>@endcan</x-slot:actions>
    </x-workspace-command-bar>

    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    <div class="reference-list" data-list-container>
        <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th class="ps-3">Contract / employee</th><th>Type</th><th>Period</th><th>Salary</th><th>Status</th><th class="text-end pe-3">Actions</th></tr></thead><tbody>
            @forelse ($contracts as $contract)
                <tr>
                    <td class="ps-3"><div class="fw-semibold">{{ $contract->contract_number }}</div><div class="small text-body-secondary">{{ $contract->employee->full_name_km ?: $contract->employee->full_name_en }} · {{ $contract->employee->employee_code }}</div></td>
                    <td><span class="badge text-bg-primary">{{ strtoupper($contract->type) }}</span></td>
                    <td class="text-nowrap">{{ $contract->start_date->format('d/m/Y') }} – {{ $contract->end_date?->format('d/m/Y') ?? 'Open-ended' }}</td>
                    <td class="text-nowrap fw-medium">{{ number_format($contract->salary_amount, $contract->salary_currency === 'KHR' ? 0 : 2) }} {{ $contract->salary_currency }}</td>
                    <td><span class="badge text-bg-{{ in_array($contract->status, ['active', 'approved'], true) ? 'success' : (in_array($contract->status, ['terminated', 'expired'], true) ? 'secondary' : 'warning') }}">{{ ucwords(str_replace('_', ' ', $contract->status)) }}</span></td>
                    <td class="text-end pe-3 text-nowrap">
                        @if ($contract->document_path)<a class="btn btn-sm btn-outline-primary" href="{{ route('contracts.download', $contract) }}" title="Download PDF"><i class="fa-solid fa-download"></i><span class="visually-hidden">Download PDF</span></a>@endif
                        @can('contract.approve')
                            @if ($contract->status === 'pending_approval')<form method="POST" action="{{ route('contracts.approve', $contract) }}" class="d-inline">@csrf<button class="btn btn-sm btn-success" type="submit">Approve</button></form>@endif
                        @endcan
                        @can('contract.create')
                            @if ($contract->type === 'fdc' && in_array($contract->status, ['active', 'expiring'], true))<a class="btn btn-sm btn-outline-primary" href="{{ route('contracts.renew', $contract) }}">Renew</a>@endif
                        @endcan
                        @can('contract.terminate')
                            @if (in_array($contract->status, ['active', 'expiring'], true))<button class="btn btn-sm btn-outline-danger" type="button" data-bs-toggle="collapse" data-bs-target="#terminate-{{ $contract->id }}">Terminate</button>@endif
                        @endcan
                    </td>
                </tr>
                @can('contract.terminate')
                    @if (in_array($contract->status, ['active', 'expiring'], true))<tr class="collapse" id="terminate-{{ $contract->id }}"><td colspan="6" class="bg-light"><form method="POST" action="{{ route('contracts.terminate', $contract) }}" class="row g-2 align-items-end">@csrf<div class="col-md-3"><label class="form-label">Termination date <span class="text-danger">*</span></label><input class="form-control form-control-sm" type="date" name="termination_date" required></div><div class="col-md-6"><label class="form-label">Reason <span class="text-danger">*</span></label><input class="form-control form-control-sm" name="termination_reason" minlength="10" required></div><div class="col-md-3"><button class="btn btn-sm btn-danger" type="submit">Confirm termination</button></div></form></td></tr>@endif
                @endcan
            @empty<tr><td colspan="6" class="py-5 text-center text-body-secondary">No employment contracts found.</td></tr>
            @endforelse
        </tbody></table></div>
        <x-pagination-footer :paginator="$contracts" />
    </div>
</x-layouts::app>