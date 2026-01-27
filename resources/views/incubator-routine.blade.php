<x-layout>
    <div class="min-h-screen flex items-center justify-center bg-gray-100 p-4">
        <!-- Form/Card Container -->
        <div class="w-full max-w-lg bg-white rounded-xl shadow-lg p-8 space-y-6">
            
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
            <form action="{{ route('incubator-routine.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <x-text-input label="Employee Name" name="employee_name" placeholder="Enter your full name" required="true"/>
                <x-text-area label="Notes" name="notes" placeholder="Add any notes here..." required="true"/>
                <x-photo-attach label="Photos" name="photos"/>
                
                <div class="flex justify-end mt-4">
                    <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg shadow hover:bg-indigo-700 transition">
                        Submit
                    </button>
                </div>
            </form>

        </div>
    </div>
</x-layout>
