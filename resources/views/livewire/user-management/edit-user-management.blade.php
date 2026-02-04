<div>
    <!-- Modal -->
    @if ($showModal)
        <div class="fixed inset-0 z-9999 flex items-center justify-center p-4" wire:ignore.self>
            <!-- Background overlay -->
            <div class="fixed inset-0 bg-black/50" wire:click="closeModal"></div>

            <!-- Modal panel -->
            <div class="relative w-full max-w-md p-6 bg-white shadow-xl rounded-lg">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Edit User</h3>
                    <button type="button" wire:click="closeModal" class="text-gray-400 hover:text-gray-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form wire:submit.prevent="updateUser">
                    <!-- First Name -->
                    <div class="mb-4">
                        <label for="firstName" class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                        <input 
                            type="text" 
                            id="firstName"
                            wire:model.live="firstName" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                            placeholder="Enter first name"
                        >
                        @error('firstName')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Last Name -->
                    <div class="mb-6">
                        <label for="lastName" class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                        <input 
                            type="text" 
                            id="lastName"
                            wire:model.live="lastName" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                            placeholder="Enter last name"
                        >
                        @error('lastName')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
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
                            variant="primary" 
                            size="sm" 
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="updateUser"
                        >
                            <span wire:loading.remove wire:target="updateUser">Update User</span>
                            <span wire:loading wire:target="updateUser">Updating...</span>
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>