<div x-data="{ formSubmitted: false }">
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
                <x-title>INCUBATOR ROUTINE CHECKLIST PER SHIFT</x-title>

                <div data-field="hatchery_man" @style(["display:none" => !$this->isFieldVisible('hatchery_man')])>
                    <x-dropdown label="Hatchery Man" name="hatchery_man" error-key="form.hatchery_man" placeholder="Select hatchery man" wire:model.live="form.hatchery_man" required>
                        @foreach($hatcheryMen as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </x-dropdown>
                </div>

                <div data-field="shift" @style(["display:none" => !$this->isFieldVisible('shift')])>
                    <x-dropdown label="Shift" name="shift" error-key="form.shift" placeholder="Select shift" wire:model.live="form.shift" required>
                        <option value="Day">Day</option>
                        <option value="Night">Night</option>
                    </x-dropdown>
                </div>

                <div data-field="incubator" @style(["display:none" => !$this->isFieldVisible('incubator')])>
                    <x-dropdown label="Incubator" name="incubator" error-key="form.incubator" placeholder="Select incubator" wire:model.live="form.incubator" required>
                        @foreach($incubators as $id => $name)
                            <option value="{{ $id }}" {{ in_array($id, $completedIncubators) ? 'disabled' : '' }}>
                                {{ $name }}{{ in_array($id, $completedIncubators) ? ' (Done)' : '' }}
                            </option>
                        @endforeach
                    </x-dropdown>
                </div>

                <div data-field="alarm_system_condition" @style(["display:none" => !$this->isFieldVisible('alarm_system_condition')])>
                    <x-dropdown label="Check for Alarm system condition" name="alarm_system_condition" error-key="form.alarm_system_condition" placeholder="Select condition" wire:model.live="form.alarm_system_condition" required>
                        <option value="Operational">Operational</option>
                        <option value="Unoperational">Unoperational</option>
                    </x-dropdown>
                </div>

                <div data-field="corrective_action" @style(["display:none" => !$this->isFieldVisible('corrective_action')])>
                    <x-text-area label="Corrective Action" name="corrective_action" error-key="form.corrective_action" placeholder="Enter your answer..." wire:model.live="form.corrective_action" required/>
                    <x-photo-attach label="Attach Photos" name="corrective_action_photos"/>
                </div>
            </div>

            <div data-step="2" class="space-y-4" @style(["display:none" => $currentStep !== 2])>
                <x-title>GENERAL MACHINE</x-title>

                <div data-field="check_incubator_doors_for_air_leakage" @style(["display:none" => !$this->isFieldVisible('check_incubator_doors_for_air_leakage')])>
                    <x-dropdown label="Check incubator doors for air leakage" name="check_incubator_doors_for_air_leakage" error-key="form.check_incubator_doors_for_air_leakage" placeholder="Select condition" wire:model.live="form.check_incubator_doors_for_air_leakage" required>
                        <option value="Pending">Pending</option>
                        <option value="Done">Done</option>
                    </x-dropdown>
                    <x-photo-attach label="Attach Photos" name="check_incubator_doors_for_air_leakage_photos"/>
                </div>

                <div data-field="checking_of_baggy_against_the_gaskets" @style(["display:none" => !$this->isFieldVisible('checking_of_baggy_against_the_gaskets')])>
                    <x-dropdown label="Checking of baggy against the gaskets" name="checking_of_baggy_against_the_gaskets" error-key="form.checking_of_baggy_against_the_gaskets" placeholder="Select condition" wire:model.live="form.checking_of_baggy_against_the_gaskets" required>
                        <option value="Pending">Pending</option>
                        <option value="Done">Done</option>
                    </x-dropdown>
                    <x-photo-attach label="Attach Photos" name="checking_of_baggy_against_the_gaskets_photos"/>
                </div>

                <div data-field="check_curtain_position_and_condition" @style(["display:none" => !$this->isFieldVisible('check_curtain_position_and_condition')])>
                    <x-dropdown label="Check curtain position and condition" name="check_curtain_position_and_condition" error-key="form.check_curtain_position_and_condition" placeholder="Select condition" wire:model.live="form.check_curtain_position_and_condition" required>
                        <option value="Pending">Pending</option>
                        <option value="Done">Done</option>
                    </x-dropdown>
                    <x-photo-attach label="Attach Photos" name="check_curtain_position_and_condition_photos"/>
                </div>

                <div data-field="check_wick_for_replacement_washing" @style(["display:none" => !$this->isFieldVisible('check_wick_for_replacement_washing')])>
                    <x-dropdown label="Check wick for replacement / washing" name="check_wick_for_replacement_washing" error-key="form.check_wick_for_replacement_washing" placeholder="Select condition" wire:model.live="form.check_wick_for_replacement_washing" required>
                        <option value="Pending">Pending</option>
                        <option value="Done">Done</option>
                    </x-dropdown>
                    <x-photo-attach label="Attach Photos" name="check_wick_for_replacement_washing_photos"/>
                </div>

                <div data-field="check_spray_nozzle_and_water_pan" @style(["display:none" => !$this->isFieldVisible('check_spray_nozzle_and_water_pan')])>
                    <x-dropdown label="Check spray nozzle and water pan" name="check_spray_nozzle_and_water_pan" error-key="form.check_spray_nozzle_and_water_pan" placeholder="Select condition" wire:model.live="form.check_spray_nozzle_and_water_pan" required>
                        <option value="Pending">Pending</option>
                        <option value="Done">Done</option>
                    </x-dropdown>
                    <x-photo-attach label="Attach Photos" name="check_spray_nozzle_and_water_pan_photos"/>
                </div>

                <div data-field="check_incubator_fans_for_vibration" @style(["display:none" => !$this->isFieldVisible('check_incubator_fans_for_vibration')])>
                    <x-dropdown label="Check incubator fans for vibration" name="check_incubator_fans_for_vibration" error-key="form.check_incubator_fans_for_vibration" placeholder="Select condition" wire:model.live="form.check_incubator_fans_for_vibration" required>
                        <option value="Pending">Pending</option>
                        <option value="Done">Done</option>
                    </x-dropdown>
                    <x-photo-attach label="Attach Photos" name="check_incubator_fans_for_vibration_photos"/>
                </div>

                <div data-field="check_rack_baffle_condition" @style(["display:none" => !$this->isFieldVisible('check_rack_baffle_condition')])>
                    <x-dropdown label="Check rack baffle condition" name="check_rack_baffle_condition" error-key="form.check_rack_baffle_condition" placeholder="Select condition" wire:model.live="form.check_rack_baffle_condition" required>
                        <option value="Pending">Pending</option>
                        <option value="Done">Done</option>
                    </x-dropdown>
                    <x-photo-attach label="Attach Photos" name="check_rack_baffle_condition_photos"/>
                </div>

                <div data-field="drain_water_out_from_air_compressor_tank" @style(["display:none" => !$this->isFieldVisible('drain_water_out_from_air_compressor_tank')])>
                    <x-dropdown label="Drain water out from air compressor tank" name="drain_water_out_from_air_compressor_tank" error-key="form.drain_water_out_from_air_compressor_tank" placeholder="Select condition" wire:model.live="form.drain_water_out_from_air_compressor_tank" required>
                        <option value="Pending">Pending</option>
                        <option value="Done">Done</option>
                    </x-dropdown>
                    <x-photo-attach label="Attach Photos" name="drain_water_out_from_air_compressor_tank_photos"/>
                </div>
            </div>

            <div data-step="3" class="space-y-4" @style(["display:none" => $currentStep !== 3])>
                <x-title>PLENUM</x-title>

                <div data-field="cleaning_incubator_roof_and_plenum" @style(["display:none" => !$this->isFieldVisible('cleaning_incubator_roof_and_plenum')])>
                    <x-dropdown label="Cleaning of incubator roof and plenum" name="cleaning_incubator_roof_and_plenum" error-key="form.cleaning_incubator_roof_and_plenum" placeholder="Select condition" wire:model.live="form.cleaning_incubator_roof_and_plenum" required>
                        <option value="Pending">Pending</option>
                        <option value="Done">Done</option>
                    </x-dropdown>
                    <x-photo-attach label="Attach Photos" name="cleaning_incubator_roof_and_plenum_photos"/>
                </div>
            </div>

            <div data-step="4" class="space-y-4" @style(["display:none" => $currentStep !== 4])>
                <x-title>CLEANING</x-title>

                <div data-field="check_water_level_of_blue_tank" @style(["display:none" => !$this->isFieldVisible('check_water_level_of_blue_tank')])>
                    <x-dropdown label="Check water level of blue tank" name="check_water_level_of_blue_tank" error-key="form.check_water_level_of_blue_tank" placeholder="Select condition" wire:model.live="form.check_water_level_of_blue_tank" required>
                        <option value="Pending">Pending</option>
                        <option value="Done">Done</option>
                    </x-dropdown>
                    <x-photo-attach label="Attach Photos" name="check_water_level_of_blue_tank_photos"/>
                </div>

                <div data-field="cleaning_of_incubator_floor_area" @style(["display:none" => !$this->isFieldVisible('cleaning_of_incubator_floor_area')])>
                    <x-dropdown label="Cleaning of incubator floor area" name="cleaning_of_incubator_floor_area" error-key="form.cleaning_of_incubator_floor_area" placeholder="Select condition" wire:model.live="form.cleaning_of_incubator_floor_area" required>
                        <option value="Pending">Pending</option>
                        <option value="Done">Done</option>
                    </x-dropdown>
                    <x-photo-attach label="Attach Photos" name="cleaning_of_incubator_floor_area_photos"/>
                </div>

                <div data-field="cleaning_of_entrance_and_exit_area_flooring" @style(["display:none" => !$this->isFieldVisible('cleaning_of_entrance_and_exit_area_flooring')])>
                    <x-dropdown label="Cleaning of entrance and exit area flooring" name="cleaning_of_entrance_and_exit_area_flooring" error-key="form.cleaning_of_entrance_and_exit_area_flooring" placeholder="Select condition" wire:model.live="form.cleaning_of_entrance_and_exit_area_flooring" required>
                        <option value="Pending">Pending</option>
                        <option value="Done">Done</option>
                    </x-dropdown>
                    <x-photo-attach label="Attach Photos" name="cleaning_of_entrance_and_exit_area_flooring_photos"/>
                </div>

                <div data-field="clean_and_refill_water_reservoir" @style(["display:none" => !$this->isFieldVisible('clean_and_refill_water_reservoir')])>
                    <x-dropdown label="Clean and refill water reservoir" name="clean_and_refill_water_reservoir" error-key="form.clean_and_refill_water_reservoir" placeholder="Select condition" wire:model.live="form.clean_and_refill_water_reservoir" required>
                        <option value="Pending">Pending</option>
                        <option value="Done">Done</option>
                    </x-dropdown>
                    <x-photo-attach label="Attach Photos" name="clean_and_refill_water_reservoir_photos"/>
                </div>
            </div>

            <div data-step="5" class="space-y-4" @style(["display:none" => $currentStep !== 5])>
                <x-title>OTHERS</x-title>

                <div data-field="egg_setting_preparation" @style(["display:none" => !$this->isFieldVisible('egg_setting_preparation')])>
                    <x-dropdown label="Egg setting preparation" name="egg_setting_preparation" error-key="form.egg_setting_preparation" placeholder="Select condition" wire:model.live="form.egg_setting_preparation" required>
                        <option value="Pending">Pending</option>
                        <option value="Done">Done</option>
                    </x-dropdown>
                    <x-photo-attach label="Attach Photos" name="egg_setting_preparation_photos"/>
                </div>

                <div data-field="egg_setting" @style(["display:none" => !$this->isFieldVisible('egg_setting')])>
                    <x-dropdown label="Egg setting" name="egg_setting" error-key="form.egg_setting" placeholder="Select condition" wire:model.live="form.egg_setting" required>
                        <option value="Pending">Pending</option>
                        <option value="Done">Done</option>
                    </x-dropdown>
                    <x-photo-attach label="Attach Photos" name="egg_setting_photos"/>
                </div>

                <div data-field="record_egg_setting_on_board" @style(["display:none" => !$this->isFieldVisible('record_egg_setting_on_board')])>
                    <x-dropdown label="Record egg setting on board" name="record_egg_setting_on_board" error-key="form.record_egg_setting_on_board" placeholder="Select condition" wire:model.live="form.record_egg_setting_on_board" required>
                        <option value="Pending">Pending</option>
                        <option value="Done">Done</option>
                    </x-dropdown>
                    <x-photo-attach label="Attach Photos" name="record_egg_setting_on_board_photos"/>
                </div>

                <div data-field="record_egg_setting_time" @style(["display:none" => !$this->isFieldVisible('record_egg_setting_time')])>
                    <x-dropdown label="Record egg setting time" name="record_egg_setting_time" error-key="form.record_egg_setting_time" placeholder="Select condition" wire:model.live="form.record_egg_setting_time" required>
                        <option value="Pending">Pending</option>
                        <option value="Done">Done</option>
                    </x-dropdown>
                    <x-photo-attach label="Attach Photos" name="record_egg_setting_time_photos"/>
                </div>

                <div data-field="assist_in_random_candling" @style(["display:none" => !$this->isFieldVisible('assist_in_random_candling')])>
                    <x-dropdown label="Assist in Random Candling" name="assist_in_random_candling" error-key="form.assist_in_random_candling" placeholder="Select condition" wire:model.live="form.assist_in_random_candling" required>
                        <option value="Pending">Pending</option>
                        <option value="Done">Done</option>
                    </x-dropdown>
                    <x-photo-attach label="Attach Photos" name="assist_in_random_candling_photos"/>
                </div>
            </div>
        </x-progress-navigation>
    </form>
</div>
