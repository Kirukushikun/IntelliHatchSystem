<x-layout>
    <x-navbar title="Incubator Machine Management" :includeSidebar="true" :user="Auth::user()">
        <div class="container mx-auto px-4 pb-8 pt-4">
            <livewire:incubator-management.display />
        </div>
    </x-navbar>
</x-layout>
