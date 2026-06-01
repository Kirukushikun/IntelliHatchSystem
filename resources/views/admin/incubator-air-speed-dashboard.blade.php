<x-layout>
    <x-navbar title="Incubator Air Speed Dashboard" :includeSidebar="true" :user="Auth::user()">
        <div class="container mx-auto px-4 pb-8 pt-4">
            <livewire:shared.forms-dashboard.incubator-air-speed-dashboard />
        </div>
    </x-navbar>
</x-layout>
