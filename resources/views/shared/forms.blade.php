<x-layout>
    <x-navbar>
        Forms
    </x-navbar>
    
    <div class="container mx-auto px-4 py-8">
        <!-- Forms Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @php
                $userType = ((int) Auth::user()->user_type) === 0 ? 'admin' : 'user';
                
                $forms = [
                    [
                        'title' => 'Incubator Routine Checklist Per Shift',
                        'description' => 'Lorem Ipsum',
                        'route' => route($userType . '.forms.incubator-routine'),
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
            
            @foreach($forms as $form)
                <a href="{{ $form['route'] }}" class="block bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300 overflow-hidden group border-l-4 {{ $borderColors[$form['color']] ?? 'border-gray-500' }}">
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
</x-layout>
