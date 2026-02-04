<x-layout>
    <x-navbar title="Incubator Routine Dashboard" :includeSidebar="true" :user="Auth::user()">
        <div class="container mx-auto px-4 pb-8 pt-4">
            <livewire:forms-dashboard.incubator-routine-dashboard />
        </div>
    </x-navbar>
</x-layout>
