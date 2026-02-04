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

    function truncateHeader($text, $maxLength = 15) {
        if (strlen($text) <= $maxLength) {
            return $text;
        }
        return substr($text, 0, $maxLength) . '...';
    }
@endphp

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.truncatable-header').forEach(function(header) {
        const fullText = header.getAttribute('data-full-text');
        const shortText = header.getAttribute('data-short-text');
        let isTruncated = true;
        
        header.style.cursor = 'pointer';
        header.title = 'Click to expand/collapse';
        
        header.addEventListener('click', function() {
            if (isTruncated) {
                header.textContent = fullText;
                isTruncated = false;
            } else {
                header.textContent = shortText;
                isTruncated = true;
            }
        });
    });
});
</script>

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
                class="w-80 pl-11 pr-4 py-3 text-sm bg-white border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all placeholder:text-gray-400 shadow-sm"
            />
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
                            <p class="text-xs md:text-sm font-semibold leading-none text-slate-700 truncatable-header" data-full-text="Egg Setting Preparation" data-short-text="{{ truncateHeader('Egg Setting Preparation') }}">
                                {{ truncateHeader('Egg Setting Preparation') }}
                            </p>
                        </th>
                        <th class="p-3 md:p-4 border-b border-slate-300 bg-slate-50">
                            <p class="text-xs md:text-sm font-semibold leading-none text-slate-700 truncatable-header" data-full-text="Record Egg Setting Time" data-short-text="{{ truncateHeader('Record Egg Setting Time') }}">
                                {{ truncateHeader('Record Egg Setting Time') }}
                            </p>
                        </th>
                        <th class="p-3 md:p-4 border-b border-slate-300 bg-slate-50">
                            <p class="text-xs md:text-sm font-semibold leading-none text-slate-700 truncatable-header" data-full-text="Assist in Random Candling" data-short-text="{{ truncateHeader('Assist in Random Candling') }}">
                                {{ truncateHeader('Assist in Random Candling') }}
                            </p>
                        </th>
                        <th class="p-3 md:p-4 border-b border-slate-300 bg-slate-50">
                            <p class="text-xs md:text-sm font-semibold leading-none text-slate-700 truncatable-header" data-full-text="Check Rack Baffle Condition" data-short-text="{{ truncateHeader('Check Rack Baffle Condition') }}">
                                {{ truncateHeader('Check Rack Baffle Condition') }}
                            </p>
                        </th>
                        <th class="p-3 md:p-4 border-b border-slate-300 bg-slate-50">
                            <p class="text-xs md:text-sm font-semibold leading-none text-slate-700 truncatable-header" data-full-text="Record Egg Setting on Board" data-short-text="{{ truncateHeader('Record Egg Setting on Board') }}">
                                {{ truncateHeader('Record Egg Setting on Board') }}
                            </p>
                        </th>
                        <th class="p-3 md:p-4 border-b border-slate-300 bg-slate-50">
                            <p class="text-xs md:text-sm font-semibold leading-none text-slate-700 truncatable-header" data-full-text="Check Water Level of Blue Tank" data-short-text="{{ truncateHeader('Check Water Level of Blue Tank') }}">
                                {{ truncateHeader('Check Water Level of Blue Tank') }}
                            </p>
                        </th>
                        <th class="p-3 md:p-4 border-b border-slate-300 bg-slate-50">
                            <p class="text-xs md:text-sm font-semibold leading-none text-slate-700 truncatable-header" data-full-text="Check Spray Nozzle and Water Pan" data-short-text="{{ truncateHeader('Check Spray Nozzle and Water Pan') }}">
                                {{ truncateHeader('Check Spray Nozzle and Water Pan') }}
                            </p>
                        </th>
                        <th class="p-3 md:p-4 border-b border-slate-300 bg-slate-50">
                            <p class="text-xs md:text-sm font-semibold leading-none text-slate-700 truncatable-header" data-full-text="Clean and Refill Water Reservoir" data-short-text="{{ truncateHeader('Clean and Refill Water Reservoir') }}">
                                {{ truncateHeader('Clean and Refill Water Reservoir') }}
                            </p>
                        </th>
                        <th class="p-3 md:p-4 border-b border-slate-300 bg-slate-50">
                            <p class="text-xs md:text-sm font-semibold leading-none text-slate-700 truncatable-header" data-full-text="Cleaning of Incubator Floor Area" data-short-text="{{ truncateHeader('Cleaning of Incubator Floor Area') }}">
                                {{ truncateHeader('Cleaning of Incubator Floor Area') }}
                            </p>
                        </th>
                        <th class="p-3 md:p-4 border-b border-slate-300 bg-slate-50">
                            <p class="text-xs md:text-sm font-semibold leading-none text-slate-700 truncatable-header" data-full-text="Check Incubator Fans for Vibration" data-short-text="{{ truncateHeader('Check Incubator Fans for Vibration') }}">
                                {{ truncateHeader('Check Incubator Fans for Vibration') }}
                            </p>
                        </th>
                        <th class="p-3 md:p-4 border-b border-slate-300 bg-slate-50">
                            <p class="text-xs md:text-sm font-semibold leading-none text-slate-700 truncatable-header" data-full-text="Check Wick for Replacement Washing" data-short-text="{{ truncateHeader('Check Wick for Replacement Washing') }}">
                                {{ truncateHeader('Check Wick for Replacement Washing') }}
                            </p>
                        </th>
                        <th class="p-3 md:p-4 border-b border-slate-300 bg-slate-50">
                            <p class="text-xs md:text-sm font-semibold leading-none text-slate-700 truncatable-header" data-full-text="Cleaning Incubator Roof and Plenum" data-short-text="{{ truncateHeader('Cleaning Incubator Roof and Plenum') }}">
                                {{ truncateHeader('Cleaning Incubator Roof and Plenum') }}
                            </p>
                        </th>
                        <th class="p-3 md:p-4 border-b border-slate-300 bg-slate-50">
                            <p class="text-xs md:text-sm font-semibold leading-none text-slate-700 truncatable-header" data-full-text="Check Curtain Position and Condition" data-short-text="{{ truncateHeader('Check Curtain Position and Condition') }}">
                                {{ truncateHeader('Check Curtain Position and Condition') }}
                            </p>
                        </th>
                        <th class="p-3 md:p-4 border-b border-slate-300 bg-slate-50">
                            <p class="text-xs md:text-sm font-semibold leading-none text-slate-700 truncatable-header" data-full-text="Check Incubator Doors for Air Leakage" data-short-text="{{ truncateHeader('Check Incubator Doors for Air Leakage') }}">
                                {{ truncateHeader('Check Incubator Doors for Air Leakage') }}
                            </p>
                        </th>
                        <th class="p-3 md:p-4 border-b border-slate-300 bg-slate-50">
                            <p class="text-xs md:text-sm font-semibold leading-none text-slate-700 truncatable-header" data-full-text="Checking of Baggy Against the Gaskets" data-short-text="{{ truncateHeader('Checking of Baggy Against the Gaskets') }}">
                                {{ truncateHeader('Checking of Baggy Against the Gaskets') }}
                            </p>
                        </th>
                        <th class="p-3 md:p-4 border-b border-slate-300 bg-slate-50">
                            <p class="text-xs md:text-sm font-semibold leading-none text-slate-700 truncatable-header" data-full-text="Drain Water Out from Air Compressor Tank" data-short-text="{{ truncateHeader('Drain Water Out from Air Compressor Tank') }}">
                                {{ truncateHeader('Drain Water Out from Air Compressor Tank') }}
                            </p>
                        </th>
                        <th class="p-3 md:p-4 border-b border-slate-300 bg-slate-50">
                            <p class="text-xs md:text-sm font-semibold leading-none text-slate-700 truncatable-header" data-full-text="Cleaning of Entrance and Exit Area Flooring" data-short-text="{{ truncateHeader('Cleaning of Entrance and Exit Area Flooring') }}">
                                {{ truncateHeader('Cleaning of Entrance and Exit Area Flooring') }}
                            </p>
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
