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
                <x-title>PLENUM TEMPERATURE AND HUMIDITY MONITORING</x-title>

                <div data-field="hatcheryman">
                    <x-dropdown label="Hatcheryman" name="hatcheryman" error-key="form.hatcheryman" placeholder="Select hatcheryman" wire:model.live="form.hatcheryman" required>
                        @foreach($hatcheryMen as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </x-dropdown>
                </div>

                <div data-field="shift">
                    <x-dropdown label="Shift" name="shift" error-key="form.shift" placeholder="Choose one..." wire:model.live="form.shift" required>
                        <option value="1st Shift">1st Shift</option>
                        <option value="2nd Shift">2nd Shift</option>
                        <option value="3rd Shift">3rd Shift</option>
                    </x-dropdown>
                </div>

                <div data-field="time">
                    <x-dropdown label="Time" name="time" error-key="form.time" placeholder="Choose one..." wire:model.live="form.time" required>
                        <option value="6:00 AM">6:00 AM</option>
                        <option value="11:00 AM">11:00 AM</option>
                        <option value="4:00 PM">4:00 PM</option>
                        <option value="9:00 PM">9:00 PM</option>
                        <option value="2:00 AM">2:00 AM</option>
                    </x-dropdown>
                </div>
            </div>

            {{-- Step 2: Plenum Readings --}}
            <div data-step="2" class="space-y-6" @style(["display:none" => $currentStep !== 2])>
                <x-title>PLENUM READINGS</x-title>

                {{-- Incubator Readings --}}
                <div data-field="incubator_readings">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                            Incubator Readings <span class="text-red-500">*</span>
                        </h3>
                        <button
                            type="button"
                            wire:click="addIncubatorReading"
                            @if(count($form['incubator_readings']) >= count($incubators)) disabled @endif
                            class="inline-flex items-center gap-1 px-3 py-1.5 text-sm font-medium rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Add Incubator
                        </button>
                    </div>

                    @error('form.incubator_readings')
                        <p class="text-red-500 text-sm mb-2">{{ $message }}</p>
                    @enderror

                    <div class="space-y-3">
                        @foreach($form['incubator_readings'] as $index => $reading)
                            @php
                                $otherIncubatorReadings = $form['incubator_readings'];
                                array_splice($otherIncubatorReadings, $index, 1);
                                $usedIncubatorIds = collect($otherIncubatorReadings)
                                    ->pluck('incubator_id')
                                    ->filter(fn($id) => $id !== '' && $id !== null)
                                    ->map(fn($id) => (int) $id)
                                    ->toArray();
                                $availableIncubators = array_diff_key($incubators, array_flip($usedIncubatorIds));
                            @endphp
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 relative">
                                @if(count($form['incubator_readings']) > 1)
                                    <button
                                        type="button"
                                        wire:click="removeIncubatorReading({{ $index }})"
                                        class="absolute top-2 right-2 text-red-400 hover:text-red-600 transition"
                                        title="Remove"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                @endif

                                <div class="grid grid-cols-1 gap-0 sm:grid-cols-3 sm:gap-3">
                                    <div>
                                        <x-dropdown
                                            label="Incubator"
                                            name="incubator_id_{{ $index }}"
                                            error-key="form.incubator_readings.{{ $index }}.incubator_id"
                                            placeholder="Select incubator"
                                            wire:model.live="form.incubator_readings.{{ $index }}.incubator_id"
                                            required
                                        >
                                            @foreach($availableIncubators as $id => $name)
                                                <option value="{{ $id }}">{{ $name }}</option>
                                            @endforeach
                                        </x-dropdown>
                                    </div>
                                    <div>
                                        <x-text-input
                                            label="Temperature"
                                            name="incubator_temperature_{{ $index }}"
                                            error-key="form.incubator_readings.{{ $index }}.temperature"
                                            :required="true"
                                            placeholder="Enter temperature"
                                            wireModel="form.incubator_readings.{{ $index }}.temperature"
                                            type="number"
                                        />
                                    </div>
                                    <div>
                                        <x-text-input
                                            label="Humidity"
                                            name="incubator_humidity_{{ $index }}"
                                            error-key="form.incubator_readings.{{ $index }}.humidity"
                                            :required="true"
                                            placeholder="Enter humidity"
                                            wireModel="form.incubator_readings.{{ $index }}.humidity"
                                            type="number"
                                        />
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Hatcher Readings --}}
                <div data-field="hatcher_readings">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                            Hatcher Readings <span class="text-red-500">*</span>
                        </h3>
                        <button
                            type="button"
                            wire:click="addHatcherReading"
                            @if(count($form['hatcher_readings']) >= count($hatchers)) disabled @endif
                            class="inline-flex items-center gap-1 px-3 py-1.5 text-sm font-medium rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Add Hatcher
                        </button>
                    </div>

                    @error('form.hatcher_readings')
                        <p class="text-red-500 text-sm mb-2">{{ $message }}</p>
                    @enderror

                    <div class="space-y-3">
                        @foreach($form['hatcher_readings'] as $index => $reading)
                            @php
                                $otherHatcherReadings = $form['hatcher_readings'];
                                array_splice($otherHatcherReadings, $index, 1);
                                $usedHatcherIds = collect($otherHatcherReadings)
                                    ->pluck('hatcher_id')
                                    ->filter(fn($id) => $id !== '' && $id !== null)
                                    ->map(fn($id) => (int) $id)
                                    ->toArray();
                                $availableHatchers = array_diff_key($hatchers, array_flip($usedHatcherIds));
                            @endphp
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 relative">
                                @if(count($form['hatcher_readings']) > 1)
                                    <button
                                        type="button"
                                        wire:click="removeHatcherReading({{ $index }})"
                                        class="absolute top-2 right-2 text-red-400 hover:text-red-600 transition"
                                        title="Remove"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                @endif

                                <div class="grid grid-cols-1 gap-0 sm:grid-cols-3 sm:gap-3">
                                    <div>
                                        <x-dropdown
                                            label="Hatcher Machine"
                                            name="hatcher_id_{{ $index }}"
                                            error-key="form.hatcher_readings.{{ $index }}.hatcher_id"
                                            placeholder="Select hatcher machine"
                                            wire:model.live="form.hatcher_readings.{{ $index }}.hatcher_id"
                                            required
                                        >
                                            @foreach($availableHatchers as $id => $name)
                                                <option value="{{ $id }}">{{ $name }}</option>
                                            @endforeach
                                        </x-dropdown>
                                    </div>
                                    <div>
                                        <x-text-input
                                            label="Temperature"
                                            name="hatcher_temperature_{{ $index }}"
                                            error-key="form.hatcher_readings.{{ $index }}.temperature"
                                            :required="true"
                                            placeholder="Enter temperature"
                                            wireModel="form.hatcher_readings.{{ $index }}.temperature"
                                            type="number"
                                        />
                                    </div>
                                    <div>
                                        <x-text-input
                                            label="Humidity"
                                            name="hatcher_humidity_{{ $index }}"
                                            error-key="form.hatcher_readings.{{ $index }}.humidity"
                                            :required="true"
                                            placeholder="Enter humidity"
                                            wireModel="form.hatcher_readings.{{ $index }}.humidity"
                                            type="number"
                                        />
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div data-field="plenum_photos">
                    <x-photo-attach label="Attach pictures" name="plenum_photos" />
                </div>
            </div>

            {{-- Step 3: Humidity & Environmental Data --}}
            <div data-step="3" class="space-y-4" @style(["display:none" => $currentStep !== 3])>
                <x-title>HUMIDITY &amp; ENVIRONMENTAL DATA</x-title>

                <div data-field="random_humidity_entrance_dumper">
                    <x-text-area label="Random humidity reading in entrance dumper intake" name="random_humidity_entrance_dumper" error-key="form.random_humidity_entrance_dumper" placeholder="Enter your answer" wire:model.live="form.random_humidity_entrance_dumper" required subtext="Indicate what machine"/>
                </div>

                <div data-field="random_humidity_top_baggy">
                    <x-text-area label="Random humidity reading at the top of the baggy" name="random_humidity_top_baggy" error-key="form.random_humidity_top_baggy" placeholder="Enter your answer" wire:model.live="form.random_humidity_top_baggy" required subtext="Indicate what machine"/>
                </div>

                <div data-field="random_entrance_dumper_incubator">
                    <x-text-input label="Random entrance Dumper Inside Incubator" name="random_entrance_dumper_incubator" error-key="form.random_entrance_dumper_incubator" :required="true" placeholder="Enter your answer" wireModel="form.random_entrance_dumper_incubator" subtext="Indicate what machine"/>
                </div>

                <div data-field="aircon_count">
                    <x-dropdown label="Ilan ang aircon na nakabukas" name="aircon_count" error-key="form.aircon_count" placeholder="Choose one..." wire:model.live="form.aircon_count" required>
                        <option value="0">0</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                        <option value="6">6</option>
                        <option value="7">7</option>
                        <option value="8">8</option>
                    </x-dropdown>
                </div>

                <div data-field="humidity_incubator_hallway">
                    <x-text-input label="Humidity at incubator hallway" name="humidity_incubator_hallway" error-key="form.humidity_incubator_hallway" :required="true" placeholder="Enter a number" wireModel="form.humidity_incubator_hallway" type="number" />
                </div>

                <div data-field="humidity_outside">
                    <x-text-input label="Humidity outside building" name="humidity_outside" error-key="form.humidity_outside" :required="true" placeholder="Enter a number" wireModel="form.humidity_outside" type="number" />
                </div>

                <div data-field="weather_condition">
                    <x-dropdown label="Lagay ng panahon" name="weather_condition" error-key="form.weather_condition" placeholder="Choose one..." wire:model.live="form.weather_condition" required>
                        <option value="Sunny">Sunny (Maliwanag)</option>
                        <option value="Partly Cloudy">Partly Cloudy (Bahagyang Maulap)</option>
                        <option value="Cloudy">Cloudy (Maulap)</option>
                        <option value="Rainy">Rainy (Maulan)</option>
                        <option value="Stormy">Stormy (Mabagyo)</option>
                    </x-dropdown>
                </div>
            </div>
        </x-progress-navigation>
    </form>
</div>
