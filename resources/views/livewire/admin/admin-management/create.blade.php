<div>
    @if ($showModal)
        <div class="fixed inset-0 z-9999 flex items-center justify-center p-4" wire:ignore.self>
            <div class="fixed inset-0 bg-black/50" wire:click="closeModal"></div>

            <div class="relative w-full max-w-md p-6 bg-white dark:bg-gray-800 shadow-xl dark:shadow-2xl rounded-lg">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Add New Account</h3>
                    <button type="button" wire:click="closeModal" class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 cursor-pointer">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form wire:submit.prevent="createAdmin">
                    <!-- First Name -->
                    <div class="mb-4">
                        <x-text-input
                            label="First Name"
                            id="firstName"
                            name="firstName"
                            type="text"
                            :wireModel="'firstName'"
                            placeholder="Enter first name"
                        />
                    </div>

                    <!-- Last Name -->
                    <div class="mb-4">
                        <x-text-input
                            label="Last Name"
                            id="lastName"
                            name="lastName"
                            type="text"
                            :wireModel="'lastName'"
                            placeholder="Enter last name"
                        />
                    </div>

                    <!-- Role -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role</label>
                        <select
                            wire:model="role"
                            class="w-full px-3 py-2 text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 dark:text-white"
                        >
                            <option value="1">Admin</option>
                            <option value="0">Superadmin</option>
                        </select>
                        @error('role') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
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
                            wire:target="createAdmin"
                        >
                            <span wire:loading.remove wire:target="createAdmin">Create Account</span>
                            <span wire:loading wire:target="createAdmin">Creating...</span>
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
