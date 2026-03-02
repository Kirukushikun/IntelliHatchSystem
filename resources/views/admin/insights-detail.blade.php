<x-layout>
    <x-navbar title="Insights" :includeSidebar="true" :user="Auth::user()">
        <div class="container mx-auto px-4 pb-8 pt-4">
            <livewire:admin.insights-detail :formTypeId="$formTypeId" />
        </div>
    </x-navbar>
</x-layout>
