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
                <x-title>HATCHER BLOWER AIR SPEED MONITORING</x-title>

                <div data-field="hatchery_man">
                    <x-dropdown label="Hatchery Man" name="hatchery_man" error-key="form.hatchery_man" placeholder="Select hatchery man" wire:model.live="form.hatchery_man" required>
                        @foreach($hatcheryMen as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </x-dropdown>
                </div>

                <div data-field="hatcher">
                    <x-dropdown label="Hatcher" name="hatcher" error-key="form.hatcher" placeholder="Select hatcher" wire:model.live="form.hatcher" required>
                        @foreach($hatchers as $id => $name)
                            <option value="{{ $id }}" {{ in_array($id, $completedHatchers) ? 'disabled' : '' }}>
                                {{ $name }}{{ in_array($id, $completedHatchers) ? ' (Done)' : '' }}
                            </option>
                        @endforeach
                    </x-dropdown>
                </div>
            </div>

            <div data-step="2" class="space-y-4" @style(["display:none" => $currentStep !== 2])>
                <x-title>FAN DETAILS</x-title>

                <div data-field="cfm_fan_reading">
                    <x-text-area label="CFM Fan Reading" name="cfm_fan_reading" error-key="form.cfm_fan_reading" placeholder="Enter CFM fan reading..." wire:model.live="form.cfm_fan_reading" required/>
                </div>

                <div data-field="cfm_fan_action_taken">
                    <x-text-area label="Action Taken" name="cfm_fan_action_taken" error-key="form.cfm_fan_action_taken" placeholder="Enter action taken..." wire:model.live="form.cfm_fan_action_taken" required/>
                </div>

                <div data-field="cfm_fan_photos">
                    <x-photo-attach label="Photos" name="cfm_fan_photos"/>
                </div>
            </div>
        </x-progress-navigation>
    </form>
</div>
