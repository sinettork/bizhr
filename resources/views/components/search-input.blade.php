@props(['placeholder' => 'Search...', 'value' => null, 'hxTarget' => '#list-content', 'hxSwap' => 'innerHTML', 'hxTrigger' => 'keyup changed delay:500ms'])

<div class="input-group reference-search" {{ $attributes }}>
    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
    <input 
        class="form-control" 
        name="search" 
        value="{{ $value ?? request('search', '') }}" 
        placeholder="{{ $placeholder }}"
        hx-get="{{ request()->url() }}"
        hx-target="{{ $hxTarget }}"
        hx-swap="{{ $hxSwap }}"
        hx-trigger="{{ $hxTrigger }}"
        hx-push-url="false"
        {{ $attributes->whereStartsWith('aria') }}
    >
</div>
