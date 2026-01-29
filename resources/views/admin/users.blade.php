<x-layout>
    <x-navbar title="Users Management" :includeSidebar="true" :user="Auth::user()">
        <div class="p-6">
            <h1 class="text-2xl font-bold text-gray-900">Users Management</h1>
            <p class="text-gray-600">Welcome to the users management.</p>
        </div>
    </x-navbar>
</x-layout>