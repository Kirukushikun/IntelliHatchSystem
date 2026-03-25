<div x-data="{ formSubmitted: @entangle('formSubmitted') }">
    <form wire:submit.prevent="submitForm" id="step-form" class="space-y-4" novalidate>
        @csrf

        <x-progress-navigation
            :current-step="$currentStep"
            :visible-step-ids="$visibleStepIds"
            :can-proceed="$this->canProceed()"
            :is-last-visible-step="$this->isLastVisibleStep()"
            :show-progress="$this->showProgress()"
        >
            {{-- Step 1: Header Information --}}
            <div data-step="1" class="space-y-4" @style(["display:none" => $currentStep !== 1])>
                <x-title>WEEKLY VOLTAGE AND AMPERE MONITORING</x-title>

                <div data-field="maintenance_personnel">
                    <x-dropdown
                        label="Maintenance Personnel"
                        name="maintenance_personnel"
                        error-key="form.maintenance_personnel"
                        placeholder="Select personnel"
                        wire:model.live="form.maintenance_personnel"
                        required
                    >
                        @foreach($personnelList as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </x-dropdown>
                </div>

                <div data-field="date">
                    <x-text-input
                        label="Date"
                        name="date"
                        error-key="form.date"
                        :required="true"
                        placeholder="Select a date"
                        wireModel="form.date"
                        type="date"
                    />
                </div>

                <div data-field="time_started">
                    <x-text-input
                        label="Time Started"
                        name="time_started"
                        error-key="form.time_started"
                        :required="true"
                        placeholder="Select time"
                        wireModel="form.time_started"
                        type="time"
                    />
                </div>
            </div>

            {{-- Step 2: Readings and Observations --}}
            <div data-step="2" class="space-y-4" @style(["display:none" => $currentStep !== 2])>
                <x-title>READINGS AND OBSERVATIONS</x-title>

                <div data-field="voltage_readings">
                    <x-text-area
                        label="Voltage Readings"
                        name="voltage_readings"
                        error-key="form.voltage_readings"
                        :required="true"
                        placeholder="Ex.: Motor and Location - 220V"
                        wire:model.live="form.voltage_readings"
                    />
                </div>

                <div data-field="ampere_readings">
                    <x-text-area
                        label="Ampere Readings"
                        name="ampere_readings"
                        error-key="form.ampere_readings"
                        :required="true"
                        placeholder="Ex.: Motor and location - 20A"
                        wire:model.live="form.ampere_readings"
                    />
                </div>

                <div data-field="voltage_ampere_photos">
                    <x-photo-attach label="Photos of Voltage and Ampere Readings" name="voltage_ampere_photos" required />
                </div>

                <div data-field="problem_corrective_action">
                    <x-text-area
                        label="Problem Encountered and Corrective Action Taken"
                        name="problem_corrective_action"
                        error-key="form.problem_corrective_action"
                        :required="true"
                        placeholder="Enter your answer"
                        wire:model.live="form.problem_corrective_action"
                    />
                </div>

                <div data-field="problem_photos">
                    <x-photo-attach label="Photos of Problem Encountered and Corrective Action Taken" name="problem_photos" />
                </div>

                <div data-field="time_finished">
                    <x-text-input
                        label="Time Finished"
                        name="time_finished"
                        error-key="form.time_finished"
                        :required="true"
                        placeholder="Select time"
                        wireModel="form.time_finished"
                        type="time"
                    />
                </div>
            </div>
        </x-progress-navigation>
    </form>
</div>
