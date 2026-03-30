<x-layout>
    <x-navbar title="Import Diesel Generator Weekly" :includeSidebar="true" :user="Auth::user()">
        <div class="container mx-auto px-4 pb-8 pt-4">
            <div>
                <livewire:admin.import-forms.diesel-generator-weekly />
            </div>
        </div>
    </x-navbar>
</x-layout>
