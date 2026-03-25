<x-layout>
    <x-navbar title="GetSet Management" :includeSidebar="true" :user="Auth::user()">
        <div class="container mx-auto px-4 pb-8 pt-4">
            <livewire:shared.management.get-set-management.display />
        </div>
    </x-navbar>
</x-layout>
