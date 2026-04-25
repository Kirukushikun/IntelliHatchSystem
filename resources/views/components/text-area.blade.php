@props([
    'label' => '', 
    'name' => '', 
    'errorKey' => null,
    'value' => '', 
    'placeholder' => 'Enter text here', 
    'required' => false,
    'subtext' => ''
])

@php
    $errorKey = $errorKey ?: $name;
@endphp

<div class="mb-4 sm:mb-6">
    @if($label)
        <label for="{{ $name }}" class="block text-base sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
        @if($subtext)
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">{!! $subtext !!}</p>
        @endif
    @endif

    <textarea
        id="{{ $name }}"
        name="{{ $name }}"
        rows="4"
        placeholder="{{ $placeholder }}"
        @if($required) required @endif
        {{ $attributes->merge(['class' => "mt-1 block w-full rounded-lg border px-4 py-3 sm:py-2 text-base sm:text-sm shadow-sm " . (
            $errors->has($errorKey)
                ? 'border-red-500 focus:border-red-500 focus:ring-red-200'
                : 'border-gray-300 dark:border-gray-600 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50'
        )]) }}>{{ old($name, $value) }}</textarea>

    @error($errorKey)
        <p class="text-red-500 text-sm sm:text-xs mt-1">{{ $message }}</p>
    @enderror
</div>
