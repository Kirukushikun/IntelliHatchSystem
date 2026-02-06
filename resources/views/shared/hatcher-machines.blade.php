<x-layout>
    <x-navbar title="Hatcher Machines Management" :includeSidebar="true" :user="Auth::user()">
        <div class="container mx-auto px-4 pb-8 pt-4">
            <livewire:hatcher-management.display />
        </div>
    </x-navbar>
</x-layout>
