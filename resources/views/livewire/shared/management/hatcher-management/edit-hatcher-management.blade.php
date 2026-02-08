<div>
    <!-- Modal -->
    @if ($showModal)
        <div class="fixed inset-0 z-9999 flex items-center justify-center p-4" wire:ignore.self>
            <!-- Background overlay -->
            <div class="fixed inset-0 bg-black/50" wire:click="closeModal"></div>

            <!-- Modal panel -->
            <div class="relative w-full max-w-md p-6 bg-white shadow-xl rounded-lg">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Edit Hatcher</h3>
                    <button type="button" wire:click="closeModal" class="text-gray-400 hover:text-gray-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form wire:submit.prevent="updateHatcher">
                    <div class="space-y-4">
                        <div>
                            <x-text-input
                                label="Hatcher Name"
                                id="hatcherName"
                                name="hatcherName"
                                type="text"
                                :wireModel="'hatcherName'"
                                placeholder="Enter hatcher name"
                                required
                            />
                            @error('hatcherName')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 mt-6">
                        <x-button variant="outline-secondary" type="button" wire:click="closeModal" class="cursor-pointer">
                            Cancel
                        </x-button>
                        <x-button variant="primary" type="submit" class="cursor-pointer">
                            Update Hatcher
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
