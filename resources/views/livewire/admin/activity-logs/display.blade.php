<div>
    <!-- Header with Title and Search -->
    <div class="flex flex-col gap-4 mb-6 md:flex-row md:items-center md:justify-between md:gap-6">
        <div class="text-center md:text-left">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Activity Logs</h1>
            <p class="text-gray-600 dark:text-gray-400">Audit trail of all authenticated user actions</p>
        </div>
        <div class="flex flex-col gap-3 md:flex-row md:gap-3 md:items-center">
            <div class="flex flex-row gap-3 items-center w-full md:w-auto">
                <div class="relative shrink-0 flex-1 md:flex-initial">
                    <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <input
                        wire:model.live="search"
                        placeholder="Search logs..."
                        class="w-full pl-11 pr-12 py-3 text-sm bg-white dark:bg-gray-800 border-2 border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent transition-all placeholder:text-gray-400 dark:placeholder:text-gray-500 shadow-sm dark:shadow-md"
                    />
                    <button type="button" wire:click="toggleFilterDropdown" class="absolute right-2 top-1/2 -translate-y-1/2 p-2 text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 transition-colors cursor-pointer">
                        <svg width="16" height="16" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg" fill="#9CA3AF" class="w-5 h-5">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M15 2v1.67l-5 4.759V14H6V8.429l-5-4.76V2h14zM7 8v5h2V8l5-4.76V3H2v.24L7 8z"/>
                        </svg>
                    </button>

                    <!-- Filter Dropdown -->
                    @if ($showFilterDropdown)
                        <div class="absolute top-full mt-2 w-80 bg-white dark:bg-gray-800 rounded-lg shadow-lg dark:shadow-xl border border-gray-200 dark:border-gray-700 z-50 left-0 right-0 md:left-auto md:right-0 md:w-80">
                            <div class="p-4">
                                <div class="grid grid-cols-2 gap-3">
                                    <!-- Action Filter -->
                                    <div>
                                        <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Action</h3>
                                        <select wire:model.live="actionFilter" class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 dark:focus:ring-blue-400 dark:bg-gray-700 dark:text-white">
                                            <option value="">All Actions</option>
                                            @foreach ($distinctActions as $action)
                                                <option value="{{ $action }}">{{ str_replace('_', ' ', ucfirst($action)) }}</option>
                                            @endforeach
                                        </select>

                                        <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-2 mt-3">User</h3>
                                        <select wire:model.live="userFilter" class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 dark:focus:ring-blue-400 dark:bg-gray-700 dark:text-white">
                                            <option value="">All Users</option>
                                            @foreach ($adminUsers as $u)
                                                <option value="{{ $u->id }}">{{ $u->full_name }} ({{ $u->username }})</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Date Range -->
                                    <div>
                                        <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Date Range</h3>
                                        <div class="space-y-2">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">From</label>
                                                <input
                                                    type="date"
                                                    wire:model="dateFrom"
                                                    class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                                    max="{{ $dateTo ?: now()->format('Y-m-d') }}"
                                                    wire:loading.attr="disabled"
                                                />
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">To</label>
                                                <input
                                                    type="date"
                                                    wire:model="dateTo"
                                                    class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                                    max="{{ now()->format('Y-m-d') }}"
                                                    min="{{ $dateFrom ?: '' }}"
                                                    wire:loading.attr="disabled"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex justify-between mt-4 pt-3 border-t border-gray-200 dark:border-gray-700">
                                    <button type="button" wire:click="resetFilters" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 cursor-pointer">Reset</button>
                                    <button type="button" wire:click="toggleFilterDropdown" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 cursor-pointer">Done</button>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Per-page selector -->
                <select wire:model.live="perPage" class="px-3 py-3 text-sm bg-white dark:bg-gray-800 border-2 border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent dark:text-white shrink-0">
                    <option value="15">15</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>

                <!-- Export dropdown -->
                <div x-data="{ open: false }" class="relative shrink-0">
                    <button
                        type="button"
                        @click="open = !open"
                        @keydown.escape.window="open = false"
                        class="flex items-center gap-2 px-4 py-3 text-sm font-medium text-white bg-orange-500 hover:bg-orange-600 rounded-lg transition-colors shadow-sm cursor-pointer"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Export
                        <svg class="w-3 h-3 transition-transform" :class="open && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div
                        x-show="open"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        @click.outside="open = false"
                        class="absolute right-0 mt-2 w-44 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 z-50"
                        style="display:none"
                    >
                        <a
                            href="{{ $exportUrls['csv'] }}"
                            class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-t-lg transition-colors"
                        >
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Export CSV
                        </a>
                        <a
                            href="{{ $exportUrls['pdf'] }}"
                            target="_blank"
                            class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-b-lg transition-colors"
                        >
                            <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            Print / PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div wire:poll.60s class="relative flex flex-col w-full h-full text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 shadow-md dark:shadow-lg rounded-lg bg-clip-border">
        <!-- Desktop Table View -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left table-auto min-w-max">
                <thead>
                    <tr>
                        <th class="p-3 md:p-4 border-b border-slate-300 dark:border-gray-600 bg-slate-50 dark:bg-gray-700 cursor-pointer hover:bg-slate-100 dark:hover:bg-gray-600" wire:click="sortBy('created_at')">
                            <p class="text-xs md:text-sm font-semibold leading-none text-slate-700 dark:text-slate-200 flex items-center gap-1">
                                Date / Time
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
                        <th class="p-3 md:p-4 border-b border-slate-300 dark:border-gray-600 bg-slate-50 dark:bg-gray-700">
                            <p class="text-xs md:text-sm font-semibold leading-none text-slate-700 dark:text-slate-200">User</p>
                        </th>
                        <th class="p-3 md:p-4 border-b border-slate-300 dark:border-gray-600 bg-slate-50 dark:bg-gray-700 cursor-pointer hover:bg-slate-100 dark:hover:bg-gray-600" wire:click="sortBy('action')">
                            <p class="text-xs md:text-sm font-semibold leading-none text-slate-700 dark:text-slate-200 flex items-center gap-1">
                                Action
                                @if ($sortField === 'action')
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
                            <p class="text-xs md:text-sm font-semibold leading-none text-slate-700 dark:text-slate-200">Description</p>
                        </th>
                        <th class="p-3 md:p-4 border-b border-slate-300 dark:border-gray-600 bg-slate-50 dark:bg-gray-700">
                            <p class="text-xs md:text-sm font-semibold leading-none text-slate-700 dark:text-slate-200">IP Address</p>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr class="even:bg-slate-50 dark:even:bg-gray-700/50 hover:bg-slate-100 dark:hover:bg-gray-700">
                            <td class="p-3 md:p-4 py-4 md:py-5 whitespace-nowrap">
                                <p class="block text-xs md:text-sm text-slate-800 dark:text-slate-200">{{ $log->created_at->format('d M, Y') }}</p>
                                <p class="block text-xs text-gray-400 dark:text-gray-500">{{ $log->created_at->format('H:i:s') }}</p>
                            </td>
                            <td class="p-3 md:p-4 py-4 md:py-5">
                                @if ($log->user)
                                    <p class="block text-xs md:text-sm font-medium text-slate-800 dark:text-slate-200">{{ $log->user->full_name }}</p>
                                    <p class="block text-xs text-gray-400 dark:text-gray-500">{{ $log->user->username }}</p>
                                    @php
                                        $roleLabel = match((int) $log->user->user_type) {
                                            0 => ['label' => 'Superadmin', 'class' => 'bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-300'],
                                            1 => ['label' => 'Admin', 'class' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300'],
                                            default => ['label' => 'User', 'class' => 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'],
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium mt-1 {{ $roleLabel['class'] }}">
                                        {{ $roleLabel['label'] }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400 dark:text-gray-500 italic">Deleted user</span>
                                @endif
                            </td>
                            <td class="p-3 md:p-4 py-4 md:py-5">
                                @php
                                    $actionColors = [
                                        'login'  => 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300',
                                        'logout' => 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300',
                                    ];
                                    $isDestructive = str_contains($log->action, 'deleted') || str_contains($log->action, 'disabled');
                                    $isCreate      = str_contains($log->action, 'created');
                                    $isUpdate      = str_contains($log->action, 'updated') || str_contains($log->action, 'changed') || str_contains($log->action, 'reset') || str_contains($log->action, 'enabled');
                                    $colorClass = $actionColors[$log->action]
                                        ?? ($isDestructive ? 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300'
                                        : ($isCreate ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-800 dark:text-emerald-300'
                                        : ($isUpdate ? 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300'
                                        : 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300')));
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $colorClass }}">
                                    {{ str_replace('_', ' ', ucfirst($log->action)) }}
                                </span>
                            </td>
                            <td class="p-3 md:p-4 py-4 md:py-5 max-w-xs">
                                <p class="block text-xs md:text-sm text-slate-800 dark:text-slate-200 break-words">{{ $log->description }}</p>
                            </td>
                            <td class="p-3 md:p-4 py-4 md:py-5 whitespace-nowrap">
                                <p class="block text-xs md:text-sm text-slate-800 dark:text-slate-200">{{ $log->ip_address ?? '—' }}</p>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">No activity logs found</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Try adjusting your search or filters</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Card View -->
        <div class="md:hidden space-y-4 p-4">
            @forelse ($logs as $log)
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm dark:shadow-md p-4 space-y-2">
                    <div class="flex justify-between items-start">
                        <div class="space-y-1">
                            @if ($log->user)
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $log->user->full_name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $log->user->username }}</p>
                            @else
                                <p class="text-sm italic text-gray-400 dark:text-gray-500">Deleted user</p>
                            @endif
                        </div>
                        <p class="text-xs text-gray-400 dark:text-gray-500 text-right">
                            {{ $log->created_at->format('d M, Y') }}<br>{{ $log->created_at->format('H:i:s') }}
                        </p>
                    </div>
                    <div>
                        @php
                            $isDestructive = str_contains($log->action, 'deleted') || str_contains($log->action, 'disabled');
                            $isCreate      = str_contains($log->action, 'created');
                            $isUpdate      = str_contains($log->action, 'updated') || str_contains($log->action, 'changed') || str_contains($log->action, 'reset') || str_contains($log->action, 'enabled');
                            $colorClass    = match(true) {
                                $log->action === 'login'  => 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300',
                                $log->action === 'logout' => 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300',
                                $isDestructive            => 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300',
                                $isCreate                 => 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-800 dark:text-emerald-300',
                                $isUpdate                 => 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300',
                                default                   => 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300',
                            };
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $colorClass }}">
                            {{ str_replace('_', ' ', ucfirst($log->action)) }}
                        </span>
                    </div>
                    <p class="text-xs text-gray-700 dark:text-gray-300 break-words">{{ $log->description }}</p>
                    @if ($log->ip_address)
                        <p class="text-xs text-gray-400 dark:text-gray-500">IP: {{ $log->ip_address }}</p>
                    @endif
                </div>
            @empty
                <div class="flex flex-col items-center py-12">
                    <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">No activity logs found</h3>
                </div>
            @endforelse
        </div>

        @if ($logs->hasPages())
            <!-- Pagination -->
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center px-3 md:px-4 py-3 border-t border-slate-200 dark:border-gray-700 gap-3 sm:gap-0">
                <div class="text-xs md:text-sm text-slate-500 dark:text-slate-400 text-center sm:text-left">
                    Showing <b>{{ $logs->firstItem() }}-{{ $logs->lastItem() }}</b> of {{ $logs->total() }}
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
</div>
