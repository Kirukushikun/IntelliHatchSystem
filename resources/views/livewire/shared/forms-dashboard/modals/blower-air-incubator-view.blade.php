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
                return 'bg-gray-200 dark:bg-gray-600 text-gray-900 dark:text-gray-100';
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
     class="fixed inset-0 z-50 overflow-y-auto bg-black/50 dark:bg-black/80"
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
                        INCUBATOR BLOWER AIR SPEED MONITORING
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
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Hatchery Man:</span>
                                <span class="text-sm text-gray-900 dark:text-gray-200">{{ $this->selectedForm->user ? ($this->selectedForm->user->first_name . ' ' . $this->selectedForm->user->last_name) : 'Unknown' }}</span>
                            </div>
                            <div class="flex justify-between items-center py-3 border-b border-gray-100 dark:border-gray-700">
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Incubator:</span>
                                <span class="text-sm text-gray-900 dark:text-gray-200">{{ $this->formData['machine_info']['name'] ?? ($this->machine_info['name'] ?? ($this->selectedForm->machine_info['name'] ?? 'N/A')) }}</span>
                            </div>
                        </div>
                        
                        <div class="mt-6 space-y-4">
                            <div class="py-3 border-b border-gray-100 dark:border-gray-700">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm font-medium text-gray-600 dark:text-gray-400">CFM Fan Reading:</span>
                                    <div class="flex items-center gap-2">
                                        {!! getPhotoButton('cfm_fan_reading', $this) !!}
                                    </div>
                                </div>
                                <div class="p-4 rounded-md bg-gray-50 dark:bg-gray-700">
                                    <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $this->formData['cfm_fan_reading'] ?? 'N/A' }}</p>
                                </div>
                            </div>
                            
                            <div class="py-3 border-b border-gray-100 dark:border-gray-700">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Action Taken:</span>
                                </div>
                                <div class="p-4 rounded-md bg-gray-50 dark:bg-gray-700">
                                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ $this->formData['cfm_fan_action_taken'] ?? 'N/A' }}</p>
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
                        class="w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-base font-medium text-gray-700 dark:text-gray-300 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm cursor-pointer">
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
     class="fixed inset-0 z-50 overflow-y-auto bg-black/50 dark:bg-black/80"
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
             class="relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 text-left shadow-xl dark:shadow-2xl transition-all w-full max-w-sm">
            
            <!-- Modal Header -->
            <div class="bg-white dark:bg-gray-800 px-4 py-3 border-b border-gray-200 dark:border-gray-700 sm:px-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white">
                        Photos
                    </h3>
                    <button type="button" 
                            @click="showPhotoModal = false; $wire.closePhotoModal()"
                            class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 shrink-0">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- Modal Body -->
            <div class="bg-white dark:bg-gray-800 px-4 py-4 sm:p-6 sm:pb-4">
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

                    <div class="mt-3 text-center text-sm text-gray-600 dark:text-gray-400">
                        <span x-text="currentPhotoIndex + 1"></span> / <span>{{ count($selectedPhotos) }}</span>
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No photos available</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">No photos have been uploaded for this field.</p>
                    </div>
                @endif
            </div>
            
            <!-- Modal Footer -->
            <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" 
                        @click="showPhotoModal = false; $wire.closePhotoModal()"
                        class="w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-base font-medium text-gray-700 dark:text-gray-300 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
@endif