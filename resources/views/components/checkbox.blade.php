@props([
    'label',
    'name',
    'errorKey' => null,
    'options' => [],
    'required' => false,
    'columns' => 5,
    'gridClass' => 'gap-2'
])

@php
    $fieldId = str_replace(['[', ']'], ['_', ''], $name);
    $errorId = $fieldId . '_error';
    $errorKey = $errorKey ?: $name;

    $wireModelAttrs = $attributes->whereStartsWith('wire:model');
    $wireModelKeys = array_keys($wireModelAttrs->getAttributes());
    $containerAttrs = $attributes->except($wireModelKeys);

    $useClientSideRequiredValidation = $required && count($wireModelKeys) === 0;
@endphp

<div class="mb-4" {{ $containerAttrs }}>
    <label class="block text-sm font-medium text-gray-700 mb-2">
        {{ $label }}
        @if($required) <span class="text-red-500">*</span> @endif
    </label>
    <div class="grid grid-cols-{{ $columns }} {{ $gridClass }}">
        @foreach($options as $value => $display)
            <div class="flex items-center">
                <input 
                    type="checkbox" 
                    id="{{ $fieldId }}_{{ $value }}" 
                    name="{{ $name }}[]" 
                    value="{{ $value }}" 
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    {{ $wireModelAttrs }}
                    @if($useClientSideRequiredValidation) onchange="validateCheckboxGroup('{{ $name }}', '{{ $errorId }}')" @endif
                >
                <label for="{{ $fieldId }}_{{ $value }}" class="ml-2 text-sm text-gray-700">{{ $display }}</label>
            </div>
        @endforeach
    </div>
    @if($useClientSideRequiredValidation)
        <div id="{{ $errorId }}" class="mt-1 text-sm text-red-600 hidden">Please select at least one option</div>
    @endif

    @error($errorKey)
        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>

@if($useClientSideRequiredValidation)
<script>
function validateCheckboxGroup(fieldName, errorId) {
    const checkboxes = document.querySelectorAll(`input[name="${fieldName}[]"]`);
    const errorDiv = document.getElementById(errorId);
    const isChecked = Array.from(checkboxes).some(checkbox => checkbox.checked);
    
    if (!isChecked) {
        errorDiv.classList.remove('hidden');
        return false;
    } else {
        errorDiv.classList.add('hidden');
        return true;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('step-form');
    if (form && !form.dataset.checkboxValidationAdded) {
        form.dataset.checkboxValidationAdded = 'true';
        form.addEventListener('submit', function(e) {
            const requiredGroups = document.querySelectorAll('[onchange*="validateCheckboxGroup"]');
            let isValid = true;
            
            requiredGroups.forEach(checkbox => {
                const fieldName = checkbox.name.replace('[]', '');
                const errorId = checkbox.id.replace(/_\d+$/, '_error');
                if (!validateCheckboxGroup(fieldName, errorId)) {
                    isValid = false;
                    document.getElementById(errorId).scrollIntoView({ behavior: 'smooth' });
                }
            });
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    }
}
</script>
@endif