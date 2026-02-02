<x-layout>
    <x-navbar title="Admin Dashboard" :includeSidebar="true" :user="Auth::user()">
        <div class="p-6">
            <livewire:admin.dashboard-stats onlyType="Incubator Routine Checklist Per Shift" :showCharts="false" />
        </div>
    </x-navbar>
</x-layout>