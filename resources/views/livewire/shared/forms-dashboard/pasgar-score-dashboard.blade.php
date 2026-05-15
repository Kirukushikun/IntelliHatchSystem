@php
    function getPasgarScoreBadge($score) {
        $score = (float) $score;
        if ($score >= 95) {
            return '<span style="display: inline-flex; align-items: center; padding: 0.25rem 0.625rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500; background-color: #dcfce7; color: #166534;">' . number_format($score, 2) . '</span>';
        } elseif ($score >= 90) {
            return '<span style="display: inline-flex; align-items: center; padding: 0.25rem 0.625rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500; background-color: #fef3c7; color: #92400e;">' . number_format($score, 2) . '</span>';
        }
        return '<span style="display: inline-flex; align-items: center; padding: 0.25rem 0.625rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500; background-color: #fecaca; color: #991b1b;">' . number_format($score, 2) . '</span>';
    }
@endphp

<div wire:poll.60s.visible wire:key="{{ now()->timestamp }}">
    <!-- Header with Title and Search -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6 mb-6">
        <div class="text-center sm:text-left">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $formType->form_name }}</h1>
            <p class="text-gray-600 dark:text-gray-400">All submitted PASGAR score forms</p>
        </div>
        <div class="relative w-full sm:w-auto sm:shrink-0">
            <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <input
                wire:model.live="search"
                placeholder="Search by personnel, PS number..."
                class="w-full pl-11 pr-20 py-3 text-sm bg-white dark:bg-gray-800 border-2 border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent transition-all placeholder:text-gray-400 dark:placeholder:text-gray-500 shadow-sm"
            />
            <!-- Print Button -->
            <button
                type="button"
                wire:click="printPasgarList"
                class="absolute right-2 top-1/2 -translate-y-1/2 p-2 text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 transition-colors cursor-pointer"
                title="Print List"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z" />
                </svg>
            </button>
            <!-- Filter Button -->
            <button type="button" wire:click="toggleFilterDropdown" class="absolute right-11 top-1/2 -translate-y-1/2 p-2 text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 transition-colors cursor-pointer">
                <svg width="16" height="16" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg" fill="#9CA3AF" class="w-5 h-5">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M15 2v1.67l-5 4.759V14H6V8.429l-5-4.76V2h14zM7 8v5h2V8l5-4.76V3H2v.24L7 8z"/>
                </svg>
            </button>

            <!-- Filter Dropdown -->
            @if ($showFilterDropdown)
                <div class="absolute top-full right-0 mt-2 w-72 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 z-50">
                    <div class="p-4">
                        <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Date Range</h3>
                        <div class="space-y-2">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">From</label>
                                <input
                                    type="date"
                                    wire:model="dateFrom"
                                    class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-200 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-transparent"
                                    max="{{ $dateTo ?: now()->format('Y-m-d') }}"
                                />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">To</label>
                                <input
                                    type="date"
                                    wire:model="dateTo"
                                    class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-200 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-transparent"
                                    max="{{ now()->format('Y-m-d') }}"
                                    min="{{ $dateFrom ?: '' }}"
                                />
                            </div>
                        </div>

                        <div class="flex justify-between mt-4 pt-3 border-t border-gray-200 dark:border-gray-700">
                            <button type="button" wire:click="resetFilters" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 transition-colors">Reset</button>
                            <button type="button" wire:click="toggleFilterDropdown" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 transition-colors">Done</button>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Table Section -->
    <div class="relative flex flex-col w-full h-full text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 shadow-md dark:shadow-lg rounded-lg bg-clip-border">
        <!-- Desktop Table View -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left table-auto min-w-max">
                <thead>
                    <tr>
                        <th class="p-3 md:p-4 border-b border-slate-300 dark:border-gray-600 bg-slate-50 dark:bg-gray-700 cursor-pointer hover:bg-slate-100 dark:hover:bg-gray-700" wire:click="sortBy('date_submitted')">
                            <p class="text-xs md:text-sm font-semibold leading-none text-slate-700 dark:text-slate-200 flex items-center gap-1">
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
                        <th class="p-3 md:p-4 border-b border-slate-300 dark:border-gray-600 bg-slate-50 dark:bg-gray-700">
                            <p class="text-xs md:text-sm font-semibold leading-none text-slate-700 dark:text-slate-200">Personnel</p>
                        </th>
                        <th class="p-3 md:p-4 border-b border-slate-300 dark:border-gray-600 bg-slate-50 dark:bg-gray-700">
                            <p class="text-xs md:text-sm font-semibold leading-none text-slate-700 dark:text-slate-200">PS Number</p>
                        </th>
                        <th class="p-3 md:p-4 border-b border-slate-300 dark:border-gray-600 bg-slate-50 dark:bg-gray-700">
                            <p class="text-xs md:text-sm font-semibold leading-none text-slate-700 dark:text-slate-200">House #</p>
                        </th>
                        <th class="p-3 md:p-4 border-b border-slate-300 dark:border-gray-600 bg-slate-50 dark:bg-gray-700">
                            <p class="text-xs md:text-sm font-semibold leading-none text-slate-700 dark:text-slate-200">Incubator #</p>
                        </th>
                        <th class="p-3 md:p-4 border-b border-slate-300 dark:border-gray-600 bg-slate-50 dark:bg-gray-700">
                            <p class="text-xs md:text-sm font-semibold leading-none text-slate-700 dark:text-slate-200">Hatcher</p>
                        </th>
                        <th class="p-3 md:p-4 border-b border-slate-300 dark:border-gray-600 bg-slate-50 dark:bg-gray-700 text-center">
                            <p class="text-xs md:text-sm font-semibold leading-none text-slate-700 dark:text-slate-200">PASGAR Avg</p>
                        </th>
                        <th class="p-3 md:p-4 border-b border-slate-300 dark:border-gray-600 bg-slate-50 dark:bg-gray-700 text-center">
                            <p class="text-xs md:text-sm font-semibold leading-none text-slate-700 dark:text-slate-200">Samples</p>
                        </th>
                        <th class="p-3 md:p-4 border-b border-slate-300 dark:border-gray-600 bg-slate-50 dark:bg-gray-700 text-center">
                            <p class="text-xs md:text-sm font-semibold leading-none text-slate-700 dark:text-slate-200">Actions</p>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($forms as $form)
                        @php
                            $formData = is_array($form->form_inputs) ? $form->form_inputs : [];
                            $hatcherName = 'N/A';
                            if (isset($formData['hatcher_number'])) {
                                $hatcher = \Illuminate\Support\Facades\DB::table('hatcher-machines')->where('id', $formData['hatcher_number'])->first();
                                $hatcherName = $hatcher->hatcherName ?? 'N/A';
                            }
                            $houseName = 'N/A';
                            if (isset($formData['house_number'])) {
                                $house = \Illuminate\Support\Facades\DB::table('house-numbers')->where('id', $formData['house_number'])->first();
                                $houseName = $house->houseNumber ?? 'N/A';
                            }
                            $incubatorName = 'N/A';
                            if (isset($formData['incubator_number'])) {
                                $inc = \Illuminate\Support\Facades\DB::table('incubator-machines')->where('id', $formData['incubator_number'])->first();
                                $incubatorName = $inc->incubatorName ?? 'N/A';
                            }
                            $personnelRaw = $formData['personnel_name'] ?? 'N/A';
                            if (is_array($personnelRaw)) {
                                $personnelNames = \Illuminate\Support\Facades\DB::table('users')->whereIn('id', $personnelRaw)->get()->map(fn($u) => trim($u->first_name . ' ' . $u->last_name))->toArray();
                                $personnelDisplay = $personnelNames ? implode(', ', $personnelNames) : 'N/A';
                            } elseif (is_numeric($personnelRaw)) {
                                $personnelUser = \Illuminate\Support\Facades\DB::table('users')->where('id', $personnelRaw)->first();
                                $personnelDisplay = $personnelUser ? trim($personnelUser->first_name . ' ' . $personnelUser->last_name) : 'N/A';
                            } else {
                                $personnelDisplay = $personnelRaw;
                            }
                        @endphp
                        <tr class="even:bg-slate-50 dark:even:bg-gray-700/50 hover:bg-slate-100 dark:hover:bg-gray-700">
                            <td class="p-3 md:p-4 py-4 md:py-5 text-left">
                                <p class="block text-xs md:text-sm text-slate-800 dark:text-slate-200">{{ $form->date_submitted ? $form->date_submitted->format('d M, Y g:i A') : 'N/A' }}</p>
                            </td>
                            <td class="p-3 md:p-4 py-4 md:py-5 text-left">
                                <p class="block text-xs md:text-sm text-slate-800 dark:text-slate-200">{{ $personnelDisplay }}</p>
                            </td>
                            <td class="p-3 md:p-4 py-4 md:py-5 text-left">
                                <p class="block text-xs md:text-sm text-slate-800 dark:text-slate-200">{{ $formData['machine_info']['name'] ?? 'N/A' }}</p>
                            </td>
                            <td class="p-3 md:p-4 py-4 md:py-5 text-left">
                                <p class="block text-xs md:text-sm text-slate-800 dark:text-slate-200">{{ $houseName }}</p>
                            </td>
                            <td class="p-3 md:p-4 py-4 md:py-5 text-left">
                                <p class="block text-xs md:text-sm text-slate-800 dark:text-slate-200">{{ $incubatorName }}</p>
                            </td>
                            <td class="p-3 md:p-4 py-4 md:py-5 text-left">
                                <p class="block text-xs md:text-sm text-slate-800 dark:text-slate-200">{{ $hatcherName }}</p>
                            </td>
                            <td class="p-3 md:p-4 py-4 md:py-5 text-center">
                                {!! getPasgarScoreBadge($formData['pasgar_average_scoring'] ?? 0) !!}
                            </td>
                            <td class="p-3 md:p-4 py-4 md:py-5 text-center">
                                <span class="text-xs md:text-sm text-slate-800 dark:text-slate-200">{{ $formData['total_samples'] ?? count($formData['samples'] ?? []) }}</span>
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
                            <td colspan="9" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">No forms submitted</h3>
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
                    $mPersonnelRaw = $formData['personnel_name'] ?? 'N/A';
                    if (is_array($mPersonnelRaw)) {
                        $mPersonnelNames = \Illuminate\Support\Facades\DB::table('users')->whereIn('id', $mPersonnelRaw)->get()->map(fn($u) => trim($u->first_name . ' ' . $u->last_name))->toArray();
                        $mPersonnelDisplay = $mPersonnelNames ? implode(', ', $mPersonnelNames) : 'N/A';
                    } elseif (is_numeric($mPersonnelRaw)) {
                        $mPersonnelUser = \Illuminate\Support\Facades\DB::table('users')->where('id', $mPersonnelRaw)->first();
                        $mPersonnelDisplay = $mPersonnelUser ? trim($mPersonnelUser->first_name . ' ' . $mPersonnelUser->last_name) : 'N/A';
                    } else {
                        $mPersonnelDisplay = $mPersonnelRaw;
                    }
                @endphp
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm dark:shadow-lg p-4 space-y-3">
                    <div class="flex justify-between items-start">
                        <div class="space-y-1">
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $form->date_submitted ? $form->date_submitted->format('d M, Y g:i A') : 'N/A' }}</p>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $mPersonnelDisplay }}</p>
                        </div>
                        <div class="text-center">
                            {!! getPasgarScoreBadge($formData['pasgar_average_scoring'] ?? 0) !!}
                        </div>
                    </div>

                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">PS Number:</span>
                            <span class="text-xs text-gray-900 dark:text-gray-200">{{ $formData['machine_info']['name'] ?? 'N/A' }}</span>
                        </div>
                        @php
                            $mHouseName = 'N/A';
                            if (isset($formData['house_number'])) {
                                $mHouse = \Illuminate\Support\Facades\DB::table('house-numbers')->where('id', $formData['house_number'])->first();
                                $mHouseName = $mHouse->houseNumber ?? 'N/A';
                            }
                            $mIncubatorName = 'N/A';
                            if (isset($formData['incubator_number'])) {
                                $mInc = \Illuminate\Support\Facades\DB::table('incubator-machines')->where('id', $formData['incubator_number'])->first();
                                $mIncubatorName = $mInc->incubatorName ?? 'N/A';
                            }
                        @endphp
                        <div class="flex justify-between">
                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">House #:</span>
                            <span class="text-xs text-gray-900 dark:text-gray-200">{{ $mHouseName }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Incubator #:</span>
                            <span class="text-xs text-gray-900 dark:text-gray-200">{{ $mIncubatorName }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Samples:</span>
                            <span class="text-xs text-gray-900 dark:text-gray-200">{{ $formData['total_samples'] ?? count($formData['samples'] ?? []) }}</span>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t border-gray-100 dark:border-gray-700">
                        <button
                            wire:click="viewDetails({{ $form->id }})"
                            class="px-3 py-1 text-xs font-medium text-blue-600 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/50 rounded-md hover:bg-blue-100 dark:hover:bg-blue-900/70 transition-colors"
                            title="View Details">
                            View
                        </button>
                        <button
                            wire:click="deleteForm({{ $form->id }})"
                            class="px-3 py-1 text-xs font-medium text-red-600 dark:text-red-300 bg-red-50 dark:bg-red-900/40 rounded-md hover:bg-red-100 dark:hover:bg-red-900/60 transition-colors"
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
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">No forms found</h3>
                </div>
            @endforelse
        </div>

        @if (is_object($forms) && method_exists($forms, 'hasPages') && $forms->hasPages())
            <!-- Pagination -->
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center px-3 md:px-4 py-3 border-t border-slate-200 dark:border-gray-700 gap-3 sm:gap-0">
                <div class="text-xs md:text-sm text-slate-500 dark:text-slate-400 text-center sm:text-left">
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
    @include('livewire.shared.forms-dashboard.modals.pasgar-score-view')

    <!-- Delete Confirmation Modal -->
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-9999 flex items-center justify-center p-4" wire:ignore.self>
            <div class="fixed inset-0 bg-black/50" wire:click="cancelDelete"></div>
            <div class="relative w-full max-w-md p-6 bg-white dark:bg-gray-800 shadow-xl dark:shadow-2xl rounded-lg">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Delete Form</h3>
                    <button type="button" wire:click="cancelDelete" class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 cursor-pointer">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div class="flex items-center mb-4">
                    <div class="shrink-0 w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-600 dark:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">Are you sure you want to delete this form?</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">This action cannot be undone and all associated data will be permanently removed.</p>
                    </div>
                </div>
                <div class="flex justify-end gap-3">
                    <x-button variant="outline-secondary" type="button" wire:click="cancelDelete" class="cursor-pointer">Cancel</x-button>
                    <x-button variant="danger" type="button" wire:click="confirmDelete" class="cursor-pointer">Delete Form</x-button>
                </div>
            </div>
        </div>
    @endif

    <script>
        window.addEventListener('openNewTab', (event) => {
            const url = event?.detail?.url;
            if (url) {
                window.open(url, '_blank', 'noopener');
            }
        });
    </script>
</div>
