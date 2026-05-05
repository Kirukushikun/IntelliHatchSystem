<div x-data="{ formSubmitted: @entangle('formSubmitted') }"
     x-on:focus-chick-weight.window="$nextTick(() => {
         let el = document.getElementById('chick_weight_' + $event.detail.index);
         if (el) el.focus();
     })">
    <form wire:submit.prevent="submitForm" id="step-form" class="space-y-4" novalidate>
        @csrf

        <x-progress-navigation
            :current-step="$currentStep"
            :visible-step-ids="$visibleStepIds"
            :can-proceed="$this->canProceed()"
            :is-last-visible-step="$this->isLastVisibleStep()"
            :show-progress="$this->showProgress()"
        >
            {{-- Step 1: Header Info --}}
            <div data-step="1" class="space-y-4" @style(["display:none" => $currentStep !== 1])>
                <x-title>PASGAR SCORE</x-title>

                <div data-field="personnel_name">
                    <x-personnel-select
                        label="Personnel Performed PASGAR Scoring"
                        name="personnel_name"
                        error-key="form.personnel_name"
                        :personnel="$users"
                        :logged-in-user-id="$loggedInUserId"
                        wire:model.live="form.personnel_name"
                        required
                    />
                </div>

                <div data-field="hatch_date" x-data>
                    <div class="flex items-end gap-2">
                        <div class="flex-1">
                            <x-text-input
                                label="Hatch Date"
                                name="hatch_date"
                                error-key="form.hatch_date"
                                :required="true"
                                placeholder="Enter a date"
                                wireModel="form.hatch_date"
                                type="date"
                            />
                        </div>
                        <button type="button"
                            class="mb-6 inline-flex items-center gap-1 px-3 py-2 text-xs font-medium rounded-lg border border-blue-200 bg-blue-50 text-blue-600 hover:bg-blue-100 dark:border-blue-700 dark:bg-blue-900/30 dark:text-blue-400 dark:hover:bg-blue-900/50 transition-colors whitespace-nowrap"
                            x-on:click="$wire.set('form.hatch_date', new Date().toISOString().split('T')[0])">
                            Today
                        </button>
                    </div>
                </div>

                <div data-field="time_started" x-data>
                    <div class="flex items-end gap-2">
                        <div class="flex-1">
                            <x-text-input
                                label="Time Started"
                                name="time_started"
                                error-key="form.time_started"
                                :required="true"
                                placeholder="Enter your answer"
                                wireModel="form.time_started"
                                type="time"
                            />
                        </div>
                        <button type="button"
                            class="mb-6 inline-flex items-center gap-1 px-3 py-2 text-xs font-medium rounded-lg border border-blue-200 bg-blue-50 text-blue-600 hover:bg-blue-100 dark:border-blue-700 dark:bg-blue-900/30 dark:text-blue-400 dark:hover:bg-blue-900/50 transition-colors whitespace-nowrap"
                            x-on:click="$wire.set('form.time_started', new Date().toTimeString().slice(0,5))">
                            Now
                        </button>
                    </div>
                </div>

                <div data-field="ps_number">
                    <x-dropdown
                        label="PS No."
                        name="ps_number"
                        error-key="form.ps_number"
                        placeholder="Select PS number"
                        wire:model.live="form.ps_number"
                        required
                    >
                        @foreach($psNumbers as $id => $label)
                            <option value="{{ $id }}">{{ $label }}</option>
                        @endforeach
                    </x-dropdown>
                </div>

                <div data-field="house_number">
                    <x-dropdown
                        label="House No."
                        name="house_number"
                        error-key="form.house_number"
                        placeholder="Select house number"
                        wire:model.live="form.house_number"
                        required
                    >
                        @foreach($houseNumbers as $id => $label)
                            <option value="{{ $id }}">{{ $label }}</option>
                        @endforeach
                    </x-dropdown>
                </div>

                <div data-field="incubator_number">
                    <x-dropdown
                        label="Incubator No."
                        name="incubator_number"
                        error-key="form.incubator_number"
                        placeholder="Select incubator"
                        wire:model.live="form.incubator_number"
                        required
                    >
                        @foreach($incubators as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </x-dropdown>
                </div>

                <div data-field="hatcher_number">
                    <x-dropdown
                        label="Hatcher No."
                        name="hatcher_number"
                        error-key="form.hatcher_number"
                        placeholder="Select hatcher"
                        wire:model.live="form.hatcher_number"
                        required
                    >
                        @foreach($hatchers as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </x-dropdown>
                </div>
            </div>

            {{-- Step 2: PASGAR Scoring --}}
            <div data-step="2" class="space-y-4" @style(["display:none" => $currentStep !== 2])>
                <x-title>PASGAR SCORING</x-title>

                {{-- Dynamic DOP Samples --}}
                <div data-field="samples" class="space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            DOP / Chick Samples <span class="text-red-500">*</span>
                        </label>
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            {{ count($form['samples']) }} sample(s)
                        </span>
                    </div>

                    @error('form.samples')
                        <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror

                    @foreach($form['samples'] as $index => $sample)
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden bg-gray-50 dark:bg-gray-800/50" wire:key="sample-{{ $sample['_key'] }}">
                            {{-- Accordion Header (always visible) --}}
                            <button type="button" wire:click="toggleSample({{ $index }})"
                                class="w-full flex items-center justify-between px-4 py-3 hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors">
                                <div class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 transition-transform {{ $expandedSample === $index ? 'rotate-90' : '' }}" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                                        DOP #{{ $index + 1 }}
                                    </h4>
                                    @if($expandedSample !== $index && is_numeric($sample['chick_weight'] ?? '') && $sample['chick_weight'] > 0)
                                        <span class="text-xs text-gray-500 dark:text-gray-400">— {{ $sample['chick_weight'] }}g</span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-xs font-medium px-2 py-1 rounded-full
                                        {{ $this->getSampleScore($index) >= 8 ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : ($this->getSampleScore($index) >= 6 ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400') }}">
                                        Score: {{ $this->getSampleScore($index) }}/100
                                    </span>
                                    @if(count($form['samples']) > 1)
                                        <span wire:click.stop="removeSample({{ $index }})"
                                            class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 transition-colors cursor-pointer"
                                            title="Remove DOP #{{ $index + 1 }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </span>
                                    @endif
                                </div>
                            </button>

                            {{-- Accordion Body (collapsible) --}}
                                <div class="px-4 pb-4 border-t border-gray-200 dark:border-gray-600 pt-3" @style(['display:none' => $expandedSample !== $index])>
                                    <div class="mb-3 space-y-3">
                                        <div>
                                            <label for="chick_weight_{{ $index }}" class="block text-xs font-medium text-gray-600 dark:text-gray-200 mb-1">
                                                Chick Weight (g) <span class="text-red-500">*</span>
                                            </label>
                                            <input type="number" id="chick_weight_{{ $index }}"
                                                wire:model.live="form.samples.{{ $index }}.chick_weight"
                                                step="0.01" min="0"
                                                placeholder="Enter weight in grams"
                                                class="chick-weight-input w-full rounded-md border-gray-300 dark:border-gray-500 dark:bg-gray-700 dark:text-gray-200 dark:placeholder-gray-400 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2">
                                            @error("form.samples.{$index}.chick_weight")
                                                <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div data-field="weighing_photo_{{ $sample['_key'] }}" wire:ignore>
                                            <x-photo-attach
                                                label="Weighing Proof Photo"
                                                name="weighing_photo_{{ $sample['_key'] }}"
                                                :max-files="3"
                                                :compact="true"
                                                :initial-photos="$this->getInitialPhotosForKey('weighing_photo_' . $sample['_key'])"
                                            />
                                        </div>
                                    </div>

                                    <p class="text-xs text-gray-500 dark:text-gray-300 mb-2">Check if the DOP has the issue (checked = has issue)</p>

                                    <div class="grid grid-cols-1 gap-2">
                                        <label class="flex items-center gap-2 cursor-pointer select-none rounded-md px-2 py-1.5 hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors">
                                            <input type="checkbox"
                                                wire:model.live="form.samples.{{ $index }}.low_reflex_alertness"
                                                class="rounded border-gray-300 text-red-500 shadow-sm focus:ring-red-500 dark:border-gray-500 dark:bg-gray-600 dark:checked:bg-red-500 dark:focus:ring-offset-gray-800 h-5 w-5">
                                            <span class="text-sm text-gray-700 dark:text-gray-200">Low Reflex / Alertness</span>
                                        </label>

                                        <label class="flex items-center gap-2 cursor-pointer select-none rounded-md px-2 py-1.5 hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors">
                                            <input type="checkbox"
                                                wire:model.live="form.samples.{{ $index }}.navel_issue"
                                                class="rounded border-gray-300 text-red-500 shadow-sm focus:ring-red-500 dark:border-gray-500 dark:bg-gray-600 dark:checked:bg-red-500 dark:focus:ring-offset-gray-800 h-5 w-5">
                                            <span class="text-sm text-gray-700 dark:text-gray-200">Navel Issue</span>
                                        </label>

                                        <label class="flex items-center gap-2 cursor-pointer select-none rounded-md px-2 py-1.5 hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors">
                                            <input type="checkbox"
                                                wire:model.live="form.samples.{{ $index }}.leg_issue"
                                                class="rounded border-gray-300 text-red-500 shadow-sm focus:ring-red-500 dark:border-gray-500 dark:bg-gray-600 dark:checked:bg-red-500 dark:focus:ring-offset-gray-800 h-5 w-5">
                                            <span class="text-sm text-gray-700 dark:text-gray-200">Leg Issue</span>
                                        </label>

                                        <label class="flex items-center gap-2 cursor-pointer select-none rounded-md px-2 py-1.5 hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors">
                                            <input type="checkbox"
                                                wire:model.live="form.samples.{{ $index }}.beak_issue"
                                                class="rounded border-gray-300 text-red-500 shadow-sm focus:ring-red-500 dark:border-gray-500 dark:bg-gray-600 dark:checked:bg-red-500 dark:focus:ring-offset-gray-800 h-5 w-5">
                                            <span class="text-sm text-gray-700 dark:text-gray-200">Beak Issue</span>
                                        </label>

                                        <label class="flex items-center gap-2 cursor-pointer select-none rounded-md px-2 py-1.5 hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors">
                                            <input type="checkbox"
                                                wire:model.live="form.samples.{{ $index }}.belly_bloated"
                                                class="rounded border-gray-300 text-red-500 shadow-sm focus:ring-red-500 dark:border-gray-500 dark:bg-gray-600 dark:checked:bg-red-500 dark:focus:ring-offset-gray-800 h-5 w-5">
                                            <span class="text-sm text-gray-700 dark:text-gray-200">Belly / Bloated</span>
                                        </label>

                                        <label class="flex items-center gap-2 cursor-pointer select-none rounded-md px-2 py-1.5 hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors">
                                            <input type="checkbox"
                                                wire:model.live="form.samples.{{ $index }}.vaccination_issue"
                                                class="rounded border-gray-300 text-red-500 shadow-sm focus:ring-red-500 dark:border-gray-500 dark:bg-gray-600 dark:checked:bg-red-500 dark:focus:ring-offset-gray-800 h-5 w-5">
                                            <span class="text-sm text-gray-700 dark:text-gray-200">Vaccination Issue</span>
                                        </label>
                                    </div>

                                    @if($sample['low_reflex_alertness'] || $sample['navel_issue'] || $sample['leg_issue'] || $sample['beak_issue'] || $sample['belly_bloated'] || $sample['vaccination_issue'])
                                        <div data-field="issue_photo_{{ $sample['_key'] }}" class="mt-3" wire:ignore>
                                            <x-photo-attach
                                                label="Issue Proof Photo"
                                                name="issue_photo_{{ $sample['_key'] }}"
                                                :max-files="3"
                                                :compact="true"
                                                :initial-photos="$this->getInitialPhotosForKey('issue_photo_' . $sample['_key'])"
                                            />
                                        </div>
                                    @endif

                                </div>
                        </div>
                    @endforeach

                    {{-- Add Sample Button --}}
                    <button type="button" wire:click="addSample"
                        wire:loading.attr="disabled"
                        wire:target="addSample"
                        class="w-full py-2.5 px-4 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-600 dark:text-gray-400 hover:border-blue-400 hover:text-blue-500 dark:hover:border-blue-500 dark:hover:text-blue-400 transition-colors flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg wire:loading.remove wire:target="addSample"
                             xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        <svg wire:loading wire:target="addSample"
                             class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="addSample">Add DOP Sample</span>
                        <span wire:loading wire:target="addSample">Adding Sample...</span>
                    </button>
                </div>

                {{-- Summary Panel --}}
                @if(count($form['samples']) > 0)
                    @php
                        $totals = $this->getIssueTotals();
                        $pasgarAverage = $this->getPasgarAverageProperty();
                    @endphp
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-blue-50 dark:bg-blue-900/20">
                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Summary</h4>

                        <div class="grid grid-cols-2 gap-2 text-sm mb-3">
                            <div class="flex justify-between col-span-2">
                                <span class="text-gray-600 dark:text-gray-400">Avg. Chick Weight:</span>
                                <span class="font-medium text-gray-800 dark:text-gray-200">{{ $this->getAverageChickWeight() }} g</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Low Reflex / Alertness:</span>
                                <span class="font-medium text-gray-800 dark:text-gray-200">{{ $totals['low_reflex_alertness'] }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Navel Issue:</span>
                                <span class="font-medium text-gray-800 dark:text-gray-200">{{ $totals['navel_issue'] }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Leg Issue:</span>
                                <span class="font-medium text-gray-800 dark:text-gray-200">{{ $totals['leg_issue'] }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Beak Issue:</span>
                                <span class="font-medium text-gray-800 dark:text-gray-200">{{ $totals['beak_issue'] }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Belly / Bloated:</span>
                                <span class="font-medium text-gray-800 dark:text-gray-200">{{ $totals['belly_bloated'] }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Vaccination Issue:</span>
                                <span class="font-medium text-gray-800 dark:text-gray-200">{{ $totals['vaccination_issue'] }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Total Samples:</span>
                                <span class="font-medium text-gray-800 dark:text-gray-200">{{ count($form['samples']) }}</span>
                            </div>
                        </div>

                        <div class="border-t border-gray-200 dark:border-gray-700 pt-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">PASGAR Average Score:</span>
                                <span class="text-lg font-bold {{ (float)$pasgarAverage >= 8 ? 'text-green-600 dark:text-green-400' : ((float)$pasgarAverage >= 6 ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400') }}">
                                    {{ $pasgarAverage }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Step 3: Completion --}}
            <div data-step="3" class="space-y-4" @style(["display:none" => $currentStep !== 3])>
                <x-title>COMPLETION</x-title>

                <div data-field="qc_personnel">
                    <x-personnel-select
                        label="QC Personnel"
                        name="qc_personnel"
                        error-key="form.qc_personnel"
                        :personnel="$qcPersonnel"
                        placeholder="Select QC personnel"
                        wire:model.live="form.qc_personnel"
                        required
                    />
                </div>

                <div data-field="time_finished" x-data>
                    <div class="flex items-end gap-2">
                        <div class="flex-1">
                            <x-text-input
                                label="Time Finished"
                                name="time_finished"
                                error-key="form.time_finished"
                                :required="true"
                                placeholder="Enter your answer"
                                wireModel="form.time_finished"
                                type="time"
                            />
                        </div>
                        <button type="button"
                            class="mb-6 inline-flex items-center gap-1 px-3 py-2 text-xs font-medium rounded-lg border border-blue-200 bg-blue-50 text-blue-600 hover:bg-blue-100 dark:border-blue-700 dark:bg-blue-900/30 dark:text-blue-400 dark:hover:bg-blue-900/50 transition-colors whitespace-nowrap"
                            x-on:click="$wire.set('form.time_finished', new Date().toTimeString().slice(0,5))">
                            Now
                        </button>
                    </div>
                </div>
            </div>
        </x-progress-navigation>
    </form>
</div>
