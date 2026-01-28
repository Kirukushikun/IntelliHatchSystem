<x-layout>
    <div class="min-h-screen flex items-center justify-center bg-gray-100 p-4">
        <!-- Form/Card Container -->
        <div class="w-full max-w-lg bg-white rounded-xl shadow-lg px-8 pt-6">
            
            <!-- User Info & Logout -->
            <div class="flex items-center justify-between mb-4 pb-4 border-b border-gray-200">
                <div class="text-sm text-gray-600">
                    Welcome, <span class="font-medium text-gray-900">{{ auth()->user()->full_name }}</span>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <x-button variant="outline-secondary" size="sm" type="submit">
                        Logout
                    </x-button>
                </form>
            </div>

            <!-- Progress and Navigation Component -->
            <x-progress-navigation :current-step="1" :total-steps="5">
                <!-- Form Content -->
                <form id="step-form" class="space-y-4">
                    @csrf
                    
                    <!-- Step 1 -->
                    <div id="step-1" class="space-y-4">
                        <x-title>
                            INCUBATOR ROUTINE CHECKLIST PER SHIFT
                        </x-title>
                        <x-dropdown label="Shift" name="shift" placeholder="Select shift" required>
                            <option value="1st Shift">1st Shift</option>
                            <option value="2nd Shift">2nd Shift</option>
                            <option value="3rd Shift">3rd Shift</option>
                        </x-dropdown>
                        <x-dropdown label="Check for Alarm system condition" name="alarm_system_condition" required>
                            <option value="N/A" selected>N/A</option>
                            <option value="Operational">Operational</option>
                            <option value="Unoperational">Unoperational</option>
                        </x-dropdown>
                        <x-text-area label="Corrective Action" name="corrective_action" placeholder="Enter your answer..." required/>
                        <x-photo-attach label="Attach Photos" name="corrective_action_photos"/>
                        <x-text-area label="Additional Notes" name="notes" placeholder="Any additional observations..."/>
                    </div>

                    <!-- Step 2 -->
                    <div id="step-2" class="space-y-4" style="display: none;">
                        <x-title>
                            PLENUM
                        </x-title>
                        <x-dropdown label="Cleaning of incubator roof and plenum" name="cleaning_incubator_roof_and_plenum" required>
                            <option value="N/A" selected>N/A</option>
                            <option value="Pending">Pending</option>
                            <option value="Done">Done</option>
                        </x-dropdown>
                        <x-photo-attach label="Attach Photos" name="cleaning_incubator_roof_and_plenum_photos"/>
                    </div>
                    
                    <!-- Step 3 -->
                    <div id="step-3" class="space-y-4" style="display: none;">
                        <x-title>
                            GENERAL MACHINE
                        </x-title>
                        <x-dropdown label="Check incubator doors for air leakage" name="check_incubator_doors_for_air_leakage" required>
                            <option value="N/A" selected>N/A</option>
                            <option value="Pending">Pending</option>
                            <option value="Done">Done</option>
                        </x-dropdown>
                        <x-photo-attach label="Attach Photos" name="check_incubator_doors_for_air_leakage_photos"/>
                        <x-dropdown label="Checking of baggy against the gaskets" name="checking_of_baggy_against_the_gaskets" required>
                            <option value="N/A" selected>N/A</option>
                            <option value="Pending">Pending</option>
                            <option value="Done">Done</option>
                        </x-dropdown>
                        <x-photo-attach label="Attach Photos" name="checking_of_baggy_against_the_gaskets_photos"/>
                        <x-dropdown label="Check curtain position and condition" name="check_curtain_position_and_condition" required>
                            <option value="N/A" selected>N/A</option>
                            <option value="Pending">Pending</option>
                            <option value="Done">Done</option>
                        </x-dropdown>
                        <x-photo-attach label="Attach Photos" name="check_curtain_position_and_condition_photos"/>
                        <x-dropdown label="Check wick for replacement / washing" name="check_wick_for_replacement_washing" required>
                            <option value="N/A" selected>N/A</option>
                            <option value="Pending">Pending</option>
                            <option value="Done">Done</option>
                        </x-dropdown>
                        <x-photo-attach label="Attach Photos" name="check_wick_for_replacement_washing_photos"/>
                        <x-dropdown label="Check spray nozzle and water pan" name="check_spray_nozzle_and_water_pan" required>
                            <option value="N/A" selected>N/A</option>
                            <option value="Pending">Pending</option>
                            <option value="Done">Done</option>
                        </x-dropdown>
                        <x-photo-attach label="Attach Photos" name="check_spray_nozzle_and_water_pan_photos"/>
                        <x-dropdown label="Check incubator fans for vibration" name="check_incubator_fans_for_vibration" required>
                            <option value="N/A" selected>N/A</option>
                            <option value="Pending">Pending</option>
                            <option value="Done">Done</option>
                        </x-dropdown>
                        <x-photo-attach label="Attach Photos" name="check_incubator_fans_for_vibration_photos"/>
                        <x-dropdown label="Check rack baffle condition" name="check_rack_baffle_condition" required>
                            <option value="N/A" selected>N/A</option>
                            <option value="Pending">Pending</option>
                            <option value="Done">Done</option>
                        </x-dropdown>
                        <x-photo-attach label="Attach Photos" name="check_rack_baffle_condition_photos"/>
                        <x-dropdown label="Drain water out from air compressor tank" name="drain_water_out_from_air_compressor_tank" required>
                            <option value="N/A" selected>N/A</option>
                            <option value="Pending">Pending</option>
                            <option value="Done">Done</option>
                        </x-dropdown>
                        <x-photo-attach label="Attach Photos" name="drain_water_out_from_air_compressor_tank_photos"/>
                    </div>

                    <!-- Step 4 -->
                    <div id="step-4" class="space-y-4" style="display: none;">
                        <x-title>
                            CLEANING
                        </x-title>
                        <x-dropdown label="Check water level of blue tank" name="check_water_level_of_blue_tank" required>
                            <option value="N/A" selected>N/A</option>
                            <option value="Pending">Pending</option>
                            <option value="Done">Done</option>
                        </x-dropdown>
                        <x-photo-attach label="Attach Photos" name="check_water_level_of_blue_tank_photos"/>
                        <x-dropdown label="Cleaning of entrance and exit area flooring" name="cleaning_of_entrance_and_exit_area_flooring" required>
                            <option value="N/A" selected>N/A</option>
                            <option value="Pending">Pending</option>
                            <option value="Done">Done</option>
                        </x-dropdown>
                        <x-photo-attach label="Attach Photos" name="cleaning_of_entrance_and_exit_area_flooring_photos"/>
                        <x-dropdown label="Clean and refill water reservoir" name="clean_and_refill_water_reservoir" required>
                            <option value="N/A" selected>N/A</option>
                            <option value="Pending">Pending</option>
                            <option value="Done">Done</option>
                        </x-dropdown>
                        <x-photo-attach label="Attach Photos" name="clean_and_refill_water_reservoir_photos"/>
                    </div>

                    <!-- Step 5 -->
                    <div id="step-5" class="space-y-4" style="display: none;">
                        <x-title>
                            OTHERS
                        </x-title>
                        <x-dropdown label="Egg setting preparation" name="egg_setting_preparation" required>
                            <option value="N/A" selected>N/A</option>
                            <option value="Pending">Pending</option>
                            <option value="Done">Done</option>
                        </x-dropdown>
                        <x-photo-attach label="Attach Photos" name="egg_setting_preparation_photos"/>
                        <x-dropdown label="Egg setting" name="egg_setting" required>
                            <option value="N/A" selected>N/A</option>
                            <option value="Pending">Pending</option>
                            <option value="Done">Done</option>
                        </x-dropdown>
                        <x-photo-attach label="Attach Photos" name="egg_setting_photos"/>
                        <x-dropdown label="Record egg setting on board" name="record_egg_setting_on_board" required>
                            <option value="N/A" selected>N/A</option>
                            <option value="Pending">Pending</option>
                            <option value="Done">Done</option>
                        </x-dropdown>
                        <x-photo-attach label="Attach Photos" name="record_egg_setting_on_board_photos"/>
                        <x-dropdown label="Record egg setting time" name="record_egg_setting_time" required>
                            <option value="N/A" selected>N/A</option>
                            <option value="Pending">Pending</option>
                            <option value="Done">Done</option>
                        </x-dropdown>
                        <x-photo-attach label="Attach Photos" name="record_egg_setting_time_photos"/>
                        <x-dropdown label="Assist in Random Candling" name="assist_in_random_candling" required>
                            <option value="N/A" selected>N/A</option>
                            <option value="Pending">Pending</option>
                            <option value="Done">Done</option>
                        </x-dropdown>
                        <x-photo-attach label="Attach Photos" name="assist_in_random_candling_photos"/>
                    </div>
                    

                </form>
            </x-progress-navigation>
        </div>
    </div>
</x-layout>
