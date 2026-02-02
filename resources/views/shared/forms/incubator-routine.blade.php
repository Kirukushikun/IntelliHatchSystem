<x-layout>
    <x-navbar :includeSidebar="true" :user="Auth::user()" :hideDate="true">
        <x-slot:navbarActions>
            <x-button 
                href="{{ ((int) Auth::user()->user_type) === 0 ? '/admin/forms' : '/user/forms' }}"
                variant="outline-secondary" 
                size="sm"
            >
                Back to Forms
            </x-button>
        </x-slot:navbarActions>
        
        <div class="p-4">
            <div class="w-full max-w-lg bg-white rounded-xl shadow-lg px-8 pt-6 pb-2 mx-auto">
                <livewire:forms.incubator-routine-form />
            </div>
        </div>
    </x-navbar>
</x-layout>