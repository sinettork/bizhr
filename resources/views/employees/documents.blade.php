<x-layouts::app title="Employee documents">
    <x-workspace-command-bar
        title="Employee documents"
        icon="fa-folder-open"
        :context="$employee->full_name_km ?: $employee->full_name_en ?: $employee->employee_code"
    >
        <x-slot:actions>
            <a class="btn btn-action-link btn-sm" href="{{ route('employees.show', $employee) }}">
                <i class="fa-solid fa-arrow-left"></i><span>Employee profile</span>
            </a>
            @canany(['employee.edit', 'employee.edit-own'])
                <button class="btn btn-action-link btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#uploadDocument">
                    <i class="fa-solid fa-circle-plus"></i><span>Add document</span>
                </button>
            @endcanany
        </x-slot:actions>
    </x-workspace-command-bar>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="reference-list" data-list-container>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Document</th>
                        <th>Number</th>
                        <th>Version</th>
                        <th>Issued</th>
                        <th>Expires</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($documents as $document)
                        <tr>
                            <td class="ps-3">
                                <div class="fw-medium">{{ $document->document_type }}</div>
                                <small class="text-body-secondary">{{ $document->original_name }}</small>
                            </td>
                            <td>{{ $document->document_number ?: '—' }}</td>
                            <td>v{{ $document->version }}</td>
                            <td>{{ $document->issued_date?->format('d M Y') ?? '—' }}</td>
                            <td>
                                {{ $document->expiry_date?->format('d M Y') ?? '—' }}
                                @if($document->expiry_date?->isPast() && $document->status !== 'revoked')
                                    <span class="badge text-bg-danger ms-1">Expired</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge text-bg-{{ $document->status === 'verified' ? 'success' : ($document->status === 'revoked' ? 'secondary' : 'warning') }}">
                                    {{ str($document->status)->replace('_', ' ')->title() }}
                                </span>
                            </td>
                            <td class="text-end pe-3 text-nowrap">
                                <a class="btn btn-action-link btn-sm" href="{{ route('employees.documents.download', [$employee, $document]) }}">
                                    <i class="fa-solid fa-download"></i><span>Download</span>
                                </a>
                                @can('employee.view-sensitive')
                                    @if($document->status === 'pending_verification')
                                        <form class="d-inline" method="POST" action="{{ route('employees.documents.verify', [$employee, $document]) }}" data-confirm="Verify this employee document? Verified documents become part of the protected audit history and must be revoked instead of deleted." data-confirm-title="Verify employee document" data-confirm-action="Verify document" data-confirm-tone="primary">
                                            @csrf
                                            <button class="btn btn-action-link btn-sm" type="submit">
                                                <i class="fa-solid fa-circle-check"></i><span>Verify</span>
                                            </button>
                                        </form>
                                    @elseif($document->status === 'verified')
                                        <button class="btn btn-action-link btn-sm text-danger" type="button" data-bs-toggle="modal" data-bs-target="#revokeDocument{{ $document->id }}">
                                            <i class="fa-solid fa-ban"></i><span>Revoke</span>
                                        </button>
                                    @endif
                                @endcan
                                @canany(['employee.edit', 'employee.edit-own'])
                                    @if($document->status === 'pending_verification')
                                        <form class="d-inline" method="POST" action="{{ route('employees.documents.destroy', [$employee, $document]) }}" data-confirm="Remove this unverified document? The stored file will be deleted and this action cannot be undone." data-confirm-title="Remove unverified document" data-confirm-action="Remove document" data-confirm-tone="danger">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-action-link btn-sm text-danger" type="submit">
                                                <i class="fa-solid fa-trash"></i><span>Remove</span>
                                            </button>
                                        </form>
                                    @endif
                                @endcanany
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="p-0"><x-empty-state class="py-5 px-3" icon="fa-folder-open" title="No employee documents" message="Uploaded employee documents and verification history will appear here." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-pagination-footer :paginator="$documents" />
    </div>

    @canany(['employee.edit', 'employee.edit-own'])
        <div class="modal fade" id="uploadDocument" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <form class="modal-content" method="POST" enctype="multipart/form-data" action="{{ route('employees.documents.store', $employee) }}">
                    @csrf
                    <div class="modal-header">
                        <h2 class="modal-title fs-5">Add employee document</h2>
                        <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label">Document type <span class="text-danger">*</span></label><input class="form-control" name="document_type" required></div>
                            <div class="col-md-6"><label class="form-label">Document number</label><input class="form-control" name="document_number"></div>
                            <div class="col-12"><label class="form-label">File <span class="text-danger">*</span></label><input class="form-control" type="file" name="document" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required><div class="form-text">PDF, image, or Word document; maximum 10 MB.</div></div>
                            <div class="col-md-6"><label class="form-label">Issued date</label><input class="form-control" type="date" name="issued_date"></div>
                            <div class="col-md-6"><label class="form-label">Expiry date</label><input class="form-control" type="date" name="expiry_date"></div>
                            <div class="col-12"><label class="form-label">Notes</label><textarea class="form-control" name="notes" rows="2"></textarea></div>
                        </div>
                    </div>
                    <x-form-save-actions save-label="Upload & close" />
                </form>
            </div>
        </div>
    @endcanany

    @can('employee.view-sensitive')
        @foreach($documents->where('status', 'verified') as $document)
            <div class="modal fade" id="revokeDocument{{ $document->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <form class="modal-content" method="POST" action="{{ route('employees.documents.revoke', [$employee, $document]) }}">
                        @csrf
                        <div class="modal-header"><h2 class="modal-title fs-5">Revoke document</h2><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div>
                        <div class="modal-body">
                            <p class="small text-body-secondary mb-3">The file will be retained for audit history and the revocation reason will be recorded.</p>
                            <label class="form-label">Revocation reason <span class="text-danger">*</span></label><textarea class="form-control" name="reason" minlength="5" maxlength="1000" rows="3" required></textarea>
                        </div>
                        <div class="modal-footer"><button class="btn btn-light" type="button" data-bs-dismiss="modal">Keep verified</button><button class="btn btn-danger" type="submit">Revoke document</button></div>
                    </form>
                </div>
            </div>
        @endforeach
    @endcan
</x-layouts::app>
