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

                <div data-field="date">
                    <x-text-input label="Date" name="date" error-key="form.date" :required="true" placeholder="Select date..." wireModel="form.date" type="date" />
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
                        <option value="7:00 AM">7:00 AM</option>
                        <option value="8:00 AM">8:00 AM</option>
                        <option value="9:00 AM">9:00 AM</option>
                        <option value="10:00 AM">10:00 AM</option>
                        <option value="11:00 AM">11:00 AM</option>
                        <option value="12:00 PM">12:00 PM</option>
                        <option value="1:00 PM">1:00 PM</option>
                        <option value="2:00 PM">2:00 PM</option>
                        <option value="3:00 PM">3:00 PM</option>
                        <option value="4:00 PM">4:00 PM</option>
                        <option value="5:00 PM">5:00 PM</option>
                        <option value="6:00 PM">6:00 PM</option>
                        <option value="7:00 PM">7:00 PM</option>
                        <option value="8:00 PM">8:00 PM</option>
                        <option value="9:00 PM">9:00 PM</option>
                        <option value="10:00 PM">10:00 PM</option>
                        <option value="11:00 PM">11:00 PM</option>
                        <option value="12:00 AM">12:00 AM</option>
                    </x-dropdown>
                </div>
            </div>

            {{-- Step 2: Plenum Readings --}}
            <div data-step="2" class="space-y-4" @style(["display:none" => $currentStep !== 2])>
                <x-title>PLENUM READINGS</x-title>

                <div data-field="plenum_incubator_1_5">
                    <x-text-input label="Plenum for Incubator 1 - 5 temperature and humidity reading" name="plenum_incubator_1_5" error-key="form.plenum_incubator_1_5" :required="true" placeholder="Enter your answer" wireModel="form.plenum_incubator_1_5" />
                </div>

                <div data-field="plenum_incubator_1_5_photos">
                    <x-photo-attach label="Attach pictures" name="plenum_incubator_1_5_photos" />
                </div>

                <div data-field="plenum_incubator_6_10">
                    <x-text-input label="Plenum for Incubator 6 - 10 temperature and humidity reading" name="plenum_incubator_6_10" error-key="form.plenum_incubator_6_10" :required="true" placeholder="Enter your answer" wireModel="form.plenum_incubator_6_10" />
                </div>

                <div data-field="plenum_incubator_6_10_photos">
                    <x-photo-attach label="Attach pictures" name="plenum_incubator_6_10_photos" />
                </div>

                <div data-field="plenum_hatcher_1_5">
                    <x-text-input label="Plenum for Hatcher 1 - 5 temperature and humidity reading" name="plenum_hatcher_1_5" error-key="form.plenum_hatcher_1_5" :required="true" placeholder="Enter your answer" wireModel="form.plenum_hatcher_1_5" />
                </div>

                <div data-field="plenum_hatcher_1_5_photos">
                    <x-photo-attach label="Attach pictures" name="plenum_hatcher_1_5_photos" />
                </div>

                <div data-field="plenum_hatcher_6_10">
                    <x-text-input label="Plenum for Hatcher 6 - 10 temperature and humidity reading" name="plenum_hatcher_6_10" error-key="form.plenum_hatcher_6_10" :required="true" placeholder="Enter your answer" wireModel="form.plenum_hatcher_6_10" />
                </div>

                <div data-field="plenum_hatcher_6_10_photos">
                    <x-photo-attach label="Attach pictures" name="plenum_hatcher_6_10_photos" />
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
