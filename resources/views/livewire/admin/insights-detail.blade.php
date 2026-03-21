<div x-on:open-print-window.window="window.open($event.detail.url, '_blank')">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.insights') }}" class="flex-shrink-0 inline-flex items-center justify-center w-8 h-8 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:border-gray-300 dark:hover:border-gray-600 transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-semibold text-gray-900 dark:text-white">{{ $formTypeName }}</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">AI-generated insights</p>
            </div>
        </div>

        <!-- Context Dropdown + Actions -->
        <div class="flex items-center gap-2">
            <!-- Context Selector -->
            <select
                wire:model.live="context"
                class="text-sm bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm"
            >
                <option value="week">Current Week</option>
                <option value="month">Current Month</option>
            </select>

            @if($status === 'ready')
                <!-- Translate / Toggle Language -->
                @if($translatedInsight !== '')
                    <button
                        wire:click="toggleLanguage"
                        class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium rounded-lg border border-purple-300 dark:border-purple-600 bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 hover:bg-purple-100 dark:hover:bg-purple-800/40 transition-colors shadow-sm"
                        title="{{ $showTranslation ? 'Show original English' : 'Show Filipino translation' }}"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
                        </svg>
                        <span class="hidden sm:inline">{{ $showTranslation ? 'English' : 'Filipino' }}</span>
                    </button>
                @else
                    <button
                        wire:click="translate"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-60 cursor-not-allowed"
                        class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium rounded-lg border border-purple-300 dark:border-purple-600 bg-white dark:bg-gray-800 text-purple-700 dark:text-purple-300 hover:bg-purple-50 dark:hover:bg-purple-900/30 transition-colors shadow-sm"
                        title="Translate to Filipino"
                    >
                        <span wire:loading.remove wire:target="translate">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
                            </svg>
                        </span>
                        <span wire:loading wire:target="translate">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                        <span wire:loading.remove wire:target="translate" class="hidden sm:inline">Isalin sa Filipino</span>
                        <span wire:loading wire:target="translate" class="hidden sm:inline">Nagsasalin…</span>
                    </button>
                @endif

                <!-- Print / Export PDF -->
                <button
                    wire:click="openPrint"
                    class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium rounded-lg border border-orange-300 dark:border-orange-600 bg-orange-50 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300 hover:bg-orange-100 dark:hover:bg-orange-800/40 transition-colors shadow-sm"
                    title="Print or export as PDF"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                    <span class="hidden sm:inline">Print / PDF</span>
                </button>

                <!-- Refresh / Re-generate -->
                <button
                    wire:click="clearCache"
                    class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors shadow-sm"
                    title="Clear cache and regenerate"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    <span class="hidden sm:inline">Regenerate</span>
                </button>
            @endif
        </div>
    </div>

    <!-- State: Idle (no cached result) -->
    @if($status === 'idle')
        <div class="flex flex-col items-center justify-center py-20 text-center">
            <div class="w-16 h-16 rounded-2xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-blue-500 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                </svg>
            </div>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No insights generated yet</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6 max-w-sm">
                Generate AI-powered insights for <strong>{{ $formTypeName }}</strong> based on
                {{ $context === 'week' ? 'this week\'s' : 'this month\'s' }} submissions.
            </p>
            <button
                wire:click="generate"
                wire:loading.attr="disabled"
                wire:loading.class="opacity-60 cursor-not-allowed"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm"
            >
                <span wire:loading.remove wire:target="generate">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </span>
                <span wire:loading wire:target="generate">
                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </span>
                <span wire:loading.remove wire:target="generate">Generate Insights</span>
                <span wire:loading wire:target="generate">Generating…</span>
            </button>

            <!-- In-progress message shown while loading -->
            <div wire:loading wire:target="generate" class="mt-4 flex items-center gap-2 text-sm text-blue-600 dark:text-blue-400">
                <svg class="w-4 h-4 animate-pulse" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
                The AI is processing your request. This may take a moment, please wait…
            </div>
        </div>
    @endif

    <!-- State: Error -->
    @if($status === 'error')
        <div class="flex flex-col items-center justify-center py-20 text-center">
            <div class="w-16 h-16 rounded-2xl bg-red-50 dark:bg-red-900/30 flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-red-500 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Something went wrong</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6 max-w-sm">{{ $errorMessage }}</p>
            <button
                wire:click="retry"
                wire:loading.attr="disabled"
                wire:loading.class="opacity-60 cursor-not-allowed"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm"
            >
                <span wire:loading.remove wire:target="retry">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                </span>
                <span wire:loading wire:target="retry">
                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </span>
                <span wire:loading.remove wire:target="retry">Retry</span>
                <span wire:loading wire:target="retry">Retrying…</span>
            </button>

            <!-- In-progress message shown during retry -->
            <div wire:loading wire:target="retry" class="mt-4 flex items-center gap-2 text-sm text-blue-600 dark:text-blue-400">
                <svg class="w-4 h-4 animate-pulse" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
                The AI is processing your request. This may take a moment, please wait…
            </div>
        </div>
    @endif

    <!-- State: Ready (show insight) -->
    @if($status === 'ready')
        <div>
            <!-- Meta bar -->
            <div class="flex flex-wrap items-center gap-3 mb-4">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-400">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block"></span>
                    Cached result
                </span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-400">
                    {{ $context === 'week' ? 'Current Week' : 'Current Month' }}
                </span>
                @if($generatedAt)
                    <span class="text-xs text-gray-400 dark:text-gray-500">
                        Generated: {{ $generatedAt }}
                    </span>
                @endif
            </div>

            <!-- Language indicator -->
            @if($showTranslation)
                <div class="flex items-center gap-2 mb-3 px-3 py-2 rounded-lg bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800">
                    <svg class="w-3.5 h-3.5 text-purple-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
                    </svg>
                    <span class="text-xs text-purple-700 dark:text-purple-300 font-medium">Isinasalin sa Filipino (Taglish)</span>
                    <button wire:click="toggleLanguage" class="ml-auto text-xs text-purple-500 hover:text-purple-700 dark:hover:text-purple-200 underline">Ipakita ang Ingles</button>
                </div>
            @endif

            <!-- Insight Content — rendered as styled section cards -->
            <div>
                {!! $this->renderedInsight() !!}
            </div>
        </div>
    @endif
</div>
