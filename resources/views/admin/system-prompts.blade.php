<x-layout>
    <x-navbar title="System Prompts" :includeSidebar="true" :user="Auth::user()">
        <div class="container mx-auto px-4 pb-8 pt-4">
            <livewire:admin.system-prompts.display />
        </div>
    </x-navbar>
</x-layout>
