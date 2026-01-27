@props([
    'label' => '', 
    'name' => '', 
    'required' => false
])

<div class="mb-6">
    @if($label)
        <label class="block text-sm font-medium text-gray-700 mb-1">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    <!-- Label triggers input -->
    <label 
        for="{{ $name }}" 
        class="mt-1 flex items-center justify-center px-4 py-6 border-2 border-dashed rounded-lg cursor-pointer hover:border-indigo-500 transition border-gray-300"
    >
        <svg class="w-5 h-5 text-gray-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
            <circle cx="8.5" cy="8.5" r="2.5"/>
            <path d="M16 10c-2 0-3 3-4.5 3s-1.499-1-3.5-1c-2 0-3.001 4-3.001 4h14.001s-1-6-3-6zM20 3h-16c-1.103 0-2 .897-2 2v12c0 1.103.897 2 2 2h16c1.103 0 2-.897 2-2v-12c0-1.103-.897-2-2-2zm0 14h-16v-12h16v12z"/>
        </svg>

        <span class="ml-2 text-gray-600">Tap to upload a photo</span>
    </label>

    <!-- Hidden file input -->
    <input 
        type="file" 
        name="{{ $name }}" 
        id="{{ $name }}" 
        class="hidden"
        @if($required) required @endif
    >
</div>
