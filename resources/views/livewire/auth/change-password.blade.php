<div>
    <!-- Change Password Button -->
    <button 
        wire:click="openModal"
        class="flex items-center w-full px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition-colors duration-200"
    >
        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
        </svg>
        Change Password
    </button>

    <!-- Modal -->
    @if($showModal)
        <div class="fixed inset-0 z-9999 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div class="fixed inset-0 z-40 transition-opacity bg-gray-500/75" wire:click="closeModal"></div>

                <!-- Modal panel -->
                <div class="relative z-50 inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold text-gray-900">Change Password</h3>
                        <button 
                            wire:click="closeModal"
                            class="text-gray-400 hover:text-gray-500 transition-colors"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form wire:submit="changePassword">
                        <!-- Current Password -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Current Password
                            </label>
                            <input 
                                type="password" 
                                wire:model="currentPassword"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('currentPassword') border-red-500 @enderror"
                                placeholder="Enter current password"
                            >
                            @error('currentPassword')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- New Password -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                New Password
                            </label>
                            <input 
                                type="password" 
                                wire:model="newPassword"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('newPassword') border-red-500 @enderror"
                                placeholder="Enter new password (min. 8 characters)"
                            >
                            @error('newPassword')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">Minimum 8 characters (NIST guidelines)</p>
                        </div>

                        <!-- Confirm New Password -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Confirm New Password
                            </label>
                            <input 
                                type="password" 
                                wire:model="newPasswordConfirmation"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('newPasswordConfirmation') border-red-500 @enderror"
                                placeholder="Confirm new password"
                            >
                            @error('newPasswordConfirmation')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Actions -->
                        <div class="flex justify-end space-x-3">
                            <x-button 
                                variant="outline-secondary" 
                                size="sm" 
                                wire:click="closeModal"
                            >
                                Cancel
                            </x-button>
                            <x-button 
                                variant="primary" 
                                size="sm" 
                                wire:click="changePassword"
                                wire:loading.attr="disabled"
                                wire:target="changePassword"
                                :loading="$processing"
                            >
                                <span wire:loading.remove wire:target="changePassword">Change Password</span>
                                <span wire:loading wire:target="changePassword">Changing...</span>
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Success Toast Listener -->
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('password-changed', (event) => {
                const toast = document.createElement('div');
                toast.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
                toast.textContent = event.message;
                document.body.appendChild(toast);
                setTimeout(() => toast.remove(), 3000);
            });
        });
    </script>
</div>
