<div>
    {{-- Warning Banner --}}
    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-950/30 p-5">
        <div class="flex items-start gap-4">
            <div class="shrink-0 w-10 h-10 bg-red-100 dark:bg-red-900/50 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-red-800 dark:text-red-300">Destructive Actions Ahead</h3>
                <p class="mt-1 text-sm text-red-700 dark:text-red-400">
                    Actions on this page permanently delete data from the system.
                    This cannot be undone. Please proceed with extreme caution.
                </p>
            </div>
        </div>
    </div>

    {{-- Wipe Form Submissions Card --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Wipe Form Submissions</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Delete form submissions and their associated photos from the system.
            </p>
        </div>

        <div class="px-6 py-5 space-y-6">
            {{-- Wipe Mode Selection --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Select Wipe Mode</label>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    {{-- All --}}
                    <label class="relative flex items-start gap-3 p-4 rounded-lg border-2 cursor-pointer transition-all duration-200
                        {{ $wipeMode === 'all' ? 'border-red-500 bg-red-50 dark:bg-red-950/20 dark:border-red-600' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500' }}">
                        <input type="radio" wire:model.live="wipeMode" value="all" class="mt-0.5 text-red-600 focus:ring-red-500">
                        <div>
                            <span class="block text-sm font-medium text-gray-900 dark:text-white">Wipe All</span>
                            <span class="block text-xs text-gray-500 dark:text-gray-400 mt-0.5">Delete all form submissions</span>
                        </div>
                    </label>

                    {{-- Date Range --}}
                    <label class="relative flex items-start gap-3 p-4 rounded-lg border-2 cursor-pointer transition-all duration-200
                        {{ $wipeMode === 'date_range' ? 'border-red-500 bg-red-50 dark:bg-red-950/20 dark:border-red-600' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500' }}">
                        <input type="radio" wire:model.live="wipeMode" value="date_range" class="mt-0.5 text-red-600 focus:ring-red-500">
                        <div>
                            <span class="block text-sm font-medium text-gray-900 dark:text-white">Date Range</span>
                            <span class="block text-xs text-gray-500 dark:text-gray-400 mt-0.5">Delete within a date range</span>
                        </div>
                    </label>

                    {{-- Year --}}
                    <label class="relative flex items-start gap-3 p-4 rounded-lg border-2 cursor-pointer transition-all duration-200
                        {{ $wipeMode === 'year' ? 'border-red-500 bg-red-50 dark:bg-red-950/20 dark:border-red-600' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500' }}">
                        <input type="radio" wire:model.live="wipeMode" value="year" class="mt-0.5 text-red-600 focus:ring-red-500">
                        <div>
                            <span class="block text-sm font-medium text-gray-900 dark:text-white">By Year</span>
                            <span class="block text-xs text-gray-500 dark:text-gray-400 mt-0.5">Delete all submissions for a year</span>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Date Range Inputs --}}
            @if($wipeMode === 'date_range')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="dateFrom" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">From Date</label>
                        <input type="date" id="dateFrom" wire:model.live="dateFrom"
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-red-500 focus:ring-red-500 text-sm">
                    </div>
                    <div>
                        <label for="dateTo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">To Date</label>
                        <input type="date" id="dateTo" wire:model.live="dateTo"
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-red-500 focus:ring-red-500 text-sm">
                    </div>
                </div>
            @endif

            {{-- Year Selection --}}
            @if($wipeMode === 'year')
                <div class="max-w-xs">
                    <label for="selectedYear" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Select Year</label>
                    @if(count($availableYears) > 0)
                        <select id="selectedYear" wire:model.live="selectedYear"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-red-500 focus:ring-red-500 text-sm">
                            <option value="">-- Select Year --</option>
                            @foreach($availableYears as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400">No submissions found.</p>
                    @endif
                </div>
            @endif

            {{-- Preview Count --}}
            <div class="flex items-center gap-4">
                <button wire:click="previewCount"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors cursor-pointer"
                        @if($wipeMode === 'year' && !$selectedYear) disabled @endif
                        @if($wipeMode === 'date_range' && !$dateFrom && !$dateTo) disabled @endif>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    Preview Count
                </button>

                @if($affectedCount > 0)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-semibold text-red-700 dark:text-red-300 bg-red-100 dark:bg-red-900/40 rounded-full">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                        {{ number_format($affectedCount) }} submissions will be deleted
                    </span>
                @endif
            </div>

            {{-- Wipe Button --}}
            <div class="pt-2 border-t border-gray-200 dark:border-gray-700">
                <button wire:click="openConfirmModal"
                        class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700 focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-colors cursor-pointer"
                        @if($wipeMode === 'year' && !$selectedYear) disabled @endif
                        @if($wipeMode === 'date_range' && !$dateFrom && !$dateTo) disabled @endif>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    Wipe Submissions
                </button>
            </div>
        </div>
    </div>

    {{-- Purge Activity Logs Card --}}
    <div class="mt-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Purge Activity Logs</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Delete activity log entries from the system.
            </p>
        </div>

        <div class="px-6 py-5 space-y-6">
            {{-- Log Wipe Mode Selection --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Select Purge Mode</label>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    {{-- All --}}
                    <label class="relative flex items-start gap-3 p-4 rounded-lg border-2 cursor-pointer transition-all duration-200
                        {{ $logWipeMode === 'all' ? 'border-red-500 bg-red-50 dark:bg-red-950/20 dark:border-red-600' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500' }}">
                        <input type="radio" wire:model.live="logWipeMode" value="all" class="mt-0.5 text-red-600 focus:ring-red-500">
                        <div>
                            <span class="block text-sm font-medium text-gray-900 dark:text-white">Purge All</span>
                            <span class="block text-xs text-gray-500 dark:text-gray-400 mt-0.5">Delete all activity logs</span>
                        </div>
                    </label>

                    {{-- Date Range --}}
                    <label class="relative flex items-start gap-3 p-4 rounded-lg border-2 cursor-pointer transition-all duration-200
                        {{ $logWipeMode === 'date_range' ? 'border-red-500 bg-red-50 dark:bg-red-950/20 dark:border-red-600' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500' }}">
                        <input type="radio" wire:model.live="logWipeMode" value="date_range" class="mt-0.5 text-red-600 focus:ring-red-500">
                        <div>
                            <span class="block text-sm font-medium text-gray-900 dark:text-white">Date Range</span>
                            <span class="block text-xs text-gray-500 dark:text-gray-400 mt-0.5">Delete within a date range</span>
                        </div>
                    </label>

                    {{-- Year --}}
                    <label class="relative flex items-start gap-3 p-4 rounded-lg border-2 cursor-pointer transition-all duration-200
                        {{ $logWipeMode === 'year' ? 'border-red-500 bg-red-50 dark:bg-red-950/20 dark:border-red-600' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500' }}">
                        <input type="radio" wire:model.live="logWipeMode" value="year" class="mt-0.5 text-red-600 focus:ring-red-500">
                        <div>
                            <span class="block text-sm font-medium text-gray-900 dark:text-white">By Year</span>
                            <span class="block text-xs text-gray-500 dark:text-gray-400 mt-0.5">Delete all logs for a year</span>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Log Date Range Inputs --}}
            @if($logWipeMode === 'date_range')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="logDateFrom" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">From Date</label>
                        <input type="date" id="logDateFrom" wire:model.live="logDateFrom"
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-red-500 focus:ring-red-500 text-sm">
                    </div>
                    <div>
                        <label for="logDateTo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">To Date</label>
                        <input type="date" id="logDateTo" wire:model.live="logDateTo"
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-red-500 focus:ring-red-500 text-sm">
                    </div>
                </div>
            @endif

            {{-- Log Year Selection --}}
            @if($logWipeMode === 'year')
                <div class="max-w-xs">
                    <label for="logSelectedYear" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Select Year</label>
                    @if(count($logAvailableYears) > 0)
                        <select id="logSelectedYear" wire:model.live="logSelectedYear"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-red-500 focus:ring-red-500 text-sm">
                            <option value="">-- Select Year --</option>
                            @foreach($logAvailableYears as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400">No activity logs found.</p>
                    @endif
                </div>
            @endif

            {{-- Log Preview Count --}}
            <div class="flex items-center gap-4">
                <button wire:click="previewLogCount"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors cursor-pointer"
                        @if($logWipeMode === 'year' && !$logSelectedYear) disabled @endif
                        @if($logWipeMode === 'date_range' && !$logDateFrom && !$logDateTo) disabled @endif>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    Preview Count
                </button>

                @if($logAffectedCount > 0)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-semibold text-red-700 dark:text-red-300 bg-red-100 dark:bg-red-900/40 rounded-full">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                        {{ number_format($logAffectedCount) }} log entries will be deleted
                    </span>
                @endif
            </div>

            {{-- Log Purge Button --}}
            <div class="pt-2 border-t border-gray-200 dark:border-gray-700">
                <button wire:click="openLogConfirmModal"
                        class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700 focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-colors cursor-pointer"
                        @if($logWipeMode === 'year' && !$logSelectedYear) disabled @endif
                        @if($logWipeMode === 'date_range' && !$logDateFrom && !$logDateTo) disabled @endif>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    Purge Activity Logs
                </button>
            </div>
        </div>
    </div>

    {{-- Form Submissions Confirmation Modal --}}
    @if($showConfirmModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 dark:bg-gray-950/70"
             wire:click.self="closeModal">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md">
                <div class="px-6 py-6">
                    {{-- Red warning icon --}}
                    <div class="flex justify-center mb-4">
                        <div class="w-14 h-14 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center">
                            <svg class="w-7 h-7 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                    </div>

                    <h3 class="text-lg font-bold text-center text-gray-900 dark:text-white mb-2">Confirm Permanent Deletion</h3>

                    <p class="text-sm text-center text-gray-600 dark:text-gray-400 mb-5">
                        This will permanently delete <strong class="text-red-600 dark:text-red-400">{{ number_format($affectedCount) }}</strong> form submissions
                        and their associated photos. This action <strong>cannot be undone</strong>.
                    </p>

                    {{-- Type to confirm --}}
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Type <strong class="text-red-600 dark:text-red-400">{{ $affectedCount }}</strong> to confirm
                        </label>
                        <input type="text" wire:model.live="confirmationText"
                               placeholder="Enter the number to confirm"
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-red-500 focus:ring-red-500 text-sm"
                               autocomplete="off">
                    </div>

                    {{-- Action buttons --}}
                    <div class="flex gap-3 justify-center">
                        <button wire:click="closeModal"
                                class="px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors cursor-pointer">
                            Cancel
                        </button>
                        <button wire:click="confirmWipe"
                                wire:loading.attr="disabled"
                                wire:target="confirmWipe"
                                class="px-4 py-2.5 text-sm font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                                @if($confirmationText !== (string) $affectedCount) disabled @endif>
                            <span wire:loading.remove wire:target="confirmWipe">Queue Deletion of {{ number_format($affectedCount) }} Submissions</span>
                            <span wire:loading wire:target="confirmWipe" class="inline-flex items-center gap-2">
                                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Queuing...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Activity Logs Confirmation Modal --}}
    @if($showLogConfirmModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 dark:bg-gray-950/70"
             wire:click.self="closeLogModal">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md">
                <div class="px-6 py-6">
                    {{-- Red warning icon --}}
                    <div class="flex justify-center mb-4">
                        <div class="w-14 h-14 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center">
                            <svg class="w-7 h-7 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                    </div>

                    <h3 class="text-lg font-bold text-center text-gray-900 dark:text-white mb-2">Confirm Activity Log Purge</h3>

                    <p class="text-sm text-center text-gray-600 dark:text-gray-400 mb-5">
                        This will permanently delete <strong class="text-red-600 dark:text-red-400">{{ number_format($logAffectedCount) }}</strong> activity log entries.
                        This action <strong>cannot be undone</strong>.
                    </p>

                    {{-- Type to confirm --}}
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Type <strong class="text-red-600 dark:text-red-400">{{ $logAffectedCount }}</strong> to confirm
                        </label>
                        <input type="text" wire:model.live="logConfirmationText"
                               placeholder="Enter the number to confirm"
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-red-500 focus:ring-red-500 text-sm"
                               autocomplete="off">
                    </div>

                    {{-- Action buttons --}}
                    <div class="flex gap-3 justify-center">
                        <button wire:click="closeLogModal"
                                class="px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors cursor-pointer">
                            Cancel
                        </button>
                        <button wire:click="confirmLogWipe"
                                wire:loading.attr="disabled"
                                wire:target="confirmLogWipe"
                                class="px-4 py-2.5 text-sm font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                                @if($logConfirmationText !== (string) $logAffectedCount) disabled @endif>
                            <span wire:loading.remove wire:target="confirmLogWipe">Queue Purge of {{ number_format($logAffectedCount) }} Entries</span>
                            <span wire:loading wire:target="confirmLogWipe" class="inline-flex items-center gap-2">
                                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Queuing...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
