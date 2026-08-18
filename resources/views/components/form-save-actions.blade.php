@props([
    'allowSaveNew' => false,
    'saveLabel' => 'Save & close',
    'newLabel' => 'Save & new',
])

@php
    $repetitiveCreate = in_array($saveLabel, ['Create announcement', 'Assign task'], true);
    $allowSaveNew = $allowSaveNew || $repetitiveCreate;
    $saveLabel = $repetitiveCreate ? 'Save & close' : $saveLabel;
@endphp

<div class="modal-footer form-save-actions">
    <button class="btn btn-light" type="button" data-bs-dismiss="modal">Close</button>
    @if ($allowSaveNew)
        <button class="btn btn-outline-primary" type="submit" name="save_action" value="new"><i class="fa-solid fa-plus me-1"></i>{{ $newLabel }}</button>
    @endif
    <button class="btn btn-primary" type="submit" name="save_action" value="close"><i class="fa-solid fa-floppy-disk me-1"></i>{{ $saveLabel }}</button>
</div>
