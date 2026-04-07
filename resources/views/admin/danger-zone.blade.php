<x-layout>
    <x-navbar title="Danger Zone" :includeSidebar="true" :user="Auth::user()">
        <div class="container mx-auto px-4 pb-8 pt-4">
            <div>
                <livewire:admin.danger-zone.display />
            </div>
        </div>
    </x-navbar>
</x-layout>
