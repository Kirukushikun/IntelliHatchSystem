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
                <x-title>HATCHER MACHINE ACCURACY TEMPERATURE CHECKING</x-title>

                <div data-field="hatchery_man">
                    <x-dropdown label="Hatchery Man" name="hatchery_man" error-key="form.hatchery_man" placeholder="Select hatchery man" wire:model.live="form.hatchery_man" required>
                        @foreach($hatcheryMen as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </x-dropdown>
                </div>

                <div data-field="cellphone_number">
                    <x-text-input label="Cellphone Number" name="cellphone_number" error-key="form.cellphone_number" :required="true" placeholder="09XXXXXXXXX or +639XXXXXXXXX" wireModel="form.cellphone_number" type="tel" />
                </div>

                <div data-field="time_of_reading">
                    <x-text-input label="Time of Reading" name="time_of_reading" error-key="form.time_of_reading" :required="true" placeholder="Select time..." wireModel="form.time_of_reading" type="time" />
                </div>

                <div data-field="shift">
                    <x-dropdown label="Shift" name="shift" error-key="form.shift" placeholder="Select shift" wire:model.live="form.shift" required>
                        <option value="1st Shift">1st Shift</option>
                        <option value="2nd Shift">2nd Shift</option>
                        <option value="3rd Shift">3rd Shift</option>
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
                <x-title>TEMPERATURE READINGS</x-title>

                <div data-field="display_temp">
                    <x-text-input label="Display Temp" name="display_temp" error-key="form.display_temp" :required="true" placeholder="Enter display temperature..." wireModel="form.display_temp" />
                </div>

                <div data-field="calibrator">
                    <x-text-input label="Calibrator" name="calibrator" error-key="form.calibrator" :required="true" placeholder="Enter calibrator reading..." wireModel="form.calibrator" />
                </div>

                <div data-field="wet_bulb">
                    <x-text-input label="Wet Bulb" name="wet_bulb" error-key="form.wet_bulb" :required="true" placeholder="Enter wet bulb reading..." wireModel="form.wet_bulb" />
                </div>

                <div data-field="dry_bulb">
                    <x-text-input label="Dry Bulb" name="dry_bulb" error-key="form.dry_bulb" :required="true" placeholder="Enter dry bulb reading..." wireModel="form.dry_bulb" />
                </div>

                <div data-field="accuracy_photos">
                    <x-photo-attach label="Photo of Display Next to Calibrator" name="accuracy_photos" />
                </div>
            </div>
        </x-progress-navigation>
    </form>
</div>
