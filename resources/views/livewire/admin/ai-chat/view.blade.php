<div x-on:open-print-window.window="window.open($event.detail.url, '_blank')">
    {{-- Poll when pending/analyzing --}}
    @if($isPending)
        <span wire:poll.3s="refresh" class="hidden"></span>
    @endif

    {{-- Header --}}
    <div class="flex items-center gap-3 mb-6">
        <a
            href="{{ route('admin.ai-chat') }}"
            class="flex-shrink-0 inline-flex items-center justify-center w-8 h-8 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:border-gray-300 dark:hover:border-gray-600 transition-colors shadow-sm"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </a>
        <div>
            <h1 class="text-xl font-semibold text-gray-900 dark:text-white">AI Chat Response</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $chat->created_at->format('M d, Y · g:i A') }}</p>
        </div>
    </div>

    {{-- Question Card --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm mb-4 overflow-hidden">
        <div class="flex items-center gap-3 px-5 py-3 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
            <svg class="w-4 h-4 text-gray-500 dark:text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Your Question</span>
        </div>
        <div class="px-5 py-4">
            <p class="text-sm text-gray-900 dark:text-white leading-relaxed">{{ $chat->prompt }}</p>
        </div>
        {{-- Metadata --}}
        <div class="px-5 py-3 bg-gray-50 dark:bg-gray-700/30 border-t border-gray-100 dark:border-gray-700 flex flex-wrap items-center gap-x-4 gap-y-1.5">
            {{-- Status --}}
            @if($chat->status === 'pending')
                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-amber-700 dark:text-amber-400">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                    Pending
                </span>
            @elseif($chat->status === 'analyzing')
                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-blue-700 dark:text-blue-400">
                    <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    Analyzing
                </span>
            @elseif($chat->status === 'done')
                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-green-700 dark:text-green-400">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                    Done
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-red-700 dark:text-red-400">
                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                    Failed
                </span>
            @endif

            {{-- Form Scope --}}
            <span class="text-xs text-gray-500 dark:text-gray-400">
                <span class="font-medium">Scope:</span>
                {{ $chat->formType ? $chat->formType->form_name : 'All Form Types' }}
            </span>

            {{-- Period --}}
            <span class="text-xs text-gray-500 dark:text-gray-400">
                <span class="font-medium">Period:</span>
                {{ $chat->contextPeriodLabel() }}
            </span>
        </div>
    </div>

    {{-- Response Area --}}
    @if($chat->status === 'pending')
        <div class="flex flex-col items-center justify-center py-20 text-center bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="w-14 h-14 rounded-2xl bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center mb-4 animate-pulse">
                <svg class="w-7 h-7 text-amber-500 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-1">Request queued</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Your request is waiting in the queue. This page will update automatically.</p>
        </div>

    @elseif($chat->status === 'analyzing')
        <div class="flex flex-col items-center justify-center py-20 text-center bg-white dark:bg-gray-800 rounded-xl border border-blue-200 dark:border-blue-800 shadow-sm">
            <div class="w-14 h-14 rounded-2xl bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center mb-4">
                <svg class="w-7 h-7 text-blue-500 dark:text-blue-400 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
            </div>
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-1">Analyzing your data</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">The AI is reviewing your hatchery data and preparing a response.</p>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-2">This usually takes 15–45 seconds.</p>
        </div>

    @elseif($chat->status === 'failed')
        <div class="flex flex-col items-center justify-center py-16 text-center bg-white dark:bg-gray-800 rounded-xl border border-red-200 dark:border-red-800 shadow-sm">
            <div class="w-14 h-14 rounded-2xl bg-red-50 dark:bg-red-900/20 flex items-center justify-center mb-4">
                <svg class="w-7 h-7 text-red-500 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-1">Request failed</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">{{ $chat->error_message ?? 'An unexpected error occurred.' }}</p>
            <a
                href="{{ route('admin.ai-chat') }}"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium bg-orange-500 hover:bg-orange-600 text-white rounded-lg shadow-sm transition-colors"
            >
                Go back and try again
            </a>
        </div>

    @elseif($chat->status === 'done')
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="flex items-center gap-3 px-5 py-3 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                <svg class="w-4 h-4 text-gray-500 dark:text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                </svg>
                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">AI Response</span>

                <!-- Print / Export PDF -->
                <button
                    wire:click="openPrint"
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                    title="Print or export as PDF"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                    Print / PDF
                </button>

                <!-- Translate button -->
                <div class="ml-auto">
                    @if($translatedResponse !== '')
                        <button
                            wire:click="toggleLanguage"
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-lg border border-purple-300 dark:border-purple-600 bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 hover:bg-purple-100 dark:hover:bg-purple-800/40 transition-colors"
                            title="{{ $showTranslation ? 'Show original English' : 'Show Filipino translation' }}"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
                            </svg>
                            {{ $showTranslation ? 'English' : 'Filipino' }}
                        </button>
                    @else
                        <button
                            wire:click="translate"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-60 cursor-not-allowed"
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-lg border border-purple-300 dark:border-purple-600 bg-white dark:bg-gray-800 text-purple-700 dark:text-purple-300 hover:bg-purple-50 dark:hover:bg-purple-900/30 transition-colors"
                            title="Translate to Filipino"
                        >
                            <span wire:loading.remove wire:target="translate">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
                                </svg>
                            </span>
                            <span wire:loading wire:target="translate">
                                <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                            </span>
                            <span wire:loading.remove wire:target="translate">Isalin sa Filipino</span>
                            <span wire:loading wire:target="translate">Nagsasalin…</span>
                        </button>
                    @endif
                </div>
            </div>

            @if($showTranslation)
                <div class="flex items-center gap-2 mx-5 mt-4 px-3 py-2 rounded-lg bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800">
                    <svg class="w-3.5 h-3.5 text-purple-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
                    </svg>
                    <span class="text-xs text-purple-700 dark:text-purple-300 font-medium">Isinasalin sa Filipino (Taglish)</span>
                    <button wire:click="toggleLanguage" class="ml-auto text-xs text-purple-500 hover:text-purple-700 dark:hover:text-purple-200 underline">Ipakita ang Ingles</button>
                </div>
            @endif

            <div class="px-5 py-5">
                {!! $this->renderedResponse() !!}
            </div>
        </div>
    @endif
</div>
