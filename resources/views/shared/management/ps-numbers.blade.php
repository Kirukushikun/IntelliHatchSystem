<x-layout>
    <x-navbar title="PS Number Management" :includeSidebar="true" :user="Auth::user()">
        <div class="container mx-auto px-4 pb-8 pt-4">
            <livewire:shared.management.ps-number-management.display />
        </div>
    </x-navbar>
</x-layout>
