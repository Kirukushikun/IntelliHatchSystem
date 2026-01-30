<x-layout>
    <x-navbar title="Users Management" :includeSidebar="true" :user="Auth::user()">
        <div class="p-4 md:p-6">
            <livewire:user-management.display />
        </div>
    </x-navbar>
</x-layout>