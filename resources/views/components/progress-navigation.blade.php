@props(['currentStep' => 1, 'totalSteps'])

<div 
    x-data="stepForm({{ json_encode($currentStep) }}, {{ json_encode($totalSteps) }})"
    x-init="initialize()"
>
    <!-- Progress Bar -->
    <div class="flex items-center justify-center space-x-1 mb-4">
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
                    this.showErrorMessage('Please fill in all required fields before submitting.');
                    return false;
                }
            }

            document.getElementById('step-form').submit();
        },

        showErrorMessage(message) {
            const existing = document.querySelector('.validation-error');
            if (existing) existing.remove();

            const div = document.createElement('div');
            div.className = 'validation-error bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4';

            div.innerHTML = `
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                    ${message}
                </div>
            `;

            const form = document.getElementById('step-form');
            form.insertBefore(div, form.firstChild);

            setTimeout(() => {
                if (div.parentNode) div.remove();
            }, 5000);
        }
    };
}
</script>
