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
                        label="Hatcheryman 3rd Shift"
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

                <div data-field="time_of_check">
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

                <div data-field="days_of_incubation">
                    <x-dropdown
                        label="Days of Incubation"
                        name="days_of_incubation"
                        error-key="form.days_of_incubation"
                        placeholder="Select incubation period..."
                        wire:model.live="form.days_of_incubation"
                        required
                        subtext="Select the current stage of incubation"
                    >
                        <option value="Day 1 to 10">Day 1 to 10</option>
                        <option value="Day 10 to 12">Day 10 to 12</option>
                        <option value="Day 12 to 14">Day 12 to 14</option>
                        <option value="Day 14 to 18">Day 14 to 18</option>
                    </x-dropdown>
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

                <div data-field="entrance_temp">
                    <x-text-input
                        label="Entrance Temp Reading (Left and Right)"
                        name="entrance_temp"
                        error-key="form.entrance_temp"
                        :required="true"
                        placeholder="e.g. Left: 99.5 F, Right: 99.5 F"
                        wireModel="form.entrance_temp"
                        subtext="Enter both left and right entrance temperature readings"
                    />
                </div>

                <div data-field="entrance_photo">
                    <x-photo-attach
                        label="Entrance Temp Photo (Optional)"
                        name="entrance_photo"
                    />
                </div>

                <div data-field="baggy">
                    <x-text-input
                        label="Baggy No. 2 from Exit Temp Reading (Left and Right)"
                        name="baggy"
                        error-key="form.baggy"
                        :required="true"
                        placeholder="e.g. Left: 100.2 F, Right: 100.2 F"
                        wireModel="form.baggy"
                        subtext="Should be 0.3 F higher than set point — enter both left and right readings"
                    />
                </div>

                <div data-field="baggy_photo">
                    <x-photo-attach
                        label="Baggy No. 2 Photo (Optional)"
                        name="baggy_photo"
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

                <div data-field="time_finished">
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
            </div>
        </x-progress-navigation>
    </form>
</div>
