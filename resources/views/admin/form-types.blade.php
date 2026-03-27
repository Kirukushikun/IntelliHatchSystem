<x-layout>
    <x-navbar title="Form Types" :includeSidebar="true" :user="Auth::user()">
        <div class="container mx-auto px-4 pb-8 pt-4">
            <livewire:admin.form-types.display />
        </div>
    </x-navbar>
</x-layout>
