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
            {{-- Step 1: Basic Information --}}
            <div data-step="1" class="space-y-4" @style(["display:none" => $currentStep !== 1])>
                <x-title subtitle="Every 24 hours after setting">INCUBATOR ENTRANCE TEMPERATURE MONITORING</x-title>

                <div data-field="hatchery_man">
                    <x-dropdown
                        label="Hatcheryman"
                        name="hatchery_man"
                        error-key="form.hatchery_man"
                        placeholder="Select your name"
                        wire:model.live="form.hatchery_man"
                        required
                        subtext="Select the hatcheryman performing this monitoring check"
                    >
                        @foreach($hatcheryMen as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </x-dropdown>
                </div>

                <div data-field="shift">
                    <x-dropdown
                        label="Shift"
                        name="shift"
                        error-key="form.shift"
                        placeholder="Select shift..."
                        wire:model.live="form.shift"
                        required
                        subtext="Select the shift for this monitoring check"
                    >
                        <option value="1st Shift">1st Shift</option>
                        <option value="2nd Shift">2nd Shift</option>
                        <option value="3rd Shift">3rd Shift</option>
                    </x-dropdown>
                </div>

                <div data-field="time_of_check" x-data>
                    <div class="flex items-end gap-2">
                        <div class="flex-1">
                            <x-text-input
                                label="Time of Checking"
                                name="time_of_check"
                                error-key="form.time_of_check"
                                :required="true"
                                placeholder="Select time..."
                                wireModel="form.time_of_check"
                                type="time"
                                subtext="Enter the time this check was performed"
                            />
                        </div>
                        <button type="button"
                            class="mb-6 inline-flex items-center gap-1 px-3 py-2 text-xs font-medium rounded-lg border border-blue-200 bg-blue-50 text-blue-600 hover:bg-blue-100 dark:border-blue-700 dark:bg-blue-900/30 dark:text-blue-400 dark:hover:bg-blue-900/50 transition-colors whitespace-nowrap"
                            x-on:click="$wire.set('form.time_of_check', new Date().toTimeString().slice(0,5))">
                            Now
                        </button>
                    </div>
                </div>

<div data-field="incubator">
                    <x-dropdown
                        label="Incubator"
                        name="incubator"
                        error-key="form.incubator"
                        placeholder="Select incubator..."
                        wire:model.live="form.incubator"
                        required
                        subtext="Select the incubator machine being monitored"
                    >
                        @foreach($incubators as $id => $name)
                            <option value="{{ $id }}" {{ in_array($id, $completedIncubators) ? 'disabled' : '' }}>
                                {{ $name }}{{ in_array($id, $completedIncubators) ? ' (Done)' : '' }}
                            </option>
                        @endforeach
                    </x-dropdown>
                </div>
            </div>

            {{-- Step 2: Temperature Readings --}}
            <div data-step="2" class="space-y-4" @style(["display:none" => $currentStep !== 2])>
                <x-title>TEMPERATURE READINGS</x-title>

                <div class="grid grid-cols-2 gap-3">
                    <div data-field="set_point_temp">
                        <x-text-input
                            label="Set Point Temp"
                            name="set_point_temp"
                            error-key="form.set_point_temp"
                            :required="true"
                            placeholder="e.g. 99.9 F"
                            wireModel="form.set_point_temp"
                            subtext="Set point temperature (e.g. 99.9 F)"
                        />
                    </div>

                    <div data-field="set_point_humidity">
                        <x-text-input
                            label="Set Point Humidity"
                            name="set_point_humidity"
                            error-key="form.set_point_humidity"
                            :required="true"
                            placeholder="e.g. 86 F"
                            wireModel="form.set_point_humidity"
                            subtext="Set point humidity (e.g. 86 F)"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div data-field="entrance_temp_left">
                        <x-text-input
                            label="Entrance Temp (Left)"
                            name="entrance_temp_left"
                            error-key="form.entrance_temp_left"
                            :required="true"
                            placeholder="e.g. 99.5 F"
                            wireModel="form.entrance_temp_left"
                            subtext="Left entrance temperature reading"
                        />
                    </div>

                    <div data-field="entrance_temp_right">
                        <x-text-input
                            label="Entrance Temp (Right)"
                            name="entrance_temp_right"
                            error-key="form.entrance_temp_right"
                            :required="true"
                            placeholder="e.g. 99.5 F"
                            wireModel="form.entrance_temp_right"
                            subtext="Right entrance temperature reading"
                        />
                    </div>
                </div>

                <div data-field="entrance_photo">
                    <x-photo-attach
                        label="Entrance Temp Photo (Optional)"
                        name="entrance_photo"
                    />
                </div>
            </div>

            {{-- Step 3: Adjustments & Completion --}}
            <div data-step="3" class="space-y-4" @style(["display:none" => $currentStep !== 3])>
                <x-title>ADJUSTMENTS &amp; COMPLETION</x-title>

                <div data-field="temp_adjustment_notes">
                    <x-text-area
                        label="Temperature Adjustments &amp; Corrective Actions"
                        name="temp_adjustment_notes"
                        error-key="form.temp_adjustment_notes"
                        placeholder="Enter any temperature adjustments made and corrective actions taken..."
                        wire:model.live="form.temp_adjustment_notes"
                        required
                        subtext="Describe any temperature adjustment on any incubator and the corrective action taken. Enter 'None' if no adjustments were made."
                    />
                </div>

                <div data-field="temp_adjustment_photo">
                    <x-photo-attach
                        label="Adjustment Photo (Optional)"
                        name="temp_adjustment_photo"
                    />
                </div>

                <div data-field="time_finished" x-data>
                    <div class="flex items-end gap-2">
                        <div class="flex-1">
                            <x-text-input
                                label="Time Finished"
                                name="time_finished"
                                error-key="form.time_finished"
                                :required="true"
                                placeholder="Select time..."
                                wireModel="form.time_finished"
                                type="time"
                                subtext="Enter the time you completed this monitoring check"
                            />
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
