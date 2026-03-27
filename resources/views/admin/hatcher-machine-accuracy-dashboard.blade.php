<x-layout>
    <x-navbar title="Hatcher Machine Accuracy Dashboard" :includeSidebar="true" :user="Auth::user()">
        <div class="container mx-auto px-4 pb-8 pt-4">
            <livewire:shared.forms-dashboard.hatcher-machine-accuracy-dashboard />
        </div>
    </x-navbar>
</x-layout>
