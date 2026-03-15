<x-layout>
    <x-navbar title="House Number Management" :includeSidebar="true" :user="Auth::user()">
        <div class="container mx-auto px-4 pb-8 pt-4">
            <livewire:shared.management.house-number-management.display />
        </div>
    </x-navbar>
</x-layout>
