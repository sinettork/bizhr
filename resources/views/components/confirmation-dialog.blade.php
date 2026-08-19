<div
    class="modal fade"
    id="appConfirmationDialog"
    tabindex="-1"
    aria-labelledby="appConfirmationDialogTitle"
    aria-describedby="appConfirmationDialogMessage"
    aria-hidden="true"
    data-app-confirm-dialog
>
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content shadow border-0 rounded-3">
            <div class="modal-body p-4">
                <div class="d-flex align-items-start gap-3">
                    <div
                        class="d-inline-flex align-items-center justify-content-center rounded-circle bg-danger-subtle text-danger flex-shrink-0"
                        style="width:2.5rem;height:2.5rem;"
                        data-confirm-icon-wrap
                    >
                        <i class="fa-solid fa-triangle-exclamation" data-confirm-icon aria-hidden="true"></i>
                    </div>
                    <div class="min-w-0 pt-1">
                        <h2 class="h6 mb-1" id="appConfirmationDialogTitle" data-confirm-title>Confirm action</h2>
                        <p class="text-body-secondary small mb-0" id="appConfirmationDialogMessage" data-confirm-message>This action cannot be undone.</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 px-4 pb-4 gap-2">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger btn-sm" data-confirm-submit>
                    <span data-confirm-submit-label>Continue</span>
                </button>
            </div>
        </div>
    </div>
</div>
