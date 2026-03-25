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
                <x-title>INCUBATOR RACK PREVENTIVE MAINTENANCE CHECKLIST</x-title>

                <p class="text-sm text-gray-500 dark:text-gray-400 -mt-2">After cleaning and disinfection activity. Every Tuesday and Sunday.</p>

                <div data-field="rack_number">
                    <x-text-input
                        label="Incubator Rack No."
                        name="rack_number"
                        error-key="form.rack_number"
                        :required="true"
                        placeholder="Enter rack number"
                        wireModel="form.rack_number"
                    />
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

                <div data-field="maintenance_personnel">
                    <x-dropdown
                        label="Name of Maintenance Personnel"
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
            </div>

            {{-- Step 2: Check 1 — Male/Female Chord Connection to Turning Sensor --}}
            <div data-step="2" class="space-y-4" @style(["display:none" => $currentStep !== 2])>
                <x-title>CHECK 1: CHORD CONNECTION TO TURNING SENSOR</x-title>

                <p class="text-sm text-gray-500 dark:text-gray-400 -mt-2">Standard: No any damages and no loose connection</p>

                <div data-field="chord_connection_status">
                    <x-dropdown
                        label="Check the male and female chord connection to turning sensor"
                        name="chord_connection_status"
                        error-key="form.chord_connection_status"
                        placeholder="Select result"
                        wire:model.live="form.chord_connection_status"
                        required
                    >
                        <option value="Yes">Yes</option>
                        <option value="No">No</option>
                    </x-dropdown>
                </div>

                <div data-field="chord_connection_corrective_action">
                    <x-text-area
                        label="Corrective Action Taken (if any)"
                        name="chord_connection_corrective_action"
                        error-key="form.chord_connection_corrective_action"
                        :required="true"
                        placeholder='Write "N/A" if not applicable.'
                        wire:model.live="form.chord_connection_corrective_action"
                    />
                </div>

                <div data-field="photo_chord_connection">
                    <x-photo-attach label="Attach before and after photos." name="photo_chord_connection" required />
                </div>
            </div>

            {{-- Step 3: Check 2 — Air Supply Hose Connection --}}
            <div data-step="3" class="space-y-4" @style(["display:none" => $currentStep !== 3])>
                <x-title>CHECK 2: AIR SUPPLY HOSE CONNECTION</x-title>

                <p class="text-sm text-gray-500 dark:text-gray-400 -mt-2">Standard: No any damages and no loose connection</p>

                <div data-field="air_hose_status">
                    <x-dropdown
                        label="Check air supply hose connection to pneumatic cylinder"
                        name="air_hose_status"
                        error-key="form.air_hose_status"
                        placeholder="Select result"
                        wire:model.live="form.air_hose_status"
                        required
                    >
                        <option value="Yes">Yes</option>
                        <option value="No">No</option>
                    </x-dropdown>
                </div>

                <div data-field="air_hose_corrective_action">
                    <x-text-area
                        label="Corrective Action Taken (if any)"
                        name="air_hose_corrective_action"
                        error-key="form.air_hose_corrective_action"
                        :required="true"
                        placeholder='Write "N/A" if not applicable.'
                        wire:model.live="form.air_hose_corrective_action"
                    />
                </div>

                <div data-field="photo_air_hose">
                    <x-photo-attach label="Attach before and after photos." name="photo_air_hose" required />
                </div>
            </div>

            {{-- Step 4: Check 3 — Wheels Condition --}}
            <div data-step="4" class="space-y-4" @style(["display:none" => $currentStep !== 4])>
                <x-title>CHECK 3: WHEELS CONDITION</x-title>

                <div data-field="wheels_status">
                    <x-dropdown
                        label="Check the wheels condition and lubricate"
                        name="wheels_status"
                        error-key="form.wheels_status"
                        placeholder="Select result"
                        wire:model.live="form.wheels_status"
                        required
                    >
                        <option value="Yes">Yes</option>
                        <option value="No">No</option>
                    </x-dropdown>
                </div>

                <div data-field="wheels_corrective_action">
                    <x-text-area
                        label="Corrective Action Taken (if any)"
                        name="wheels_corrective_action"
                        error-key="form.wheels_corrective_action"
                        :required="true"
                        placeholder='Write "N/A" if not applicable.'
                        wire:model.live="form.wheels_corrective_action"
                    />
                </div>

                <div data-field="photo_wheels">
                    <x-photo-attach label="Attach before and after photos." name="photo_wheels" required />
                </div>
            </div>

            {{-- Step 5: Check 4 — Steel Frame Members --}}
            <div data-step="5" class="space-y-4" @style(["display:none" => $currentStep !== 5])>
                <x-title>CHECK 4: INCUBATOR RACK STEEL FRAME</x-title>

                <p class="text-sm text-gray-500 dark:text-gray-400 -mt-2">Standard: Free from any dents and damages</p>

                <div data-field="steel_frame_status">
                    <x-dropdown
                        label="Check all incubator rack steel frame members"
                        name="steel_frame_status"
                        error-key="form.steel_frame_status"
                        placeholder="Select result"
                        wire:model.live="form.steel_frame_status"
                        required
                    >
                        <option value="Yes">Yes</option>
                        <option value="No">No</option>
                    </x-dropdown>
                </div>

                <div data-field="steel_frame_corrective_action">
                    <x-text-area
                        label="Corrective Action Taken (if any)"
                        name="steel_frame_corrective_action"
                        error-key="form.steel_frame_corrective_action"
                        :required="true"
                        placeholder='Write "N/A" if not applicable.'
                        wire:model.live="form.steel_frame_corrective_action"
                    />
                </div>

                <div data-field="photo_steel_frame">
                    <x-photo-attach label="Attach before and after photos." name="photo_steel_frame" required />
                </div>
            </div>

            {{-- Step 6: Check 5 — Bolts Connection --}}
            <div data-step="6" class="space-y-4" @style(["display:none" => $currentStep !== 6])>
                <x-title>CHECK 5: BOLTS CONNECTION</x-title>

                <p class="text-sm text-gray-500 dark:text-gray-400 -mt-2">Standard: No missing and loose bolts</p>

                <div data-field="bolts_status">
                    <x-dropdown
                        label="Check for all bolts connection"
                        name="bolts_status"
                        error-key="form.bolts_status"
                        placeholder="Select result"
                        wire:model.live="form.bolts_status"
                        required
                    >
                        <option value="Yes">Yes</option>
                        <option value="No">No</option>
                    </x-dropdown>
                </div>

                <div data-field="bolts_corrective_action">
                    <x-text-area
                        label="Corrective Action Taken (if any)"
                        name="bolts_corrective_action"
                        error-key="form.bolts_corrective_action"
                        :required="true"
                        placeholder='Write "N/A" if not applicable.'
                        wire:model.live="form.bolts_corrective_action"
                    />
                </div>

                <div data-field="photo_bolts">
                    <x-photo-attach label="Attach before and after photos." name="photo_bolts" required />
                </div>
            </div>

            {{-- Step 7: Check 6 — Turning Sensor --}}
            <div data-step="7" class="space-y-4" @style(["display:none" => $currentStep !== 7])>
                <x-title>CHECK 6: TURNING SENSOR</x-title>

                <p class="text-sm text-gray-500 dark:text-gray-400 -mt-2">Using incubator rack tester</p>

                <div data-field="turning_sensor_status">
                    <x-dropdown
                        label="Check turning sensor working properly"
                        name="turning_sensor_status"
                        error-key="form.turning_sensor_status"
                        placeholder="Select result"
                        wire:model.live="form.turning_sensor_status"
                        required
                    >
                        <option value="Yes">Yes</option>
                        <option value="No">No</option>
                    </x-dropdown>
                </div>

                <div data-field="turning_sensor_corrective_action">
                    <x-text-area
                        label="Corrective Action Taken (if any)"
                        name="turning_sensor_corrective_action"
                        error-key="form.turning_sensor_corrective_action"
                        :required="true"
                        placeholder='Write "N/A" if not applicable.'
                        wire:model.live="form.turning_sensor_corrective_action"
                    />
                </div>

                <div data-field="photo_turning_sensor">
                    <x-photo-attach label="Attach before and after photos." name="photo_turning_sensor" required />
                </div>
            </div>

            {{-- Step 8: Check 7 — Pneumatic Cylinder --}}
            <div data-step="8" class="space-y-4" @style(["display:none" => $currentStep !== 8])>
                <x-title>CHECK 7: PNEUMATIC CYLINDER</x-title>

                <p class="text-sm text-gray-500 dark:text-gray-400 -mt-2">Using incubator rack tester</p>

                <div data-field="pneumatic_cylinder_status">
                    <x-dropdown
                        label="Check pneumatic cylinder working properly"
                        name="pneumatic_cylinder_status"
                        error-key="form.pneumatic_cylinder_status"
                        placeholder="Select result"
                        wire:model.live="form.pneumatic_cylinder_status"
                        required
                    >
                        <option value="Yes">Yes</option>
                        <option value="No">No</option>
                    </x-dropdown>
                </div>

                <div data-field="pneumatic_cylinder_corrective_action">
                    <x-text-area
                        label="Corrective Action Taken (if any)"
                        name="pneumatic_cylinder_corrective_action"
                        error-key="form.pneumatic_cylinder_corrective_action"
                        :required="true"
                        placeholder='Write "N/A" if not applicable.'
                        wire:model.live="form.pneumatic_cylinder_corrective_action"
                    />
                </div>

                <div data-field="photo_pneumatic_cylinder">
                    <x-photo-attach label="Attach before and after photos." name="photo_pneumatic_cylinder" required />
                </div>
            </div>

            {{-- Step 9: Check 8 — Smooth Turning --}}
            <div data-step="9" class="space-y-4" @style(["display:none" => $currentStep !== 9])>
                <x-title>CHECK 8: SMOOTH TURNING OF SETTER RACK</x-title>

                <div data-field="smooth_turning_status">
                    <x-dropdown
                        label="Check for the left and right smooth turning of setter rack"
                        name="smooth_turning_status"
                        error-key="form.smooth_turning_status"
                        placeholder="Select result"
                        wire:model.live="form.smooth_turning_status"
                        required
                    >
                        <option value="Yes">Yes</option>
                        <option value="No">No</option>
                    </x-dropdown>
                </div>

                <div data-field="smooth_turning_corrective_action">
                    <x-text-area
                        label="Corrective Action Taken (if any)"
                        name="smooth_turning_corrective_action"
                        error-key="form.smooth_turning_corrective_action"
                        :required="true"
                        placeholder='Write "N/A" if not applicable.'
                        wire:model.live="form.smooth_turning_corrective_action"
                    />
                </div>

                <div data-field="photo_smooth_turning">
                    <x-photo-attach label="Attach before and after photos." name="photo_smooth_turning" required />
                </div>
            </div>

            {{-- Step 10: Check 9 — Turning Angle --}}
            <div data-step="10" class="space-y-4" @style(["display:none" => $currentStep !== 10])>
                <x-title>CHECK 9: TURNING ANGLE OF SETTER RACK</x-title>

                <p class="text-sm text-gray-500 dark:text-gray-400 -mt-2">Standard: Between 40 to 45 degrees only</p>

                <div data-field="turning_angle_status">
                    <x-dropdown
                        label="Check for the left and right turning angle of setter rack"
                        name="turning_angle_status"
                        error-key="form.turning_angle_status"
                        placeholder="Select result"
                        wire:model.live="form.turning_angle_status"
                        required
                    >
                        <option value="Yes">Yes</option>
                        <option value="No">No</option>
                    </x-dropdown>
                </div>

                <div data-field="left_turning_angle">
                    <x-text-input
                        label="Left Turning Angle Readings"
                        name="left_turning_angle"
                        error-key="form.left_turning_angle"
                        :required="true"
                        placeholder="Enter reading"
                        wireModel="form.left_turning_angle"
                        type="number"
                        step="0.1"
                        subtext="In degrees"
                    />
                </div>

                <div data-field="right_turning_angle">
                    <x-text-input
                        label="Right Turning Angle Readings"
                        name="right_turning_angle"
                        error-key="form.right_turning_angle"
                        :required="true"
                        placeholder="Enter reading"
                        wireModel="form.right_turning_angle"
                        type="number"
                        step="0.1"
                        subtext="In degrees"
                    />
                </div>

                <div data-field="turning_angle_corrective_action">
                    <x-text-area
                        label="Corrective Action Taken (if any)"
                        name="turning_angle_corrective_action"
                        error-key="form.turning_angle_corrective_action"
                        :required="true"
                        placeholder='Write "N/A" if not applicable.'
                        wire:model.live="form.turning_angle_corrective_action"
                    />
                </div>

                <div data-field="photo_turning_angle">
                    <x-photo-attach label="Attach before and after photos." name="photo_turning_angle" required />
                </div>
            </div>

            {{-- Step 11: Check 10 — Lubricate Turning Racks Bolts --}}
            <div data-step="11" class="space-y-4" @style(["display:none" => $currentStep !== 11])>
                <x-title>CHECK 10: LUBRICATE TURNING RACKS BOLTS</x-title>

                <div data-field="lubricate_bolts_status">
                    <x-dropdown
                        label="Lubricate all turning racks bolts"
                        name="lubricate_bolts_status"
                        error-key="form.lubricate_bolts_status"
                        placeholder="Select result"
                        wire:model.live="form.lubricate_bolts_status"
                        required
                    >
                        <option value="Yes">Yes</option>
                        <option value="No">No</option>
                    </x-dropdown>
                </div>

                <div data-field="lubricate_bolts_corrective_action">
                    <x-text-area
                        label="Corrective Action Taken (if any)"
                        name="lubricate_bolts_corrective_action"
                        error-key="form.lubricate_bolts_corrective_action"
                        :required="true"
                        placeholder='Write "N/A" if not applicable.'
                        wire:model.live="form.lubricate_bolts_corrective_action"
                    />
                </div>

                <div data-field="photo_lubricate_bolts">
                    <x-photo-attach label="Attach before and after photos." name="photo_lubricate_bolts" required />
                </div>
            </div>

            {{-- Step 12: Check 11 — Plastic Curtain & Completion --}}
            <div data-step="12" class="space-y-4" @style(["display:none" => $currentStep !== 12])>
                <x-title>CHECK 11: PLASTIC CURTAIN &amp; COMPLETION</x-title>

                <div data-field="plastic_curtain_status">
                    <x-dropdown
                        label="Check good condition of incubator rack plastic curtain"
                        name="plastic_curtain_status"
                        error-key="form.plastic_curtain_status"
                        placeholder="Select result"
                        wire:model.live="form.plastic_curtain_status"
                        required
                    >
                        <option value="Yes">Yes</option>
                        <option value="No">No</option>
                    </x-dropdown>
                </div>

                <div data-field="plastic_curtain_corrective_action">
                    <x-text-area
                        label="Corrective Action Taken (if any)"
                        name="plastic_curtain_corrective_action"
                        error-key="form.plastic_curtain_corrective_action"
                        :required="true"
                        placeholder='Write "N/A" if not applicable.'
                        wire:model.live="form.plastic_curtain_corrective_action"
                    />
                </div>

                <div data-field="photo_plastic_curtain">
                    <x-photo-attach label="Attach before and after photos." name="photo_plastic_curtain" required />
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
