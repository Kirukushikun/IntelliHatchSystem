@props([
    'label' => '',
    'name' => '',
    'errorKey' => null,
    'personnel' => [],
    'loggedInUserId' => null,
    'required' => false,
    'placeholder' => 'Select personnel',
])

@php
    $errorKey = $errorKey ?: $name;
    $wireModelAttrs = $attributes->whereStartsWith('wire:model');
    $personnelJson = json_encode(
        collect($personnel)->map(fn ($n, $id) => ['id' => (string) $id, 'name' => $n])->values()->toArray()
    );
@endphp

<div
    class="mb-4 sm:mb-6 relative"
    x-data="{
        open: false,
        fieldName: '{{ $name }}',
        personnel: {{ $personnelJson }},
        toggle() { this.open = !this.open },
        close() { this.open = false },
        get selected() {
            let val = $wire.form?.[this.fieldName];
            return Array.isArray(val) ? val.map(String) : [];
        },
        get summaryText() {
            let sel = this.selected;
            if (sel.length === 0) return '{{ $placeholder }}';
            let names = this.personnel.filter(p => sel.includes(p.id)).map(p => p.name);
            if (names.length <= 2) return names.join(', ');
            return names[0] + ' + ' + (names.length - 1) + ' more';
        }
    }"
    x-on:click.outside="close()"
    x-on:keydown.escape.window="close()"
>
    @if($label)
        <label class="block text-base sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    {{-- Dropdown trigger --}}
    <button
        type="button"
        x-on:click="toggle()"
        class="mt-1 w-full rounded-lg border px-4 py-3 sm:py-2 text-base sm:text-sm shadow-sm cursor-pointer text-left flex items-center justify-between bg-white dark:bg-gray-800 {{ $errors->has($errorKey) ? 'border-red-500 focus:border-red-500 focus:ring-red-200' : 'border-gray-300 dark:border-gray-600 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50' }}"
    >
        <span
            x-text="summaryText"
            class="truncate"
            :class="selected.length === 0 ? 'text-gray-400' : 'text-gray-900 dark:text-gray-100'"
        ></span>
        <svg class="h-4 w-4 text-gray-400 shrink-0 ml-2 transition-transform duration-200" :class="open && 'rotate-180'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
        </svg>
    </button>

    {{-- Dropdown panel --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute z-50 mt-1 w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 shadow-lg max-h-52 overflow-y-auto"
        x-cloak
    >
        @forelse($personnel as $id => $fullName)
            @php $isLoggedIn = $loggedInUserId !== null && (int) $id === (int) $loggedInUserId; @endphp
            <label class="flex items-center gap-2.5 px-4 py-2.5 sm:py-2 cursor-pointer select-none hover:bg-gray-50 dark:hover:bg-gray-700 {{ $isLoggedIn ? 'bg-indigo-50 dark:bg-indigo-900/20' : '' }}">
                <input
                    type="checkbox"
                    value="{{ $id }}"
                    class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 {{ $isLoggedIn ? 'cursor-not-allowed' : '' }}"
                    {{ $wireModelAttrs }}
                    @if($isLoggedIn) x-on:click.prevent @endif
                >
                <span class="text-base sm:text-sm text-gray-700 dark:text-gray-300">
                    {{ $fullName }}
                    @if($isLoggedIn)
                        <span class="text-xs text-gray-400 dark:text-gray-500">(You)</span>
                    @endif
                </span>
            </label>
        @empty
            <p class="text-sm text-gray-400 px-4 py-2">No personnel available.</p>
        @endforelse
    </div>

    @error($errorKey)
        <p class="text-red-500 text-sm sm:text-xs mt-1">{{ $message }}</p>
    @enderror
</div>
