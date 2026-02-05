<div>
    <form wire:submit.prevent="changePassword">
        <!-- Current Password -->
        <div class="mb-4">
            <x-text-input 
                label="Current Password"
                name="currentPassword"
                type="password"
                wire:model="currentPassword"
                placeholder="Enter current password"
                required
                icon="lock"
            />
        </div>

        <!-- New Password -->
        <div class="mb-4">
            <x-text-input 
                label="New Password"
                name="newPassword"
                type="password"
                wire:model="newPassword"
                placeholder="Enter new password (min. 8 characters)"
                required
                icon="lock"
            />
            <p class="mt-1 text-xs text-gray-500">Minimum 8 characters</p>
        </div>

        <!-- Confirm New Password -->
        <div class="mb-6">
            <x-text-input 
                label="Confirm New Password"
                name="newPasswordConfirmation"
                type="password"
                wire:model="newPasswordConfirmation"
                placeholder="Confirm new password"
                required
                icon="lock"
            />
        </div>

        <!-- Actions -->
        <div class="flex justify-end space-x-3">
            <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-150">
                Cancel
            </a>
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

<!-- Toast Component -->
<x-toast />
