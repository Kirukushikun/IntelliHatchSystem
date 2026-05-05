<x-layout>
    <!-- Toast notifications -->
    <x-toast :messages="array_filter([
        'error' => session('error'),
        'success' => session('success'),
        'warning' => session('warning')
    ])" />
    
    <!-- Simple Public Navbar -->
    <nav class="shadow-lg border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 sticky top-0 z-30">
        <div class="mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo/Brand -->
                <div class="flex items-center space-x-3">
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">IntelliHatch System</h1>
                </div>
                
                <!-- Right Side Actions -->
                <div class="flex items-center space-x-4">
                    <!-- Dark Mode Toggle -->
                    <x-dark-mode-toggle />
                    
                </div>
            </div>
        </div>
    </nav>
    
    <div class="min-h-screen flex items-center justify-center bg-linear-to-br from-orange-100 dark:from-gray-900 via-orange-200 dark:via-gray-800 to-orange-300 dark:to-gray-900 p-4">
        <div class="w-full max-w-md bg-white dark:bg-gray-800 rounded-xl shadow-lg dark:shadow-xl px-8 pt-6 pb-8">
            
            <x-title subtitle="Log in to your account">
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
            
        </div>
    </div>
</x-layout>
