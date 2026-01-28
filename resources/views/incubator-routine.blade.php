<x-layout>
    <div class="min-h-screen flex items-center justify-center bg-gray-100 p-4">
        <!-- Form/Card Container -->
        <div class="w-full max-w-lg bg-white rounded-xl shadow-lg px-8 pt-6">
            
            <!-- User Info & Logout -->
            <div class="flex items-center justify-between mb-4 pb-4 border-b border-gray-200">
                <div class="text-sm text-gray-600">
                    Welcome, <span class="font-medium text-gray-900">{{ auth()->user()->full_name }}</span>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <x-button variant="outline-secondary" size="sm" type="submit">
                        Logout
                    </x-button>
                </form>
            </div>
            
            <!-- Title Component -->
            <x-title>
                INCUBATOR ROUTINE CHECKLIST PER SHIFT
            </x-title>

            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Form Content -->
            <form class="space-y-4">
                @csrf
                <x-dropdown label="Shift" name="shift" placeholder="Select shift" required>
                    <option value="1st Shift">1st Shift</option>
                    <option value="2nd Shift">2nd Shift</option>
                    <option value="3rd Shift">3rd Shift</option>
                </x-dropdown>
                <x-dropdown label="Check for Alarm system condition" name="alarm_system_condition" required>
                    <option value="N/A" selected>N/A</option>
                    <option value="Operational">Operational</option>
                    <option value="Unoperational">Unoperational</option>
                </x-dropdown>
                <x-text-area label="Corrective Action" name="corrective_action" placeholder="Enter your answer..." required/>
                <x-photo-attach label="Attach Photos" name="photos"/>
                
                <div class="flex justify-end mt-4">
                    <x-button variant="primary" type="button" fullWidth>
                        Submit Checklist
                    </x-button>
                </div>
            </form>
        </div>
    </div>
</x-layout>
