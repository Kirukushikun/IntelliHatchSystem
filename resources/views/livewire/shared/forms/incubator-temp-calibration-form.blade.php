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
                <x-title>INCUBATOR TEMPERATURE CALIBRATION</x-title>

                <div data-field="hatchery_man">
                    <x-dropdown label="Hatcheryman" name="hatchery_man" error-key="form.hatchery_man" placeholder="Select hatchery man" wire:model.live="form.hatchery_man" required>
                        @foreach($hatcheryMen as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </x-dropdown>
                </div>

                <div data-field="shift">
                    <x-dropdown label="Shift" name="shift" error-key="form.shift" placeholder="Select shift" wire:model.live="form.shift" required>
                        <option value="1st Shift">1st Shift</option>
                        <option value="2nd Shift">2nd Shift</option>
                        <option value="3rd Shift">3rd Shift</option>
                    </x-dropdown>
                </div>

                <div data-field="time_started" x-data>
                    <div class="flex items-end gap-2">
                        <div class="flex-1">
                            <x-text-input label="Time Started" name="time_started" error-key="form.time_started" :required="true" placeholder="Select time..." wireModel="form.time_started" type="time" />
                        </div>
                        <button type="button"
                            class="mb-6 inline-flex items-center gap-1 px-3 py-2 text-xs font-medium rounded-lg border border-blue-200 bg-blue-50 text-blue-600 hover:bg-blue-100 dark:border-blue-700 dark:bg-blue-900/30 dark:text-blue-400 dark:hover:bg-blue-900/50 transition-colors whitespace-nowrap"
                            x-on:click="$wire.set('form.time_started', new Date().toTimeString().slice(0,5))">
                            Now
                        </button>
                    </div>
                </div>

                <div data-field="incubator">
                    <x-dropdown label="Incubator Machine" name="incubator" error-key="form.incubator" placeholder="Select incubator" wire:model.live="form.incubator" required>
                        @foreach($incubators as $id => $name)
                            <option value="{{ $id }}" {{ in_array($id, $completedIncubators) ? 'disabled' : '' }}>
                                {{ $name }}{{ in_array($id, $completedIncubators) ? ' (Done)' : '' }}
                            </option>
                        @endforeach
                    </x-dropdown>
                </div>

                <div data-field="machine_temp_set_point">
                    <x-text-input label="Machine Temperature Set Point" name="machine_temp_set_point" error-key="form.machine_temp_set_point" :required="true" placeholder="e.g. 99.5" wireModel="form.machine_temp_set_point" type="number" step="0.01" />
                </div>
            </div>

            <div data-step="2" class="space-y-4" @style(["display:none" => $currentStep !== 2])>
                <x-title>TEMPERATURE & HUMIDITY READINGS</x-title>

                <div data-field="machine_temp">
                    <x-text-input label="Machine Temp. Reading" name="machine_temp" error-key="form.machine_temp" :required="true" placeholder="e.g. 99.8" wireModel="form.machine_temp" type="number" step="0.01" />
                </div>

                <div data-field="calibrator_temp">
                    <x-text-input label="Calibrator Temp. Reading" name="calibrator_temp" error-key="form.calibrator_temp" :required="true" placeholder="e.g. 99.9" wireModel="form.calibrator_temp" type="number" step="0.01" />
                </div>

                <div data-field="reading_photos">
                    <x-photo-attach label="Photo of Machine and Calibrator Reading (Optional)" name="reading_photos" :required="false" />
                </div>

                <div data-field="machine_humidity_set_point">
                    <x-text-input label="Machine Humidity Set Point" name="machine_humidity_set_point" error-key="form.machine_humidity_set_point" :required="true" placeholder="e.g. 55.0" wireModel="form.machine_humidity_set_point" type="number" step="0.01" />
                </div>

                <div data-field="humidity_reading">
                    <x-text-input label="Humidity Reading" name="humidity_reading" error-key="form.humidity_reading" :required="true" placeholder="Enter humidity..." wireModel="form.humidity_reading" type="number" step="0.01" />
                </div>

                <div data-field="humidity_photos">
                    <x-photo-attach label="Photo of Humidity Reading (Optional)" name="humidity_photos" :required="false" />
                </div>
            </div>

            <div data-step="3" class="space-y-4" @style(["display:none" => $currentStep !== 3])>
                <x-title>COMPLETION</x-title>

                <div data-field="corrective_action">
                    <x-text-area label="Corrective Action (Optional)" name="corrective_action" error-key="form.corrective_action" placeholder="Enter corrective action..." wire:model.live="form.corrective_action" />
                </div>

                <div data-field="approver">
                    <x-text-input label="Approver on Machine Temperature Adjustment" name="approver" error-key="form.approver" :required="true" placeholder="Enter approver name..." wireModel="form.approver" type="text" />
                </div>

                <div data-field="time_finished" x-data>
                    <div class="flex items-end gap-2">
                        <div class="flex-1">
                            <x-text-input label="Time Finished" name="time_finished" error-key="form.time_finished" :required="true" placeholder="Select time..." wireModel="form.time_finished" type="time" />
                        </div>
                        <button type="button"
                            class="mb-6 inline-flex items-center gap-1 px-3 py-2 text-xs font-medium rounded-lg border border-blue-200 bg-blue-50 text-blue-600 hover:bg-blue-100 dark:border-blue-700 dark:bg-blue-900/30 dark:text-blue-400 dark:hover:bg-blue-900/50 transition-colors whitespace-nowrap"
                            x-on:click="$wire.set('form.time_finished', new Date().toTimeString().slice(0,5))">
                            Now
                        </button>
                    </div>
                </div>
            </div>
        </x-progress-navigation>
    </form>
</div>
