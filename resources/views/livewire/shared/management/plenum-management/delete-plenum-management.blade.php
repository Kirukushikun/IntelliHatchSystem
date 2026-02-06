<div>
    <!-- Modal -->
    @if ($showModal)
        <div class="fixed inset-0 z-9999 flex items-center justify-center p-4" wire:ignore.self>
            <!-- Background overlay -->
            <div class="fixed inset-0 bg-black/50" wire:click="closeModal"></div>

            <!-- Modal panel -->
            <div class="relative w-full max-w-md p-6 bg-white shadow-xl rounded-lg">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Delete Plenum</h3>
                    <button type="button" wire:click="closeModal" class="text-gray-400 hover:text-gray-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="mb-6">
                    <p class="text-sm text-gray-600">
                        Are you sure you want to delete <strong>{{ $plenumName }}</strong>? This action cannot be undone.
                    </p>
                </div>

                <div class="flex justify-end gap-3">
                    <x-button variant="outline-secondary" type="button" wire:click="closeModal" class="cursor-pointer">
                        Cancel
                    </x-button>
                    <x-button variant="danger" type="button" wire:click="deletePlenum" class="cursor-pointer">
                        Delete Plenum
                    </x-button>
                </div>
            </div>
        </div>
    @endif
</div>
