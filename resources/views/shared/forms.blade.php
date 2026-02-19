<x-layout>
    @php
        $forms = [
            [
                'title' => 'Incubator Routine Checklist Per Shift',
                'description' => 'Lorem Ipsum',
                'route' => '/forms/incubator-routine',
                'color' => 'amber',
            ],
            [
                'title' => 'Hatcher Blower Air Speed Monitoring',
                'description' => 'Lorem Ipsum',
                'route' => '/forms/blower-air-hatcher',
                'color' => 'amber',
            ],
            [
                'title' => 'Incubator Blower Air Speed Monitoring',
                'description' => 'Lorem Ipsum',
                'route' => '/forms/blower-air-incubator',
                'color' => 'amber',
            ],
            [
                'title' => 'Hatchery Sullair Air Compressor Weekly PMS Checklist',
                'description' => 'Lorem Ipsum',
                'route' => '/forms/hatchery-sullair',
                'color' => 'amber',
            ]
        ];
        
        $borderColors = [
            'amber' => 'border-amber-500',
            'blue' => 'border-blue-500',
            'green' => 'border-green-500',
            'red' => 'border-red-500',
            'purple' => 'border-purple-500',
            'gray' => 'border-gray-500',
        ];
    @endphp
    
    <x-navbar title="Forms" :includeSidebar="Auth::check()" :user="Auth::user()">
        <div class="min-h-screen bg-linear-to-br from-orange-50 dark:from-gray-900 via-white dark:via-gray-800 to-orange-100 dark:to-gray-900" x-data="{ query: '' }">
            <!-- Hero Section -->
            <div class="container mx-auto px-4 py-6">
                <!-- Search Section -->
                <div class="max-w-2xl mx-auto mb-6">
                    <div class="relative">
                        <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <input
                            type="text"
                            x-model="query"
                            placeholder="Search forms..."
                            class="w-full pl-11 pr-4 py-4 text-lg bg-white dark:bg-gray-800 border-2 border-gray-300 dark:border-gray-600 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500 dark:focus:ring-orange-400 focus:border-transparent transition-all placeholder:text-gray-400 dark:placeholder:text-gray-500 shadow-lg dark:shadow-xl"
                        />
                    </div>
                </div>

                <!-- Forms Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 max-w-6xl mx-auto">
                    @foreach($forms as $form)
                        <a href="{{ $form['route'] }}"
                           x-show="!query || '{{ strtolower($form['title']) }}'.includes(query.toLowerCase())"
                           class="block bg-white dark:bg-gray-800 rounded-lg shadow-md hover:shadow-lg dark:shadow-xl dark:hover:shadow-2xl transition-all duration-300 overflow-hidden group border border-l-4 border-gray-200 dark:border-gray-700 border-l-amber-500 cursor-pointer transform hover:scale-[1.02] hover:-translate-y-1">
                            <!-- Card Header -->
                            <div class="px-6 py-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-3 mr-2">
                                        <x-title subtitle="{{ $form['description'] }}">
                                            {{ $form['title'] }}
                                        </x-title>
                                    </div>
                                    <svg class="w-5 h-5 text-gray-400 dark:text-gray-500 group-hover:text-gray-600 dark:group-hover:text-gray-300 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
                
                <!-- Empty State (when no forms are available) -->
                @if(empty($forms))
                    <div class="text-center py-12">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full mb-4">
                            <svg class="w-8 h-8 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No forms available</h3>
                        <p class="text-gray-500 dark:text-gray-400">Check back later for available forms.</p>
                    </div>
                @endif
            </div>
        </div>
    </x-navbar>
</x-layout>