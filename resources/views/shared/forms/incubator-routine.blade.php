<x-layout>
    <x-navbar :includeSidebar="true" :user="Auth::user()" :hideDate="true">
        <x-slot:navbarActions>
            <x-button 
                href="{{ ((int) Auth::user()->user_type) === 0 ? '/admin/forms' : '/user/forms' }}"
                variant="outline-secondary" 
                size="sm"
            >
                Back to Forms
            </x-button>
        </x-slot:navbarActions>
        
        <div class="p-4">
            <!-- Form/Card Container -->
            <div class="w-full max-w-lg bg-white rounded-xl shadow-lg px-8 pt-6 pb-2 mx-auto">
                <!-- Progress and Navigation Component -->
                @php
                $incubatorSchedule = [
                    '_daily' => [
                        'shift', 'alarm_system_condition', 'corrective_action',
                        'check_incubator_doors_for_air_leakage',
                        'drain_water_out_from_air_compressor_tank',
                        'check_water_level_of_blue_tank',
                        'incubator_machine_inspected'
                    ],
                    'Monday-1st Shift' => [
                        'cleaning_of_incubator_floor_area',
                        'assist_in_random_candling'
                    ],
                    'Monday-2nd Shift' => [
                        'check_spray_nozzle_and_water_pan',
                        'check_incubator_fans_for_vibration',
                        'egg_setting_preparation'
                    ],
                    'Monday-3rd Shift' => [
                        'checking_of_baggy_against_the_gaskets',
                        'check_curtain_position_and_condition',
                        'check_rack_baffle_condition',
                        'egg_setting',
                        'record_egg_setting_on_board',
                        'record_egg_setting_time'
                    ],
                    'Tuesday-1st Shift' => [],
                    'Tuesday-2nd Shift' => [
                        'check_wick_for_replacement_washing',
                        'cleaning_of_incubator_floor_area',
                        'clean_and_refill_water_reservoir'
                    ],
                    'Tuesday-3rd Shift' => [
                        'check_spray_nozzle_and_water_pan',
                        'check_incubator_fans_for_vibration'
                    ],
                    'Wednesday-1st Shift' => [
                        'cleaning_of_incubator_floor_area',
                        'cleaning_of_entrance_and_exit_area_flooring'
                    ],
                    'Wednesday-2nd Shift' => [],
                    'Wednesday-3rd Shift' => [
                        'cleaning_incubator_roof_and_plenum',
                        'check_spray_nozzle_and_water_pan',
                        'check_incubator_fans_for_vibration'
                    ],
                    'Thursday-1st Shift' => [
                        'cleaning_of_incubator_floor_area'
                    ],
                    'Thursday-2nd Shift' => [
                        'check_spray_nozzle_and_water_pan',
                        'check_incubator_fans_for_vibration',
                        'egg_setting_preparation'
                    ],
                    'Thursday-3rd Shift' => [
                        'checking_of_baggy_against_the_gaskets',
                        'check_curtain_position_and_condition',
                        'check_rack_baffle_condition',
                        'egg_setting',
                        'record_egg_setting_on_board',
                        'record_egg_setting_time'
                    ],
                    'Friday-1st Shift' => [],
                    'Friday-2nd Shift' => [
                        'check_wick_for_replacement_washing',
                        'cleaning_of_incubator_floor_area',
                        'clean_and_refill_water_reservoir'
                    ],
                    'Friday-3rd Shift' => [
                        'check_spray_nozzle_and_water_pan',
                        'check_incubator_fans_for_vibration'
                    ],
                    'Saturday-1st Shift' => [],
                    'Saturday-2nd Shift' => [],
                    'Saturday-3rd Shift' => [
                        'check_spray_nozzle_and_water_pan',
                        'check_incubator_fans_for_vibration'
                    ],
                    'Sunday-1st Shift' => [
                        'cleaning_of_incubator_floor_area'
                    ],
                    'Sunday-2nd Shift' => [
                        'cleaning_of_incubator_floor_area'
                    ],
                    'Sunday-3rd Shift' => [
                        'cleaning_incubator_roof_and_plenum',
                        'check_spray_nozzle_and_water_pan',
                        'check_incubator_fans_for_vibration'
                    ],
                ];

                $incubatorMachineInspectedOptions = [
                    1, 
                    2, 
                    3, 
                    4, 
                    5, 
                    6, 
                    7, 
                    8, 
                    9, 
                    10
                ];

                @endphp
                
                <x-progress-navigation :current-step="1" :total-steps="5" :schedule="$incubatorSchedule">
                    <!-- Form Content -->
                    <form id="step-form" class="space-y-4">
                        @csrf
                        
                        <!-- Step 1: DAILY ITEMS (Always visible) -->
                        <div id="step-1" class="space-y-4">
                            <x-title>
                                INCUBATOR ROUTINE CHECKLIST
                            </x-title>
                            <x-dropdown label="Shift" name="shift" placeholder="Select shift" required>
                                <option value="" hidden>Select shift</option>
                                <option value="1st Shift">1st Shift</option>
                                <option value="2nd Shift">2nd Shift</option>
                                <option value="3rd Shift">3rd Shift</option>
                            </x-dropdown>
                            <x-dropdown label="Check for Alarm system condition" name="alarm_system_condition" placeholder="Select condition" required>
                                <option value="" hidden>Select condition</option>
                                <option value="Operational">Operational</option>
                                <option value="Unoperational">Unoperational</option>
                            </x-dropdown>
                            <x-text-area label="Corrective Action" name="corrective_action" placeholder="Enter your answer..." required/>
                            <x-photo-attach label="Attach Photos" name="corrective_action_photos"/>
                        </div>

                        <!-- Step 2: GENERAL MACHINE - DAILY & SCHEDULED -->
                        <div id="step-2" class="space-y-4" style="display: none;">
                            <x-title>
                                GENERAL MACHINE
                            </x-title>
                            
                            <!-- DAILY ITEM -->
                            <div>
                                <x-dropdown label="Check incubator doors for air leakage" name="check_incubator_doors_for_air_leakage" placeholder="Select condition" required>
                                    <option value="" hidden>Select condition</option>
                                    <option value="Pending">Pending</option>
                                    <option value="Done">Done</option>
                                </x-dropdown>
                                <x-photo-attach label="Attach Photos" name="check_incubator_doors_for_air_leakage_photos"/>
                            </div>
                            
                            <!-- SCHEDULED ITEM -->
                            <div>
                                <x-dropdown label="Checking of baggy against the gaskets" name="checking_of_baggy_against_the_gaskets" placeholder="Select condition" required>
                                    <option value="" hidden>Select condition</option>
                                    <option value="Pending">Pending</option>
                                    <option value="Done">Done</option>
                                </x-dropdown>
                                <x-photo-attach label="Attach Photos" name="checking_of_baggy_against_the_gaskets_photos"/>
                            </div>
                            
                            <!-- SCHEDULED ITEM -->
                            <div>
                                <x-dropdown label="Check curtain position and condition" name="check_curtain_position_and_condition" placeholder="Select condition" required>
                                    <option value="" hidden>Select condition</option>
                                    <option value="Pending">Pending</option>
                                    <option value="Done">Done</option>
                                </x-dropdown>
                                <x-photo-attach label="Attach Photos" name="check_curtain_position_and_condition_photos"/>
                            </div>
                            
                            <!-- SCHEDULED ITEM -->
                            <div>
                                <x-dropdown label="Check wick for replacement / washing" name="check_wick_for_replacement_washing" placeholder="Select condition" required>
                                    <option value="" hidden>Select condition</option>
                                    <option value="Pending">Pending</option>
                                    <option value="Done">Done</option>
                                </x-dropdown>
                                <x-photo-attach label="Attach Photos" name="check_wick_for_replacement_washing_photos"/>
                            </div>
                            
                            <!-- SCHEDULED ITEM -->
                            <div>
                                <x-dropdown label="Check spray nozzle and water pan" name="check_spray_nozzle_and_water_pan" placeholder="Select condition" required>
                                    <option value="" hidden>Select condition</option>
                                    <option value="Pending">Pending</option>
                                    <option value="Done">Done</option>
                                </x-dropdown>
                                <x-photo-attach label="Attach Photos" name="check_spray_nozzle_and_water_pan_photos"/>
                            </div>
                            
                            <!-- SCHEDULED ITEM -->
                            <div>
                                <x-dropdown label="Check incubator fans for vibration" name="check_incubator_fans_for_vibration" placeholder="Select condition" required>
                                    <option value="" hidden>Select condition</option>
                                    <option value="Pending">Pending</option>
                                    <option value="Done">Done</option>
                                </x-dropdown>
                                <x-photo-attach label="Attach Photos" name="check_incubator_fans_for_vibration_photos"/>
                            </div>
                            
                            <!-- SCHEDULED ITEM -->
                            <div>
                                <x-dropdown label="Check rack baffle condition" name="check_rack_baffle_condition" placeholder="Select condition" required>
                                    <option value="" hidden>Select condition</option>
                                    <option value="Pending">Pending</option>
                                    <option value="Done">Done</option>
                                </x-dropdown>
                                <x-photo-attach label="Attach Photos" name="check_rack_baffle_condition_photos"/>
                            </div>
                            
                            <!-- DAILY ITEM -->
                            <div>
                                <x-dropdown label="Drain water out from air compressor tank" name="drain_water_out_from_air_compressor_tank" placeholder="Select condition" required>
                                    <option value="" hidden>Select condition</option>
                                    <option value="Pending">Pending</option>
                                    <option value="Done">Done</option>
                                </x-dropdown>
                                <x-photo-attach label="Attach Photos" name="drain_water_out_from_air_compressor_tank_photos"/>
                            </div>
                        </div>

                        <!-- Step 3: PLENUM - SCHEDULED ONLY -->
                        <div id="step-3" class="space-y-4" style="display: none;">
                            <x-title>
                                PLENUM
                            </x-title>
                            
                            <!-- SCHEDULED ITEM - Wednesday 3rd & Sunday 3rd -->
                            <div>
                                <x-dropdown label="Cleaning of incubator roof and plenum" name="cleaning_incubator_roof_and_plenum" placeholder="Select condition" required>
                                    <option value="" hidden>Select condition</option>
                                    <option value="Pending">Pending</option>
                                    <option value="Done">Done</option>
                                </x-dropdown>
                                <x-photo-attach label="Attach Photos" name="cleaning_incubator_roof_and_plenum_photos"/>
                            </div>
                        </div>
                        
                        <!-- Step 4: CLEANING - DAILY & SCHEDULED -->
                        <div id="step-4" class="space-y-4" style="display: none;">
                            <x-title>
                                CLEANING
                            </x-title>
                            
                            <!-- DAILY ITEM -->
                            <div>
                                <x-dropdown label="Check water level of blue tank" name="check_water_level_of_blue_tank" placeholder="Select condition" required>
                                    <option value="" hidden>Select condition</option>
                                    <option value="Pending">Pending</option>
                                    <option value="Done">Done</option>
                                </x-dropdown>
                                <x-photo-attach label="Attach Photos" name="check_water_level_of_blue_tank_photos"/>
                            </div>
                            
                            <!-- SCHEDULED ITEM -->
                            <div>
                                <x-dropdown label="Cleaning of incubator floor area" name="cleaning_of_incubator_floor_area" placeholder="Select condition" required>
                                    <option value="" hidden>Select condition</option>
                                    <option value="Pending">Pending</option>
                                    <option value="Done">Done</option>
                                </x-dropdown>
                                <x-photo-attach label="Attach Photos" name="cleaning_of_incubator_floor_area_photos"/>
                            </div>
                            
                            <!-- SCHEDULED ITEM -->
                            <div>
                                <x-dropdown label="Cleaning of entrance and exit area flooring" name="cleaning_of_entrance_and_exit_area_flooring" placeholder="Select condition" required>
                                    <option value="" hidden>Select condition</option>
                                    <option value="Pending">Pending</option>
                                    <option value="Done">Done</option>
                                </x-dropdown>
                                <x-photo-attach label="Attach Photos" name="cleaning_of_entrance_and_exit_area_flooring_photos"/>
                            </div>
                            
                            <!-- SCHEDULED ITEM -->
                            <div>
                                <x-dropdown label="Clean and refill water reservoir" name="clean_and_refill_water_reservoir" placeholder="Select condition" required>
                                    <option value="" hidden>Select condition</option>
                                    <option value="Pending">Pending</option>
                                    <option value="Done">Done</option>
                                </x-dropdown>
                                <x-photo-attach label="Attach Photos" name="clean_and_refill_water_reservoir_photos"/>
                            </div>
                        </div>

                        <!-- Step 5: OTHERS - SCHEDULED ONLY -->
                        <div id="step-5" class="space-y-4" style="display: none;">
                            <x-title>
                                OTHERS
                            </x-title>
                            
                            <!-- SCHEDULED ITEM -->
                            <div>
                                <x-dropdown label="Egg setting preparation" name="egg_setting_preparation" placeholder="Select condition" required>
                                    <option value="" hidden>Select condition</option>
                                    <option value="Pending">Pending</option>
                                    <option value="Done">Done</option>
                                </x-dropdown>
                                <x-photo-attach label="Attach Photos" name="egg_setting_preparation_photos"/>
                            </div>
                            
                            <!-- SCHEDULED ITEM -->
                            <div>
                                <x-dropdown label="Egg setting" name="egg_setting" placeholder="Select condition" required>
                                    <option value="" hidden>Select condition</option>
                                    <option value="Pending">Pending</option>
                                    <option value="Done">Done</option>
                                </x-dropdown>
                                <x-photo-attach label="Attach Photos" name="egg_setting_photos"/>
                            </div>
                            
                            <!-- SCHEDULED ITEM -->
                            <div>
                                <x-dropdown label="Record egg setting on board" name="record_egg_setting_on_board" placeholder="Select condition" required>
                                    <option value="" hidden>Select condition</option>
                                    <option value="Pending">Pending</option>
                                    <option value="Done">Done</option>
                                </x-dropdown>
                                <x-photo-attach label="Attach Photos" name="record_egg_setting_on_board_photos"/>
                            </div>
                            
                            <!-- SCHEDULED ITEM -->
                            <div>
                                <x-dropdown label="Record egg setting time" name="record_egg_setting_time" placeholder="Select condition" required>
                                    <option value="" hidden>Select condition</option>
                                    <option value="Pending">Pending</option>
                                    <option value="Done">Done</option>
                                </x-dropdown>
                                <x-photo-attach label="Attach Photos" name="record_egg_setting_time_photos"/>
                            </div>
                            
                            <!-- SCHEDULED ITEM -->
                            <div data-field="assist_in_random_candling">
                                <x-dropdown label="Assist in Random Candling" name="assist_in_random_candling" placeholder="Select condition" required>
                                    <option value="" hidden>Select condition</option>
                                    <option value="Pending">Pending</option>
                                    <option value="Done">Done</option>
                                </x-dropdown>
                                <x-photo-attach label="Attach Photos" name="assist_in_random_candling_photos"/>
                            </div>
                            
                            <!-- DAILY ITEM -->
                            <div data-field="incubator_machine_inspected">
                                <x-checkbox 
                                    label="Incubator Machine Inspected" 
                                    name="incubator_machine_inspected"
                                    :options="$incubatorMachineInspectedOptions"
                                    :required="true"
                                    :columns="2"
                                />
                            </div>
                        </div>

                    </form>
                </x-progress-navigation>
            </div>
        </div>
    </x-navbar>
</x-layout>