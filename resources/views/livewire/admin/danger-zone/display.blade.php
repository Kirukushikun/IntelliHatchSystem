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
                        <label for="dateFrom" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">From Date</label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                                <svg class="h-4.5 w-4.5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <input type="date" id="dateFrom" wire:model.live="dateFrom"
                                   class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700/50 dark:text-white pl-10 pr-4 py-2.5 text-sm shadow-sm hover:border-gray-400 dark:hover:border-gray-500 focus:border-red-500 focus:ring-2 focus:ring-red-500/20 dark:focus:ring-red-500/30 transition-colors">
                        </div>
                    </div>
                    <div>
                        <label for="dateTo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">To Date</label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                                <svg class="h-4.5 w-4.5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <input type="date" id="dateTo" wire:model.live="dateTo"
                                   class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700/50 dark:text-white pl-10 pr-4 py-2.5 text-sm shadow-sm hover:border-gray-400 dark:hover:border-gray-500 focus:border-red-500 focus:ring-2 focus:ring-red-500/20 dark:focus:ring-red-500/30 transition-colors">
                        </div>
                    </div>
                </div>
            @endif

            {{-- Year Selection --}}
            @if($wipeMode === 'year')
                <div class="max-w-xs">
                    <label for="selectedYear" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Select Year</label>
                    @if(count($availableYears) > 0)
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                                <svg class="h-4.5 w-4.5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <select id="selectedYear" wire:model.live="selectedYear"
                                    class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700/50 dark:text-white pl-10 pr-10 py-2.5 text-sm shadow-sm hover:border-gray-400 dark:hover:border-gray-500 focus:border-red-500 focus:ring-2 focus:ring-red-500/20 dark:focus:ring-red-500/30 appearance-none transition-colors">
                                <option value="">-- Select Year --</option>
                                @foreach($availableYears as $year)
                                    <option value="{{ $year }}">{{ $year }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                <svg class="h-4 w-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400 italic">No submissions found.</p>
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

    {{-- Purge Attachment Photos Card --}}
    <div class="mt-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Purge Attachment Photos</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Delete photos from form submissions without removing the form records. Affected forms will be marked as "Photos Expired".
            </p>
        </div>

        <div class="px-6 py-5 space-y-6">
            {{-- Purge Mode Selection --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Select Purge Mode</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    {{-- By Quarter --}}
                    <label class="relative flex items-start gap-3 p-4 rounded-lg border-2 cursor-pointer transition-all duration-200
                        {{ $photoPurgeMode === 'quarter' ? 'border-orange-500 bg-orange-50 dark:bg-orange-950/20 dark:border-orange-600' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500' }}">
                        <input type="radio" wire:model.live="photoPurgeMode" value="quarter" class="mt-0.5 text-orange-600 focus:ring-orange-500">
                        <div>
                            <span class="block text-sm font-medium text-gray-900 dark:text-white">By Quarter</span>
                            <span class="block text-xs text-gray-500 dark:text-gray-400 mt-0.5">Select a year and quarter (Q1–Q4)</span>
                        </div>
                    </label>

                    {{-- Custom Range --}}
                    <label class="relative flex items-start gap-3 p-4 rounded-lg border-2 cursor-pointer transition-all duration-200
                        {{ $photoPurgeMode === 'custom' ? 'border-orange-500 bg-orange-50 dark:bg-orange-950/20 dark:border-orange-600' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500' }}">
                        <input type="radio" wire:model.live="photoPurgeMode" value="custom" class="mt-0.5 text-orange-600 focus:ring-orange-500">
                        <div>
                            <span class="block text-sm font-medium text-gray-900 dark:text-white">Custom Range</span>
                            <span class="block text-xs text-gray-500 dark:text-gray-400 mt-0.5">Specify a custom date range</span>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Quarter Selection --}}
            @if($photoPurgeMode === 'quarter')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="photoSelectedYear" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Select Year</label>
                        @if(count($photoAvailableYears) > 0)
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                                    <svg class="h-4.5 w-4.5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <select id="photoSelectedYear" wire:model.live="photoSelectedYear"
                                        class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700/50 dark:text-white pl-10 pr-10 py-2.5 text-sm shadow-sm hover:border-gray-400 dark:hover:border-gray-500 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 dark:focus:ring-orange-500/30 appearance-none transition-colors">
                                    <option value="">-- Select Year --</option>
                                    @foreach($photoAvailableYears as $year)
                                        <option value="{{ $year }}">{{ $year }}</option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                    <svg class="h-4 w-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </div>
                        @else
                            <p class="text-sm text-gray-500 dark:text-gray-400 italic">No submissions with photos found.</p>
                        @endif
                    </div>
                    <div>
                        <label for="photoSelectedQuarter" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Select Quarter</label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                                <svg class="h-4.5 w-4.5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <select id="photoSelectedQuarter" wire:model.live="photoSelectedQuarter"
                                    class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700/50 dark:text-white pl-10 pr-10 py-2.5 text-sm shadow-sm hover:border-gray-400 dark:hover:border-gray-500 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 dark:focus:ring-orange-500/30 appearance-none transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                    @if(!$photoSelectedYear) disabled @endif>
                                <option value="">-- Select Quarter --</option>
                                @foreach($availableQuarters as $q)
                                    <option value="{{ $q }}">{{ $q }} — {{ ['Q1' => 'Jan–Mar', 'Q2' => 'Apr–Jun', 'Q3' => 'Jul–Sep', 'Q4' => 'Oct–Dec'][$q] }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                <svg class="h-4 w-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Custom Date Range Inputs --}}
            @if($photoPurgeMode === 'custom')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="photoDateFrom" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">From Date</label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                                <svg class="h-4.5 w-4.5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <input type="date" id="photoDateFrom" wire:model.live="photoDateFrom"
                                   class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700/50 dark:text-white pl-10 pr-4 py-2.5 text-sm shadow-sm hover:border-gray-400 dark:hover:border-gray-500 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 dark:focus:ring-orange-500/30 transition-colors">
                        </div>
                    </div>
                    <div>
                        <label for="photoDateTo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">To Date</label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                                <svg class="h-4.5 w-4.5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <input type="date" id="photoDateTo" wire:model.live="photoDateTo"
                                   class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700/50 dark:text-white pl-10 pr-4 py-2.5 text-sm shadow-sm hover:border-gray-400 dark:hover:border-gray-500 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 dark:focus:ring-orange-500/30 transition-colors">
                        </div>
                    </div>
                </div>
            @endif

            {{-- Preview Count --}}
            <div class="flex items-center gap-4">
                <button wire:click="previewPhotoCount"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors cursor-pointer"
                        @if($photoPurgeMode === 'quarter' && (!$photoSelectedYear || !$photoSelectedQuarter)) disabled @endif
                        @if($photoPurgeMode === 'custom' && !$photoDateFrom && !$photoDateTo) disabled @endif>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    Preview Count
                </button>

                @if($photoAffectedCount > 0)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-semibold text-orange-700 dark:text-orange-300 bg-orange-100 dark:bg-orange-900/40 rounded-full">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                        {{ number_format($photoAffectedCount) }} forms will have photos purged
                    </span>
                @endif
            </div>

            {{-- Purge Button --}}
            <div class="pt-2 border-t border-gray-200 dark:border-gray-700">
                <button wire:click="openPhotoConfirmModal"
                        class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-orange-600 rounded-lg hover:bg-orange-700 focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-colors cursor-pointer"
                        @if($photoPurgeMode === 'quarter' && (!$photoSelectedYear || !$photoSelectedQuarter)) disabled @endif
                        @if($photoPurgeMode === 'custom' && !$photoDateFrom && !$photoDateTo) disabled @endif>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    Purge Photos
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
                        <label for="logDateFrom" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">From Date</label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                                <svg class="h-4.5 w-4.5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <input type="date" id="logDateFrom" wire:model.live="logDateFrom"
                                   class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700/50 dark:text-white pl-10 pr-4 py-2.5 text-sm shadow-sm hover:border-gray-400 dark:hover:border-gray-500 focus:border-red-500 focus:ring-2 focus:ring-red-500/20 dark:focus:ring-red-500/30 transition-colors">
                        </div>
                    </div>
                    <div>
                        <label for="logDateTo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">To Date</label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                                <svg class="h-4.5 w-4.5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <input type="date" id="logDateTo" wire:model.live="logDateTo"
                                   class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700/50 dark:text-white pl-10 pr-4 py-2.5 text-sm shadow-sm hover:border-gray-400 dark:hover:border-gray-500 focus:border-red-500 focus:ring-2 focus:ring-red-500/20 dark:focus:ring-red-500/30 transition-colors">
                        </div>
                    </div>
                </div>
            @endif

            {{-- Log Year Selection --}}
            @if($logWipeMode === 'year')
                <div class="max-w-xs">
                    <label for="logSelectedYear" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Select Year</label>
                    @if(count($logAvailableYears) > 0)
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                                <svg class="h-4.5 w-4.5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <select id="logSelectedYear" wire:model.live="logSelectedYear"
                                    class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700/50 dark:text-white pl-10 pr-10 py-2.5 text-sm shadow-sm hover:border-gray-400 dark:hover:border-gray-500 focus:border-red-500 focus:ring-2 focus:ring-red-500/20 dark:focus:ring-red-500/30 appearance-none transition-colors">
                                <option value="">-- Select Year --</option>
                                @foreach($logAvailableYears as $year)
                                    <option value="{{ $year }}">{{ $year }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                <svg class="h-4 w-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400 italic">No activity logs found.</p>
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
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                                <svg class="h-4.5 w-4.5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <input type="text" wire:model.live="confirmationText"
                                   placeholder="Enter the number to confirm"
                                   class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700/50 dark:text-white pl-10 pr-4 py-2.5 text-sm shadow-sm hover:border-gray-400 dark:hover:border-gray-500 focus:border-red-500 focus:ring-2 focus:ring-red-500/20 dark:focus:ring-red-500/30 transition-colors"
                                   autocomplete="off">
                        </div>
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

    {{-- Photo Purge Confirmation Modal --}}
    @if($showPhotoConfirmModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 dark:bg-gray-950/70"
             wire:click.self="closePhotoModal">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md">
                <div class="px-6 py-6">
                    {{-- Orange warning icon --}}
                    <div class="flex justify-center mb-4">
                        <div class="w-14 h-14 bg-orange-100 dark:bg-orange-900/30 rounded-full flex items-center justify-center">
                            <svg class="w-7 h-7 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    </div>

                    <h3 class="text-lg font-bold text-center text-gray-900 dark:text-white mb-2">Confirm Photo Purge</h3>

                    <p class="text-sm text-center text-gray-600 dark:text-gray-400 mb-5">
                        This will permanently delete all attachment photos from <strong class="text-orange-600 dark:text-orange-400">{{ number_format($photoAffectedCount) }}</strong> form submissions.
                        The form records will be kept but marked as <strong>"Photos Expired"</strong>. This action <strong>cannot be undone</strong>.
                    </p>

                    {{-- Type to confirm --}}
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Type <strong class="text-orange-600 dark:text-orange-400">{{ $photoAffectedCount }}</strong> to confirm
                        </label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                                <svg class="h-4.5 w-4.5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <input type="text" wire:model.live="photoConfirmationText"
                                   placeholder="Enter the number to confirm"
                                   class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700/50 dark:text-white pl-10 pr-4 py-2.5 text-sm shadow-sm hover:border-gray-400 dark:hover:border-gray-500 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 dark:focus:ring-orange-500/30 transition-colors"
                                   autocomplete="off">
                        </div>
                    </div>

                    {{-- Action buttons --}}
                    <div class="flex gap-3 justify-center">
                        <button wire:click="closePhotoModal"
                                class="px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors cursor-pointer">
                            Cancel
                        </button>
                        <button wire:click="confirmPhotoPurge"
                                wire:loading.attr="disabled"
                                wire:target="confirmPhotoPurge"
                                class="px-4 py-2.5 text-sm font-semibold text-white bg-orange-600 rounded-lg hover:bg-orange-700 transition-colors cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                                @if($photoConfirmationText !== (string) $photoAffectedCount) disabled @endif>
                            <span wire:loading.remove wire:target="confirmPhotoPurge">Queue Purge of Photos from {{ number_format($photoAffectedCount) }} Forms</span>
                            <span wire:loading wire:target="confirmPhotoPurge" class="inline-flex items-center gap-2">
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
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                                <svg class="h-4.5 w-4.5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <input type="text" wire:model.live="logConfirmationText"
                                   placeholder="Enter the number to confirm"
                                   class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700/50 dark:text-white pl-10 pr-4 py-2.5 text-sm shadow-sm hover:border-gray-400 dark:hover:border-gray-500 focus:border-red-500 focus:ring-2 focus:ring-red-500/20 dark:focus:ring-red-500/30 transition-colors"
                                   autocomplete="off">
                        </div>
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
