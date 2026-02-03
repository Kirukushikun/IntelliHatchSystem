<div>
    <!-- Modal -->
    @if($showModal)
        <div class="fixed inset-0 z-9999 flex items-center justify-center p-4" wire:ignore.self>
            <!-- Background overlay -->
            <div class="fixed inset-0 bg-black/50" wire:click="closeModal"></div>

            <!-- Modal panel -->
            <div class="relative w-full max-w-md p-6 bg-white shadow-xl rounded-lg">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Reset Password</h3>
                    <button type="button" wire:click="closeModal" class="text-gray-400 hover:text-gray-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Warning Icon and Message -->
                <div class="flex items-center mb-4">
                    <div class="shrink-0 w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                        <svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-yellow-600">
                            <g transform="translate(0 -1028.4)">
                                <g transform="matrix(.70711 .70711 -.70711 .70711 740.06 298.16)">
                                    <path d="m10.541 1028.9c-3.3134 0-5.9997 2.6-5.9997 6 0 3.3 2.6863 6 5.9997 6 3.314 0 6-2.7 6-6 0-3.4-2.686-6-6-6zm0 2c1.105 0 2 0.9 2 2s-0.895 2-2 2c-1.1042 0-1.9997-0.9-1.9997-2s0.8955-2 1.9997-2z" fill="#f39c12"/>
                                    <g fill="#f1c40f">
                                        <path d="m10 0c-3.3137 0-6 2.6863-6 6s2.6863 6 6 6c3.314 0 6-2.6863 6-6s-2.686-6-6-6zm0 2c1.105 0 2 0.8954 2 2s-0.895 2-2 2c-1.1046 0-2-0.8954-2-2s0.8954-2 2-2z" transform="translate(0 1028.4)"/>
                                        <rect height="2" width="6" y="1039.4" x="7"/>
                                        <path d="m8 13v9l2 2 2-2v-1l-2-1 2-1v-1l-1-1 1-1v-3z" transform="translate(0 1028.4)"/>
                                    </g>
                                    <path d="m11 1041.4v4l1-1v-3h-1zm0 4v2.5l1-0.5v-1l-1-1zm0 3.5v2.5l1-1v-1l-1-0.5z" fill="#f39c12"/>
                                    <path d="m9 1041.4v10l1 1v-4-7h-1z" fill="#f39c12"/>
                                </g>
                            </g>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-900">Reset password for <span class="font-semibold">{{ $userName }}</span>?</p>
                        <p class="text-sm text-gray-500">This will reset back to the default password.</p>
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
                        variant="warning" 
                        size="sm" 
                        wire:click="resetPassword"
                        wire:loading.attr="disabled"
                        wire:target="resetPassword"
                        :loading="$processing"
                    >
                        <span wire:loading.remove wire:target="resetPassword">Reset Password</span>
                        <span wire:loading wire:target="resetPassword">Resetting...</span>
                    </x-button>
                </div>
                </div>
            </div>
        </div>
    @endif
</div>
