<x-layouts::app title="Import data">
    <x-workspace-command-bar title="Import data" icon="fa-file-import" />
    @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    <div class="alert alert-info small mb-3"><strong>Recommended order:</strong> Branches → Departments → Positions and Employment types → Employees. Excel (.xlsx) is recommended; CSV remains supported. Download the template, keep the column order unchanged, preview errors, then confirm. Existing records with the same code are updated; other rows are created.</div>
    <div class="row g-3">
        @forelse($types as $key => $type)
            <div class="col-md-6 col-xl-4" @if($selectedType === $key) id="selected-import" @endif>
                <div class="card border-0 shadow-sm h-100 {{ $selectedType === $key ? 'border border-primary' : '' }}">
                    <div class="card-body">
                        <h2 class="h6">{{ $type['label'] }}</h2>
                        <p class="small text-body-secondary">Excel (.xlsx) recommended · CSV supported · up to 2,000 rows</p>
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <a class="btn btn-action-link btn-sm" href="{{ route('imports.template', $key) }}"><i class="fa-solid fa-file-excel"></i><span>Excel template</span></a>
                            <a class="btn btn-action-link btn-sm" href="{{ route('imports.template', ['type' => $key, 'format' => 'csv']) }}"><i class="fa-solid fa-file-csv"></i><span>CSV</span></a>
                        </div>
                        <form method="POST" action="{{ route('imports.preview', $key) }}" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3"><label class="form-label">Excel or CSV file <span class="text-danger">*</span></label><input class="form-control" type="file" name="file" accept=".xlsx,.csv,text/csv" required></div>
                            <button class="btn btn-primary w-100"><i class="fa-solid fa-eye"></i> Preview import</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12"><div class="alert alert-secondary">Your account does not have permission to import data.</div></div>
        @endforelse
    </div>
    @if($selectedType)<script nonce="{{ request()->attributes->get('csp_nonce') }}">document.getElementById('selected-import')?.scrollIntoView({behavior:'smooth',block:'center'});</script>@endif
</x-layouts::app>
