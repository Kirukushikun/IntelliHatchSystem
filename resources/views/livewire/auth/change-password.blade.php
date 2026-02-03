<div>
    <form wire:submit.prevent="changePassword">
        <!-- Current Password -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Current Password
            </label>
            <input 
                type="password" 
                wire:model="currentPassword"
                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 {{ $errors->has('currentPassword') ? 'border-red-500' : 'border-gray-300' }}"
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
                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 {{ $errors->has('newPassword') ? 'border-red-500' : 'border-gray-300' }}"
                placeholder="Enter new password (min. 8 characters)"
            >
            @error('newPassword')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
            <p class="mt-1 text-xs text-gray-500">Minimum 8 characters </p>
        </div>

        <!-- Confirm New Password -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Confirm New Password
            </label>
            <input 
                type="password" 
                wire:model="newPasswordConfirmation"
                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 {{ $errors->has('newPasswordConfirmation') ? 'border-red-500' : 'border-gray-300' }}"
                placeholder="Confirm new password"
            >
            @error('newPasswordConfirmation')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
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

<!-- Success Toast Listener -->
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('password-changed', (event) => {
            const toast = document.createElement('div');
            toast.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
            toast.textContent = event.message;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
            
            // Redirect after successful password change
            setTimeout(() => {
                window.location.href = '{{ route("admin.dashboard") }}';
            }, 1500);
        });
    });
</script>
