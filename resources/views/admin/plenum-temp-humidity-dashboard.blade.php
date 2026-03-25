<x-layout>
    <x-navbar title="Plenum Temperature & Humidity Dashboard" :includeSidebar="true" :user="Auth::user()">
        <div class="container mx-auto px-4 pb-8 pt-4">
            <livewire:shared.forms-dashboard.plenum-temp-humidity-dashboard />
        </div>
    </x-navbar>
</x-layout>
