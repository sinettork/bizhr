@props(['asset' => null])

<div class="row g-3">
    <div class="col-12">
        <div class="d-flex align-items-center gap-3 p-3 border rounded-3 bg-body-tertiary">
            <div class="flex-shrink-0 rounded-3 overflow-hidden border bg-white d-flex align-items-center justify-content-center" style="width:72px;height:72px;">
                @if($asset?->image_path)
                    <img src="{{ asset('storage/'.$asset->image_path) }}" alt="{{ $asset->name }}" class="w-100 h-100" style="object-fit:cover;">
                @else
                    <i class="fa-solid fa-image text-body-secondary fs-4"></i>
                @endif
            </div>
            <div class="flex-grow-1 min-w-0">
                <label class="form-label mb-1">Asset photo</label>
                <input class="form-control" type="file" name="image" accept="image/jpeg,image/png,image/webp">
                <div class="form-text">JPG, PNG or WebP · max 4 MB · recommended square or 4:3 image.</div>
            </div>
        </div>
    </div>

    <div class="col-md-4"><label class="form-label">Asset code</label><input class="form-control" name="asset_code" value="{{ old('asset_code', $asset?->asset_code) }}" required></div>
    <div class="col-md-4"><label class="form-label">Name</label><input class="form-control" name="name" value="{{ old('name', $asset?->name) }}" required></div>
    <div class="col-md-4"><label class="form-label">Category</label><input class="form-control" name="category" value="{{ old('category', $asset?->category) }}" required></div>
    <div class="col-md-4"><label class="form-label">Serial number</label><input class="form-control" name="serial_number" value="{{ old('serial_number', $asset?->serial_number) }}"></div>
    <div class="col-md-2"><label class="form-label">Purchase date</label><input class="form-control" type="date" name="purchase_date" value="{{ old('purchase_date', $asset?->purchase_date?->toDateString()) }}"></div>
    <div class="col-md-2"><label class="form-label">Cost</label><input class="form-control" type="number" step=".01" name="purchase_cost" value="{{ old('purchase_cost', $asset?->purchase_cost) }}"></div>
    <div class="col-md-2"><label class="form-label">Currency</label><select class="form-select" name="currency">@foreach(['USD','KHR'] as $currency)<option @selected(old('currency', $asset?->currency ?? 'USD') === $currency)>{{ $currency }}</option>@endforeach</select></div>
    <div class="col-md-2"><label class="form-label">Condition</label><select class="form-select" name="condition">@foreach(['new','good','fair','poor'] as $condition)<option @selected(old('condition', $asset?->condition ?? 'good') === $condition)>{{ $condition }}</option>@endforeach</select></div>
    <div class="col-12"><label class="form-label">Notes</label><textarea class="form-control" name="notes">{{ old('notes', $asset?->notes) }}</textarea></div>
</div>
