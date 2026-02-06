<div>
    <!-- Modal -->
    @if($showModal)
        <div class="fixed inset-0 z-9999 flex items-center justify-center p-4" wire:ignore.self>
            <!-- Background overlay -->
            <div class="fixed inset-0 bg-black/50" wire:click="closeModal"></div>

            <!-- Modal panel -->
            <div class="relative w-full max-w-md p-6 bg-white shadow-xl rounded-lg">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">
                        {{ !$isActive ? 'Activate Hatcher' : 'Deactivate Hatcher' }}
                    </h3>
                    <button type="button" wire:click="closeModal" class="text-gray-400 hover:text-gray-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Warning Icon and Message -->
                <div class="flex items-center mb-4">
                    <div class="shrink-0 w-12 h-12 {{ $isDisabled ? 'bg-green-100' : 'bg-red-100' }} rounded-full flex items-center justify-center">
                        @if($isDisabled)
                            <svg width="24" height="24" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-green-600">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M11.4669 3.72684C11.7558 3.91574 11.8369 4.30308 11.648 4.59198L7.39799 11.092C7.29783 11.2452 7.13556 11.3467 6.95402 11.3699C6.77247 11.3931 6.58989 11.3355 6.45446 11.2124L3.70446 8.71241C3.44905 8.48022 3.43023 8.08494 3.66242 7.82953C3.89461 7.57412 4.28989 7.55529 4.5453 7.78749L6.75292 9.79441L10.6018 3.90792C10.7907 3.61902 11.178 3.53795 11.4669 3.72684Z" fill="#16A34A"/>
                            </svg>
                        @else
                            <svg width="24" height="24" viewBox="0 0 48 48" version="1" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-red-600">
                                <path fill="#D50000" d="M24,6C14.1,6,6,14.1,6,24s8.1,18,18,18s18-8.1,18-18S33.9,6,24,6z M24,10c3.1,0,6,1.1,8.4,2.8L12.8,32.4 C11.1,30,10,27.1,10,24C10,16.3,16.3,10,24,10z M24,38c-3.1,0-6-1.1-8.4-2.8l19.6-19.6C36.9,18,38,20.9,38,24C38,31.7,31.7,38,24,38 z"/>
                            </svg>
                        @endif
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-900">
                            {{ !$isActive ? 'Activate hatcher' : 'Deactivate hatcher' }} <span class="font-semibold">{{ $hatcheryName }}</span>?
                        </p>
                        <p class="text-sm text-gray-500">
                            @if(!$isActive)
                                This will make the hatcher available for use again.
                            @else
                                This will make the hatcher unavailable for use. All data will be preserved.
                            @endif
                        </p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end space-x-3">
                    <x-button 
                        variant="outline-secondary" 
                        size="sm" 
                        wire:click="closeModal"
                        wire:loading.attr="disabled"
                        wire:target="closeModal"
                    >
                        <span wire:target="closeModal">Cancel</span>
                    </x-button>
                    <x-button 
                        variant="{{ !$isActive ? 'success' : 'danger' }}" 
                        size="sm" 
                        wire:click="toggleStatus"
                        wire:loading.attr="disabled"
                        wire:target="toggleStatus"
                        :loading="$processing"
                    >
                        <span wire:loading.remove wire:target="toggleStatus">
                            {{ !$isActive ? 'Activate Hatcher' : 'Deactivate Hatcher' }}
                        </span>
                        <span wire:loading wire:target="toggleStatus">
                            {{ !$isActive ? 'Activating...' : 'Deactivating...' }}
                        </span>
                    </x-button>
                </div>
            </div>
        </div>
    @endif
</div>
