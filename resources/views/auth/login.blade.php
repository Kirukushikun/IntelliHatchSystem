<x-layout>
    <div class="min-h-screen flex items-center justify-center bg-gray-100 p-4">
        <div class="w-full max-w-md bg-white rounded-xl shadow-lg px-8 pt-6 pb-8">
            
            <x-title>
                Login
            </x-title>

            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Login Form -->
            <form action="{{ route('login.submit') }}" method="POST" class="space-y-4">
                @csrf
                
                <x-text-input 
                    label="First Name" 
                    name="first_name" 
                    placeholder="Enter your first name" 
                    required 
                    value="{{ old('first_name') }}"
                />
                
                <x-text-input 
                    label="Last Name" 
                    name="last_name" 
                    placeholder="Enter your last name" 
                    required 
                    value="{{ old('last_name') }}"
                />
                
                <x-text-input 
                    label="Password" 
                    name="password" 
                    type="password" 
                    placeholder="Enter your password" 
                    required 
                />
                
                <div class="flex items-center justify-between">
                    <label class="flex items-center">
                        <input type="checkbox" name="remember" class="rounded border-gray-300 text-orange-600 shadow-sm focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm text-gray-600">Remember me</span>
                    </label>
                </div>
                
                <div class="flex justify-end mt-6">
                    <x-button variant="primary" type="submit" fullWidth>
                        Login
                    </x-button>
                </div>
            </form>
            
            <div class="mt-6 text-center text-sm text-gray-600">
                <p>Test Credentials:</p>
                <p class="font-mono">John Doe / password</p>
            </div>
        </div>
    </div>
</x-layout>
