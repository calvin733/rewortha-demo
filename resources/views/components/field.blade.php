@props(['name', 'label', 'type' => 'text', 'value' => '', 'help' => null, 'textarea' => false])

@php
    $displayValue = $type === 'password' ? '' : old($name, $value);
    $displayValue = is_scalar($displayValue) ? (string) $displayValue : '';
    $invalid = $errors->has($name);
    $descriptionIds = array_filter([$help ? $name.'_help' : null, $invalid ? $name.'_error' : null]);
    $controlAttributes = $attributes->merge([
        'class' => 'field-input',
        'aria-invalid' => $invalid ? 'true' : 'false',
        'aria-describedby' => $descriptionIds ? implode(' ', $descriptionIds) : null,
    ]);
@endphp

<div class="min-w-0">
    <label for="{{ $name }}" class="field-label">
        {{ $label }}
        @if($attributes->has('required'))<span class="text-primary" aria-hidden="true">*</span>@endif
    </label>

    @if($type === 'select')
        <select id="{{ $name }}" name="{{ $name }}" {{ $controlAttributes }}>{{ $slot }}</select>
    @elseif($textarea)
        <textarea id="{{ $name }}" name="{{ $name }}" rows="4" {{ $controlAttributes }}>{{ $displayValue }}</textarea>
    @else
        <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ $displayValue }}" {{ $controlAttributes }}>
    @endif

    @if($help)<p id="{{ $name }}_help" class="field-help">{{ $help }}</p>@endif
    @error($name)<p id="{{ $name }}_error" class="field-error">{{ $message }}</p>@enderror
</div>
