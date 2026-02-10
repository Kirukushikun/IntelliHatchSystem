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
                return is_string($value[0] ?? '') ? ucfirst($value[0]) : 'N/A';
            } else {
                return 'N/A';
            }
        }
        
        return ucfirst($value);
    }
    
    function getPhotoButton($field, $livewire) {
        $photoCount = $livewire->getPhotoCount($field);
        
        if ($photoCount > 0) {
            return '<button 
                @click="$wire.viewPhotos(\'' . $field . '\')"
                class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-semibold rounded-lg border border-blue-200 bg-blue-50 text-blue-700 hover:bg-blue-100 hover:border-blue-300 transition-all duration-150 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <span>Photos (' . $photoCount . ')</span>
            </button>';
        } else {
            return '<span class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-medium rounded-lg bg-gray-100 text-gray-500 border border-gray-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span>No Photos</span>
            </span>';
        }
    }
    
    function getDynamicFieldConfig() {
        return [
            // Basic info fields
            'shift' => ['label' => 'Shift:', 'type' => 'status', 'section' => 'basic'],
            'alarm_system_condition' => ['label' => 'Check for Alarm system condition:', 'type' => 'status', 'section' => 'basic'],
            'corrective_action' => ['label' => 'Corrective Action:', 'type' => 'text', 'section' => 'basic'],
            
            // PLENUM Section
            'cleaning_incubator_roof_and_plenum' => ['label' => 'Cleaning of incubator roof and plenum:', 'type' => 'status', 'section' => 'plenum'],
            
            // GENERAL MACHINE Section
            'check_incubator_doors_for_air_leakage' => ['label' => 'Check incubator doors for air leakage:', 'type' => 'status', 'section' => 'general'],
            'checking_of_baggy_against_the_gaskets' => ['label' => 'Checking of baggy against the gaskets:', 'type' => 'status', 'section' => 'general'],
            'check_curtain_position_and_condition' => ['label' => 'Check curtain position and condition:', 'type' => 'status', 'section' => 'general'],
            'check_wick_for_replacement_washing' => ['label' => 'Check wick for replacement / washing:', 'type' => 'status', 'section' => 'general'],
            'check_spray_nozzle_and_water_pan' => ['label' => 'Check spray nozzle and water pan:', 'type' => 'status', 'section' => 'general'],
            'check_incubator_fans_for_vibration' => ['label' => 'Check incubator fans for vibration:', 'type' => 'status', 'section' => 'general'],
            'check_rack_baffle_condition' => ['label' => 'Check rack baffle condition:', 'type' => 'status', 'section' => 'general'],
            'drain_water_out_from_air_compressor_tank' => ['label' => 'Drain water out from air compressor tank:', 'type' => 'status', 'section' => 'general'],
            
            // CLEANING Section
            'check_water_level_of_blue_tank' => ['label' => 'Check water level of blue tank:', 'type' => 'status', 'section' => 'cleaning'],
            'cleaning_of_incubator_floor_area' => ['label' => 'Cleaning of incubator floor area:', 'type' => 'status', 'section' => 'cleaning'],
            'cleaning_of_entrance_and_exit_area_flooring' => ['label' => 'Cleaning of entrance and exit area flooring:', 'type' => 'status', 'section' => 'cleaning'],
            'clean_and_refill_water_reservoir' => ['label' => 'Clean and refill water reservoir:', 'type' => 'status', 'section' => 'cleaning'],
            
            // OTHERS Section
            'egg_setting_preparation' => ['label' => 'Egg setting preparation:', 'type' => 'status', 'section' => 'others'],
            'egg_setting' => ['label' => 'Egg setting:', 'type' => 'status', 'section' => 'others'],
            'record_egg_setting_on_board' => ['label' => 'Record egg setting on board:', 'type' => 'status', 'section' => 'others'],
            'record_egg_setting_time' => ['label' => 'Record egg setting time:', 'type' => 'status', 'section' => 'others'],
            'assist_in_random_candling' => ['label' => 'Assist in Random Candling:', 'type' => 'status', 'section' => 'others'],
        ];
    }
    
    function getSectionConfig() {
        return [
            'basic' => ['title' => 'Basic Information', 'order' => 1],
            'plenum' => ['title' => 'I - PLENUM', 'order' => 2],
            'general' => ['title' => 'II - GENERAL MACHINE', 'order' => 3],
            'cleaning' => ['title' => 'III - CLEANING', 'order' => 4],
            'others' => ['title' => 'IV - OTHERS', 'order' => 5],
        ];
    }
    
    function groupFieldsBySection($formData) {
        $fieldConfig = getDynamicFieldConfig();
        $sectionConfig = getSectionConfig();
        $grouped = [];
        
        // Initialize sections
        foreach ($sectionConfig as $key => $config) {
            $grouped[$key] = [
                'title' => $config['title'],
                'order' => $config['order'],
                'fields' => []
            ];
        }
        
        // Group fields by section
        foreach ($formData as $field => $value) {
            if (isset($fieldConfig[$field])) {
                $config = $fieldConfig[$field];
                $section = $config['section'];
                $grouped[$section]['fields'][$field] = array_merge($config, ['value' => $value]);
            }
        }
        
        // Sort sections by order
        uasort($grouped, function($a, $b) {
            return $a['order'] - $b['order'];
        });
        
        return $grouped;
    }
@endphp

<!-- Modal Backdrop -->
<div x-data="{ showModal: @entangle('showModal') }" 
     x-show="showModal" 
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
        <div x-show="showModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all w-full max-w-4xl">
            
            <!-- Modal Header -->
            <div class="bg-white px-4 py-3 border-b border-gray-200 sm:px-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">
                        INCUBATOR ROUTINE CHECKLIST PER SHIFT
                    </h3>
                    <button type="button" 
                            @click="showModal = false; $wire.closeModal()"
                            class="rounded-md bg-white text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- Modal Body -->
            <div class="bg-white px-4 py-4 sm:p-6 sm:pb-4 max-h-[70vh] overflow-y-auto">
                @if($selectedForm)
                    @php
                        $groupedFields = groupFieldsBySection($formData);
                    @endphp
                    
                    <!-- Basic Information -->
                    <div class="mb-8">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex justify-between items-center py-2 border-b border-gray-100 bg-white">
                                <span class="text-sm font-medium text-gray-600">Date:</span>
                                <span class="text-sm text-gray-900">{{ $selectedForm->date_submitted ? $selectedForm->date_submitted->format('M d, Y H:i') : 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-100 bg-gray-50">
                                <span class="text-sm font-medium text-gray-600">Shift:</span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ getStatusColor($formData['shift'] ?? 'N/A') }}">
                                    {{ formatDisplayValue($formData['shift'] ?? 'N/A') }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-100 bg-white">
                                <span class="text-sm font-medium text-gray-600">Hatchery Man:</span>
                                <span class="text-sm text-gray-900">{{ $selectedForm->user ? ($selectedForm->user->first_name . ' ' . $selectedForm->user->last_name) : 'Unknown' }}</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-100 bg-gray-50">
                                <span class="text-sm font-medium text-gray-600">Incubator:</span>
                                <span class="text-sm text-gray-900">{{ $selectedForm->incubator ? $selectedForm->incubator->incubatorName : 'N/A' }}</span>
                            </div>
                        </div>
                        
                        @if(isset($groupedFields['basic']['fields']) && !empty($groupedFields['basic']['fields']))
                            <div class="mt-4 space-y-3">
                                @php $fieldIndex = 0; @endphp
                                @foreach($groupedFields['basic']['fields'] as $field => $config)
                                    @if($field !== 'shift' && $field !== 'hatchery_man' && $field !== 'incubator')
                                        @if($field === 'alarm_system_condition')
                                            <div class="flex justify-between items-center py-2 border-b border-gray-100 {{ $fieldIndex % 2 === 0 ? 'bg-white' : 'bg-gray-50' }}">
                                                <span class="text-sm font-medium text-gray-600">{{ $config['label'] }}</span>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ getStatusColor($config['value'] ?? 'N/A') }}">
                                                    {{ formatDisplayValue($config['value'] ?? 'N/A') }}
                                                </span>
                                            </div>
                                            @if(isset($groupedFields['basic']['fields']['corrective_action']) && !empty($groupedFields['basic']['fields']['corrective_action']['value']))
                                                <div class="py-2 {{ ($fieldIndex + 1) % 2 === 0 ? 'bg-white' : 'bg-gray-50' }}">
                                                    <span class="text-sm font-medium text-gray-600 block mb-2">{{ $groupedFields['basic']['fields']['corrective_action']['label'] }}</span>
                                                    <div class="bg-gray-50 p-3 rounded-md">
                                                        <p class="text-sm text-gray-700 mb-2">{{ $groupedFields['basic']['fields']['corrective_action']['value'] }}</p>
                                                        <div class="mt-2">
                                                            {!! getPhotoButton('corrective_action', $this) !!}
                                                        </div>
                                                    </div>
                                                </div>
                                                @php $fieldIndex += 2; @endphp
                                            @endif
                                        @elseif($field === 'corrective_action')
                                            <!-- Skip corrective action as it's handled above -->
                                        @elseif($config['type'] === 'status')
                                            <div class="flex justify-between items-center py-2 border-b border-gray-100 {{ $fieldIndex % 2 === 0 ? 'bg-white' : 'bg-gray-50' }}">
                                                <span class="text-sm font-medium text-gray-600">{{ $config['label'] }}</span>
                                                <div class="flex items-center gap-2">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ getStatusColor($config['value'] ?? 'N/A') }}">
                                                        {{ formatDisplayValue($config['value'] ?? 'N/A') }}
                                                    </span>
                                                    {!! getPhotoButton($field, $this) !!}
                                                </div>
                                            </div>
                                        @elseif($config['type'] === 'text' && !empty($config['value']))
                                            <div class="py-2 {{ $fieldIndex % 2 === 0 ? 'bg-white' : 'bg-gray-50' }}">
                                                <span class="text-sm font-medium text-gray-600 block mb-2">{{ $config['label'] }}</span>
                                                <div class="bg-gray-50 p-3 rounded-md">
                                                    <p class="text-sm text-gray-700">{{ $config['value'] }}</p>
                                                </div>
                                            </div>
                                        @endif
                                        @php $fieldIndex++; @endphp
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </div>
                    
                    <!-- Dynamic Sections -->
                    @foreach($groupedFields as $sectionKey => $section)
                        @if($sectionKey !== 'basic' && !empty($section['fields']))
                            <div class="mb-8">
                                <h4 class="text-base font-semibold text-gray-900 mb-4 pb-2 border-b-2 border-gray-200">{{ $section['title'] }}</h4>
                                <div class="space-y-2">
                                    @php $fieldIndex = 0; @endphp
                                    @foreach($section['fields'] as $field => $config)
                                        @if($config['type'] === 'status')
                                            <div class="flex justify-between items-center py-2 border-b border-gray-100 {{ $fieldIndex % 2 === 0 ? 'bg-white' : 'bg-gray-50' }}">
                                                <span class="text-sm font-medium text-gray-600">{{ $config['label'] }}</span>
                                                <div class="flex items-center gap-2">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ getStatusColor($config['value'] ?? 'N/A') }}">
                                                        {{ formatDisplayValue($config['value'] ?? 'N/A') }}
                                                    </span>
                                                    {!! getPhotoButton($field, $this) !!}
                                                </div>
                                            </div>
                                        @elseif($config['type'] === 'text' && !empty($config['value']))
                                            <div class="py-2 {{ $fieldIndex % 2 === 0 ? 'bg-white' : 'bg-gray-50' }}">
                                                <span class="text-sm font-medium text-gray-600 block mb-2">{{ $config['label'] }}</span>
                                                <div class="bg-gray-50 p-3 rounded-md">
                                                    <p class="text-sm text-gray-700">{{ $config['value'] }}</p>
                                                </div>
                                            </div>
                                        @endif
                                        @php $fieldIndex++; @endphp
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
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
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" 
                        @click="showModal = false; $wire.closeModal()"
                        class="w-full inline-flex justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm">
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
                            <button type="button" @click="currentPhotoIndex = (currentPhotoIndex - 1 + count($selectedPhotos)) % count($selectedPhotos)" class="absolute left-2 top-1/2 -translate-y-1/2 z-10 bg-black/60 hover:bg-black/80 text-white p-3 rounded-full shadow-lg">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                            </button>
                        @endif

                        @if(count($selectedPhotos) > 1)
                            <button type="button" @click="currentPhotoIndex = (currentPhotoIndex + 1) % count($selectedPhotos)" class="absolute right-2 top-1/2 -translate-y-1/2 z-10 bg-black/60 hover:bg-black/80 text-white p-3 rounded-full shadow-lg">
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
