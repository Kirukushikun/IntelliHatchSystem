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
            {{-- Step 1: Header --}}
            <div data-step="1" class="space-y-4" @style(["display:none" => $currentStep !== 1])>
                <x-title>INCUBATOR AIR SPEED WEEKLY MONITORING</x-title>
                <p class="text-sm text-gray-500 dark:text-gray-400">Use Kestrel or Anemometer — Every Sunday First Shift Hatcheryman — 1 Setter (Active) - 1 Form</p>

                <div data-field="hatchery_man">
                    <x-personnel-select
                        label="Name"
                        name="hatchery_man"
                        error-key="form.hatchery_man"
                        :personnel="$hatcheryMen"
                        :logged-in-user-id="$loggedInUserId"
                        wire:model.live="form.hatchery_man"
                        required
                    />
                </div>

                <div data-field="incubator">
                    <x-dropdown label="Incubator No." name="incubator" error-key="form.incubator" placeholder="Select incubator" wire:model.live="form.incubator" required>
                        @foreach($incubators as $id => $name)
                            <option value="{{ $id }}" {{ in_array($id, $completedIncubators) ? 'disabled' : '' }}>
                                {{ $name }}{{ in_array($id, $completedIncubators) ? ' (Done)' : '' }}
                            </option>
                        @endforeach
                    </x-dropdown>
                </div>
            </div>

            {{-- Step 2: Left Baggy - Top --}}
            <div data-step="2" class="space-y-4" @style(["display:none" => $currentStep !== 2])>
                <x-title>LEFT BAGGY - TOP - AIRSPEED READING</x-title>
                <p class="text-sm text-gray-500 dark:text-gray-400">Target: 0.3 to 0.6 m/s</p>

                <div data-field="left_baggy_top_reading">
                    <x-text-input label="Airspeed Reading" name="left_baggy_top_reading" error-key="form.left_baggy_top_reading" placeholder="Enter airspeed reading..." wire:model.live="form.left_baggy_top_reading" required subtext="At the ENTRANCE, place the kestrel / anemometer between trays where air passes through.<br>Get and record the reading at the top, middle and bottom of the baggy."/>
                </div>

                <div data-field="left_baggy_top_photos">
                    <x-photo-attach label="Photos" name="left_baggy_top_photos"/>
                </div>
            </div>

            {{-- Step 3: Left Baggy - Middle --}}
            <div data-step="3" class="space-y-4" @style(["display:none" => $currentStep !== 3])>
                <x-title>LEFT BAGGY - MIDDLE - AIRSPEED READING</x-title>
                <p class="text-sm text-gray-500 dark:text-gray-400">Target: 0.3 to 0.6 m/s</p>

                <div data-field="left_baggy_middle_reading">
                    <x-text-input label="Airspeed Reading" name="left_baggy_middle_reading" error-key="form.left_baggy_middle_reading" placeholder="Enter airspeed reading..." wire:model.live="form.left_baggy_middle_reading" required subtext="At the ENTRANCE, place the kestrel / anemometer between trays where air passes through.<br>Get and record the reading at the top, middle and bottom of the baggy."/>
                </div>

                <div data-field="left_baggy_middle_photos">
                    <x-photo-attach label="Photos" name="left_baggy_middle_photos"/>
                </div>
            </div>

            {{-- Step 4: Left Baggy - Bottom --}}
            <div data-step="4" class="space-y-4" @style(["display:none" => $currentStep !== 4])>
                <x-title>LEFT BAGGY - BOTTOM - AIRSPEED READING</x-title>
                <p class="text-sm text-gray-500 dark:text-gray-400">Target: 0.3 to 0.6 m/s</p>

                <div data-field="left_baggy_bottom_reading">
                    <x-text-input label="Airspeed Reading" name="left_baggy_bottom_reading" error-key="form.left_baggy_bottom_reading" placeholder="Enter airspeed reading..." wire:model.live="form.left_baggy_bottom_reading" required subtext="At the ENTRANCE, place the kestrel / anemometer between trays where air passes through.<br>Get and record the reading at the top, middle and bottom of the baggy."/>
                </div>

                <div data-field="left_baggy_bottom_photos">
                    <x-photo-attach label="Photos" name="left_baggy_bottom_photos"/>
                </div>
            </div>

            {{-- Step 5: Right Baggy - Top --}}
            <div data-step="5" class="space-y-4" @style(["display:none" => $currentStep !== 5])>
                <x-title>RIGHT BAGGY - TOP - AIRSPEED READING</x-title>
                <p class="text-sm text-gray-500 dark:text-gray-400">Target: 0.3 to 0.6 m/s</p>

                <div data-field="right_baggy_top_reading">
                    <x-text-input label="Airspeed Reading" name="right_baggy_top_reading" error-key="form.right_baggy_top_reading" placeholder="Enter airspeed reading..." wire:model.live="form.right_baggy_top_reading" required subtext="At the ENTRANCE, place the kestrel / anemometer between trays where air passes through.<br>Get and record the reading at the top, middle and bottom of the baggy."/>
                </div>

                <div data-field="right_baggy_top_photos">
                    <x-photo-attach label="Photos" name="right_baggy_top_photos"/>
                </div>
            </div>

            {{-- Step 6: Right Baggy - Middle --}}
            <div data-step="6" class="space-y-4" @style(["display:none" => $currentStep !== 6])>
                <x-title>RIGHT BAGGY - MIDDLE - AIRSPEED READING</x-title>
                <p class="text-sm text-gray-500 dark:text-gray-400">Target: 0.3 to 0.6 m/s</p>

                <div data-field="right_baggy_middle_reading">
                    <x-text-input label="Airspeed Reading" name="right_baggy_middle_reading" error-key="form.right_baggy_middle_reading" placeholder="Enter airspeed reading..." wire:model.live="form.right_baggy_middle_reading" required subtext="At the ENTRANCE, place the kestrel / anemometer between trays where air passes through.<br>Get and record the reading at the top, middle and bottom of the baggy."/>
                </div>

                <div data-field="right_baggy_middle_photos">
                    <x-photo-attach label="Photos" name="right_baggy_middle_photos"/>
                </div>
            </div>

            {{-- Step 7: Right Baggy - Bottom --}}
            <div data-step="7" class="space-y-4" @style(["display:none" => $currentStep !== 7])>
                <x-title>RIGHT BAGGY - BOTTOM - AIRSPEED READING</x-title>
                <p class="text-sm text-gray-500 dark:text-gray-400">Target: 0.3 to 0.6 m/s</p>

                <div data-field="right_baggy_bottom_reading">
                    <x-text-input label="Airspeed Reading" name="right_baggy_bottom_reading" error-key="form.right_baggy_bottom_reading" placeholder="Enter airspeed reading..." wire:model.live="form.right_baggy_bottom_reading" required subtext="At the ENTRANCE, place the kestrel / anemometer between trays where air passes through.<br>Get and record the reading at the top, middle and bottom of the baggy."/>
                </div>

                <div data-field="right_baggy_bottom_photos">
                    <x-photo-attach label="Photos" name="right_baggy_bottom_photos"/>
                </div>
            </div>

            {{-- Step 8: Remarks --}}
            <div data-step="8" class="space-y-4" @style(["display:none" => $currentStep !== 8])>
                <x-title>REMARKS</x-title>

                <div data-field="remarks">
                    <x-text-area label="Remarks" name="remarks" error-key="form.remarks" placeholder="Enter remarks..." wire:model.live="form.remarks" required/>
                </div>
            </div>
        </x-progress-navigation>
    </form>
</div>
