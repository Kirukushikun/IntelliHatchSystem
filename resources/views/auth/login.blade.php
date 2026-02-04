<x-layout>
    <!-- Toast notifications -->
    <x-toast :messages="array_filter([
        'error' => session('error'),
        'success' => session('success'),
        'warning' => session('warning')
    ])" />
    
    <div class="min-h-screen flex items-center justify-center bg-linear-to-br from-white via-orange-150 to-orange-300 p-4">
        <div class="w-full max-w-md bg-white rounded-xl shadow-lg px-8 pt-6 pb-8">
            
            <x-title subtitle="Log in to your admin account">
                IntelliHatch System
            </x-title>

            <!-- Login Form -->
            <form action="{{ route('login.submit') }}" method="POST">
                @csrf
                
                <x-text-input 
                    label="Username" 
                    name="username" 
                    placeholder="Enter your username"  
                    value="{{ old('username') }}"
                    class="-mt-2"
                    icon="user"
                />

                <x-text-input 
                    label="Password" 
                    name="password" 
                    type="password" 
                    placeholder="Enter your password" 
                    icon="lock"
                />
                
                @error('login')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @endif
                
                <div class="flex justify-end mt-6">
                    <x-button variant="primary" type="submit" class="mt-1" fullWidth>
                        Login
                    </x-button>
                </div>
            </form>
            
            <!-- Back to Home -->
            <div class="mt-2 text-center">
                <x-button 
                    href="/" 
                    variant="link" 
                    size="sm"
                    icon="arrow-left"
                >
                    Back to Home
                </x-button>
            </div>
        </div>
    </div>
</x-layout>
