<x-layout>
    <x-navbar title="Admin Dashboard" :includeSidebar="true" :user="Auth::user()">
        <div class="container mx-auto px-4 pb-8 pt-4">
            <livewire:admin.dashboard-stats :showCharts="true" />
        </div>
    </x-navbar>
</x-layout>