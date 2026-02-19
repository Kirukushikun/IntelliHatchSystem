@php
    function getStatusColor($status) {
        // Handle non-string values (arrays, null, etc.)
        if (!is_string($status)) {
            if (is_array($status)) {
                $status = is_string($status[0] ?? '') ? $status[0] : 'N/A';
            } else {
                $status = 'N/A';
            }
        }
        
        $status = strtolower(trim($status));
        
        switch($status) {
            case 'done':
            case 'operational':
                return 'bg-green-100 text-green-800';
            case 'pending':
                return 'bg-yellow-100 text-yellow-800';
            case 'unoperational':
                return 'bg-red-100 text-red-800';
            case 'n/a':
            case 'na':
                return 'bg-gray-100 text-gray-800';
            default:
                return 'bg-blue-100 text-blue-800';
        }
    }
    
    function formatDisplayValue($value) {
        // Handle non-string values (arrays, null, etc.)
        if (!is_string($value)) {
            if (is_array($value)) {
                return is_string($value[0] ?? '') ? $value[0] : 'N/A';
            } else {
                return 'N/A';
            }
        }
        
        // Return the value as-is (don't change case for shift values)
        return $value;
    }
    
    function getPhotoButton($field, $livewire) {
        $photoCount = $livewire->getPhotoCount($field);
        
        if ($photoCount > 0) {
            return '<button 
                @click="$wire.viewPhotos(\'' . $field . '\')"
                class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-semibold rounded-lg border border-blue-200 dark:border-blue-600 bg-blue-50 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300 hover:bg-blue-100 dark:hover:bg-blue-900/70 hover:border-blue-300 dark:hover:border-blue-500 transition-all duration-150 shadow-sm cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <span>Photos (' . $photoCount . ')</span>
            </button>';
        } else {
            return '<span class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-medium rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span>No Photos</span>
            </span>';
        }
    }
@endphp

<!-- Modal Backdrop -->
<div x-data="{ showModal: @entangle('showModal').live }" 
     x-show="showModal" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 overflow-y-auto bg-gray-500/75"
     style="display: none;"
     @click.self="showModal = false; $wire.closeModal()">    
    <div class="flex min-h-full items-center justify-center p-4 text-center">
        <!-- Modal Panel -->
        <div x-show="showModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 text-left shadow-xl dark:shadow-2xl transition-all w-full max-w-4xl"
             @click.stop>
            
            <!-- Modal Header -->
            <div class="bg-white dark:bg-gray-800 px-4 py-3 border-b border-gray-200 dark:border-gray-700 sm:px-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">
                        INCUBATOR ROUTINE CHECKLIST PER SHIFT
                    </h3>
                    <button type="button" 
                            @click="showModal = false; $wire.closeModal()"
                            class="rounded-md bg-white dark:bg-gray-800 text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-800 cursor-pointer">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- Modal Body -->
            <div class="bg-white dark:bg-gray-800 px-4 py-4 sm:p-6 sm:pb-4 max-h-[70vh] overflow-y-auto">
                @if($this->selectedForm)
                    <!-- Basic Information -->
                    <div class="mb-8">
                        <div class="space-y-4">
                            <div class="flex justify-between items-center py-3 border-b border-gray-100 dark:border-gray-700">
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Date:</span>
                                <span class="text-sm text-gray-900 dark:text-gray-200">{{ $this->selectedForm->date_submitted ? $this->selectedForm->date_submitted->format('M d, Y H:i') : 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between items-center py-3 border-b border-gray-100 dark:border-gray-700">
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Shift:</span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ getStatusColor($this->formData['shift'] ?? 'N/A') }}">
                                    {{ $this->formData['shift'] ?? 'N/A' }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center py-3 border-b border-gray-100 dark:border-gray-700">
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Hatchery Man:</span>
                                <span class="text-sm text-gray-900 dark:text-gray-200">{{ $this->selectedForm->user ? ($this->selectedForm->user->first_name . ' ' . $this->selectedForm->user->last_name) : 'Unknown' }}</span>
                            </div>
                            <div class="flex justify-between items-center py-3 border-b border-gray-100 dark:border-gray-700">
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Incubator:</span>
                                <span class="text-sm text-gray-900 dark:text-gray-200">{{ $this->selectedForm->machine_info['name'] ?? 'N/A' }}</span>
                            </div>
                        </div>
                        
                        <div class="mt-6 space-y-4">
                            <div class="flex justify-between items-center py-3 border-b border-gray-100 dark:border-gray-700">
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Check for Alarm system condition:</span>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ getStatusColor($this->formData['alarm_system_condition'] ?? 'N/A') }}">
                                        {{ $this->formData['alarm_system_condition'] ?? 'N/A' }}
                                    </span>
                                </div>
                            </div>
                            @if(isset($this->formData['corrective_action']) && $this->formData['corrective_action'])
                                <div class="py-3 border-b border-gray-100 dark:border-gray-700">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Corrective Action:</span>
                                        <div class="flex items-center gap-2">
                                            {!! getPhotoButton('corrective_action', $this) !!}
                                        </div>
                                    </div>
                                    <div class="p-4 rounded-md bg-gray-50 dark:bg-gray-700">
                                        <p class="text-sm text-gray-700 dark:text-gray-300">{{ $this->formData['corrective_action'] }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- PLENUM Section -->
                    <div class="mb-8">
                        <h4 class="text-base font-semibold text-gray-900 mb-4 pb-2 border-b-2 border-gray-200">I - PLENUM</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <span class="text-sm font-medium text-gray-600">Cleaning of incubator roof and plenum:</span>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ getStatusColor($this->formData['cleaning_incubator_roof_and_plenum'] ?? 'N/A') }}">
                                        {{ formatDisplayValue($this->formData['cleaning_incubator_roof_and_plenum'] ?? 'N/A') }}
                                    </span>
                                    {!! getPhotoButton('cleaning_incubator_roof_and_plenum', $this) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- GENERAL MACHINE Section -->
                    <div class="mb-8">
                        <h4 class="text-base font-semibold text-gray-900 mb-4 pb-2 border-b-2 border-gray-200">II - GENERAL MACHINE</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <span class="text-sm font-medium text-gray-600">Check incubator doors for air leakage:</span>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ getStatusColor($this->formData['check_incubator_doors_for_air_leakage'] ?? 'N/A') }}">
                                        {{ formatDisplayValue($this->formData['check_incubator_doors_for_air_leakage'] ?? 'N/A') }}
                                    </span>
                                    {!! getPhotoButton('check_incubator_doors_for_air_leakage', $this) !!}
                                </div>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <span class="text-sm font-medium text-gray-600">Checking of baggy against the gaskets:</span>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ getStatusColor($this->formData['checking_of_baggy_against_the_gaskets'] ?? 'N/A') }}">
                                        {{ formatDisplayValue($this->formData['checking_of_baggy_against_the_gaskets'] ?? 'N/A') }}
                                    </span>
                                    {!! getPhotoButton('checking_of_baggy_against_the_gaskets', $this) !!}
                                </div>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <span class="text-sm font-medium text-gray-600">Check curtain position and condition:</span>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ getStatusColor($this->formData['check_curtain_position_and_condition'] ?? 'N/A') }}">
                                        {{ formatDisplayValue($this->formData['check_curtain_position_and_condition'] ?? 'N/A') }}
                                    </span>
                                    {!! getPhotoButton('check_curtain_position_and_condition', $this) !!}
                                </div>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <span class="text-sm font-medium text-gray-600">Check wick for replacement / washing:</span>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ getStatusColor($this->formData['check_wick_for_replacement_washing'] ?? 'N/A') }}">
                                        {{ formatDisplayValue($this->formData['check_wick_for_replacement_washing'] ?? 'N/A') }}
                                    </span>
                                    {!! getPhotoButton('check_wick_for_replacement_washing', $this) !!}
                                </div>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <span class="text-sm font-medium text-gray-600">Check spray nozzle and water pan:</span>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ getStatusColor($this->formData['check_spray_nozzle_and_water_pan'] ?? 'N/A') }}">
                                        {{ formatDisplayValue($this->formData['check_spray_nozzle_and_water_pan'] ?? 'N/A') }}
                                    </span>
                                    {!! getPhotoButton('check_spray_nozzle_and_water_pan', $this) !!}
                                </div>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <span class="text-sm font-medium text-gray-600">Check incubator fans for vibration:</span>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ getStatusColor($this->formData['check_incubator_fans_for_vibration'] ?? 'N/A') }}">
                                        {{ formatDisplayValue($this->formData['check_incubator_fans_for_vibration'] ?? 'N/A') }}
                                    </span>
                                    {!! getPhotoButton('check_incubator_fans_for_vibration', $this) !!}
                                </div>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <span class="text-sm font-medium text-gray-600">Check rack baffle condition:</span>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ getStatusColor($this->formData['check_rack_baffle_condition'] ?? 'N/A') }}">
                                        {{ formatDisplayValue($this->formData['check_rack_baffle_condition'] ?? 'N/A') }}
                                    </span>
                                    {!! getPhotoButton('check_rack_baffle_condition', $this) !!}
                                </div>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <span class="text-sm font-medium text-gray-600">Drain water out from air compressor tank:</span>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ getStatusColor($this->formData['drain_water_out_from_air_compressor_tank'] ?? 'N/A') }}">
                                        {{ formatDisplayValue($this->formData['drain_water_out_from_air_compressor_tank'] ?? 'N/A') }}
                                    </span>
                                    {!! getPhotoButton('drain_water_out_from_air_compressor_tank', $this) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- CLEANING Section -->
                    <div class="mb-8">
                        <h4 class="text-base font-semibold text-gray-900 mb-4 pb-2 border-b-2 border-gray-200">III - CLEANING</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <span class="text-sm font-medium text-gray-600">Check water level of blue tank:</span>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ getStatusColor($this->formData['check_water_level_of_blue_tank'] ?? 'N/A') }}">
                                        {{ formatDisplayValue($this->formData['check_water_level_of_blue_tank'] ?? 'N/A') }}
                                    </span>
                                    {!! getPhotoButton('check_water_level_of_blue_tank', $this) !!}
                                </div>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <span class="text-sm font-medium text-gray-600">Cleaning of incubator floor area:</span>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ getStatusColor($this->formData['cleaning_of_incubator_floor_area'] ?? 'N/A') }}">
                                        {{ formatDisplayValue($this->formData['cleaning_of_incubator_floor_area'] ?? 'N/A') }}
                                    </span>
                                    {!! getPhotoButton('cleaning_of_incubator_floor_area', $this) !!}
                                </div>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <span class="text-sm font-medium text-gray-600">Cleaning of entrance and exit area flooring:</span>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ getStatusColor($this->formData['cleaning_of_entrance_and_exit_area_flooring'] ?? 'N/A') }}">
                                        {{ formatDisplayValue($this->formData['cleaning_of_entrance_and_exit_area_flooring'] ?? 'N/A') }}
                                    </span>
                                    {!! getPhotoButton('cleaning_of_entrance_and_exit_area_flooring', $this) !!}
                                </div>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <span class="text-sm font-medium text-gray-600">Clean and refill water reservoir:</span>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ getStatusColor($this->formData['clean_and_refill_water_reservoir'] ?? 'N/A') }}">
                                        {{ formatDisplayValue($this->formData['clean_and_refill_water_reservoir'] ?? 'N/A') }}
                                    </span>
                                    {!! getPhotoButton('clean_and_refill_water_reservoir', $this) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- OTHERS Section -->
                    <div class="mb-8">
                        <h4 class="text-base font-semibold text-gray-900 mb-4 pb-2 border-b-2 border-gray-200">IV - OTHERS</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <span class="text-sm font-medium text-gray-600">Egg setting preparation:</span>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ getStatusColor($this->formData['egg_setting_preparation'] ?? 'N/A') }}">
                                        {{ formatDisplayValue($this->formData['egg_setting_preparation'] ?? 'N/A') }}
                                    </span>
                                    {!! getPhotoButton('egg_setting_preparation', $this) !!}
                                </div>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <span class="text-sm font-medium text-gray-600">Egg setting:</span>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ getStatusColor($this->formData['egg_setting'] ?? 'N/A') }}">
                                        {{ formatDisplayValue($this->formData['egg_setting'] ?? 'N/A') }}
                                    </span>
                                    {!! getPhotoButton('egg_setting', $this) !!}
                                </div>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <span class="text-sm font-medium text-gray-600">Record egg setting on board:</span>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ getStatusColor($this->formData['record_egg_setting_on_board'] ?? 'N/A') }}">
                                        {{ formatDisplayValue($this->formData['record_egg_setting_on_board'] ?? 'N/A') }}
                                    </span>
                                    {!! getPhotoButton('record_egg_setting_on_board', $this) !!}
                                </div>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <span class="text-sm font-medium text-gray-600">Record egg setting time:</span>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ getStatusColor($this->formData['record_egg_setting_time'] ?? 'N/A') }}">
                                        {{ formatDisplayValue($this->formData['record_egg_setting_time'] ?? 'N/A') }}
                                    </span>
                                    {!! getPhotoButton('record_egg_setting_time', $this) !!}
                                </div>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <span class="text-sm font-medium text-gray-600">Assist in Random Candling:</span>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ getStatusColor($this->formData['assist_in_random_candling'] ?? 'N/A') }}">
                                        {{ formatDisplayValue($this->formData['assist_in_random_candling'] ?? 'N/A') }}
                                    </span>
                                    {!! getPhotoButton('assist_in_random_candling', $this) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No form data available</h3>
                        <p class="mt-1 text-sm text-gray-500">The form data could not be loaded.</p>
                    </div>
                @endif
            </div>
            
            <!-- Modal Footer -->
            <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" 
                        @click="showModal = false; $wire.closeModal()"
                        class="w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-base font-medium text-gray-700 dark:text-gray-300 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm cursor-pointer">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Photo Viewer Modal -->
@if(count($selectedPhotos) > 0)
<div x-data="{ 
    showPhotoModal: @entangle('showPhotoModal'), 
    currentPhotoIndex: 0,
    selectedPhotos: @js($selectedPhotos)
}" 
    x-init="currentPhotoIndex = Math.min(currentPhotoIndex, selectedPhotos.length - 1) || 0" 
     x-show="showPhotoModal" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 overflow-y-auto bg-gray-500/75"
     style="display: none;">
    
    <div class="flex min-h-full items-center justify-center p-4 text-center">
        <!-- Modal Panel -->
        <div x-show="showPhotoModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all w-full max-w-sm">
            
            <!-- Modal Header -->
            <div class="bg-white px-4 py-3 border-b border-gray-200 sm:px-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900">
                        Photos
                    </h3>
                    <button type="button" 
                            @click="showPhotoModal = false; $wire.closePhotoModal()"
                            class="text-gray-400 hover:text-gray-600 shrink-0">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- Modal Body -->
            <div class="bg-white px-4 py-4 sm:p-6 sm:pb-4">
                @if(count($selectedPhotos) > 0)
                    <div class="relative w-full max-w-sm aspect-square bg-gray-900 rounded-lg overflow-hidden">
                        <template x-if="selectedPhotos[currentPhotoIndex]">
                            <img :src="selectedPhotos[currentPhotoIndex]?.url || ''" class="w-full h-full object-contain">
                        </template>

                        @if(count($selectedPhotos) > 1)
                            <button type="button" @click="currentPhotoIndex = (currentPhotoIndex - 1 + selectedPhotos.length) % selectedPhotos.length" class="absolute left-2 top-1/2 -translate-y-1/2 z-10 bg-black/60 hover:bg-black/80 text-white p-3 rounded-full shadow-lg">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                            </button>
                        @endif

                        @if(count($selectedPhotos) > 1)
                            <button type="button" @click="currentPhotoIndex = (currentPhotoIndex + 1) % selectedPhotos.length" class="absolute right-2 top-1/2 -translate-y-1/2 z-10 bg-black/60 hover:bg-black/80 text-white p-3 rounded-full shadow-lg">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>
                        @endif
                    </div>

                    <div class="mt-3 text-center text-sm text-gray-600">
                        <span x-text="currentPhotoIndex + 1"></span> / <span>{{ count($selectedPhotos) }}</span>
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No photos available</h3>
                        <p class="mt-1 text-sm text-gray-500">No photos have been uploaded for this field.</p>
                    </div>
                @endif
            </div>
            
            <!-- Modal Footer -->
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" 
                        @click="showPhotoModal = false; $wire.closePhotoModal()"
                        class="w-full inline-flex justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
@endif
