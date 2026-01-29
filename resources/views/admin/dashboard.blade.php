<x-layout>
    <x-navbar title="Admin Dashboard" :includeSidebar="true" :user="Auth::user()">
        <div class="p-6">
            <h1 class="text-2xl font-bold text-gray-900">Admin Dashboard</h1>
            <p class="text-gray-600">Welcome to the admin dashboard.</p>
        </div>
    </x-navbar>
</x-layout>