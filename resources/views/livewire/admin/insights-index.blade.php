<div>
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">AI Insights</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">Select a form to view AI-generated insights for the current week or month.</p>
    </div>

    @if(empty($formTypes))
        <div class="flex flex-col items-center justify-center py-16 text-center">
            <svg class="w-16 h-16 text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <p class="text-gray-500 dark:text-gray-400 text-sm">No form types found.</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach($formTypes as $type)
                <a
                    href="{{ route('admin.insights.show', $type['id']) }}"
                    class="group block bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md hover:border-blue-400 dark:hover:border-blue-500 transition-all duration-200 overflow-hidden"
                >
                    <!-- Card Header -->
                    <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center gap-3">
                        <div class="flex-shrink-0 w-9 h-9 rounded-lg bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center group-hover:bg-blue-100 dark:group-hover:bg-blue-900/50 transition-colors">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                            </svg>
                        </div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white leading-tight group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                            {{ $type['name'] }}
                        </h3>
                    </div>

                    <!-- Card Body -->
                    <div class="px-5 py-4">
                        <div class="grid grid-cols-2 gap-3">
                            <div class="text-center p-2 rounded-lg bg-gray-50 dark:bg-gray-700/50">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">This Week</p>
                                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $type['week_count'] }}</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500">submissions</p>
                            </div>
                            <div class="text-center p-2 rounded-lg bg-gray-50 dark:bg-gray-700/50">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">This Month</p>
                                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $type['month_count'] }}</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500">submissions</p>
                            </div>
                        </div>
                    </div>

                    <!-- Card Footer -->
                    <div class="px-5 py-3 bg-gray-50 dark:bg-gray-700/30 flex items-center justify-between">
                        <span class="text-xs text-gray-500 dark:text-gray-400">View AI Insights</span>
                        <svg class="w-4 h-4 text-gray-400 dark:text-gray-500 group-hover:text-blue-500 dark:group-hover:text-blue-400 group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
