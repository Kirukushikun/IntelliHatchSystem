<x-layout>
    <x-navbar title="Forms Dashboard" :includeSidebar="true" :user="Auth::user()">
        <div class="container mx-auto px-4 pb-8 pt-4">
            <livewire:admin.forms-by-type :typeId="$typeId" />
        </div>
    </x-navbar>
</x-layout>
