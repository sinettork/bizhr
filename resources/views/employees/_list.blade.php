<div class="reference-list" data-list-container id="list-content">
    <div class="d-none align-items-center justify-content-between gap-2 px-3 py-2 border-bottom bg-body-tertiary" data-employee-selection-bar>
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-check-square text-primary"></i>
            <span class="fw-semibold"><span data-employee-selected-count>0</span> selected</span>
            <span class="small text-body-secondary d-none d-md-inline">Current page selection</span>
        </div>
        <div class="d-flex align-items-center gap-1">
            <button class="btn btn-action-link btn-sm" type="button" data-employee-export-selected>
                <i class="fa-solid fa-file-export"></i><span>Export selected</span>
            </button>
            <button class="btn btn-action-link btn-sm" type="button" data-employee-clear-selection>
                <i class="fa-solid fa-xmark"></i><span>Clear</span>
            </button>
        </div>
    </div>

    @include('employees._table', ['employees' => $employees])
</div>
