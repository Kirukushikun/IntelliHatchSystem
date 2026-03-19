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
            {{-- Step 1: Header Info --}}
            <div data-step="1" class="space-y-4" @style(["display:none" => $currentStep !== 1])>
                <x-title>PASGAR SCORE</x-title>

                <p class="text-sm text-gray-500 dark:text-gray-400 -mt-2">50 samples per PS</p>

                <div data-field="personnel_name">
                    <x-text-input
                        label="Personnel Performed PASGAR Scoring"
                        name="personnel_name"
                        error-key="form.personnel_name"
                        :required="true"
                        placeholder="Enter your name"
                        wireModel="form.personnel_name"
                    />
                </div>

                <div data-field="hatch_date">
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

                <div data-field="time_started">
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

                <div data-field="average_chick_weight">
                    <x-text-input
                        label="Average Chick Weight"
                        name="average_chick_weight"
                        error-key="form.average_chick_weight"
                        :required="true"
                        placeholder="Enter your answer"
                        wireModel="form.average_chick_weight"
                        type="number"
                        step="0.01"
                        subtext="Standard: 35.0 grams and above"
                    />
                </div>

                <div data-field="low_reflex_alertness_qty">
                    <x-text-input
                        label="Low Reflex / Alertness Qty"
                        name="low_reflex_alertness_qty"
                        error-key="form.low_reflex_alertness_qty"
                        :required="true"
                        placeholder="Enter a number"
                        wireModel="form.low_reflex_alertness_qty"
                        type="number"
                        min="0"
                    />
                </div>

                <div data-field="navel_issue_qty">
                    <x-text-input
                        label="Navel Issue Qty"
                        name="navel_issue_qty"
                        error-key="form.navel_issue_qty"
                        :required="true"
                        placeholder="Enter a number"
                        wireModel="form.navel_issue_qty"
                        type="number"
                        min="0"
                    />
                </div>

                <div data-field="leg_issue_qty">
                    <x-text-input
                        label="Leg Issue Qty"
                        name="leg_issue_qty"
                        error-key="form.leg_issue_qty"
                        :required="true"
                        placeholder="Enter a number"
                        wireModel="form.leg_issue_qty"
                        type="number"
                        min="0"
                    />
                </div>

                <div data-field="beak_issue_qty">
                    <x-text-input
                        label="Beak Issue Qty"
                        name="beak_issue_qty"
                        error-key="form.beak_issue_qty"
                        :required="true"
                        placeholder="Enter a number"
                        wireModel="form.beak_issue_qty"
                        type="number"
                        min="0"
                    />
                </div>

                <div data-field="belly_bloated_qty">
                    <x-text-input
                        label="Belly / Bloated Qty"
                        name="belly_bloated_qty"
                        error-key="form.belly_bloated_qty"
                        :required="true"
                        placeholder="Enter a number"
                        wireModel="form.belly_bloated_qty"
                        type="number"
                        min="0"
                    />
                </div>

                <div data-field="pasgar_average_scoring">
                    <x-text-input
                        label="PASGAR Average Scoring"
                        name="pasgar_average_scoring"
                        error-key="form.pasgar_average_scoring"
                        :required="true"
                        placeholder="Enter your answer"
                        wireModel="form.pasgar_average_scoring"
                    />
                </div>
            </div>

            {{-- Step 3: DOP & Completion --}}
            <div data-step="3" class="space-y-4" @style(["display:none" => $currentStep !== 3])>
                <x-title>DOP &amp; COMPLETION</x-title>

                <div data-field="dop_prime_qty">
                    <x-text-input
                        label="DOP Prime Qty"
                        name="dop_prime_qty"
                        error-key="form.dop_prime_qty"
                        :required="true"
                        placeholder="Enter a number"
                        wireModel="form.dop_prime_qty"
                        type="number"
                        min="0"
                        subtext="In Box"
                    />
                </div>

                <div data-field="dop_prime_box_numbers">
                    <x-text-input
                        label="DOP Prime Box Number/s"
                        name="dop_prime_box_numbers"
                        error-key="form.dop_prime_box_numbers"
                        :required="true"
                        placeholder="Enter your answer"
                        wireModel="form.dop_prime_box_numbers"
                        subtext="Not box qty. Ex. Box 1, 2, 3..."
                    />
                </div>

                <div data-field="dop_jr_prime_qty">
                    <x-text-input
                        label="DOP JR Prime Qty"
                        name="dop_jr_prime_qty"
                        error-key="form.dop_jr_prime_qty"
                        :required="true"
                        placeholder="Enter a number"
                        wireModel="form.dop_jr_prime_qty"
                        type="number"
                        min="0"
                        subtext="In Box"
                    />
                </div>

                <div data-field="dop_jr_prime_box_numbers">
                    <x-text-input
                        label="DOP JR Prime Box Number/s"
                        name="dop_jr_prime_box_numbers"
                        error-key="form.dop_jr_prime_box_numbers"
                        :required="true"
                        placeholder="Enter your answer"
                        wireModel="form.dop_jr_prime_box_numbers"
                        subtext="Not box qty. Ex. Box 1, 2, 3..."
                    />
                </div>

                <div data-field="form_photo">
                    <x-photo-attach label="Photo of Form with Data" name="form_photo" required />
                </div>

                <div data-field="qc_personnel">
                    <x-text-input
                        label="QC Personnel"
                        name="qc_personnel"
                        error-key="form.qc_personnel"
                        :required="true"
                        placeholder="Enter your answer"
                        wireModel="form.qc_personnel"
                    />
                </div>

                <div data-field="time_finished">
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
            </div>
        </x-progress-navigation>
    </form>
</div>
