<div>
    <!-- Header with Title, Search, and Add User -->
    <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-1 gap-0 md:gap-2">
        <div class="py-2">
            <h1 class="text-2xl font-bold text-slate-900">Users Management</h1>
            <p class="text-sm text-slate-600 mt-1">Manage your users here</p>
        </div>
        <div class="flex gap-2 items-center">
            <div class="w-full md:w-auto md:min-w-50 relative">
                <div class="relative">
                    <input
                        wire:model.live="search"
                        class="bg-white w-full pr-11 h-10 pl-3 py-2 placeholder:text-slate-400 text-slate-700 text-sm border border-slate-200 rounded transition duration-200 ease focus:outline-none focus:border-slate-400 hover:border-slate-400 shadow-sm focus:shadow-md"
                        placeholder="Search for users..."
                    />
                    <button
                        class="absolute h-8 w-8 right-1 top-1 my-auto px-2 flex items-center bg-white rounded"
                        type="button"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-8 h-8 text-slate-600">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                    </button>
                </div>
            </div>
            <button type="button" wire:click="$dispatch('openCreateModal')" class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-orange-600 border border-orange-600 rounded-lg hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 transition-all duration-150 whitespace-nowrap">
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
                                <div class="flex gap-1 md:gap-2 justify-center">
                                    <button type="button" wire:click="editUser({{ $user->id }})" class="inline-flex items-center justify-center px-3 py-1.5 text-sm font-medium text-gray-700 bg-transparent border border-transparent rounded-lg hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-all duration-150">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4">
                                            <path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-8.4 8.4a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32l8.4-8.4Z" />
                                            <path d="M5.25 5.25a3 3 0 0 0-3 3v10.5a3 3 0 0 0 3 3h10.5a3 3 0 0 0 3-3V13.5a.75.75 0 0 0-1.5 0v5.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5V8.25a1.5 1.5 0 0 1 1.5-1.5h5.25a.75.75 0 0 0 0-1.5H5.25Z" />
                                        </svg>
                                    </button>
                                    <button type="button" wire:click="deleteUser({{ $user->id }})" wire:confirm="Are you sure you want to delete this user?" class="inline-flex items-center justify-center px-3 py-1.5 text-sm font-medium text-gray-700 bg-transparent border border-transparent rounded-lg hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-all duration-150">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4 text-red-600">
                                            <path fill-rule="evenodd" d="M16.5 4.478v.227a48.816 48.816 0 0 1 3.878.512.75.75 0 1 1-.256 1.478l-.209-.035-1.005 13.07a3 3 0 0 1-2.991 2.77H8.084a3 3 0 0 1-2.991-2.77L4.087 6.66l-.209.035a.75.75 0 0 1-.256-1.478A48.567 48.567 0 0 1 7.5 4.705v-.227c0-1.564 1.213-2.9 2.816-2.951a52.662 52.662 0 0 1 3.369 0c1.603.051 2.815 1.387 2.815 2.951Zm-6.136-1.452a51.196 51.196 0 0 1 3.273 0C14.39 3.05 15 3.684 15 4.478v.113a49.488 49.488 0 0 0-6 0v-.113c0-.794.609-1.428 1.364-1.452Zm-.355 5.945a.75.75 0 1 0-1.5.058l.347 9a.75.75 0 1 0 1.499-.058l-.346-9Zm5.48.058a.75.75 0 1 0-1.498-.058l-.347 9a.75.75 0 0 0 1.5.058l.345-9Z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-4 text-center text-slate-500">
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
