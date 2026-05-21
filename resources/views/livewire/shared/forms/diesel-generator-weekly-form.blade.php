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
                <x-title>HATCHERY DIESEL GENERATOR WEEKLY MAINTENANCE CHECKLIST</x-title>

                <p class="text-sm text-gray-500 dark:text-gray-400 -mt-2">Weekly maintenance checklist for diesel generator sets.</p>

                <div data-field="technician_id">
                    <x-personnel-select
                        label="Name of Maintenance Technician"
                        name="technician_id"
                        error-key="form.technician_id"
                        :personnel="$personnelList"
                        :logged-in-user-id="$loggedInUserId"
                        wire:model.live="form.technician_id"
                        required
                    />
                </div>

                <div data-field="gen_set_number">
                    <x-dropdown
                        label="Diesel Generator Set #"
                        name="gen_set_number"
                        error-key="form.gen_set_number"
                        placeholder="Select generator set"
                        wire:model.live="form.gen_set_number"
                        required
                    >
                        @foreach($genSetList as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </x-dropdown>
                </div>
            </div>

            {{-- Step 2: BATTERY CONDITION (for 2 Batteries) --}}
            <div data-step="2" class="space-y-4" @style(["display:none" => $currentStep !== 2])>
                <x-title>BATTERY CONDITION (FOR 2 BATTERIES)</x-title>

                <div data-field="battery_condition_status">
                    <x-dropdown
                        label="BATTERY CONDITION (for 2 Batteries)"
                        name="battery_condition_status"
                        error-key="form.battery_condition_status"
                        placeholder="Select result"
                        wire:model.live="form.battery_condition_status"
                        required
                    >
                        <option value="Okay">Okay</option>
                        <option value="Not Okay">Not Okay</option>
                    </x-dropdown>
                </div>

                <div data-field="battery_condition_problem" class="relative">
                    <button type="button" @click="$wire.set('form.battery_condition_problem', 'N/A')" class="absolute right-0 top-0 z-10 text-xs font-medium px-2 py-0.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded text-gray-600 dark:text-gray-300 transition-colors">N/A</button>
                    <x-text-area
                        label="If not okay, explain why and identify the problem."
                        name="battery_condition_problem"
                        error-key="form.battery_condition_problem"
                        :required="true"
                        placeholder='Write "N/A" if not applicable.'
                        wire:model.live="form.battery_condition_problem"
                    />
                </div>

                <div data-field="battery_condition_corrective_action" class="relative">
                    <button type="button" @click="$wire.set('form.battery_condition_corrective_action', 'N/A')" class="absolute right-0 top-0 z-10 text-xs font-medium px-2 py-0.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded text-gray-600 dark:text-gray-300 transition-colors">N/A</button>
                    <x-text-area
                        label="Corrective Action Taken"
                        name="battery_condition_corrective_action"
                        error-key="form.battery_condition_corrective_action"
                        :required="true"
                        placeholder='Write "N/A" if not applicable.'
                        wire:model.live="form.battery_condition_corrective_action"
                    />
                </div>

                <div data-field="battery_voltage_reading">
                    <x-text-input
                        label="Battery Voltage Reading (see from genset monitor)"
                        name="battery_voltage_reading"
                        error-key="form.battery_voltage_reading"
                        :required="true"
                        placeholder="Enter battery voltage reading"
                        wireModel="form.battery_voltage_reading"
                    />
                </div>

                <div data-field="photo_battery_condition">
                    <x-photo-attach label="Attach picture of battery voltage reading from genset monitor." name="photo_battery_condition" required />
                </div>
            </div>

            {{-- Step 3: LUBRICATION - Check for oil level --}}
            <div data-step="3" class="space-y-4" @style(["display:none" => $currentStep !== 3])>
                <x-title>LUBRICATION — CHECK FOR OIL LEVEL</x-title>

                <div data-field="lub_oil_level_status">
                    <x-dropdown
                        label="LUBRICATION - Check for oil level"
                        name="lub_oil_level_status"
                        error-key="form.lub_oil_level_status"
                        placeholder="Select result"
                        wire:model.live="form.lub_oil_level_status"
                        required
                    >
                        <option value="Okay">Okay</option>
                        <option value="Not Okay">Not Okay</option>
                    </x-dropdown>
                </div>

                <div data-field="lub_oil_level_problem" class="relative">
                    <button type="button" @click="$wire.set('form.lub_oil_level_problem', 'N/A')" class="absolute right-0 top-0 z-10 text-xs font-medium px-2 py-0.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded text-gray-600 dark:text-gray-300 transition-colors">N/A</button>
                    <x-text-area
                        label="If not okay, explain why and identify the problem."
                        name="lub_oil_level_problem"
                        error-key="form.lub_oil_level_problem"
                        :required="true"
                        placeholder='Write "N/A" if not applicable.'
                        wire:model.live="form.lub_oil_level_problem"
                    />
                </div>

                <div data-field="lub_oil_level_corrective_action" class="relative">
                    <button type="button" @click="$wire.set('form.lub_oil_level_corrective_action', 'N/A')" class="absolute right-0 top-0 z-10 text-xs font-medium px-2 py-0.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded text-gray-600 dark:text-gray-300 transition-colors">N/A</button>
                    <x-text-area
                        label="Corrective Action Taken"
                        name="lub_oil_level_corrective_action"
                        error-key="form.lub_oil_level_corrective_action"
                        :required="true"
                        placeholder='Write "N/A" if not applicable.'
                        wire:model.live="form.lub_oil_level_corrective_action"
                    />
                </div>

                <div data-field="photo_lub_oil_level">
                    <x-photo-attach label="Attach necessary pictures or document for reference." name="photo_lub_oil_level" required />
                </div>
            </div>

            {{-- Step 4: COOLING SYSTEM - Check for leaks --}}
            <div data-step="4" class="space-y-4" @style(["display:none" => $currentStep !== 4])>
                <x-title>COOLING SYSTEM — CHECK FOR LEAKS</x-title>

                <div data-field="cool_leaks_status">
                    <x-dropdown
                        label="COOLING SYSTEM - Check for leaks"
                        name="cool_leaks_status"
                        error-key="form.cool_leaks_status"
                        placeholder="Select result"
                        wire:model.live="form.cool_leaks_status"
                        required
                    >
                        <option value="Okay">Okay</option>
                        <option value="Not Okay">Not Okay</option>
                    </x-dropdown>
                </div>

                <div data-field="cool_leaks_problem" class="relative">
                    <button type="button" @click="$wire.set('form.cool_leaks_problem', 'N/A')" class="absolute right-0 top-0 z-10 text-xs font-medium px-2 py-0.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded text-gray-600 dark:text-gray-300 transition-colors">N/A</button>
                    <x-text-area
                        label="If not okay, explain why and identify the problem."
                        name="cool_leaks_problem"
                        error-key="form.cool_leaks_problem"
                        :required="true"
                        placeholder='Write "N/A" if not applicable.'
                        wire:model.live="form.cool_leaks_problem"
                    />
                </div>

                <div data-field="cool_leaks_corrective_action" class="relative">
                    <button type="button" @click="$wire.set('form.cool_leaks_corrective_action', 'N/A')" class="absolute right-0 top-0 z-10 text-xs font-medium px-2 py-0.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded text-gray-600 dark:text-gray-300 transition-colors">N/A</button>
                    <x-text-area
                        label="Corrective Action Taken"
                        name="cool_leaks_corrective_action"
                        error-key="form.cool_leaks_corrective_action"
                        :required="true"
                        placeholder='Write "N/A" if not applicable.'
                        wire:model.live="form.cool_leaks_corrective_action"
                    />
                </div>

                <div data-field="photo_cool_leaks">
                    <x-photo-attach label="Attach necessary pictures or document for reference." name="photo_cool_leaks" required />
                </div>
            </div>

            {{-- Step 5: COOLING SYSTEM - Check for radiator restriction --}}
            <div data-step="5" class="space-y-4" @style(["display:none" => $currentStep !== 5])>
                <x-title>COOLING SYSTEM — CHECK FOR RADIATOR RESTRICTION</x-title>

                <div data-field="cool_radiator_status">
                    <x-dropdown
                        label="COOLING SYSTEM - Check for radiator restriction"
                        name="cool_radiator_status"
                        error-key="form.cool_radiator_status"
                        placeholder="Select result"
                        wire:model.live="form.cool_radiator_status"
                        required
                    >
                        <option value="Okay">Okay</option>
                        <option value="Not Okay">Not Okay</option>
                    </x-dropdown>
                </div>

                <div data-field="cool_radiator_problem" class="relative">
                    <button type="button" @click="$wire.set('form.cool_radiator_problem', 'N/A')" class="absolute right-0 top-0 z-10 text-xs font-medium px-2 py-0.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded text-gray-600 dark:text-gray-300 transition-colors">N/A</button>
                    <x-text-area
                        label="If not okay, explain why and identify the problem."
                        name="cool_radiator_problem"
                        error-key="form.cool_radiator_problem"
                        :required="true"
                        placeholder='Write "N/A" if not applicable.'
                        wire:model.live="form.cool_radiator_problem"
                    />
                </div>

                <div data-field="cool_radiator_corrective_action" class="relative">
                    <button type="button" @click="$wire.set('form.cool_radiator_corrective_action', 'N/A')" class="absolute right-0 top-0 z-10 text-xs font-medium px-2 py-0.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded text-gray-600 dark:text-gray-300 transition-colors">N/A</button>
                    <x-text-area
                        label="Corrective Action Taken"
                        name="cool_radiator_corrective_action"
                        error-key="form.cool_radiator_corrective_action"
                        :required="true"
                        placeholder='Write "N/A" if not applicable.'
                        wire:model.live="form.cool_radiator_corrective_action"
                    />
                </div>

                <div data-field="photo_cool_radiator">
                    <x-photo-attach label="Attach necessary pictures or document for reference." name="photo_cool_radiator" required />
                </div>
            </div>

            {{-- Step 6: COOLING SYSTEM - Check for hose and connections --}}
            <div data-step="6" class="space-y-4" @style(["display:none" => $currentStep !== 6])>
                <x-title>COOLING SYSTEM — CHECK FOR HOSE AND CONNECTIONS</x-title>

                <div data-field="cool_hose_status">
                    <x-dropdown
                        label="COOLING SYSTEM - Check for hose and connections"
                        name="cool_hose_status"
                        error-key="form.cool_hose_status"
                        placeholder="Select result"
                        wire:model.live="form.cool_hose_status"
                        required
                    >
                        <option value="Okay">Okay</option>
                        <option value="Not Okay">Not Okay</option>
                    </x-dropdown>
                </div>

                <div data-field="cool_hose_problem" class="relative">
                    <button type="button" @click="$wire.set('form.cool_hose_problem', 'N/A')" class="absolute right-0 top-0 z-10 text-xs font-medium px-2 py-0.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded text-gray-600 dark:text-gray-300 transition-colors">N/A</button>
                    <x-text-area
                        label="If not okay, explain why and identify the problem."
                        name="cool_hose_problem"
                        error-key="form.cool_hose_problem"
                        :required="true"
                        placeholder='Write "N/A" if not applicable.'
                        wire:model.live="form.cool_hose_problem"
                    />
                </div>

                <div data-field="cool_hose_corrective_action" class="relative">
                    <button type="button" @click="$wire.set('form.cool_hose_corrective_action', 'N/A')" class="absolute right-0 top-0 z-10 text-xs font-medium px-2 py-0.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded text-gray-600 dark:text-gray-300 transition-colors">N/A</button>
                    <x-text-area
                        label="Corrective Action Taken"
                        name="cool_hose_corrective_action"
                        error-key="form.cool_hose_corrective_action"
                        :required="true"
                        placeholder='Write "N/A" if not applicable.'
                        wire:model.live="form.cool_hose_corrective_action"
                    />
                </div>

                <div data-field="photo_cool_hose">
                    <x-photo-attach label="Attach necessary pictures or document for reference." name="photo_cool_hose" required />
                </div>
            </div>

            {{-- Step 7: COOLING SYSTEM - Coolant level --}}
            <div data-step="7" class="space-y-4" @style(["display:none" => $currentStep !== 7])>
                <x-title>COOLING SYSTEM — COOLANT LEVEL</x-title>

                <div data-field="cool_coolant_level_status">
                    <x-dropdown
                        label="COOLING SYSTEM - Coolant level"
                        name="cool_coolant_level_status"
                        error-key="form.cool_coolant_level_status"
                        placeholder="Select result"
                        wire:model.live="form.cool_coolant_level_status"
                        required
                    >
                        <option value="Okay">Okay</option>
                        <option value="Not Okay">Not Okay</option>
                    </x-dropdown>
                </div>

                <div data-field="cool_coolant_level_problem" class="relative">
                    <button type="button" @click="$wire.set('form.cool_coolant_level_problem', 'N/A')" class="absolute right-0 top-0 z-10 text-xs font-medium px-2 py-0.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded text-gray-600 dark:text-gray-300 transition-colors">N/A</button>
                    <x-text-area
                        label="If not okay, explain why and identify the problem."
                        name="cool_coolant_level_problem"
                        error-key="form.cool_coolant_level_problem"
                        :required="true"
                        placeholder='Write "N/A" if not applicable.'
                        wire:model.live="form.cool_coolant_level_problem"
                    />
                </div>

                <div data-field="cool_coolant_level_corrective_action" class="relative">
                    <button type="button" @click="$wire.set('form.cool_coolant_level_corrective_action', 'N/A')" class="absolute right-0 top-0 z-10 text-xs font-medium px-2 py-0.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded text-gray-600 dark:text-gray-300 transition-colors">N/A</button>
                    <x-text-area
                        label="Corrective Action Taken"
                        name="cool_coolant_level_corrective_action"
                        error-key="form.cool_coolant_level_corrective_action"
                        :required="true"
                        placeholder='Write "N/A" if not applicable.'
                        wire:model.live="form.cool_coolant_level_corrective_action"
                    />
                </div>

                <div data-field="photo_cool_coolant_level">
                    <x-photo-attach label="Attach necessary pictures or document for reference." name="photo_cool_coolant_level" required />
                </div>
            </div>

            {{-- Step 8: COOLING SYSTEM - Belt condition and tension --}}
            <div data-step="8" class="space-y-4" @style(["display:none" => $currentStep !== 8])>
                <x-title>COOLING SYSTEM — BELT CONDITION AND TENSION</x-title>

                <div data-field="cool_belt_status">
                    <x-dropdown
                        label="COOLING SYSTEM - Belt condition and tension"
                        name="cool_belt_status"
                        error-key="form.cool_belt_status"
                        placeholder="Select result"
                        wire:model.live="form.cool_belt_status"
                        required
                    >
                        <option value="Okay">Okay</option>
                        <option value="Not Okay">Not Okay</option>
                    </x-dropdown>
                </div>

                <div data-field="cool_belt_problem" class="relative">
                    <button type="button" @click="$wire.set('form.cool_belt_problem', 'N/A')" class="absolute right-0 top-0 z-10 text-xs font-medium px-2 py-0.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded text-gray-600 dark:text-gray-300 transition-colors">N/A</button>
                    <x-text-area
                        label="If not okay, explain why and identify the problem."
                        name="cool_belt_problem"
                        error-key="form.cool_belt_problem"
                        :required="true"
                        placeholder='Write "N/A" if not applicable.'
                        wire:model.live="form.cool_belt_problem"
                    />
                </div>

                <div data-field="cool_belt_corrective_action" class="relative">
                    <button type="button" @click="$wire.set('form.cool_belt_corrective_action', 'N/A')" class="absolute right-0 top-0 z-10 text-xs font-medium px-2 py-0.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded text-gray-600 dark:text-gray-300 transition-colors">N/A</button>
                    <x-text-area
                        label="Corrective Action Taken"
                        name="cool_belt_corrective_action"
                        error-key="form.cool_belt_corrective_action"
                        :required="true"
                        placeholder='Write "N/A" if not applicable.'
                        wire:model.live="form.cool_belt_corrective_action"
                    />
                </div>

                <div data-field="photo_cool_belt">
                    <x-photo-attach label="Attach necessary pictures or document for reference." name="photo_cool_belt" required />
                </div>
            </div>

            {{-- Step 9: FUEL - Check for leaks --}}
            <div data-step="9" class="space-y-4" @style(["display:none" => $currentStep !== 9])>
                <x-title>FUEL — CHECK FOR LEAKS</x-title>

                <div data-field="fuel_leaks_status">
                    <x-dropdown
                        label="FUEL - Check for leaks"
                        name="fuel_leaks_status"
                        error-key="form.fuel_leaks_status"
                        placeholder="Select result"
                        wire:model.live="form.fuel_leaks_status"
                        required
                    >
                        <option value="Okay">Okay</option>
                        <option value="Not Okay">Not Okay</option>
                    </x-dropdown>
                </div>

                <div data-field="fuel_leaks_problem" class="relative">
                    <button type="button" @click="$wire.set('form.fuel_leaks_problem', 'N/A')" class="absolute right-0 top-0 z-10 text-xs font-medium px-2 py-0.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded text-gray-600 dark:text-gray-300 transition-colors">N/A</button>
                    <x-text-area
                        label="If not okay, explain why and identify the problem."
                        name="fuel_leaks_problem"
                        error-key="form.fuel_leaks_problem"
                        :required="true"
                        placeholder='Write "N/A" if not applicable.'
                        wire:model.live="form.fuel_leaks_problem"
                    />
                </div>

                <div data-field="fuel_leaks_corrective_action" class="relative">
                    <button type="button" @click="$wire.set('form.fuel_leaks_corrective_action', 'N/A')" class="absolute right-0 top-0 z-10 text-xs font-medium px-2 py-0.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded text-gray-600 dark:text-gray-300 transition-colors">N/A</button>
                    <x-text-area
                        label="Corrective Action Taken"
                        name="fuel_leaks_corrective_action"
                        error-key="form.fuel_leaks_corrective_action"
                        :required="true"
                        placeholder='Write "N/A" if not applicable.'
                        wire:model.live="form.fuel_leaks_corrective_action"
                    />
                </div>

                <div data-field="photo_fuel_leaks">
                    <x-photo-attach label="Attach necessary pictures or document for reference." name="photo_fuel_leaks" required />
                </div>
            </div>

            {{-- Step 10: ENGINE RELATED - Check for unusual vibration --}}
            <div data-step="10" class="space-y-4" @style(["display:none" => $currentStep !== 10])>
                <x-title>ENGINE RELATED — CHECK FOR UNUSUAL VIBRATION</x-title>

                <div data-field="engine_vibration_status">
                    <x-dropdown
                        label="ENGINE RELATED - Check for unusual vibration"
                        name="engine_vibration_status"
                        error-key="form.engine_vibration_status"
                        placeholder="Select result"
                        wire:model.live="form.engine_vibration_status"
                        required
                    >
                        <option value="Okay">Okay</option>
                        <option value="Not Okay">Not Okay</option>
                    </x-dropdown>
                </div>

                <div data-field="engine_vibration_problem" class="relative">
                    <button type="button" @click="$wire.set('form.engine_vibration_problem', 'N/A')" class="absolute right-0 top-0 z-10 text-xs font-medium px-2 py-0.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded text-gray-600 dark:text-gray-300 transition-colors">N/A</button>
                    <x-text-area
                        label="If not okay, explain why and identify the problem."
                        name="engine_vibration_problem"
                        error-key="form.engine_vibration_problem"
                        :required="true"
                        placeholder='Write "N/A" if not applicable.'
                        wire:model.live="form.engine_vibration_problem"
                    />
                </div>

                <div data-field="engine_vibration_corrective_action" class="relative">
                    <button type="button" @click="$wire.set('form.engine_vibration_corrective_action', 'N/A')" class="absolute right-0 top-0 z-10 text-xs font-medium px-2 py-0.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded text-gray-600 dark:text-gray-300 transition-colors">N/A</button>
                    <x-text-area
                        label="Corrective Action Taken"
                        name="engine_vibration_corrective_action"
                        error-key="form.engine_vibration_corrective_action"
                        :required="true"
                        placeholder='Write "N/A" if not applicable.'
                        wire:model.live="form.engine_vibration_corrective_action"
                    />
                </div>

                <div data-field="photo_engine_vibration">
                    <x-photo-attach label="Attach necessary pictures or document for reference." name="photo_engine_vibration" required />
                </div>
            </div>

            {{-- Step 11: MAIN GENERATOR - Check for windings and electrical connections --}}
            <div data-step="11" class="space-y-4" @style(["display:none" => $currentStep !== 11])>
                <x-title>MAIN GENERATOR — CHECK FOR WINDINGS AND ELECTRICAL CONNECTIONS</x-title>

                <div data-field="main_gen_windings_status">
                    <x-dropdown
                        label="MAIN GENERATOR - Check for windings and electrical connections"
                        name="main_gen_windings_status"
                        error-key="form.main_gen_windings_status"
                        placeholder="Select result"
                        wire:model.live="form.main_gen_windings_status"
                        required
                    >
                        <option value="Okay">Okay</option>
                        <option value="Not Okay">Not Okay</option>
                    </x-dropdown>
                </div>

                <div data-field="main_gen_windings_problem" class="relative">
                    <button type="button" @click="$wire.set('form.main_gen_windings_problem', 'N/A')" class="absolute right-0 top-0 z-10 text-xs font-medium px-2 py-0.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded text-gray-600 dark:text-gray-300 transition-colors">N/A</button>
                    <x-text-area
                        label="If not okay, explain why and identify the problem."
                        name="main_gen_windings_problem"
                        error-key="form.main_gen_windings_problem"
                        :required="true"
                        placeholder='Write "N/A" if not applicable.'
                        wire:model.live="form.main_gen_windings_problem"
                    />
                </div>

                <div data-field="main_gen_windings_corrective_action" class="relative">
                    <button type="button" @click="$wire.set('form.main_gen_windings_corrective_action', 'N/A')" class="absolute right-0 top-0 z-10 text-xs font-medium px-2 py-0.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded text-gray-600 dark:text-gray-300 transition-colors">N/A</button>
                    <x-text-area
                        label="Corrective Action Taken"
                        name="main_gen_windings_corrective_action"
                        error-key="form.main_gen_windings_corrective_action"
                        :required="true"
                        placeholder='Write "N/A" if not applicable.'
                        wire:model.live="form.main_gen_windings_corrective_action"
                    />
                </div>

                <div data-field="photo_main_gen_windings">
                    <x-photo-attach label="Attach necessary pictures or document for reference." name="photo_main_gen_windings" required />
                </div>
            </div>

            {{-- Step 12: SWITCH GEAR - Check power distribution wiring and connections --}}
            <div data-step="12" class="space-y-4" @style(["display:none" => $currentStep !== 12])>
                <x-title>SWITCH GEAR — CHECK POWER DISTRIBUTION WIRING AND CONNECTIONS</x-title>

                <div data-field="switch_gear_status">
                    <x-dropdown
                        label="SWITCH GEAR - Check power distribution wiring and connections"
                        name="switch_gear_status"
                        error-key="form.switch_gear_status"
                        placeholder="Select result"
                        wire:model.live="form.switch_gear_status"
                        required
                    >
                        <option value="Okay">Okay</option>
                        <option value="Not Okay">Not Okay</option>
                    </x-dropdown>
                </div>

                <div data-field="switch_gear_problem" class="relative">
                    <button type="button" @click="$wire.set('form.switch_gear_problem', 'N/A')" class="absolute right-0 top-0 z-10 text-xs font-medium px-2 py-0.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded text-gray-600 dark:text-gray-300 transition-colors">N/A</button>
                    <x-text-area
                        label="If not okay, explain why and identify the problem."
                        name="switch_gear_problem"
                        error-key="form.switch_gear_problem"
                        :required="true"
                        placeholder='Write "N/A" if not applicable.'
                        wire:model.live="form.switch_gear_problem"
                    />
                </div>

                <div data-field="switch_gear_corrective_action" class="relative">
                    <button type="button" @click="$wire.set('form.switch_gear_corrective_action', 'N/A')" class="absolute right-0 top-0 z-10 text-xs font-medium px-2 py-0.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded text-gray-600 dark:text-gray-300 transition-colors">N/A</button>
                    <x-text-area
                        label="Corrective Action Taken"
                        name="switch_gear_corrective_action"
                        error-key="form.switch_gear_corrective_action"
                        :required="true"
                        placeholder='Write "N/A" if not applicable.'
                        wire:model.live="form.switch_gear_corrective_action"
                    />
                </div>

                <div data-field="photo_switch_gear">
                    <x-photo-attach label="Attach necessary pictures or document for reference." name="photo_switch_gear" required />
                </div>
            </div>

            {{-- Step 13: Test Run --}}
            <div data-step="13" class="space-y-4" @style(["display:none" => $currentStep !== 13])>
                <x-title>ACTUAL TEST RUN</x-title>

                <p class="text-sm text-gray-500 dark:text-gray-400 -mt-2">Actual test run for 1 hour with load.</p>

                <div data-field="test_run_conducted">
                    <x-dropdown
                        label="Actual test run"
                        name="test_run_conducted"
                        error-key="form.test_run_conducted"
                        placeholder="Select"
                        wire:model.live="form.test_run_conducted"
                        required
                    >
                        <option value="Okay">Okay</option>
                        <option value="Not Okay">Not Okay</option>
                    </x-dropdown>
                </div>

                <div data-field="test_run_time" class="relative">
                    <button type="button" @click="$wire.set('form.test_run_time', 'N/A')" class="absolute right-0 top-0 z-10 text-xs font-medium px-2 py-0.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded text-gray-600 dark:text-gray-300 transition-colors">N/A</button>
                    <x-text-input
                        label="Indicate the time start and end time of the actual test run conducted"
                        name="test_run_time"
                        error-key="form.test_run_time"
                        :required="true"
                        placeholder='e.g. 08:00 AM - 09:00 AM. Write "N/A" if not conducted.'
                        wireModel="form.test_run_time"
                    />
                </div>

                <div data-field="previous_running_time">
                    <x-text-input
                        label="Previous reading of running time"
                        name="previous_running_time"
                        error-key="form.previous_running_time"
                        :required="true"
                        placeholder="Enter previous running time reading"
                        wireModel="form.previous_running_time"
                    />
                </div>

                <div data-field="present_running_time">
                    <x-text-input
                        label="Present reading of running time"
                        name="present_running_time"
                        error-key="form.present_running_time"
                        :required="true"
                        placeholder="Enter present running time reading"
                        wireModel="form.present_running_time"
                    />
                </div>

                <div data-field="line_voltages" class="relative">
                    <button type="button" @click="$wire.set('form.line_voltages', 'N/A')" class="absolute right-0 top-0 z-10 text-xs font-medium px-2 py-0.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded text-gray-600 dark:text-gray-300 transition-colors">N/A</button>
                    <x-text-input
                        label="Indicate the three (3) line voltages (V) reading"
                        name="line_voltages"
                        error-key="form.line_voltages"
                        :required="true"
                        placeholder='e.g. L1: 220V, L2: 220V, L3: 221V. Write "N/A" if not applicable.'
                        wireModel="form.line_voltages"
                    />
                </div>

                <div data-field="hertz_reading" class="relative">
                    <button type="button" @click="$wire.set('form.hertz_reading', 'N/A')" class="absolute right-0 top-0 z-10 text-xs font-medium px-2 py-0.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded text-gray-600 dark:text-gray-300 transition-colors">N/A</button>
                    <x-text-input
                        label="Indicate the hertz (Hz) reading"
                        name="hertz_reading"
                        error-key="form.hertz_reading"
                        :required="true"
                        placeholder='Enter Hz reading. Write "N/A" if not applicable.'
                        wireModel="form.hertz_reading"
                    />
                </div>

                <div data-field="oil_pressure_kpa" class="relative">
                    <button type="button" @click="$wire.set('form.oil_pressure_kpa', 'N/A')" class="absolute right-0 top-0 z-10 text-xs font-medium px-2 py-0.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded text-gray-600 dark:text-gray-300 transition-colors">N/A</button>
                    <x-text-input
                        label="Indicate the oil pressure reading (kPa)"
                        name="oil_pressure_kpa"
                        error-key="form.oil_pressure_kpa"
                        :required="true"
                        placeholder='Enter oil pressure in kPa. Write "N/A" if not applicable.'
                        wireModel="form.oil_pressure_kpa"
                    />
                </div>

                <div data-field="running_condition">
                    <x-dropdown
                        label="Indicate running condition"
                        name="running_condition"
                        error-key="form.running_condition"
                        placeholder="Select running condition"
                        wire:model.live="form.running_condition"
                        required
                    >
                        <option value="Normal">Normal</option>
                        <option value="Abnormal">Abnormal</option>
                    </x-dropdown>
                </div>
            </div>

            {{-- Step 14: Summary & Diesel Status --}}
            <div data-step="14" class="space-y-4" @style(["display:none" => $currentStep !== 14])>
                <x-title>SUMMARY &amp; DIESEL STATUS</x-title>

                <div data-field="notes" class="relative">
                    <button type="button" @click="$wire.set('form.notes', 'N/A')" class="absolute right-0 top-0 z-10 text-xs font-medium px-2 py-0.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded text-gray-600 dark:text-gray-300 transition-colors">N/A</button>
                    <x-text-area
                        label="Notes and other concerns"
                        name="notes"
                        error-key="form.notes"
                        :required="true"
                        placeholder='Enter any notes or other concerns. Write "N/A" if none.'
                        wire:model.live="form.notes"
                    />
                </div>

                <div data-field="diesel_tank_level">
                    <x-dropdown
                        label="Diesel Tank Level"
                        name="diesel_tank_level"
                        error-key="form.diesel_tank_level"
                        placeholder="Select tank level"
                        wire:model.live="form.diesel_tank_level"
                        required
                    >
                        <option value="Full Tank">Full Tank</option>
                        <option value="Half Tank">Half Tank</option>
                        <option value="For Refill">For Refill</option>
                    </x-dropdown>
                </div>

                <div data-field="refill_date" class="relative">
                    <button type="button" @click="$wire.set('form.refill_date', 'N/A')" class="absolute right-0 top-0 z-10 text-xs font-medium px-2 py-0.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded text-gray-600 dark:text-gray-300 transition-colors">N/A</button>
                    <x-text-input
                        label="If for refill, when will it be refilled?"
                        name="refill_date"
                        error-key="form.refill_date"
                        :required="true"
                        placeholder='Enter refill date or schedule. Write "N/A" if full tank or not for refill.'
                        wireModel="form.refill_date"
                    />
                </div>

                <div data-field="available_diesel_stock">
                    <x-text-input
                        label="Available Diesel Stock Level"
                        name="available_diesel_stock"
                        error-key="form.available_diesel_stock"
                        :required="true"
                        placeholder="Enter stock level in liters"
                        wireModel="form.available_diesel_stock"
                        subtext="In Liter"
                    />
                </div>
            </div>
        </x-progress-navigation>
    </form>
</div>
