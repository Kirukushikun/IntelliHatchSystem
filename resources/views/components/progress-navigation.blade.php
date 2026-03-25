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
    <div class="flex justify-between items-center my-6 w-full max-w-lg">

        <div class="flex items-center">
            @if(((int) $currentStep) > 1)
                <x-button
                    wire:click="previousStep"
                    wire:loading.attr="disabled"
                    wire:target="previousStep,nextStep,submitForm"
                    variant="outline-secondary"
                    type="button"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                <span class="text-xs text-gray-400 dark:text-gray-500 tabular-nums">
                    {{ $currentIndex + 1 }} / {{ $totalSteps }}
                </span>
                <div class="w-full h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
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
                    Next
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
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
                    Submit
                </x-button>
            @endif
        </div>

    </div>
</div>