<x-layout>
    <x-navbar title="Dashboard" :includeSidebar="true" :user="Auth::user()">
        <div class="container mx-auto px-4 pb-8 pt-4">
            <div class="mb-6">
                <h1 class="text-2xl font-semibold text-gray-900">User Dashboard</h1>
                <p class="text-gray-600">Welcome back, {{ Auth::user()->first_name }}!</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Quick Actions Card -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h2>
                    <div class="space-y-3">
                        <a href="{{ route('user.forms') }}" class="block w-full text-center px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors">
                            View Forms
                        </a>
                    </div>
                </div>
                
                <!-- User Info Card -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Account Information</h2>
                    <div class="space-y-2">
                        <p><strong>Name:</strong> {{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</p>
                        <p><strong>Username:</strong> {{ Auth::user()->username }}</p>
                        <p><strong>Email:</strong> {{ Auth::user()->email }}</p>
                    </div>
                </div>
            </div>
        </div>
    </x-navbar>
</x-layout>
