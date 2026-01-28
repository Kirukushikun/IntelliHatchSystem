@props(['currentStep' => 1, 'totalSteps'])

<div x-data="{ 
    currentStep: {{ $currentStep }}, 
    totalSteps: {{ $totalSteps }},
    init() {
        this.$watch('currentStep', () => {
            this.updateSteps();
        });
        this.updateSteps();
    },
    updateSteps() {
        // Hide all steps
        for (let i = 1; i <= this.totalSteps; i++) {
            const stepElement = document.getElementById(`step-${i}`);
            if (stepElement) {
                stepElement.style.display = i === this.currentStep ? 'block' : 'none';
            }
        }
    }
}">
    <!-- Progress Bar -->
    <div class="flex items-center justify-center space-x-1 mb-4">
        @for ($i = 1; $i <= $totalSteps; $i++)
            @if ($i < $totalSteps)
                <!-- Circle and connecting line -->
                <div class="flex items-center">
                    <div class="w-3 h-3 rounded-full" 
                         :class="currentStep >= {{ $i }} ? 'bg-orange-500' : (currentStep + 1 == {{ $i }} ? 'bg-gray-300' : 'bg-white border-2 border-gray-300')"></div>
                    <div class="w-4 h-0.5" 
                         :class="currentStep > {{ $i }} ? 'bg-orange-500' : 'bg-gray-300'"></div>
                </div>
            @else
                <!-- Last circle (no connecting line) -->
                <div class="w-3 h-3 rounded-full" 
                     :class="currentStep >= {{ $i }} ? 'bg-orange-500' : (currentStep + 1 == {{ $i }} ? 'bg-gray-300' : 'bg-white border-2 border-gray-300')"></div>
            @endif
        @endfor
    </div>

    <!-- Form Content Slot -->
    <div class="space-y-4">
        {{ $slot }}
    </div>

    <!-- Navigation (separate from slot) -->
    <div class="flex justify-between items-center my-6 w-full max-w-lg">
        <!-- Previous Button -->
        <x-button 
            x-show="currentStep > 1"
            @click="currentStep--"
            variant="outline-secondary"
            type="button"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </x-button>
        
        <div x-show="currentStep <= 1" class="w-8"></div> <!-- Spacer for consistent layout -->

        <!-- Next Button -->
        <x-button 
            x-show="currentStep < totalSteps"
            @click="currentStep++"
            variant="primary"
            type="button"
        >
            Next
            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </x-button>
        
        <!-- Submit Button -->
        <x-button 
            x-show="currentStep == totalSteps"
            variant="success"
            type="submit"
            form="step-form"
        >
            Submit
        </x-button>
    </div>
</div>
