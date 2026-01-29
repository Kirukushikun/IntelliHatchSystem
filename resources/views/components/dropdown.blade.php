@props([
    'label' => '',
    'name' => '',
    'required' => false,
    'placeholder' => 'Select an option'
])

<style>
    select:invalid { color: #9ca3af; }
    select option { color: black; }
</style>

<div class="mb-6">
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 mb-1">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    <select 
        id="{{ $name }}"
        name="{{ $name }}"
        @if($required) required @endif
        class="mt-1 block w-full rounded-lg border px-4 py-2 shadow-sm cursor-pointer
            {{ $errors->has($name) 
                ? 'border-red-500 focus:border-red-500 focus:ring-red-200' 
                : 'border-gray-300 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50' }}"
    >
        <option value="" disabled selected hidden>{{ $placeholder }}</option>
        {{ $slot }}
    </select>

    @error($name)
        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>