<x-layout>
    <x-navbar title="Import Incubator Routine" :includeSidebar="true" :user="Auth::user()">
        <div class="container mx-auto px-4 pb-8 pt-4">
            <div>
                <livewire:admin.import-forms.incubator-routine />
            </div>
        </div>
    </x-navbar>
</x-layout>
