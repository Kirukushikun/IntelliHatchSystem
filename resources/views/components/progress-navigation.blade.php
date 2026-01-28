@props(['currentStep' => 1, 'totalSteps', 'schedule' => null])

<div 
    x-data="stepForm({{ json_encode($currentStep) }}, {{ json_encode($totalSteps) }}, {{ json_encode($schedule) }})"
    x-init="initialize()"
>
    <!-- Progress Bar -->
    <div class="flex items-center justify-center mb-4">
        <template x-for="i in totalSteps" :key="i">
            <div class="flex items-center">
                <div class="w-3 h-3 rounded-full" 
                     :class="currentStep >= i ? 'bg-orange-500' : (currentStep + 1 == i ? 'bg-gray-300' : 'bg-white border-2 border-gray-300')"></div>
                <div x-show="i < totalSteps" class="w-4 h-0.5" 
                     :class="currentStep > i ? 'bg-orange-500' : 'bg-gray-300'"></div>
            </div>
        </template>
    </div>

    <!-- Slot content (steps) -->
    <div class="space-y-4">
        {{ $slot }}
    </div>

    <!-- Navigation -->
    <div class="flex justify-between items-center my-6 w-full max-w-lg">

        <x-button 
            x-show="currentStep > 1"
            @click="previousStep()"
            variant="outline-secondary"
            type="button"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </x-button>

        <div x-show="currentStep <= 1" class="w-8"></div>

        <x-button 
            x-show="!isLastVisibleStep()"
            @click="nextStep()"
            variant="primary"
            type="button"
            ::disabled="!canProceed()"
        >
            Next
            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </x-button>

        <x-button 
            x-show="isLastVisibleStep()"
            variant="success"
            type="button"
            @click="validateAndSubmit()"
        >
            Submit
        </x-button>

    </div>
</div>

<script>
function stepForm(currentStep, totalSteps, schedule = null) {
    return {
        currentStep,
        totalSteps,
        selectedShift: '',
        currentDay: '',
        schedule: schedule || {},

        initialize() {
            // Get current day in Asia/Manila timezone
            this.currentDay = this.getCurrentDay();
            
            // Start with only step 1 visible if schedule filtering is enabled
            if (this.schedule && Object.keys(this.schedule).length > 0) {
                this.totalSteps = 1;
            }
            
            this.updateSteps();

            this.$watch('currentStep', () => {
                this.updateSteps();
            });

            // Only set up schedule filtering if schedule is provided
            if (this.schedule && Object.keys(this.schedule).length > 0) {
                // Watch for shift selection
                const shiftDropdown = document.querySelector('select[name="shift"]');
                if (shiftDropdown) {
                    shiftDropdown.addEventListener('change', (e) => {
                        this.selectedShift = e.target.value;
                        
                        if (this.selectedShift) {
                            // Filter fields based on schedule
                            this.filterFieldsBySchedule();
                            // Recalculate which steps are visible
                            this.recalculateSteps();
                        } else {
                            // If shift is deselected, reset to initial state
                            this.totalSteps = 1;
                            this.currentStep = 1;
                            this.updateSteps();
                        }
                    });
                }
            }
        },

        getCurrentDay() {
            // Get current date in Asia/Manila timezone
            const options = { timeZone: 'Asia/Manila', weekday: 'long' };
            const formatter = new Intl.DateTimeFormat('en-US', options);
            return formatter.format(new Date());
        },

        filterFieldsBySchedule() {
            if (!this.selectedShift || !this.schedule || Object.keys(this.schedule).length === 0) return;

            const scheduleKey = `${this.currentDay}-${this.selectedShift}`;
            const allowedFields = this.schedule[scheduleKey] || [];

            console.log('Current Day:', this.currentDay);
            console.log('Selected Shift:', this.selectedShift);
            console.log('Schedule Key:', scheduleKey);
            console.log('Allowed Fields:', allowedFields);

            // Get daily fields from schedule config if provided
            const dailyFields = this.schedule._daily || [];
            console.log('Daily Fields:', dailyFields);

            // Get all step divs (not the form)
            for (let i = 2; i <= 5; i++) {
                const step = document.getElementById(`step-${i}`);
                if (!step) continue;
                
                // Find all direct child divs that contain fields (but not the title)
                const allChildren = Array.from(step.children);
                const fieldContainers = allChildren.filter(child => {
                    // Skip if it's not a div or if it's the space-y-4 wrapper
                    if (child.tagName !== 'DIV') return false;
                    if (child.classList.contains('space-y-4')) return false;
                    
                    // Check if it has a field inside
                    const hasField = child.querySelector('select[name], input[name], textarea[name]');
                    return hasField !== null;
                });
                
                console.log(`Step ${step.id} containers:`, fieldContainers.length);
                
                let hasVisibleFields = false;
                
                fieldContainers.forEach(container => {
                    // Get the main field (dropdown) in this container
                    const mainField = container.querySelector('select[name], input[name], textarea[name]');
                    
                    if (!mainField) return;
                    
                    const fieldName = mainField.getAttribute('name');
                    if (!fieldName || fieldName === '_token') return;
                    
                    console.log(`Checking field: ${fieldName}, Daily: ${dailyFields.includes(fieldName)}, Allowed: ${allowedFields.includes(fieldName)}`);
                    
                    // Check if this field should be visible
                    const shouldShow = dailyFields.includes(fieldName) || allowedFields.includes(fieldName);
                    
                    if (shouldShow) {
                        hasVisibleFields = true;
                        container.style.display = '';
                        // Enable all fields in this container
                        container.querySelectorAll('[name]').forEach(f => f.removeAttribute('disabled'));
                    } else {
                        container.style.display = 'none';
                        // Disable all fields in this container
                        container.querySelectorAll('[name]').forEach(f => {
                            f.setAttribute('disabled', 'disabled');
                            f.removeAttribute('required');
                        });
                    }
                });
                
                console.log(`Step ${step.id} has visible fields:`, hasVisibleFields);
                
                // Mark step as empty if no visible fields
                if (!hasVisibleFields) {
                    step.setAttribute('data-empty', 'true');
                    step.style.display = 'none';
                } else {
                    step.removeAttribute('data-empty');
                }
            }

            // Hide empty steps after filtering
            this.hideEmptySteps();
        },

        hideEmptySteps() {
            for (let i = 2; i <= 5; i++) { // Start from 2, step 1 is always visible
                const step = document.getElementById(`step-${i}`);
                if (!step) continue;

                // The step should already be marked as empty by filterFieldsBySchedule
                // Just ensure it's hidden
                if (step.hasAttribute('data-empty')) {
                    step.style.display = 'none';
                    console.log(`Step ${step.id} is empty and hidden`);
                } else {
                    console.log(`Step ${step.id} is visible`);
                }
            }
        },

        recalculateSteps() {
            // Count visible steps (step 1 + non-empty steps)
            let visibleSteps = 1; // Step 1 is always counted
            
            for (let i = 2; i <= 5; i++) {
                const step = document.getElementById(`step-${i}`);
                if (step && !step.hasAttribute('data-empty')) {
                    visibleSteps++;
                }
            }
            
            this.totalSteps = visibleSteps;
            
            // Reset to step 1 when recalculating (after shift selection)
            this.currentStep = 1;
            
            // Update step display
            this.updateSteps();
        },

        getVisibleStepIds() {
            const visibleIds = [1]; // Step 1 is always visible
            
            for (let i = 2; i <= 5; i++) {
                const step = document.getElementById(`step-${i}`);
                if (step && !step.hasAttribute('data-empty')) {
                    visibleIds.push(i);
                }
            }
            
            console.log('Visible step IDs:', visibleIds); // Debug log
            return visibleIds;
        },

        getNextVisibleStep() {
            const visibleIds = this.getVisibleStepIds();
            const currentIndex = visibleIds.indexOf(this.currentStep);
            
            if (currentIndex !== -1 && currentIndex < visibleIds.length - 1) {
                return visibleIds[currentIndex + 1];
            }
            
            return null;
        },

        getPreviousVisibleStep() {
            const visibleIds = this.getVisibleStepIds();
            const currentIndex = visibleIds.indexOf(this.currentStep);
            
            if (currentIndex > 0) {
                return visibleIds[currentIndex - 1];
            }
            
            return null;
        },

        nextStep() {
            const nextStep = this.getNextVisibleStep();
            if (nextStep) {
                this.currentStep = nextStep;
            }
        },

        previousStep() {
            const prevStep = this.getPreviousVisibleStep();
            if (prevStep) {
                this.currentStep = prevStep;
            }
        },

        canProceed() {
            // If schedule is provided and we're on step 1, require shift selection
            if (this.schedule && Object.keys(this.schedule).length > 0 && this.currentStep === 1) {
                return this.selectedShift !== '';
            }
            return true;
        },

        isLastVisibleStep() {
            const visibleIds = this.getVisibleStepIds();
            return this.currentStep === visibleIds[visibleIds.length - 1];
        },

        updateSteps() {
            const visibleIds = this.getVisibleStepIds();
            
            // If current step is not in visible list, move to nearest visible step
            if (!visibleIds.includes(this.currentStep)) {
                // Find the closest visible step
                for (let i = this.currentStep; i >= 1; i--) {
                    if (visibleIds.includes(i)) {
                        this.currentStep = i;
                        break;
                    }
                }
            }
            
            // Hide all steps first
            for (let i = 1; i <= 5; i++) {
                const step = document.getElementById(`step-${i}`);
                if (step) {
                    step.style.display = 'none';
                }
            }
            
            // Show only the current step if it's visible
            const currentStepElement = document.getElementById(`step-${this.currentStep}`);
            if (currentStepElement && !currentStepElement.hasAttribute('data-empty')) {
                currentStepElement.style.display = 'block';
            }
        },

        validateAndSubmit() {
            const visibleIds = this.getVisibleStepIds();
            
            for (const stepId of visibleIds) {
                const step = document.getElementById(`step-${stepId}`);
                if (!step) continue;

                const requiredFields = step.querySelectorAll('[required]:not([disabled])');
                let hasError = false;

                requiredFields.forEach(field => {
                    const container = field.closest('div');
                    if (container && container.style.display === 'none') return;
                    
                    if (!field.value.trim()) {
                        hasError = true;
                    }
                });

                if (hasError) {
                    this.currentStep = stepId;
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