<x-layout>
    <x-navbar title="Weekly Volt/Ampere Dashboard" :includeSidebar="true" :user="Auth::user()">
        <div class="container mx-auto px-4 pb-8 pt-4">
            <livewire:shared.forms-dashboard.weekly-volt-ampere-dashboard />
        </div>
    </x-navbar>
</x-layout>
