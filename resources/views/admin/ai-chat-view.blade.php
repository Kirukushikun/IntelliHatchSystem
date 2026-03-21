<x-layout>
    <x-navbar title="AI Chat" :includeSidebar="true" :user="Auth::user()">
        <div class="container mx-auto px-4 pb-8 pt-4">
            <livewire:admin.ai-chat.view :chatId="$chatId" />
        </div>
    </x-navbar>
</x-layout>
