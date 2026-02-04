@php
    function getStatusPill($value) {
        $value = trim($value);
        $lowerValue = strtolower($value);
        
        switch($lowerValue) {
            case 'pending':
                return '<span style="display: inline-flex; align-items: center; padding: 0.25rem 0.625rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500; background-color: #fef3c7; color: #92400e;">Pending</span>';
            case 'done':
            case 'operational':
                return '<span style="display: inline-flex; align-items: center; padding: 0.25rem 0.625rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500; background-color: #dcfce7; color: #166534;">' . ucfirst($lowerValue) . '</span>';
            case 'n/a':
            case 'na':
                return '<span style="display: inline-flex; align-items: center; padding: 0.25rem 0.625rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500; background-color: #f3f4f6; color: #374151;">N/A</span>';
            case 'unoperational':
                return '<span style="display: inline-flex; align-items: center; padding: 0.25rem 0.625rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500; background-color: #fecaca; color: #991b1b;">Unoperational</span>';
            default:
                // For shift values and other text
                if (in_array($lowerValue, ['1st shift', '2nd shift', '3rd shift'])) {
                    return '<span style="display: inline-flex; align-items: center; padding: 0.25rem 0.625rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500; background-color: #dbeafe; color: #1e40af;">' . ucfirst($lowerValue) . '</span>';
                }
                // For corrective_action field, just show as text
                if ($value && strlen($value) > 10) {
                    return '<span style="display: inline-flex; align-items: center; padding: 0.25rem 0.625rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500; background-color: #f3e8ff; color: #6b21a8;">' . substr($value, 0, 15) . '...</span>';
                }
                // Debug: show the actual value if it doesn't match
                return '<span style="display: inline-flex; align-items: center; padding: 0.25rem 0.625rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500; background-color: #f3f4f6; color: #374151;" title="Original: \'' . $value . '\'">' . ($value ?: 'Empty') . '</span>';
        }
    }
@endphp

<div wire:poll.2s>
    <!-- Header with Title and Search -->
    <div class="flex items-center justify-between gap-6 mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">{{ $formType->form_name }}</h1>
            <p class="text-gray-600">All submitted forms of this type</p>
        </div>
        <div class="relative shrink-0">
            <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <input
                wire:model.live="search"
                placeholder="Search forms..."
                class="w-80 pl-11 pr-12 py-3 text-sm bg-white border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all placeholder:text-gray-400 shadow-sm"
            />
            <button type="button" wire:click="toggleFilterDropdown" class="absolute right-2 top-1/2 -translate-y-1/2 p-2 text-gray-400 hover:text-gray-600 transition-colors">
                <svg width="16" height="16" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg" fill="#9CA3AF" class="w-5 h-5">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M15 2v1.67l-5 4.759V14H6V8.429l-5-4.76V2h14zM7 8v5h2V8l5-4.76V3H2v.24L7 8z"/>
                </svg>
            </button>
            
            <!-- Filter Dropdown -->
            @if ($showFilterDropdown)
                <div class="absolute top-full right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                    <div class="p-4">
                        <div class="grid grid-cols-2 gap-1">
                            <!-- Shift Filter Column -->
                            <div>
                                <h3 class="text-sm font-medium text-gray-900 mb-3">Shift</h3>
                                <div class="space-y-2">
                                    <label class="flex items-center">
                                        <input type="radio" wire:model="shiftFilter" value="all" class="mr-2">
                                        <span class="text-sm text-gray-700">All Shifts</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" wire:model="shiftFilter" value="1st Shift" class="mr-2">
                                        <span class="text-sm text-gray-700">1st Shift</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" wire:model="shiftFilter" value="2nd Shift" class="mr-2">
                                        <span class="text-sm text-gray-700">2nd Shift</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" wire:model="shiftFilter" value="3rd Shift" class="mr-2">
                                        <span class="text-sm text-gray-700">3rd Shift</span>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Date Filter Column -->
                            <div>
                                <h3 class="text-sm font-medium text-gray-900 mb-3">Date Range</h3>
                                <div class="space-y-2">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">From</label>
                                        <input 
                                            type="date" 
                                            wire:model="dateFrom"
                                            class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-transparent"
                                            placeholder="YYYY-MM-DD"
                                            max="{{ $dateTo ?: now()->format('Y-m-d') }}"
                                            wire:target="dateFrom"
                                            wire:loading.attr="disabled"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">To</label>
                                        <input 
                                            type="date" 
                                            wire:model="dateTo"
                                            class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-transparent"
                                            placeholder="YYYY-MM-DD"
                                            max="{{ now()->format('Y-m-d') }}"
                                            min="{{ $dateFrom ?: '' }}"
                                            wire:target="dateTo"
                                            wire:loading.attr="disabled"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex justify-between mt-4 pt-3 border-t border-gray-200">
                            <button type="button" wire:click="resetFilters" class="text-sm text-gray-600 hover:text-gray-800">Reset</button>
                            <button type="button" wire:click="toggleFilterDropdown" class="text-sm text-blue-600 hover:text-blue-800">Done</button>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Table Section -->
    <div class="relative flex flex-col w-full h-full text-gray-700 bg-white shadow-md rounded-lg bg-clip-border">
        <div class="overflow-x-auto">
            <table class="w-full text-left table-auto min-w-max">
                <thead>
                    <tr>
                        <th class="p-3 md:p-4 border-b border-slate-300 bg-slate-50 cursor-pointer hover:bg-slate-100" wire:click="sortBy('date_submitted')">
                            <p class="text-xs md:text-sm font-semibold leading-none text-slate-700 flex items-center gap-1">
                                Date Submitted
                                @if ($sortField === 'date_submitted')
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if ($sortDirection === 'asc')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        @endif
                                    </svg>
                                @endif
                            </p>
                        </th>
                        <th class="p-3 md:p-4 border-b border-slate-300 bg-slate-50 cursor-pointer hover:bg-slate-100" wire:click="sortBy('uploaded_by')">
                            <p class="text-xs md:text-sm font-semibold leading-none text-slate-700 flex items-center gap-1">
                                Submitted By
                                @if ($sortField === 'uploaded_by')
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if ($sortDirection === 'asc')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        @endif
                                    </svg>
                                @endif
                            </p>
                        </th>
                        <th class="p-3 md:p-4 border-b border-slate-300 bg-slate-50">
                            <p class="text-xs md:text-sm font-semibold leading-none text-slate-700">
                                Incubator
                            </p>
                        </th>
                        <th class="p-3 md:p-4 border-b border-slate-300 bg-slate-50">
                            <p class="text-xs md:text-sm font-semibold leading-none text-slate-700">
                                Shift
                            </p>
                        </th>
                        <th class="p-3 md:p-4 border-b border-slate-300 bg-slate-50">
                            <p class="text-xs md:text-sm font-semibold leading-none text-slate-700">
                                Egg Setting
                            </p>
                        </th>
                        <th class="p-3 md:p-4 border-b border-slate-300 bg-slate-50">
                            <p class="text-xs md:text-sm font-semibold leading-none text-slate-700">
                                Corrective Action
                            </p>
                        </th>
                        <th class="p-3 md:p-4 border-b border-slate-300 bg-slate-50">
                            <p class="text-xs md:text-sm font-semibold leading-none text-slate-700">
                                Alarm System
                            </p>
                        </th>
                        <th class="p-3 md:p-4 border-b border-slate-300 bg-slate-50">
                            <button 
                                wire:click="toggleHeader('egg_setting_preparation')"
                                class="text-xs md:text-sm font-semibold leading-none text-slate-700 hover:text-blue-600 transition-colors cursor-pointer text-left w-full"
                                title="Egg Setting Preparation (click to expand/collapse)">
                                {{ $this->truncateText('Egg Setting Preparation', 10, 'egg_setting_preparation') }}
                            </button>
                        </th>
                        <th class="p-3 md:p-4 border-b border-slate-300 bg-slate-50">
                            <button 
                                wire:click="toggleHeader('record_egg_setting_time')"
                                class="text-xs md:text-sm font-semibold leading-none text-slate-700 hover:text-blue-600 transition-colors cursor-pointer text-left w-full"
                                title="Record Egg Setting Time (click to expand/collapse)">
                                {{ $this->truncateText('Record Egg Setting Time', 10, 'record_egg_setting_time') }}
                            </button>
                        </th>
                        <th class="p-3 md:p-4 border-b border-slate-300 bg-slate-50">
                            <button 
                                wire:click="toggleHeader('assist_in_random_candling')"
                                class="text-xs md:text-sm font-semibold leading-none text-slate-700 hover:text-blue-600 transition-colors cursor-pointer text-left w-full"
                                title="Assist in Random Candling (click to expand/collapse)">
                                {{ $this->truncateText('Assist in Random Candling', 10, 'assist_in_random_candling') }}
                            </button>
                        </th>
                        <th class="p-3 md:p-4 border-b border-slate-300 bg-slate-50">
                            <button 
                                wire:click="toggleHeader('check_rack_baffle_condition')"
                                class="text-xs md:text-sm font-semibold leading-none text-slate-700 hover:text-blue-600 transition-colors cursor-pointer text-left w-full"
                                title="Check Rack Baffle Condition (click to expand/collapse)">
                                {{ $this->truncateText('Check Rack Baffle Condition', 10, 'check_rack_baffle_condition') }}
                            </button>
                        </th>
                        <th class="p-3 md:p-4 border-b border-slate-300 bg-slate-50">
                            <button 
                                wire:click="toggleHeader('record_egg_setting_on_board')"
                                class="text-xs md:text-sm font-semibold leading-none text-slate-700 hover:text-blue-600 transition-colors cursor-pointer text-left w-full"
                                title="Record Egg Setting on Board (click to expand/collapse)">
                                {{ $this->truncateText('Record Egg Setting on Board', 10, 'record_egg_setting_on_board') }}
                            </button>
                        </th>
                        <th class="p-3 md:p-4 border-b border-slate-300 bg-slate-50">
                            <button 
                                wire:click="toggleHeader('check_water_level_of_blue_tank')"
                                class="text-xs md:text-sm font-semibold leading-none text-slate-700 hover:text-blue-600 transition-colors cursor-pointer text-left w-full"
                                title="Check Water Level of Blue Tank (click to expand/collapse)">
                                {{ $this->truncateText('Check Water Level of Blue Tank', 10, 'check_water_level_of_blue_tank') }}
                            </button>
                        </th>
                        <th class="p-3 md:p-4 border-b border-slate-300 bg-slate-50">
                            <button 
                                wire:click="toggleHeader('check_spray_nozzle_and_water_pan')"
                                class="text-xs md:text-sm font-semibold leading-none text-slate-700 hover:text-blue-600 transition-colors cursor-pointer text-left w-full"
                                title="Check Spray Nozzle and Water Pan (click to expand/collapse)">
                                {{ $this->truncateText('Check Spray Nozzle and Water Pan', 10, 'check_spray_nozzle_and_water_pan') }}
                            </button>
                        </th>
                        <th class="p-3 md:p-4 border-b border-slate-300 bg-slate-50">
                            <button 
                                wire:click="toggleHeader('clean_and_refill_water_reservoir')"
                                class="text-xs md:text-sm font-semibold leading-none text-slate-700 hover:text-blue-600 transition-colors cursor-pointer text-left w-full"
                                title="Clean and Refill Water Reservoir (click to expand/collapse)">
                                {{ $this->truncateText('Clean and Refill Water Reservoir', 10, 'clean_and_refill_water_reservoir') }}
                            </button>
                        </th>
                        <th class="p-3 md:p-4 border-b border-slate-300 bg-slate-50">
                            <button 
                                wire:click="toggleHeader('cleaning_of_incubator_floor_area')"
                                class="text-xs md:text-sm font-semibold leading-none text-slate-700 hover:text-blue-600 transition-colors cursor-pointer text-left w-full"
                                title="Cleaning of Incubator Floor Area (click to expand/collapse)">
                                {{ $this->truncateText('Cleaning of Incubator Floor Area', 10, 'cleaning_of_incubator_floor_area') }}
                            </button>
                        </th>
                        <th class="p-3 md:p-4 border-b border-slate-300 bg-slate-50">
                            <button 
                                wire:click="toggleHeader('check_incubator_fans_for_vibration')"
                                class="text-xs md:text-sm font-semibold leading-none text-slate-700 hover:text-blue-600 transition-colors cursor-pointer text-left w-full"
                                title="Check Incubator Fans for Vibration (click to expand/collapse)">
                                {{ $this->truncateText('Check Incubator Fans for Vibration', 10, 'check_incubator_fans_for_vibration') }}
                            </button>
                        </th>
                        <th class="p-3 md:p-4 border-b border-slate-300 bg-slate-50">
                            <button 
                                wire:click="toggleHeader('check_wick_for_replacement_washing')"
                                class="text-xs md:text-sm font-semibold leading-none text-slate-700 hover:text-blue-600 transition-colors cursor-pointer text-left w-full"
                                title="Check Wick for Replacement Washing (click to expand/collapse)">
                                {{ $this->truncateText('Check Wick for Replacement Washing', 10, 'check_wick_for_replacement_washing') }}
                            </button>
                        </th>
                        <th class="p-3 md:p-4 border-b border-slate-300 bg-slate-50">
                            <button 
                                wire:click="toggleHeader('cleaning_incubator_roof_and_plenum')"
                                class="text-xs md:text-sm font-semibold leading-none text-slate-700 hover:text-blue-600 transition-colors cursor-pointer text-left w-full"
                                title="Cleaning Incubator Roof and Plenum (click to expand/collapse)">
                                {{ $this->truncateText('Cleaning Incubator Roof and Plenum', 10, 'cleaning_incubator_roof_and_plenum') }}
                            </button>
                        </th>
                        <th class="p-3 md:p-4 border-b border-slate-300 bg-slate-50">
                            <button 
                                wire:click="toggleHeader('check_curtain_position_and_condition')"
                                class="text-xs md:text-sm font-semibold leading-none text-slate-700 hover:text-blue-600 transition-colors cursor-pointer text-left w-full"
                                title="Check Curtain Position and Condition (click to expand/collapse)">
                                {{ $this->truncateText('Check Curtain Position and Condition', 10, 'check_curtain_position_and_condition') }}
                            </button>
                        </th>
                        <th class="p-3 md:p-4 border-b border-slate-300 bg-slate-50">
                            <button 
                                wire:click="toggleHeader('check_incubator_doors_for_air_leakage')"
                                class="text-xs md:text-sm font-semibold leading-none text-slate-700 hover:text-blue-600 transition-colors cursor-pointer text-left w-full"
                                title="Check Incubator Doors for Air Leakage (click to expand/collapse)">
                                {{ $this->truncateText('Check Incubator Doors for Air Leakage', 10, 'check_incubator_doors_for_air_leakage') }}
                            </button>
                        </th>
                        <th class="p-3 md:p-4 border-b border-slate-300 bg-slate-50">
                            <button 
                                wire:click="toggleHeader('checking_of_baggy_against_the_gaskets')"
                                class="text-xs md:text-sm font-semibold leading-none text-slate-700 hover:text-blue-600 transition-colors cursor-pointer text-left w-full"
                                title="Checking of Baggy Against the Gaskets (click to expand/collapse)">
                                {{ $this->truncateText('Checking of Baggy Against the Gaskets', 10, 'checking_of_baggy_against_the_gaskets') }}
                            </button>
                        </th>
                        <th class="p-3 md:p-4 border-b border-slate-300 bg-slate-50">
                            <button 
                                wire:click="toggleHeader('drain_water_out_from_air_compressor_tank')"
                                class="text-xs md:text-sm font-semibold leading-none text-slate-700 hover:text-blue-600 transition-colors cursor-pointer text-left w-full"
                                title="Drain Water Out from Air Compressor Tank (click to expand/collapse)">
                                {{ $this->truncateText('Drain Water Out from Air Compressor Tank', 10, 'drain_water_out_from_air_compressor_tank') }}
                            </button>
                        </th>
                        <th class="p-3 md:p-4 border-b border-slate-300 bg-slate-50">
                            <button 
                                wire:click="toggleHeader('cleaning_of_entrance_and_exit_area_flooring')"
                                class="text-xs md:text-sm font-semibold leading-none text-slate-700 hover:text-blue-600 transition-colors cursor-pointer text-left w-full"
                                title="Cleaning of Entrance and Exit Area Flooring (click to expand/collapse)">
                                {{ $this->truncateText('Cleaning of Entrance and Exit Area Flooring', 10, 'cleaning_of_entrance_and_exit_area_flooring') }}
                            </button>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($forms as $form)
                        <tr class="even:bg-slate-50 hover:bg-slate-100">
                            <td class="p-3 md:p-4 py-4 md:py-5 text-center">
                                <p class="block text-xs md:text-sm text-slate-800">{{ $form->date_submitted ? $form->date_submitted->format('M d, Y H:i') : 'N/A' }}</p>
                            </td>
                            <td class="p-3 md:p-4 py-4 md:py-5 text-center">
                                <p class="block text-xs md:text-sm text-slate-800">{{ $form->user ? ($form->user->first_name . ' ' . $form->user->last_name) : 'Unknown' }}</p>
                            </td>
                            <td class="p-3 md:p-4 py-4 md:py-5 text-center">
                                <p class="block text-xs md:text-sm text-slate-800">{{ $form->incubator ? $form->incubator->incubatorName : 'N/A' }}</p>
                            </td>
                            @php
                                $formData = is_array($form->form_inputs) ? $form->form_inputs : [];
                            @endphp
                            <td class="p-3 md:p-4 py-4 md:py-5 text-center">
                                {!! getStatusPill($formData['shift'] ?? 'N/A') !!}
                            </td>
                            <td class="p-3 md:p-4 py-4 md:py-5 text-center">
                                {!! getStatusPill($formData['egg_setting'] ?? 'N/A') !!}
                            </td>
                            <td class="p-3 md:p-4 py-4 md:py-5">
                                <div class="max-w-xs" style="max-width: 200px;">
                                    @php
                                        $correctiveAction = $formData['corrective_action'] ?? 'N/A';
                                        $displayText = htmlspecialchars($correctiveAction);
                                        $isLong = strlen($correctiveAction) > 60;
                                    @endphp
                                    @if($isLong)
                                        <div class="truncated-text" 
                                             style="display: -webkit-box; 
                                                    -webkit-line-clamp: 2; 
                                                    -webkit-box-orient: vertical; 
                                                    overflow: hidden; 
                                                    text-overflow: ellipsis; 
                                                    font-size: 0.75rem; 
                                                    line-height: 1.4; 
                                                    color: #374151; 
                                                    cursor: pointer; 
                                                    word-wrap: break-word;
                                                    word-break: break-word;
                                                    white-space: normal;"
                                             title="{{ $displayText }}"
                                             onclick="this.classList.toggle('expanded')">
                                            {{ $displayText }}
                                        </div>
                                        <style>
                                        .truncated-text.expanded {
                                            display: block !important;
                                            -webkit-line-clamp: unset !important;
                                        }
                                        </style>
                                    @else
                                        <div style="font-size: 0.75rem; 
                                                   line-height: 1.4; 
                                                   color: #374151; 
                                                   word-wrap: break-word;
                                                   word-break: break-word;
                                                   white-space: normal;">
                                            {{ $displayText }}
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="p-3 md:p-4 py-4 md:py-5 text-center">
                                {!! getStatusPill($formData['alarm_system_condition'] ?? 'N/A') !!}
                            </td>
                            <td class="p-3 md:p-4 py-4 md:py-5 text-center">
                                {!! getStatusPill($formData['egg_setting_preparation'] ?? 'N/A') !!}
                            </td>
                            <td class="p-3 md:p-4 py-4 md:py-5 text-center">
                                {!! getStatusPill($formData['record_egg_setting_time'] ?? 'N/A') !!}
                            </td>
                            <td class="p-3 md:p-4 py-4 md:py-5 text-center">
                                {!! getStatusPill($formData['assist_in_random_candling'] ?? 'N/A') !!}
                            </td>
                            <td class="p-3 md:p-4 py-4 md:py-5 text-center">
                                {!! getStatusPill($formData['check_rack_baffle_condition'] ?? 'N/A') !!}
                            </td>
                            <td class="p-3 md:p-4 py-4 md:py-5 text-center">
                                {!! getStatusPill($formData['record_egg_setting_on_board'] ?? 'N/A') !!}
                            </td>
                            <td class="p-3 md:p-4 py-4 md:py-5 text-center">
                                {!! getStatusPill($formData['check_water_level_of_blue_tank'] ?? 'N/A') !!}
                            </td>
                            <td class="p-3 md:p-4 py-4 md:py-5 text-center">
                                {!! getStatusPill($formData['check_spray_nozzle_and_water_pan'] ?? 'N/A') !!}
                            </td>
                            <td class="p-3 md:p-4 py-4 md:py-5 text-center">
                                {!! getStatusPill($formData['clean_and_refill_water_reservoir'] ?? 'N/A') !!}
                            </td>
                            <td class="p-3 md:p-4 py-4 md:py-5 text-center">
                                {!! getStatusPill($formData['cleaning_of_incubator_floor_area'] ?? 'N/A') !!}
                            </td>
                            <td class="p-3 md:p-4 py-4 md:py-5 text-center">
                                {!! getStatusPill($formData['check_incubator_fans_for_vibration'] ?? 'N/A') !!}
                            </td>
                            <td class="p-3 md:p-4 py-4 md:py-5 text-center">
                                {!! getStatusPill($formData['check_wick_for_replacement_washing'] ?? 'N/A') !!}
                            </td>
                            <td class="p-3 md:p-4 py-4 md:py-5 text-center">
                                {!! getStatusPill($formData['cleaning_incubator_roof_and_plenum'] ?? 'N/A') !!}
                            </td>
                            <td class="p-3 md:p-4 py-4 md:py-5 text-center">
                                {!! getStatusPill($formData['check_curtain_position_and_condition'] ?? 'N/A') !!}
                            </td>
                            <td class="p-3 md:p-4 py-4 md:py-5 text-center">
                                {!! getStatusPill($formData['check_incubator_doors_for_air_leakage'] ?? 'N/A') !!}
                            </td>
                            <td class="p-3 md:p-4 py-4 md:py-5 text-center">
                                {!! getStatusPill($formData['checking_of_baggy_against_the_gaskets'] ?? 'N/A') !!}
                            </td>
                            <td class="p-3 md:p-4 py-4 md:py-5 text-center">
                                {!! getStatusPill($formData['drain_water_out_from_air_compressor_tank'] ?? 'N/A') !!}
                            </td>
                            <td class="p-3 md:p-4 py-4 md:py-5 text-center">
                                {!! getStatusPill($formData['cleaning_of_entrance_and_exit_area_flooring'] ?? 'N/A') !!}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="23" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <h3 class="text-lg font-medium text-gray-900">No forms found</h3>
                                    <p class="text-sm text-gray-500 mt-1">Try adjusting your search criteria</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($forms->hasPages())
            <!-- Pagination -->
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center px-3 md:px-4 py-3 border-t border-slate-200 gap-3 sm:gap-0">
                <div class="text-xs md:text-sm text-slate-500 text-center sm:text-left">
                    Showing <b>{{ $forms->firstItem() }}-{{ $forms->lastItem() }}</b> of {{ $forms->total() }}
                </div>
                <x-custom-pagination 
                    :current-page="$currentPage"
                    :last-page="$lastPage"
                    :pages="$pages"
                    on-page-change="gotoPage"
                />
            </div>
        @endif
    </div>
</div>