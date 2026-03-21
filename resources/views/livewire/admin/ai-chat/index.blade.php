<div>
    {{-- Smart polling: only active when requests are in-progress --}}
    @if($hasPending)
        <span wire:poll.5s="$refresh" class="hidden"></span>
    @endif

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">AI Chat</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Ask questions about your hatchery data and get AI-generated insights.</p>
        </div>
        <button
            wire:click="toggleForm"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg
                   {{ $showForm
                       ? 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600'
                       : 'bg-orange-500 hover:bg-orange-600 text-white shadow-sm' }}
                   transition-colors cursor-pointer"
        >
            @if($showForm)
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                Cancel
            @else
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                New Request
            @endif
        </button>
    </div>

    {{-- Create Form --}}
    @if($showForm)
        <div
            x-data
            x-show="true"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            class="bg-white dark:bg-gray-800 rounded-xl border border-orange-200 dark:border-orange-800 shadow-sm mb-6 overflow-hidden"
        >
            <div class="flex items-center gap-3 px-5 py-4 bg-orange-50 dark:bg-orange-900/20 border-b border-orange-200 dark:border-orange-800">
                <div class="w-7 h-7 rounded-lg bg-orange-500 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                </div>
                <h2 class="text-sm font-semibold text-orange-900 dark:text-orange-100">New AI Request</h2>
            </div>

            <div class="px-5 py-5 space-y-4">
                {{-- Prompt --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Your Question
                        <span class="text-gray-400 font-normal ml-1">(min 10 characters)</span>
                    </label>
                    <textarea
                        wire:model="prompt"
                        rows="4"
                        placeholder="e.g. What are the temperature trends this week? Are there any machines showing abnormal readings? Summarize hatcher performance for this month."
                        class="w-full text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-transparent resize-none transition-colors"
                    ></textarea>
                    @error('prompt')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Options Row --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Data Scope</label>
                        <select
                            wire:model="selectedFormTypeId"
                            class="w-full text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-transparent"
                        >
                            <option value="">All Form Types</option>
                            @foreach($formTypes as $ft)
                                <option value="{{ $ft['id'] }}">{{ $ft['form_name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Time Period</label>
                        <select
                            wire:model="contextPeriod"
                            class="w-full text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-transparent"
                        >
                            <option value="week">Current Week</option>
                            <option value="month">Current Month</option>
                            <option value="all">Last 90 Days</option>
                        </select>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="flex justify-end">
                    <button
                        wire:click="submit"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-60 cursor-not-allowed"
                        class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium bg-orange-500 hover:bg-orange-600 text-white rounded-lg shadow-sm transition-colors cursor-pointer"
                    >
                        <span wire:loading.remove wire:target="submit">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </span>
                        <span wire:loading wire:target="submit">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                        </span>
                        <span wire:loading.remove wire:target="submit">Submit Request</span>
                        <span wire:loading wire:target="submit">Submitting...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Chat List --}}
    @if($chats->isEmpty())
        <div class="flex flex-col items-center justify-center py-20 text-center">
            <div class="w-16 h-16 rounded-2xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                </svg>
            </div>
            <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-1">No requests yet</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Ask the AI a question about your hatchery data to get started.</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach($chats as $chat)
                <div class="group bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-all duration-200 overflow-hidden">
                    <div class="px-5 py-4 flex items-start gap-4">
                        {{-- Status Icon --}}
                        <div class="shrink-0 mt-0.5">
                            @if($chat->status === 'pending')
                                <div class="w-8 h-8 rounded-full bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center animate-pulse">
                                    <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            @elseif($chat->status === 'analyzing')
                                <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-blue-600 dark:text-blue-400 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                    </svg>
                                </div>
                            @elseif($chat->status === 'done')
                                <div class="w-8 h-8 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            @else
                                <div class="w-8 h-8 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            @endif
                        </div>

                        {{-- Main Content --}}
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-900 dark:text-white font-medium line-clamp-2 leading-snug">
                                {{ $chat->prompt }}
                            </p>
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-2">
                                {{-- Status Badge --}}
                                @if($chat->status === 'pending')
                                    <span class="inline-flex items-center gap-1 text-xs font-medium text-amber-700 dark:text-amber-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                        Pending
                                    </span>
                                @elseif($chat->status === 'analyzing')
                                    <span class="inline-flex items-center gap-1 text-xs font-medium text-blue-700 dark:text-blue-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                        Analyzing...
                                    </span>
                                @elseif($chat->status === 'done')
                                    <span class="inline-flex items-center gap-1 text-xs font-medium text-green-700 dark:text-green-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                        Done
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-xs font-medium text-red-700 dark:text-red-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                        Failed
                                    </span>
                                @endif

                                {{-- Meta --}}
                                <span class="text-xs text-gray-400 dark:text-gray-500">
                                    {{ $chat->created_at->diffForHumans() }}
                                </span>

                                @if($chat->formType)
                                    <span class="text-xs text-gray-400 dark:text-gray-500">
                                        · {{ $chat->formType->form_name }}
                                    </span>
                                @endif

                                <span class="text-xs text-gray-400 dark:text-gray-500">
                                    · {{ $chat->contextPeriodLabel() }}
                                </span>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="shrink-0 flex items-center gap-1.5 self-center">
                            @if($chat->status === 'done')
                                <a
                                    href="{{ route('admin.ai-chat.view', $chat->id) }}"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                                >
                                    View
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            @elseif($chat->status === 'failed')
                                <button
                                    wire:click="retry({{ $chat->id }})"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium rounded-lg bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 hover:bg-amber-200 dark:hover:bg-amber-900/50 transition-colors cursor-pointer"
                                    title="Retry"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                    Retry
                                </button>
                            @endif

                            @if(in_array($chat->status, ['done', 'failed']))
                                <button
                                    @click="if(confirm('Delete this request?')) $wire.delete({{ $chat->id }})"
                                    class="inline-flex items-center justify-center w-7 h-7 rounded-lg text-gray-400 dark:text-gray-500 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-500 dark:hover:text-red-400 transition-colors cursor-pointer"
                                    title="Delete"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if($chats->hasPages())
            <div class="mt-6">
                {{ $chats->links() }}
            </div>
        @endif
    @endif
</div>
