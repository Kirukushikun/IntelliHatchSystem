<x-layout>
    @php
        $forms = [
            [
                'title' => 'Incubator Routine Checklist Per Shift',
                'description' => 'Lorem Ipsum',
                'route' => '/forms/incubator-routine',
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
    
    <!-- Simple Public Navbar -->
    <nav class="shadow-lg border-b border-gray-200 bg-white sticky top-0 z-30">
        <div class="mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo/Brand -->
                <div class="flex items-center space-x-3">
                    <h1 class="text-xl font-bold text-gray-900">IntelliHatch System</h1>
                </div>
                
                <!-- Login -->
                <div class="flex items-center space-x-4">
                    <a href="/login" class="inline-flex items-center px-4 py-2 text-sm font-medium text-orange-600 hover:text-orange-700 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Login
                    </a>
                </div>
            </div>
        </div>
    </nav>
    
    <div class="min-h-screen bg-linear-to-br from-orange-50 via-white to-orange-100" x-data="{ query: '' }">
            <!-- Hero Section -->
            <div class="container mx-auto px-4 py-6">
                <!-- Search Section -->
                <div class="max-w-2xl mx-auto mb-6">
                    <div class="relative">
                        <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <input
                            type="text"
                            x-model="query"
                            placeholder="Search forms..."
                            class="w-full pl-11 pr-4 py-4 text-lg bg-white border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all placeholder:text-gray-400 shadow-lg"
                        />
                    </div>
                </div>

                <!-- Forms Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 max-w-6xl mx-auto">
                    @foreach($forms as $form)
                        <a href="{{ $form['route'] }}"
                           x-show="!query || '{{ strtolower($form['title']) }}'.includes(query.toLowerCase())"
                           class="block bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300 overflow-hidden group border-l-4 {{ $borderColors[$form['color']] ?? 'border-gray-500' }}">
                            <!-- Card Header -->
                            <div class="px-6 py-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-3 mr-2">
                                        <x-title subtitle="{{ $form['description'] }}">
                                            {{ $form['title'] }}
                                        </x-title>
                                    </div>
                                    <svg class="w-5 h-5 text-gray-400 group-hover:text-gray-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

                <!-- Empty State (when no forms are available) -->
                @if(empty($forms))
                    <div class="text-center py-6">
                        <div class="inline-flex items-center justify-center w-20 h-20 bg-gray-100 rounded-full mb-6">
                            <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">No forms available</h3>
                        <p class="text-gray-600 text-lg">Check back later for available forms.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layout>
