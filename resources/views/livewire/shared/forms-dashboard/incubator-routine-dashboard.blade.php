@php
    function getStatusPill($value) {
        // Debug: Log the actual value being passed
        \Log::info('getStatusPill called with value', [
            'value' => $value,
            'type' => gettype($value),
            'length' => strlen($value ?? ''),
            'trimmed' => trim($value),
        ]);
        
        $value = trim($value);
        $lowerValue = strtolower($value);
        
        switch($lowerValue) {
            case 'pending':
                return '<span style="display: inline-flex; align-items: center; padding: 0.25rem 0.625rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500; background-color: #fef3c7; color: #92400e;">Pending</span>';
            case 'done':
            case 'operational':
                return '<span style="display: inline-flex; align-items: center; padding: 0.25rem 0.625rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500; background-color: #dcfce7; color: #166534;">' . ucfirst($lowerValue) . '</span>';
            case 'n/a':
            case 'na':
                return '<span style="display: inline-flex; align-items: center; padding: 0.25rem 0.625rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500; background-color: #f3f4f6; color: #374151;">N/A</span>';
            case 'unoperational':
                return '<span style="display: inline-flex; align-items: center; padding: 0.25rem 0.625rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500; background-color: #fecaca; color: #991b1b;">Unoperational</span>';
            default:
                // For shift values and other text
                if (in_array($lowerValue, ['1st shift', '2nd shift', '3rd shift'])) {
                    return '<span style="display: inline-flex; align-items: center; padding: 0.25rem 0.625rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500; background-color: #dbeafe; color: #1e40af;">' . $value . '</span>';
                }
                // For corrective_action field, just show as text
                if ($value && strlen($value) > 10) {
                    return '<span style="display: inline-flex; align-items: center; padding: 0.25rem 0.625rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500; background-color: #f3e8ff; color: #6b21a8;">' . substr($value, 0, 15) . '...</span>';
                }
                // Debug: show the actual value if it doesn't match
                return '<span style="display: inline-flex; align-items: center; padding: 0.25rem 0.625rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500; background-color: #f3f4f6; color: #374151;" title="Original: \'' . $value . '\'">' . ($value ?: 'Empty') . '</span>';
        }
    }
@endphp

<div wire:poll.2s wire:key="{{ now()->timestamp }}">
    <!-- Header with Title and Search -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6 mb-6">
        <div class="text-center sm:text-left">
            <h1 class="text-2xl font-semibold text-gray-900">{{ $formType->form_name }}</h1>
            <p class="text-gray-600">All submitted forms of this type</p>
        </div>
        <div class="relative w-full sm:w-auto sm:shrink-0">
            <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <input
                wire:model.live="search"
                placeholder="Search forms..."
                class="w-full pl-11 pr-12 py-3 text-sm bg-white border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all placeholder:text-gray-400 shadow-sm"
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
                            <!-- Shift Filter Column -->
                            <div>
                                <h3 class="text-sm font-medium text-gray-900 mb-3">Shift</h3>
                                <div class="space-y-2">
                                    <label class="flex items-center">
                                        <input type="radio" wire:model="shiftFilter" value="all" class="mr-2">
                                        <span class="text-sm text-gray-700">All Shifts</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" wire:model="shiftFilter" value="1st Shift" class="mr-2">
                                        <span class="text-sm text-gray-700">1st Shift</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" wire:model="shiftFilter" value="2nd Shift" class="mr-2">
                                        <span class="text-sm text-gray-700">2nd Shift</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" wire:model="shiftFilter" value="3rd Shift" class="mr-2">
                                        <span class="text-sm text-gray-700">3rd Shift</span>
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
    </div>

    <!-- Quick Filters for Today's Shifts -->
    <div class="flex flex-wrap gap-3 mb-6">
        <div class="text-sm font-medium text-gray-700 self-center">Today:</div>
        @foreach (['1st Shift', '2nd Shift', '3rd Shift'] as $shift)
            <button 
                wire:click="quickFilterTodayShift('{{ $shift }}')"
                class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-colors
                    @if ($shiftFilter === $shift && $dateFrom === now()->format('Y-m-d') && $dateTo === now()->format('Y-m-d'))
                        bg-blue-600 text-white hover:bg-blue-700
                    @else
                        bg-gray-100 text-gray-700 hover:bg-gray-200
                    @endif
                "
            >
                {{ $shift }}
                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                    @if ($shiftFilter === $shift && $dateFrom === now()->format('Y-m-d') && $dateTo === now()->format('Y-m-d'))
                        bg-blue-100 text-blue-800
                    @else
                        bg-gray-200 text-gray-600
                    @endif
                ">
                    {{ $todayShiftCounts[$shift] ?? 0 }}
                </span>
            </button>
        @endforeach
    </div>

    <!-- Table Section -->
    <div class="relative flex flex-col w-full h-full text-gray-700 bg-white shadow-md rounded-lg bg-clip-border">
        <!-- Desktop Table View -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left table-auto min-w-max">
                <thead>
                    <tr>
                        <th class="p-3 md:p-4 border-b border-slate-300 bg-slate-50 cursor-pointer hover:bg-slate-100" wire:click="sortBy('date_submitted')">
                            <p class="text-xs md:text-sm font-semibold leading-none text-slate-700 flex items-center gap-1">
                                Date Submitted
                                @if ($sortField === 'date_submitted')
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
                        <th class="p-3 md:p-4 border-b border-slate-300 bg-slate-50">
                            <p class="text-xs md:text-sm font-semibold leading-none text-slate-700">
                                Hatchery Man
                            </p>
                        </th>
                        <th class="p-3 md:p-4 border-b border-slate-300 bg-slate-50">
                            <p class="text-xs md:text-sm font-semibold leading-none text-slate-700">
                                Incubator
                            </p>
                        </th>
                        <th class="p-3 md:p-4 border-b border-slate-300 bg-slate-50 text-center cursor-pointer hover:bg-slate-100" wire:click="sortBy('shift')">
                            <p class="text-xs md:text-sm font-semibold leading-none text-slate-700 flex items-center justify-center gap-1">
                                Shift
                                @if ($sortField === 'shift')
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
                        <th class="p-3 md:p-4 border-b border-slate-300 text-center">
                            <p class="text-xs md:text-sm font-semibold leading-none text-slate-700">
                                Actions
                            </p>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($forms as $form)
                        <tr class="even:bg-slate-50 hover:bg-slate-100">
                            <td class="p-3 md:p-4 py-4 md:py-5 text-left">
                                <p class="block text-xs md:text-sm text-slate-800">{{ $form->date_submitted ? $form->date_submitted->format('M d, Y H:i') : 'N/A' }}</p>
                            </td>
                            <td class="p-3 md:p-4 py-4 md:py-5 text-left">
                                <p class="block text-xs md:text-sm text-slate-800">{{ $form->user ? ($form->user->first_name . ' ' . $form->user->last_name) : 'Unknown' }}</p>
                            </td>
                            <td class="p-3 md:p-4 py-4 md:py-5 text-left">
                                <p class="block text-xs md:text-sm text-slate-800">{{ $form->incubator ? $form->incubator->incubatorName : 'N/A' }}</p>
                            </td>
                            @php
                                $formData = is_array($form->form_inputs) ? $form->form_inputs : [];
                            @endphp
                            <td class="p-3 md:p-4 py-4 md:py-5 text-center">
                                {!! getStatusPill($formData['shift'] ?? 'N/A') !!}
                            </td>
                            <td class="p-3 md:p-4 py-4 md:py-5 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button 
                                        wire:click="viewDetails({{ $form->id }})"
                                        class="px-3 py-1 text-xs font-medium text-blue-600 bg-blue-50 rounded-md hover:bg-blue-100 transition-colors"
                                        title="View Details">
                                        View
                                    </button>
                                    <button 
                                        wire:click="deleteForm({{ $form->id }})"
                                        class="px-3 py-1 text-xs font-medium text-red-600 bg-red-50 rounded-md hover:bg-red-100 transition-colors"
                                        title="Delete Form">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <h3 class="text-lg font-medium text-gray-900">No forms submitted</h3>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Card View -->
        <div class="md:hidden space-y-4 p-4">
            @forelse ($forms as $form)
                @php
                    $formData = is_array($form->form_inputs) ? $form->form_inputs : [];
                @endphp
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4 space-y-3">
                    <div class="flex justify-between items-start">
                        <div class="space-y-1">
                            <p class="text-xs text-gray-500">{{ $form->date_submitted ? $form->date_submitted->format('M d, Y H:i') : 'N/A' }}</p>
                        </div>
                        <div class="text-center">
                            {!! getStatusPill($formData['shift'] ?? 'N/A') !!}
                        </div>
                    </div>
                    
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-xs font-medium text-gray-500">Hatchery Man:</span>
                            <span class="text-xs text-gray-900">{{ $form->user ? ($form->user->first_name . ' ' . $form->user->last_name) : 'Unknown' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs font-medium text-gray-500">Incubator:</span>
                            <span class="text-xs text-gray-900">{{ $form->incubator ? $form->incubator->incubatorName : 'N/A' }}</span>
                        </div>
                    </div>
                    
                    <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                        <button 
                            wire:click="viewDetails({{ $form->id }})"
                            class="px-3 py-1 text-xs font-medium text-blue-600 bg-blue-50 rounded-md hover:bg-blue-100 transition-colors"
                            title="View Details">
                            View
                        </button>
                        <button 
                            wire:click="deleteForm({{ $form->id }})"
                            class="px-3 py-1 text-xs font-medium text-red-600 bg-red-50 rounded-md hover:bg-red-100 transition-colors"
                            title="Delete Form">
                            Delete
                        </button>
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center py-12">
                    <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900">No forms found</h3>
                </div>
            @endforelse
        </div>
        
        @if ($forms->hasPages())
            <!-- Pagination -->
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center px-3 md:px-4 py-3 border-t border-slate-200 gap-3 sm:gap-0">
                <div class="text-xs md:text-sm text-slate-500 text-center sm:text-left">
                    Showing <b>{{ $forms->firstItem() }}-{{ $forms->lastItem() }}</b> of {{ $forms->total() }}
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
    
    <!-- Form Details Modal -->
    @include('livewire.shared.forms-dashboard.modals.incubator-routine-view')
    
    <!-- Delete Confirmation Modal -->
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-9999 overflow-y-auto" style="display: block;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div class="fixed inset-0 transition-opacity" wire:click="cancelDelete">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>

                <!-- Modal panel -->
                <div class="relative z-10000 inline-block align-middle bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">
                                    Delete Form
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        Are you sure you want to delete this form? This action cannot be undone and all associated data will be permanently removed.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click="confirmDelete" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Delete
                        </button>
                        <button type="button" wire:click="cancelDelete" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>