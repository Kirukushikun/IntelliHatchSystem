<x-layout>
    <div class="flex h-screen bg-gray-100">
        <x-sidebar :user="Auth::user()" />
        
        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Navbar with Sidebar Toggle -->
            <x-navbar>
                Users Management
            </x-navbar>
            
            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto bg-gray-50">
                <div class="p-6">
                    <h1 class="text-2xl font-bold text-gray-900">Users Management</h1>
                    <p class="text-gray-600">Welcome to the users management.</p>
                </div>
            </main>
        </div>
    </div>
</x-layout>