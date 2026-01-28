@props(['currentStep' => 1, 'totalSteps'])

<div 
    x-data="stepForm({{ json_encode($currentStep) }}, {{ json_encode($totalSteps) }})"
    x-init="initialize()"
>
    <!-- Progress Bar -->
    <div class="flex items-center justify-center mb-4">
        @for ($i = 1; $i <= $totalSteps; $i++)
            @if ($i < $totalSteps)
                <div class="flex items-center">
                    <div class="w-3 h-3 rounded-full" 
                         :class="currentStep >= {{ $i }} ? 'bg-orange-500' : (currentStep + 1 == {{ $i }} ? 'bg-gray-300' : 'bg-white border-2 border-gray-300')"></div>
                    <div class="w-4 h-0.5" 
                         :class="currentStep > {{ $i }} ? 'bg-orange-500' : 'bg-gray-300'"></div>
                </div>
            @else
                <div class="w-3 h-3 rounded-full" 
                     :class="currentStep >= {{ $i }} ? 'bg-orange-500' : (currentStep + 1 == {{ $i }} ? 'bg-gray-300' : 'bg-white border-2 border-gray-300')"></div>
            @endif
        @endfor
    </div>

    <!-- Slot content (steps) -->
    <div class="space-y-4">
        {{ $slot }}
    </div>

    <!-- Navigation -->
    <div class="flex justify-between items-center my-6 w-full max-w-lg">

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

        <div x-show="currentStep <= 1" class="w-8"></div>

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

        <x-button 
            x-show="currentStep == totalSteps"
            variant="success"
            type="button"
            @click="validateAndSubmit()"
        >
            Submit
        </x-button>

    </div>
</div>

<script>
function stepForm(currentStep, totalSteps) {
    return {
        currentStep,
        totalSteps,

        initialize() {
            this.updateSteps();

            this.$watch('currentStep', () => {
                this.updateSteps();
            });
        },

        updateSteps() {
            for (let i = 1; i <= this.totalSteps; i++) {
                const step = document.getElementById(`step-${i}`);
                if (step) {
                    step.style.display = i === this.currentStep ? 'block' : 'none';
                }
            }
        },

        validateAndSubmit() {
            for (let i = 1; i <= this.totalSteps; i++) {
                const step = document.getElementById(`step-${i}`);
                if (!step) continue;

                const requiredFields = step.querySelectorAll('[required]');
                let hasError = false;

                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        hasError = true;
                    }
                });

                if (hasError) {
                    this.currentStep = i;
                    this.updateSteps();
                    step.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    return false;
                }
            }

            document.getElementById('step-form').submit();
        },
    };
}
</script>
