@props(['searchPlaceholder' => 'Search...', 'hasStatus' => false, 'statusOptions' => [], 'statusValue' => '', 'hxTarget' => '#list-content', 'hxSwap' => 'innerHTML'])

<form 
    {{ $attributes->merge(['class' => 'reference-filter-form', 'method' => 'GET']) }}
    @if($attributes->has('hx-get'))
        {{ $attributes }}
    @else
        hx-get="{{ request()->url() }}"
        hx-target="{{ $hxTarget }}"
        hx-swap="{{ $hxSwap }}"
        hx-trigger="submit, keyup[keyCode=='Enter'] from input[name='search'], change from select[name='status']"
    @endif
>
    <x-search-input :placeholder="$searchPlaceholder" :hxTarget="$hxTarget" :hxSwap="$hxSwap" />
    
    @if($hasStatus && count($statusOptions) > 0)
        <select class="form-select reference-status" name="status" hx-trigger="change" hx-target="{{ $hxTarget }}" hx-swap="{{ $hxSwap }}">
            <option value="">All statuses</option>
            @foreach($statusOptions as $option)
                <option value="{{ $option }}" @selected($statusValue === $option)>
                    {{ ucfirst(str_replace('_', ' ', $option)) }}
                </option>
            @endforeach
        </select>
    @endif
    
    {{ $slot }}
    
    <button class="btn btn-primary reference-search-button" type="submit">Search</button>
</form>
