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

            <!-- Progress and Navigation Component -->
            <x-progress-navigation :current-step="1" :total-steps="3">
                <!-- Title Component -->
                <x-title>
                    INCUBATOR ROUTINE CHECKLIST PER SHIFT
                </x-title>
                
                <!-- Form Content -->
                <form id="step-form" class="space-y-4">
                    @csrf
                    
                    <!-- Step 1 -->
                    <div id="step-1" class="space-y-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Step 1: Basic Information</h3>
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
                    </div>
                    
                    <!-- Step 2 -->
                    <div id="step-2" class="space-y-4" style="display: none;">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Step 2: Temperature & Humidity</h3>
                        <x-text-input label="Temperature (°C)" name="temperature" placeholder="Enter temperature" required/>
                        <x-text-input label="Humidity (%)" name="humidity" placeholder="Enter humidity" required/>
                        <x-dropdown label="Temperature Status" name="temp_status" required>
                            <option value="Normal">Normal</option>
                            <option value="High">High</option>
                            <option value="Low">Low</option>
                        </x-dropdown>
                    </div>
                    
                    <!-- Step 3 -->
                    <div id="step-3" class="space-y-4" style="display: none;">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Step 3: Additional Notes</h3>
                        <x-text-area label="Corrective Action" name="corrective_action" placeholder="Enter your answer..." required/>
                        <x-photo-attach label="Attach Photos" name="photos"/>
                        <x-text-area label="Additional Notes" name="notes" placeholder="Any additional observations..."/>
                    </div>
                </form>
            </x-progress-navigation>
        </div>
    </div>
</x-layout>
