<x-layout>
    <x-navbar title="Import Hatcher Temp Calibration" :includeSidebar="true" :user="Auth::user()">
        <div class="container mx-auto px-4 pb-8 pt-4">
            <div>
                <livewire:admin.import-forms.hatcher-temp-calibration />
            </div>
        </div>
    </x-navbar>
</x-layout>
