<x-layouts::app title="My profile">
    <x-workspace-command-bar title="My profile" icon="fa-user-gear" />
    @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
        @csrf @method('PUT')
        <div class="row g-3">
            <div class="col-lg-4">
                <div class="card h-100"><div class="card-header">Profile image</div><div class="card-body">
                    <label class="image-upload-zone" for="avatar">
                        @if($user->avatar_path)
                            <img id="avatar-preview" src="{{ route('profile.avatar') }}" alt="{{ $user->name }}">
                        @else
                            <img id="avatar-preview" src="" alt="" hidden>
                            <span class="image-upload-placeholder"><i class="fa-solid fa-cloud-arrow-up"></i><strong>Upload profile image</strong><small>JPG, PNG or WebP · max 2 MB<br>Square image recommended</small></span>
                        @endif
                    </label>
                    <input class="visually-hidden" id="avatar" name="avatar" type="file" accept="image/jpeg,image/png,image/webp" data-image-preview="avatar-preview">
                    @if($user->avatar_path)<label class="form-check mt-3"><input class="form-check-input" type="checkbox" name="remove_avatar" value="1"><span class="form-check-label">Remove current image</span></label>@endif
                </div></div>
            </div>
            <div class="col-lg-8">
                <div class="card h-100"><div class="card-header">Account information</div><div class="card-body row g-3">
                    <div class="col-md-6"><label class="form-label">Name</label><input class="form-control" name="name" value="{{ old('name', $user->name) }}" required></div>
                    <div class="col-md-6"><label class="form-label">Email address</label><input class="form-control" type="email" name="email" value="{{ old('email', $user->email) }}" required><div class="form-text">Changing email requires verification again.</div></div>
                    @if($user->employee)<div class="col-12"><div class="alert alert-light border mb-0"><i class="fa-solid fa-id-badge me-2 text-primary"></i>Linked employee: <a href="{{ route('employees.show', $user->employee) }}">{{ $user->employee->getFullName() }} · {{ $user->employee->employee_code }}</a></div></div>@endif
                </div></div>
            </div>
        </div>
        <div class="d-flex justify-content-end gap-2 mt-3"><a class="btn btn-light" href="{{ route('dashboard') }}">Close</a><button class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Save profile</button></div>
    </form>
</x-layouts::app>
