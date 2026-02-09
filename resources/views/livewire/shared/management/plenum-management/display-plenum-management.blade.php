<div wire:poll.2s>
    <!-- Header with Title, Search, and Add Plenum -->
    <div class="flex flex-col gap-4 mb-6 md:flex-row md:items-center md:justify-between md:gap-6">
        <div class="text-center md:text-left">
            <h1 class="text-2xl font-semibold text-gray-900">Plenum Machines Management</h1>
            <p class="text-gray-600">Manage plenums.</p>
        </div>
        <div class="flex flex-col gap-3 md:flex-row md:gap-3 md:items-center">
            <div class="flex flex-row gap-3 items-center w-full md:w-auto">
                <div class="relative shrink-0 flex-1 md:flex-initial">
                    <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <input
                        wire:model.live="search"
                        placeholder="Search plenums..."
                        class="w-full pl-11 pr-12 py-3 text-sm bg-white border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all placeholder:text-gray-400 shadow-sm"
                    />
                    <button type="button" wire:click="toggleFilterDropdown" class="absolute right-2 top-1/2 -translate-y-1/2 p-2 text-gray-400 hover:text-gray-600 transition-colors">
                        <svg width="16" height="16" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg" fill="#9CA3AF" class="w-5 h-5">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M15 2v1.67l-5 4.759V14H6V8.429l-5-4.76V2h14zM7 8v5h2V8l5-4.76V3H2v.24L7 8z"/>
                        </svg>
                    </button>
                
                <!-- Filter Dropdown -->
                @if ($showFilterDropdown)
                    <div class="absolute top-full mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-50 left-0 right-0 md:left-auto md:right-0 md:w-80">
                        <div class="p-4">
                            <div class="grid grid-cols-2 gap-1">
                                <!-- Status Filter Column -->
                                <div>
                                    <h3 class="text-sm font-medium text-gray-900 mb-3">Status</h3>
                                    <div class="space-y-2">
                                        <label class="flex items-center">
                                            <input type="radio" wire:model="statusFilter" value="all" class="mr-2">
                                            <span class="text-sm text-gray-700">All Plenums</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="radio" wire:model="statusFilter" value="active" class="mr-2">
                                            <span class="text-sm text-gray-700">Active</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="radio" wire:model="statusFilter" value="inactive" class="mr-2">
                                            <span class="text-sm text-gray-700">Inactive</span>
                                        </label>
                                    </div>
                                </div>
                                
                                <!-- Date Filter Column -->
                                <div>
                                    <h3 class="text-sm font-medium text-gray-900 mb-3">Date Range</h3>
                                    <div class="space-y-2">
                                        <div>
                                            <label class="block text-xs text-gray-600 mb-1">From</label>
                                            <input type="date" wire:model="dateFrom" class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500">
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-600 mb-1">To</label>
                                            <input type="date" wire:model="dateTo" class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500">
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
                <button type="button" wire:click="$dispatch('openCreateModal')" class="inline-flex items-center justify-center px-4 py-3 text-sm font-medium text-white bg-orange-600 border border-orange-600 rounded-lg hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 transition-all duration-150 whitespace-nowrap shrink-0 md:px-4">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4 md:mr-2">
                        <path d="M5.25 6.375a4.125 4.125 0 1 1 8.25 0 4.125 4.125 0 0 1-8.25 0ZM2.25 19.125a7.125 7.125 0 0 1 14.25 0v.003l-.001.119a.75.75 0 0 1-.363.63 13.067 13.067 0 0 1-6.761 1.873c-2.472 0-4.786-.684-6.76-1.873a.75.75 0 0 1-.364-.63l-.001-.122ZM18.75 7.5a.75.75 0 0 0-1.5 0v2.25H15a.75.75 0 0 0 0 1.5h2.25v2.25a.75.75 0 0 0 1.5 0v-2.25H21a.75.75 0 0 0 0-1.5h-2.25V7.5Z" />
                    </svg>
                    <span class="hidden md:inline">Add Plenum</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Plenums Table -->
    <div class="relative flex flex-col w-full h-full text-gray-700 bg-white shadow-md rounded-lg bg-clip-border">
        <!-- Desktop Table View -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left table-auto min-w-max">
                <thead>
                    <tr>
                        <th class="p-3 md:p-4 border-b border-slate-300 bg-slate-50 cursor-pointer hover:bg-slate-100" wire:click="sortBy('plenumName')">
                            <p class="text-xs md:text-sm font-semibold leading-none text-slate-700 flex items-center gap-1">
                                Plenum Name
                                @if ($sortField === 'plenumName')
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
                        <th class="p-3 md:p-4 border-b border-slate-300 bg-slate-50 cursor-pointer hover:bg-slate-100" wire:click="sortBy('creationDate')">
                            <p class="text-xs md:text-sm font-semibold leading-none text-slate-700 flex items-center gap-1">
                                Creation Date
                                @if ($sortField === 'creationDate')
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
                    @forelse ($plenums as $plenum)
                        <tr class="even:bg-slate-50 hover:bg-slate-100">
                            <td class="p-3 md:p-4 py-4 md:py-5">
                                <p class="block text-xs md:text-sm text-slate-800">{{ $plenum->plenumName }}</p>
                            </td>
                            <td class="p-3 md:p-4 py-4 md:py-5">
                                <p class="block text-xs md:text-sm text-slate-800">{{ $plenum->creationDate->format('M d, Y') }}</p>
                            </td>
                            <td class="p-3 md:p-4 py-4 md:py-5 text-center">
                                @if(!$plenum->isActive)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Inactive
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Active
                                    </span>
                                @endif
                            </td>
                            <td class="p-3 md:p-4 py-4 md:py-5">
                                <div class="flex gap-1 md:gap-2 justify-center">
                                    <button 
                                        wire:click="$dispatch('openEditModal', '{{ $plenum->id }}')"
                                        class="px-3 py-1 text-xs font-medium text-blue-600 bg-blue-50 rounded-md hover:bg-blue-100 transition-colors"
                                        title="Edit Plenum">
                                        Edit
                                    </button>
                                    <button 
                                        wire:click="$dispatch('openDisableModal', '{{ $plenum->id }}')"
                                        class="px-3 py-1 text-xs font-medium {{ $plenum->isActive ? 'text-red-600 bg-red-50 hover:bg-red-100' : 'text-green-600 bg-green-50 hover:bg-green-100' }} rounded-md transition-colors"
                                        title="{{ $plenum->isActive ? 'Disable Plenum' : 'Enable Plenum' }}">
                                        {{ $plenum->isActive ? 'Disable' : 'Enable' }}
                                    </button>
                                    <button 
                                        wire:click="$dispatch('openDeleteModal', '{{ $plenum->id }}')"
                                        class="px-3 py-1 text-xs font-medium text-red-600 bg-red-50 rounded-md hover:bg-red-100 transition-colors"
                                        title="Delete Plenum">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-400 mb-4" fill="currentColor" viewBox="0 0 36 36" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M33.18,26.11,20.35,13.28A9.28,9.28,0,0,0,7.54,2.79l-1.34.59,5.38,5.38L8.76,11.59,3.38,6.21,2.79,7.54A9.27,9.27,0,0,0,13.28,20.35L26.11,33.18a2,2,0,0,0,2.83,0l4.24-4.24A2,2,0,0,0,33.18,26.11Zm-5.66,5.66L13.88,18.12l-.57.16a7.27,7.27,0,0,1-9.31-7,7.2,7.2,0,0,1,.15-1.48l4.61,4.61l5.66-5.66L9.81,4.15a7.27,7.27,0,0,1,8.47,9.16l-.16.57L31.77,27.53Z"></path>
                                        <circle cx="27.13" cy="27.09" r="1.3" transform="translate(-11.21 27.12) rotate(-45)"></circle>
                                    </svg>
                                    <h3 class="text-lg font-medium text-gray-900">No plenums found</h3>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Card View -->
        <div class="md:hidden space-y-4 p-4">
            @forelse ($plenums as $plenum)
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4 space-y-3">
                    <div class="flex justify-between items-start">
                        <div class="space-y-1">
                            <p class="text-sm font-semibold text-gray-900">{{ $plenum->plenumName }}</p>
                            <p class="text-xs text-gray-500">{{ $plenum->creationDate->format('M d, Y') }}</p>
                        </div>
                        <div class="text-center">
                            @if(!$plenum->isActive)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Inactive
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Active
                                </span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                        <button 
                            wire:click="$dispatch('openEditModal', '{{ $plenum->id }}')"
                            class="px-3 py-1 text-xs font-medium text-blue-600 bg-blue-50 rounded-md hover:bg-blue-100 transition-colors"
                            title="Edit Plenum">
                            Edit
                        </button>
                        <button 
                            wire:click="$dispatch('openDisableModal', '{{ $plenum->id }}')"
                            class="px-3 py-1 text-xs font-medium {{ $plenum->isActive ? 'text-red-600 bg-red-50 hover:bg-red-100' : 'text-green-600 bg-green-50 hover:bg-green-100' }} rounded-md transition-colors"
                            title="{{ $plenum->isActive ? 'Disable Plenum' : 'Enable Plenum' }}">
                            {{ $plenum->isActive ? 'Disable' : 'Enable' }}
                        </button>
                        <button 
                            wire:click="$dispatch('openDeleteModal', '{{ $plenum->id }}')"
                            class="px-3 py-1 text-xs font-medium text-red-600 bg-red-50 rounded-md hover:bg-red-100 transition-colors"
                            title="Delete Plenum">
                            Delete
                        </button>
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center py-12">
                    <svg class="w-12 h-12 text-gray-400 mb-4" fill="currentColor" viewBox="0 0 36 36" xmlns="http://www.w3.org/2000/svg">
                        <path d="M33.18,26.11,20.35,13.28A9.28,9.28,0,0,0,7.54,2.79l-1.34.59,5.38,5.38L8.76,11.59,3.38,6.21,2.79,7.54A9.27,9.27,0,0,0,13.28,20.35L26.11,33.18a2,2,0,0,0,2.83,0l4.24-4.24A2,2,0,0,0,33.18,26.11Zm-5.66,5.66L13.88,18.12l-.57.16a7.27,7.27,0,0,1-9.31-7,7.2,7.2,0,0,1,.15-1.48l4.61,4.61l5.66-5.66L9.81,4.15a7.27,7.27,0,0,1,8.47,9.16l-.16.57L31.77,27.53Z"></path>
                        <circle cx="27.13" cy="27.09" r="1.3" transform="translate(-11.21 27.12) rotate(-45)"></circle>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900">No plenums found</h3>
                    <p class="text-sm text-gray-500 mt-1">Try adjusting your search criteria</p>
                </div>
            @endforelse
        </div>
        
        @if ($plenums->hasPages())
            <!-- Pagination -->
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center px-3 md:px-4 py-3 border-t border-slate-200 gap-3 sm:gap-0">
                <div class="text-xs md:text-sm text-slate-500 text-center sm:text-left">
                    Showing <b>{{ $plenums->firstItem() }}-{{ $plenums->lastItem() }}</b> of {{ $plenums->total() }}
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

    <!-- Include Create Modal -->
    <livewire:shared.management.plenum-management.create on:plenumCreated="$refresh" />
    
    <!-- Include Edit Modal -->
    <livewire:shared.management.plenum-management.edit on:plenumUpdated="$refresh" />
    
    <!-- Include Delete Modal -->
    <livewire:shared.management.plenum-management.delete on:plenumDeleted="$refresh" />
    
    <!-- Include Disable/Enable Modal -->
    <livewire:shared.management.plenum-management.disable on:statusToggled="$refresh" />
</div>
