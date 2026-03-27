<!-- Detail Modal -->
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
        <div x-show="showModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:scale-95"
             class="relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 text-left shadow-xl transition-all w-full max-w-4xl"
             @click.stop>
            <div class="bg-white dark:bg-gray-800 px-6 py-3 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">INCUBATOR ENTRANCE TEMPERATURE MONITORING</h3>
                    <button type="button" @click="showModal = false; $wire.closeModal()" class="rounded-md text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none cursor-pointer">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 px-6 py-4 max-h-[70vh] overflow-y-auto">
                @if($this->selectedForm)
                    <div class="space-y-4 mb-6">
                        <div class="flex justify-between items-center py-3 border-b border-gray-100 dark:border-gray-700"><span class="text-sm font-medium text-gray-600 dark:text-gray-400">Date Submitted:</span><span class="text-sm text-gray-900 dark:text-gray-200">{{ $this->selectedForm->date_submitted ? $this->selectedForm->date_submitted->format('d M, Y g:i A') : 'N/A' }}</span></div>
                        <div class="flex justify-between items-center py-3 border-b border-gray-100 dark:border-gray-700"><span class="text-sm font-medium text-gray-600 dark:text-gray-400">Hatchery Man:</span><span class="text-sm text-gray-900 dark:text-gray-200">{{ $this->selectedForm->user ? ($this->selectedForm->user->first_name . ' ' . $this->selectedForm->user->last_name) : 'Unknown' }}</span></div>
                        <div class="flex justify-between items-center py-3 border-b border-gray-100 dark:border-gray-700"><span class="text-sm font-medium text-gray-600 dark:text-gray-400">Days of Incubation:</span><span class="text-sm text-gray-900 dark:text-gray-200">{{ $this->formData['days_of_incubation'] ?? 'N/A' }}</span></div>
                        <div class="flex justify-between items-center py-3 border-b border-gray-100 dark:border-gray-700"><span class="text-sm font-medium text-gray-600 dark:text-gray-400">Time of Check:</span><span class="text-sm text-gray-900 dark:text-gray-200">{{ $this->formData['time_of_check'] ?? 'N/A' }}</span></div>
                        <div class="flex justify-between items-center py-3 border-b border-gray-100 dark:border-gray-700"><span class="text-sm font-medium text-gray-600 dark:text-gray-400">Time Finished:</span><span class="text-sm text-gray-900 dark:text-gray-200">{{ $this->formData['time_finished'] ?? 'N/A' }}</span></div>
                    </div>
                    <div class="mb-6">
                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide mb-4">Machine Info</h4>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center py-3 border-b border-gray-100 dark:border-gray-700"><span class="text-sm font-medium text-gray-600 dark:text-gray-400">Incubator:</span><span class="text-sm text-gray-900 dark:text-gray-200">{{ $this->formData['machine_info']['name'] ?? 'N/A' }}</span></div>
                        </div>
                    </div>
                    <div class="mb-6">
                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide mb-4">Temperature Readings</h4>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center py-3 border-b border-gray-100 dark:border-gray-700"><span class="text-sm font-medium text-gray-600 dark:text-gray-400">Set Point Temp:</span><span class="text-sm text-gray-900 dark:text-gray-200">{{ $this->formData['set_point_temp'] ?? 'N/A' }}</span></div>
                            <div class="flex justify-between items-center py-3 border-b border-gray-100 dark:border-gray-700"><span class="text-sm font-medium text-gray-600 dark:text-gray-400">Set Point Humidity:</span><span class="text-sm text-gray-900 dark:text-gray-200">{{ $this->formData['set_point_humidity'] ?? 'N/A' }}</span></div>
                            <div class="flex justify-between items-center py-3 border-b border-gray-100 dark:border-gray-700"><span class="text-sm font-medium text-gray-600 dark:text-gray-400">Entrance Temp:</span><span class="text-sm text-gray-900 dark:text-gray-200">{{ $this->formData['entrance_temp'] ?? 'N/A' }}</span></div>
                            <div class="py-3 border-b border-gray-100 dark:border-gray-700">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Entrance Photos:</span>
                                    @php $photoCount = $this->getPhotoCount('entrance_photo'); @endphp
                                    @if($photoCount > 0)
                                        <button @click="$wire.viewPhotos('entrance_photo')" class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-semibold rounded-lg border border-blue-200 dark:border-blue-600 bg-blue-50 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300 hover:bg-blue-100 transition-all cursor-pointer">Photos ({{ $photoCount }})</button>
                                    @else
                                        <span class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-medium rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-600">No Photos</span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex justify-between items-center py-3 border-b border-gray-100 dark:border-gray-700"><span class="text-sm font-medium text-gray-600 dark:text-gray-400">Baggy No. 2:</span><span class="text-sm text-gray-900 dark:text-gray-200">{{ $this->formData['baggy'] ?? 'N/A' }}</span></div>
                            <div class="py-3 border-b border-gray-100 dark:border-gray-700">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Baggy Photos:</span>
                                    @php $photoCount = $this->getPhotoCount('baggy_photo'); @endphp
                                    @if($photoCount > 0)
                                        <button @click="$wire.viewPhotos('baggy_photo')" class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-semibold rounded-lg border border-blue-200 dark:border-blue-600 bg-blue-50 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300 hover:bg-blue-100 transition-all cursor-pointer">Photos ({{ $photoCount }})</button>
                                    @else
                                        <span class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-medium rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-600">No Photos</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide mb-4">Temperature Adjustment</h4>
                        <div class="space-y-4">
                            <div class="py-3 border-b border-gray-100 dark:border-gray-700"><span class="text-sm font-medium text-gray-600 dark:text-gray-400 block mb-1">Notes:</span><span class="text-sm text-gray-900 dark:text-gray-200">{{ $this->formData['temp_adjustment_notes'] ?? 'N/A' }}</span></div>
                            <div class="py-3 border-b border-gray-100 dark:border-gray-700">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Adjustment Photos:</span>
                                    @php $photoCount = $this->getPhotoCount('temp_adjustment_photo'); @endphp
                                    @if($photoCount > 0)
                                        <button @click="$wire.viewPhotos('temp_adjustment_photo')" class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-semibold rounded-lg border border-blue-200 dark:border-blue-600 bg-blue-50 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300 hover:bg-blue-100 transition-all cursor-pointer">Photos ({{ $photoCount }})</button>
                                    @else
                                        <span class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-medium rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-600">No Photos</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-8"><svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg><h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No form data available</h3></div>
                @endif
            </div>
            <div class="bg-gray-50 dark:bg-gray-700 px-6 py-3 flex justify-end">
                <button type="button" @click="showModal = false; $wire.closeModal()" class="inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none cursor-pointer">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Photo Viewer Modal -->
@if(count($selectedPhotos) > 0)
<div x-data="{ showPhotoModal: @entangle('showPhotoModal'), currentPhotoIndex: 0, selectedPhotos: @js($selectedPhotos) }"
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
    <div class="flex min-h-full items-center justify-center p-4">
        <div x-show="showPhotoModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 text-left shadow-xl w-full max-w-sm">
            <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Photos</h3>
                <button type="button" @click="showPhotoModal = false; $wire.closePhotoModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
            </div>
            <div class="p-4">
                <div class="relative w-full aspect-square bg-gray-900 rounded-lg overflow-hidden">
                    <template x-if="selectedPhotos[currentPhotoIndex]"><img :src="selectedPhotos[currentPhotoIndex]?.url || ''" class="w-full h-full object-contain"></template>
                    @if(count($selectedPhotos) > 1)
                        <button type="button" @click="currentPhotoIndex = (currentPhotoIndex - 1 + selectedPhotos.length) % selectedPhotos.length" class="absolute left-2 top-1/2 -translate-y-1/2 z-10 bg-black/60 hover:bg-black/80 text-white p-3 rounded-full shadow-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg></button>
                        <button type="button" @click="currentPhotoIndex = (currentPhotoIndex + 1) % selectedPhotos.length" class="absolute right-2 top-1/2 -translate-y-1/2 z-10 bg-black/60 hover:bg-black/80 text-white p-3 rounded-full shadow-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg></button>
                    @endif
                </div>
                <div class="mt-3 text-center text-sm text-gray-600 dark:text-gray-400"><span x-text="currentPhotoIndex + 1"></span> / <span>{{ count($selectedPhotos) }}</span></div>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 flex justify-end">
                <button type="button" @click="showPhotoModal = false; $wire.closePhotoModal()" class="inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 cursor-pointer">Close</button>
            </div>
        </div>
    </div>
</div>
@endif
