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
            <div data-step="1" class="space-y-4" @style(["display:none" => $currentStep !== 1])>
                <x-title>HATCHERY SULLAIR AIR COMPRESSOR WEEKLY PMS CHECKLIST</x-title>

                <div data-field="hatchery_man">
                    <x-dropdown label="Hatchery Man" name="hatchery_man" error-key="form.hatchery_man" placeholder="Select hatchery man" wire:model.live="form.hatchery_man" required>
                        @foreach($hatcheryMen as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </x-dropdown>
                </div>

                <div data-field="cellphone_number">
                    <x-text-input label="Cellphone Number" name="cellphone_number" :required="true" placeholder="Enter cellphone number..." wireModel="form.cellphone_number" />
                </div>

                <div data-field="sullair_number">
                    <x-dropdown label="Sullair Number" name="sullair_number" error-key="form.sullair_number" placeholder="Select sullair" wire:model.live="form.sullair_number" required>
                        <option value="Sullair 1 (Inside Incubation Area)">Sullair 1 (Inside Incubation Area)</option>
                        <option value="Sullair 2 (Maintenance Area)">Sullair 2 (Maintenance Area)</option>
                    </x-dropdown>
                </div>
            </div>

            <div data-step="2" class="space-y-4" @style(["display:none" => $currentStep !== 2])>
                <x-title>READINGS</x-title>

                <div data-field="actual_psi_reading">
                    <x-text-input label="Actual PSI Reading" name="actual_psi_reading" :required="true" placeholder="Enter PSI reading..." wireModel="form.actual_psi_reading" subtext="Required: 60 psi to 90 psi" />
                </div>

                <div data-field="actual_temperature_reading">
                    <x-text-input label="Actual Temperature Reading" name="actual_temperature_reading" :required="true" placeholder="Enter temperature reading..." wireModel="form.actual_temperature_reading" subtext="150 F to 200 F" />
                </div>

                <div data-field="actual_psi_temperature_photos">
                    <x-photo-attach label="Photos of Actual PSI and Temperature Reading" name="actual_psi_temperature_photos"/>
                </div>

                <div data-field="actual_volt_reading">
                    <x-text-input label="Actual Volt Reading" name="actual_volt_reading" :required="true" placeholder="Enter volt reading..." wireModel="form.actual_volt_reading" />
                </div>

                <div data-field="actual_volt_photos">
                    <x-photo-attach label="Photos" name="actual_volt_photos"/>
                </div>

                <div data-field="actual_ampere_reading">
                    <x-text-input label="Actual Ampere Reading" name="actual_ampere_reading" :required="true" placeholder="Enter ampere reading..." wireModel="form.actual_ampere_reading" />
                </div>

                <div data-field="actual_ampere_photos">
                    <x-photo-attach label="Photos" name="actual_ampere_photos"/>
                </div>
            </div>

            <div data-step="3" class="space-y-4" @style(["display:none" => $currentStep !== 3])>
                <x-title>CONDITION STATUS</x-title>

                <div data-field="status_wiring_lugs_control">
                    <x-dropdown label="Status of wiring, lugs and control" name="status_wiring_lugs_control" error-key="form.status_wiring_lugs_control" placeholder="Select status" wire:model.live="form.status_wiring_lugs_control" required>
                        <option value="Good Condition">Good Condition</option>
                        <option value="With Minimal Damage">With Minimal Damage</option>
                        <option value="For Replacement">For Replacement</option>
                    </x-dropdown>
                    <x-photo-attach label="Photos" name="status_wiring_lugs_control_photos"/>
                </div>

                <div data-field="status_solenoid_valve">
                    <x-dropdown label="Status of Solenoid valve" name="status_solenoid_valve" error-key="form.status_solenoid_valve" placeholder="Select status" wire:model.live="form.status_solenoid_valve" required>
                        <option value="Good Condition">Good Condition</option>
                        <option value="With Minimal Damage">With Minimal Damage</option>
                        <option value="For Replacement">For Replacement</option>
                    </x-dropdown>
                    <x-photo-attach label="Photos" name="status_solenoid_valve_photos"/>
                </div>

                <div data-field="status_fan_motor">
                    <x-dropdown label="Status of Fan Motor" name="status_fan_motor" error-key="form.status_fan_motor" placeholder="Select status" wire:model.live="form.status_fan_motor" required>
                        <option value="Good Condition">Good Condition</option>
                        <option value="With Minimal Damage">With Minimal Damage</option>
                        <option value="For Replacement">For Replacement</option>
                    </x-dropdown>
                    <x-photo-attach label="Photos" name="status_fan_motor_photos"/>
                </div>
            </div>

            <div data-step="4" class="space-y-4" @style(["display:none" => $currentStep !== 4])>
                <x-title>HOSE / OIL / BELT</x-title>

                <div data-field="status_hose">
                    <x-dropdown label="Status of Hose" name="status_hose" error-key="form.status_hose" placeholder="Select status" wire:model.live="form.status_hose" required>
                        <option value="No Leak">No Leak</option>
                        <option value="With Leak for Repair">With Leak for Repair</option>
                        <option value="With Leak for Replacement">With Leak for Replacement</option>
                    </x-dropdown>
                    <x-photo-attach label="Photos" name="status_hose_photos"/>
                </div>

                <div data-field="actual_oil_level_status">
                    <x-dropdown label="Actual Oil Level Status" name="actual_oil_level_status" error-key="form.actual_oil_level_status" placeholder="Select status" wire:model.live="form.actual_oil_level_status" required>
                        <option value="Above or On Its Level Requirement">Above or On Its Level Requirement</option>
                        <option value="For Refill">For Refill</option>
                    </x-dropdown>
                    <x-photo-attach label="Photos" name="actual_oil_level_status_photos"/>
                </div>

                <div data-field="tension_belt_status">
                    <x-dropdown label="Tension Belt Status" name="tension_belt_status" error-key="form.tension_belt_status" placeholder="Select status" wire:model.live="form.tension_belt_status" required>
                        <option value="Good Condition">Good Condition</option>
                        <option value="For Replacement">For Replacement</option>
                    </x-dropdown>
                    <x-photo-attach label="Photos" name="tension_belt_status_photos"/>
                </div>
            </div>

            <div data-step="5" class="space-y-4" @style(["display:none" => $currentStep !== 5])>
                <x-title>FILTERS / PIPES / DRYER</x-title>

                <div data-field="status_water_filter">
                    <x-dropdown label="Status of water filter" name="status_water_filter" error-key="form.status_water_filter" placeholder="Select status" wire:model.live="form.status_water_filter" required>
                        <option value="Good Condition">Good Condition</option>
                        <option value="For Replacement">For Replacement</option>
                    </x-dropdown>
                    <x-photo-attach label="Photos" name="status_water_filter_photos"/>
                </div>

                <div data-field="air_pipe_status">
                    <x-dropdown label="Air pipe status" name="air_pipe_status" error-key="form.air_pipe_status" placeholder="Select status" wire:model.live="form.air_pipe_status" required>
                        <option value="No Any Leak">No Any Leak</option>
                        <option value="With Leak For Repair or Replacement">With Leak For Repair or Replacement</option>
                    </x-dropdown>
                    <x-photo-attach label="Photos" name="air_pipe_status_photos"/>
                </div>

                <div data-field="air_dryer_status">
                    <x-dropdown label="Air dryer status" name="air_dryer_status" error-key="form.air_dryer_status" placeholder="Select status" wire:model.live="form.air_dryer_status" required>
                        <option value="Clean and Good Status">Clean and Good Status</option>
                        <option value="For Repair and Replacement">For Repair and Replacement</option>
                    </x-dropdown>
                    <x-photo-attach label="Photos" name="air_dryer_status_photos"/>
                </div>

                <div data-field="inspected_by">
                    <x-text-area label="Inspected by" name="inspected_by" error-key="form.inspected_by" placeholder="Enter inspected by..." wire:model.live="form.inspected_by" required/>
                </div>
            </div>
        </x-progress-navigation>
    </form>
</div>
