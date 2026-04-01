<x-layout>
    <x-navbar title="User Activity Dashboard" :includeSidebar="true" :user="Auth::user()">
        <div class="container mx-auto px-4 pb-8 pt-4">
            <div>
                <livewire:admin.user-activity-dashboard />
            </div>
        </div>
    </x-navbar>
</x-layout>
