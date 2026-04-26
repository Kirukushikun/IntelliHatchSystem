@props([
    'currentStep' => 1,
    'visibleStepIds' => [1],
    'canProceed' => true,
    'isLastVisibleStep' => false,
    'showProgress' => false,
])

@php
    $visibleStepIds = array_values(array_unique(array_map('intval', $visibleStepIds ?? [1])));
    sort($visibleStepIds);

    $currentIndex = array_search((int) $currentStep, $visibleStepIds, true);
    $currentIndex = $currentIndex === false ? 0 : $currentIndex;
@endphp

<div>
    <!-- Slot content (steps) -->
    <div class="space-y-4">
        {{ $slot }}
    </div>

    <!-- Navigation with inline progress -->
    <div class="flex justify-between items-center my-4 sm:my-6 w-full max-w-lg">

        <div class="flex items-center">
            @if(((int) $currentStep) > 1)
                <x-button
                    wire:click="previousStep"
                    wire:loading.attr="disabled"
                    wire:target="previousStep,nextStep,submitForm"
                    variant="outline-secondary"
                    type="button"
                >
                    <svg wire:loading wire:target="previousStep"
                         class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <svg wire:loading.remove wire:target="previousStep"
                         class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </x-button>
            @else
                <div class="w-8"></div>
            @endif
        </div>

        <!-- Progress bar in the center -->
        @if($showProgress)
            @php
                $totalSteps = count($visibleStepIds);
                $progressPct = $totalSteps > 1 ? round(($currentIndex / ($totalSteps - 1)) * 100) : 100;
            @endphp
            <div class="flex flex-col items-center gap-1 flex-1 mx-3">
                <span class="text-sm sm:text-xs text-gray-400 dark:text-gray-500 tabular-nums">
                    {{ $currentIndex + 1 }} / {{ $totalSteps }}
                </span>
                <div class="w-full h-2 sm:h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                    <div class="h-full bg-orange-500 rounded-full transition-all duration-300"
                         style="width: {{ $progressPct }}%"></div>
                </div>
            </div>
        @endif

        <div class="flex items-center">
            @if(!$isLastVisibleStep)
                <x-button
                    wire:click="nextStep"
                    wire:loading.attr="disabled"
                    wire:target="previousStep,nextStep,submitForm"
                    variant="primary"
                    type="button"
                    :disabled="!$canProceed"
                >
                    <span wire:loading.remove wire:target="nextStep">Next</span>
                    <svg wire:loading.remove wire:target="nextStep"
                         class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                    <svg wire:loading wire:target="nextStep"
                         class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </x-button>
            @else
                <x-button
                    wire:click="submitForm"
                    wire:loading.attr="disabled"
                    wire:target="previousStep,nextStep,submitForm"
                    variant="success"
                    type="button"
                >
                    <span wire:loading.remove wire:target="submitForm">Submit</span>
                    <svg wire:loading wire:target="submitForm"
                         class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </x-button>
            @endif
        </div>

    </div>

    {{-- Fullscreen submission overlay --}}
    <div wire:loading wire:target="submitForm"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 dark:bg-black/60 backdrop-blur-sm">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl px-8 py-6 flex flex-col items-center gap-3 max-w-xs mx-4">
            <svg class="animate-spin w-8 h-8 text-orange-500" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p class="text-sm font-medium text-gray-700 dark:text-gray-200">Submitting form...</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">Please do not close this page.</p>
        </div>
    </div>
</div>