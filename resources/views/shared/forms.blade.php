<x-layout>
    @php
        $forms = [
            [
                'title' => 'Incubator Routine Checklist Per Shift',
                'description' => 'Lorem Ipsum',
                'route' => Auth::check() && Auth::user()->user_type === 0 ? route('admin.forms.incubator-routine') : route('user.forms.incubator-routine'),
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
        <div class="container mx-auto px-4 pb-8 pt-4" x-data="{ query: '' }">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
                <div class="mb-2 text-center sm:text-left">
                    <h1 class="text-2xl font-semibold text-gray-900">Forms</h1>
                    <p class="text-gray-600">Choose a form to get started.</p>
                </div>
                <div class="relative w-full sm:w-auto sm:shrink-0">
                    <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <input
                        type="text"
                        x-model="query"
                        placeholder="Search forms..."
                        class="w-full pl-11 pr-4 py-3 text-sm bg-white border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all placeholder:text-gray-400 shadow-sm"
                    />
                </div>
            </div>

            <!-- Forms Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
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
                <div class="text-center py-12">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No forms available</h3>
                    <p class="text-gray-500">Check back later for available forms.</p>
                </div>
            @endif
        </div>
    </x-navbar>
</x-layout>