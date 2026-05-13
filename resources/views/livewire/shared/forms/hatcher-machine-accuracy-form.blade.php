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
            <div data-step="1" class="space-y-4" @style(["display:none" => $currentStep !== 1])>
                <x-title>HATCHER MACHINE ACCURACY TEMPERATURE CHECKING</x-title>

                <div data-field="hatchery_man">
                    <x-personnel-select
                        label="Hatchery Man"
                        name="hatchery_man"
                        error-key="form.hatchery_man"
                        :personnel="$hatcheryMen"
                        :logged-in-user-id="$loggedInUserId"
                        wire:model.live="form.hatchery_man"
                        required
                    />
                </div>

                <div data-field="cellphone_number">
                    <x-text-input label="Cellphone Number" name="cellphone_number" error-key="form.cellphone_number" :required="true" placeholder="09XXXXXXXXX or +639XXXXXXXXX" wireModel="form.cellphone_number" type="tel" />
                </div>

                <div data-field="time_of_reading" x-data>
                    <div class="flex items-end gap-2">
                        <div class="flex-1">
                            <x-text-input label="Time of Reading" name="time_of_reading" error-key="form.time_of_reading" :required="true" placeholder="Select time..." wireModel="form.time_of_reading" type="time" />
                        </div>
                        <button type="button"
                            class="mb-6 inline-flex items-center gap-1 px-3 py-2 text-xs font-medium rounded-lg border border-blue-200 bg-blue-50 text-blue-600 hover:bg-blue-100 dark:border-blue-700 dark:bg-blue-900/30 dark:text-blue-400 dark:hover:bg-blue-900/50 transition-colors whitespace-nowrap"
                            x-on:click="$wire.set('form.time_of_reading', new Date().toTimeString().slice(0,5))">
                            Now
                        </button>
                    </div>
                </div>

                <div data-field="shift">
                    <x-dropdown label="Shift" name="shift" error-key="form.shift" placeholder="Select shift" wire:model.live="form.shift" required>
                        <option value="1st Shift">1st Shift</option>
                        <option value="2nd Shift">2nd Shift</option>
                        <option value="3rd Shift">3rd Shift</option>
                    </x-dropdown>
                </div>
            </div>

            <div data-step="2" class="space-y-4" @style(["display:none" => $currentStep !== 2])>
                <x-title>TEMPERATURE READINGS — ALL HATCHERS</x-title>

                @forelse($this->form['hatchers'] as $index => $hatcher)
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 space-y-3"
                         wire:key="hatcher-{{ $hatcher['id'] }}"
                         id="hatcher-card-{{ $hatcher['id'] }}">
                        <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200">
                            {{ $hatcher['name'] }}
                        </h3>

                        <div data-field="hatchers.{{ $index }}.set_point_temp">
                            <x-text-input
                                label="Set Point Temperature"
                                name="hatchers_{{ $index }}_set_point_temp"
                                error-key="form.hatchers.{{ $index }}.set_point_temp"
                                :required="true"
                                placeholder="Enter set point temperature..."
                                wireModel="form.hatchers.{{ $index }}.set_point_temp"
                                type="number"
                                step="0.01"
                            />
                        </div>

                        <div data-field="hatchers.{{ $index }}.display_temp">
                            <x-text-input
                                label="Display Temp"
                                name="hatchers_{{ $index }}_display_temp"
                                error-key="form.hatchers.{{ $index }}.display_temp"
                                :required="true"
                                placeholder="Enter display temperature..."
                                wireModel="form.hatchers.{{ $index }}.display_temp"
                                type="number"
                                step="0.01"
                            />
                        </div>

                        <div data-field="hatchers.{{ $index }}.calibrator">
                            <x-text-input
                                label="Calibrator"
                                name="hatchers_{{ $index }}_calibrator"
                                error-key="form.hatchers.{{ $index }}.calibrator"
                                :required="true"
                                placeholder="Enter calibrator reading..."
                                wireModel="form.hatchers.{{ $index }}.calibrator"
                                type="number"
                                step="0.01"
                            />
                        </div>

                        <div data-field="hatchers.{{ $index }}.humidity_set_point">
                            <x-text-input
                                label="Humidity Set Point"
                                name="hatchers_{{ $index }}_humidity_set_point"
                                error-key="form.hatchers.{{ $index }}.humidity_set_point"
                                :required="true"
                                placeholder="Enter humidity set point..."
                                wireModel="form.hatchers.{{ $index }}.humidity_set_point"
                                type="number"
                                step="0.01"
                            />
                        </div>

                        <div data-field="hatchers.{{ $index }}.humidity_machine_reading">
                            <x-text-input
                                label="Humidity Machine Reading"
                                name="hatchers_{{ $index }}_humidity_machine_reading"
                                error-key="form.hatchers.{{ $index }}.humidity_machine_reading"
                                :required="true"
                                placeholder="Enter humidity machine reading..."
                                wireModel="form.hatchers.{{ $index }}.humidity_machine_reading"
                                type="number"
                                step="0.01"
                            />
                        </div>

                        <div data-field="accuracy_photos_{{ $hatcher['id'] }}">
                            <x-photo-attach label="Photo (Display next to Calibrator)" name="accuracy_photos_{{ $hatcher['id'] }}" />
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                        <p class="text-sm">No active hatchers found.</p>
                    </div>
                @endforelse
            </div>
        </x-progress-navigation>
    </form>
</div>
