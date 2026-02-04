<div>
    <!-- Header with Title, Search, and Add User -->
    <div class="flex items-center justify-between gap-6 mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Users Management</h1>
            <p class="text-gray-600">Manage your users here</p>
        </div>
        <div class="flex gap-3 items-center">
            <div class="relative shrink-0">
                <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <input
                    wire:model.live="search"
                    placeholder="Search users..."
                    class="w-80 pl-11 pr-12 py-3 text-sm bg-white border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all placeholder:text-gray-400 shadow-sm"
                />
                <button type="button" wire:click="toggleFilterDropdown" class="absolute right-2 top-1/2 -translate-y-1/2 p-2 text-gray-400 hover:text-gray-600 transition-colors">
                    <svg width="16" height="16" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg" fill="#9CA3AF" class="w-5 h-5">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M15 2v1.67l-5 4.759V14H6V8.429l-5-4.76V2h14zM7 8v5h2V8l5-4.76V3H2v.24L7 8z"/>
                    </svg>
                </button>
                
                <!-- Filter Dropdown -->
                @if ($showFilterDropdown)
                    <div class="absolute top-full right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                        <div class="p-4">
                            <div class="grid grid-cols-2 gap-1">
                                <!-- Status Filter Column -->
                                <div>
                                    <h3 class="text-sm font-medium text-gray-900 mb-3">Status</h3>
                                    <div class="space-y-2">
                                        <label class="flex items-center">
                                            <input type="radio" wire:model="statusFilter" value="all" class="mr-2">
                                            <span class="text-sm text-gray-700">All Users</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="radio" wire:model="statusFilter" value="enabled" class="mr-2">
                                            <span class="text-sm text-gray-700">Enabled</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="radio" wire:model="statusFilter" value="disabled" class="mr-2">
                                            <span class="text-sm text-gray-700">Disabled</span>
                                        </label>
                                    </div>
                                </div>
                                
                                <!-- Date Filter Column -->
                                <div>
                                    <h3 class="text-sm font-medium text-gray-900 mb-3">Date Range</h3>
                                    <div class="space-y-2">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">From</label>
                                            <input 
                                                type="date" 
                                                wire:model="dateFrom"
                                                class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-transparent"
                                                placeholder="YYYY-MM-DD"
                                                max="{{ $dateTo ?: now()->format('Y-m-d') }}"
                                                wire:target="dateFrom"
                                                wire:loading.attr="disabled"
                                                x-on:change="$wire.set('dateTo', $el.value > $wire.get('dateTo') ? '' : $wire.get('dateTo'))"
                                            />
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">To</label>
                                            <input 
                                                type="date" 
                                                wire:model="dateTo"
                                                class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-transparent"
                                                placeholder="YYYY-MM-DD"
                                                max="{{ now()->format('Y-m-d') }}"
                                                min="{{ $dateFrom ?: '' }}"
                                                wire:target="dateTo"
                                                wire:loading.attr="disabled"
                                                x-on:change="$el.value < $wire.get('dateFrom') ? $wire.set('dateTo', '') : null"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex justify-between mt-4 pt-3 border-t border-gray-200">
                                <button type="button" wire:click="resetFilters" class="text-sm text-gray-600 hover:text-gray-800">Reset</button>
                                <button type="button" wire:click="toggleFilterDropdown" class="text-sm text-blue-600 hover:text-blue-800">Done</button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            <button type="button" wire:click="$dispatch('openCreateModal')" class="inline-flex items-center justify-center px-4 py-3 text-sm font-medium text-white bg-orange-600 border border-orange-600 rounded-lg hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 transition-all duration-150 whitespace-nowrap">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4 mr-2">
                    <path d="M5.25 6.375a4.125 4.125 0 1 1 8.25 0 4.125 4.125 0 0 1-8.25 0ZM2.25 19.125a7.125 7.125 0 0 1 14.25 0v.003l-.001.119a.75.75 0 0 1-.363.63 13.067 13.067 0 0 1-6.761 1.873c-2.472 0-4.786-.684-6.76-1.873a.75.75 0 0 1-.364-.63l-.001-.122ZM18.75 7.5a.75.75 0 0 0-1.5 0v2.25H15a.75.75 0 0 0 0 1.5h2.25v2.25a.75.75 0 0 0 1.5 0v-2.25H21a.75.75 0 0 0 0-1.5h-2.25V7.5Z" />
                </svg>
                Add User
            </button>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('message') }}
        </div>
    @endif

    <!-- Table Section -->
    <div wire:poll class="relative flex flex-col w-full h-full text-gray-700 bg-white shadow-md rounded-lg bg-clip-border">
        <div class="overflow-x-auto">
            <table class="w-full text-left table-auto min-w-max">
                <thead>
                    <tr>
                        <th class="p-3 md:p-4 border-b border-slate-300 bg-slate-50 cursor-pointer hover:bg-slate-100" wire:click="sortBy('first_name')">
                            <p class="text-xs md:text-sm font-semibold leading-none text-slate-700 flex items-center gap-1">
                                First Name
                                @if ($sortField === 'first_name')
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if ($sortDirection === 'asc')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        @endif
                                    </svg>
                                @endif
                            </p>
                        </th>
                        <th class="p-3 md:p-4 border-b border-slate-300 bg-slate-50 cursor-pointer hover:bg-slate-100" wire:click="sortBy('last_name')">
                            <p class="text-xs md:text-sm font-semibold leading-none text-slate-700 flex items-center gap-1">
                                Last Name
                                @if ($sortField === 'last_name')
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if ($sortDirection === 'asc')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        @endif
                                    </svg>
                                @endif
                            </p>
                        </th>
                        <th class="p-3 md:p-4 border-b border-slate-300 bg-slate-50 cursor-pointer hover:bg-slate-100" wire:click="sortBy('username')">
                            <p class="text-xs md:text-sm font-semibold leading-none text-slate-700 flex items-center gap-1">
                                Username
                                @if ($sortField === 'username')
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if ($sortDirection === 'asc')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        @endif
                                    </svg>
                                @endif
                            </p>
                        </th>
                        <th class="p-3 md:p-4 border-b border-slate-300 bg-slate-50 cursor-pointer hover:bg-slate-100" wire:click="sortBy('created_at')">
                            <p class="text-xs md:text-sm font-semibold leading-none text-slate-700 flex items-center gap-1">
                                Created
                                @if ($sortField === 'created_at')
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if ($sortDirection === 'asc')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        @endif
                                    </svg>
                                @endif
                            </p>
                        </th>
                        <th class="p-3 md:p-4 border-b border-slate-300 bg-slate-50 text-center">
                            <p class="text-xs md:text-sm font-semibold leading-none text-slate-700">
                                Status
                            </p>
                        </th>
                        <th class="p-3 md:p-4 border-b border-slate-300 bg-slate-50 text-center">
                            <p class="text-xs md:text-sm font-semibold leading-none text-slate-700">
                                Actions
                            </p>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr class="even:bg-slate-50 hover:bg-slate-100">
                            <td class="p-3 md:p-4 py-4 md:py-5">
                                <p class="block text-xs md:text-sm text-slate-800">{{ $user->first_name }}</p>
                            </td>
                            <td class="p-3 md:p-4 py-4 md:py-5">
                                <p class="block text-xs md:text-sm text-slate-800">{{ $user->last_name }}</p>
                            </td>
                            <td class="p-3 md:p-4 py-4 md:py-5">
                                <p class="block text-xs md:text-sm text-slate-800">{{ $user->username }}</p>
                            </td>
                            <td class="p-3 md:p-4 py-4 md:py-5">
                                <p class="block text-xs md:text-sm text-slate-800">{{ $user->created_at->format('M d, Y') }}</p>
                            </td>
                            <td class="p-3 md:p-4 py-4 md:py-5 text-center">
                                @if($user->is_disabled)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Disabled
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Active
                                    </span>
                                @endif
                            </td>
                            <td class="p-3 md:p-4 py-4 md:py-5">
                                <div class="flex gap-1 md:gap-2 justify-center">
                                    <x-button 
                                        variant="ghost" 
                                        size="sm" 
                                        wire:click="$dispatch('openEditModal', '{{ $user->id }}')"
                                        class="p-2"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                                            <path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-8.4 8.4a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32l8.4-8.4Z" />
                                            <path d="M5.25 5.25a3 3 0 0 0-3 3v10.5a3 3 0 0 0 3 3h10.5a3 3 0 0 0 3-3V13.5a.75.75 0 0 0-1.5 0v5.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5V8.25a1.5 1.5 0 0 1 1.5-1.5h5.25a.75.75 0 0 0 0-1.5H5.25Z" />
                                        </svg>
                                    </x-button>
                                    <x-button 
                                        variant="ghost" 
                                        size="sm" 
                                        wire:click="$dispatch('openResetPasswordModal', '{{ $user->id }}')"
                                        class="p-2"
                                    >
                                        <svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-yellow-600">
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
                                    </x-button>
                                    <x-button 
                                        variant="ghost" 
                                        size="sm" 
                                        wire:click="$dispatch('openDisableModal', '{{ $user->id }}')"
                                        class="p-2"
                                    >
                                        @if($user->is_disabled)
                                            <svg width="24" height="24" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-green-600">
                                                <path fill-rule="evenodd" clip-rule="evenodd" d="M11.4669 3.72684C11.7558 3.91574 11.8369 4.30308 11.648 4.59198L7.39799 11.092C7.29783 11.2452 7.13556 11.3467 6.95402 11.3699C6.77247 11.3931 6.58989 11.3355 6.45446 11.2124L3.70446 8.71241C3.44905 8.48022 3.43023 8.08494 3.66242 7.82953C3.89461 7.57412 4.28989 7.55529 4.5453 7.78749L6.75292 9.79441L10.6018 3.90792C10.7907 3.61902 11.178 3.53795 11.4669 3.72684Z" fill="#16A34A"/>
                                            </svg>
                                        @else
                                            <svg width="24" height="24" viewBox="0 0 48 48" version="1" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-red-600">
                                                <path fill="#D50000" d="M24,6C14.1,6,6,14.1,6,24s8.1,18,18,18s18-8.1,18-18S33.9,6,24,6z M24,10c3.1,0,6,1.1,8.4,2.8L12.8,32.4 C11.1,30,10,27.1,10,24C10,16.3,16.3,10,24,10z M24,38c-3.1,0-6-1.1-8.4-2.8l19.6-19.6C36.9,18,38,20.9,38,24C38,31.7,31.7,38,24,38 z"/>
                                            </svg>
                                        @endif
                                    </x-button>
                                    <x-button 
                                        variant="ghost" 
                                        size="sm" 
                                        wire:click="$dispatch('openDeleteModal', '{{ $user->id }}')"
                                        class="p-2"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 text-red-600">
                                            <path fill-rule="evenodd" d="M16.5 4.478v.227a48.816 48.816 0 0 1 3.878.512.75.75 0 1 1-.256 1.478l-.209-.035-1.005 13.07a3 3 0 0 1-2.991 2.77H8.084a3 3 0 0 1-2.991-2.77L4.087 6.66l-.209.035a.75.75 0 0 1-.256-1.478A48.567 48.567 0 0 1 7.5 4.705v-.227c0-1.564 1.213-2.9 2.816-2.951a52.662 52.662 0 0 1 3.369 0c1.603.051 2.815 1.387 2.815 2.951Zm-6.136-1.452a51.196 51.196 0 0 1 3.273 0C14.39 3.05 15 3.684 15 4.478v.113a49.488 49.488 0 0 0-6 0v-.113c0-.794.609-1.428 1.364-1.452Zm-.355 5.945a.75.75 0 1 0-1.5.058l.347 9a.75.75 0 1 0 1.499-.058l-.346-9Zm5.48.058a.75.75 0 1 0-1.498-.058l-.347 9a.75.75 0 0 0 1.5.058l.345-9Z" clip-rule="evenodd" />
                                        </svg>
                                    </x-button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-4 text-center text-slate-500">
                                No users found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <!-- Pagination -->
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center px-3 md:px-4 py-3 border-t border-slate-200 gap-3 sm:gap-0">
                <div class="text-xs md:text-sm text-slate-500 text-center sm:text-left">
                    Showing <b>{{ $users->firstItem() }}-{{ $users->lastItem() }}</b> of {{ $users->total() }}
                </div>
                <x-custom-pagination 
                    :current-page="$currentPage"
                    :last-page="$lastPage"
                    :pages="$pages"
                    on-page-change="gotoPage"
                />
            </div>
        @endif
    </div>

    <!-- Include Create User Modal -->
    <livewire:user-management.create on:userCreated="$refresh" />
    
    <!-- Include Edit User Modal -->
    <livewire:user-management.edit on:userUpdated="$refresh" />
    
    <!-- Include Delete User Modal -->
    <livewire:user-management.delete on:userDeleted="$refresh" />
    
    <!-- Include Reset Password Modal -->
    <livewire:user-management.reset-password on:passwordReset="$refresh" />
    
    <!-- Include Disable/Enable Modal -->
    <livewire:user-management.disable on:statusToggled="$refresh" />
    
    <!-- Toast Container -->
    <div x-data="{ toasts: [] }" x-init="window.addEventListener('showToast', (event) => {
        toasts.push({ message: event.detail.message, type: event.detail.type, id: Date.now() });
        setTimeout(() => {
            toasts.shift();
        }, 3000);
    })" class="fixed top-4 right-4 z-50 space-y-2">
        <template x-for="toast in toasts" :key="toast.id">
            <div 
                x-show="toast"
                x-transition:enter="transform ease-out duration-300 transition"
                x-transition:enter-start="translate-x-full opacity-0"
                x-transition:enter-end="translate-x-0 opacity-100"
                x-transition:leave="transform ease-in duration-200 transition"
                x-transition:leave-start="translate-x-0 opacity-100"
                x-transition:leave-end="translate-x-full opacity-0"
                :class="{
                    'bg-green-500 text-white': toast.type === 'success',
                    'bg-red-500 text-white': toast.type === 'error',
                    'bg-yellow-500 text-white': toast.type === 'warning'
                }"
                class="px-6 py-3 rounded-lg shadow-lg flex items-center space-x-2"
            >
                <div x-show="toast.type === 'success'">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <div x-show="toast.type === 'error'">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>
                <div x-show="toast.type === 'warning'">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <span x-text="toast.message"></span>
            </div>
        </template>
    </div>
</div>
