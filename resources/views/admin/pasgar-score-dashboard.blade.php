<x-layout>
    <x-navbar title="PASGAR Score Dashboard" :includeSidebar="true" :user="Auth::user()">
        <div class="container mx-auto px-4 pb-8 pt-4">
            <livewire:shared.forms-dashboard.pasgar-score-dashboard />
        </div>
    </x-navbar>
</x-layout>
