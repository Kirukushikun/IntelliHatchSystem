@props(['label' => '', 'name' => '', 'value' => ''])

<div class="mb-4">
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-gray-700">{{ $label }}</label>
    @endif
    <textarea 
        name="{{ $name }}" 
        id="{{ $name }}" 
        rows="4" 
        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
    >{{ $value }}</textarea>
</div>
