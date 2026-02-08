<x-layout>
    <!-- Simple Public Navbar -->
    <nav class="shadow-lg border-b border-gray-200 bg-white sticky top-0 z-30">
        <div class="mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo/Brand -->
                <div class="flex items-center space-x-3">
                    <h1 class="text-xl font-bold text-gray-900">IntelliHatch System</h1>
                </div>
                
                <!-- Back to Forms -->
                <div class="flex items-center space-x-4">
                    <a href="{{ Auth::check() ? '/admin/forms' : '/' }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        Back to Forms
                    </a>
                </div>
            </div>
        </div>
    </nav>
    
    <div class="min-h-screen bg-gray-50">
        
        <div class="p-4">
            <div class="w-full max-w-lg bg-white rounded-xl shadow-lg px-8 pt-6 pb-2 mx-auto">
                <livewire:shared.forms.incubator-routine-form />
            </div>
        </div>
    </div>
</x-layout>