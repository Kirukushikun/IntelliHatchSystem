<x-layout>
    <!-- Toast notifications -->
    <x-toast :messages="array_filter([
        'error' => session('error'),
        'success' => session('success'),
        'warning' => session('warning')
    ])" />
    
    <!-- Simple Public Navbar -->
    <nav class="shadow-lg border-b border-gray-200 bg-white sticky top-0 z-30">
        <div class="mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo/Brand -->
                <div class="flex items-center space-x-3">
                    <h1 class="text-xl font-bold text-gray-900">IntelliHatch System</h1>
                </div>
                
                <!-- Back to Home -->
                <div class="flex items-center space-x-4">
                    <x-button 
                        href="/" 
                        variant="secondary" 
                        size="sm"
                        icon="arrow-left"
                    >
                        Back to Home
                    </x-button>
                </div>
            </div>
        </div>
    </nav>
    
    <div class="min-h-screen flex items-center justify-center bg-linear-to-br from-white via-orange-150 to-orange-300 p-4">
        <div class="w-full max-w-md bg-white rounded-xl shadow-lg px-8 pt-6 pb-8">
            
            <x-title subtitle="Log in to your {{ request('type', 'user') }} account">
                IntelliHatch System
            </x-title>

            <!-- Login Form -->
            <form action="{{ route('login.submit') }}" method="POST">
                @csrf
                
                <!-- Hidden user type field -->
                <input type="hidden" name="user_type" value="{{ request('type', 'user') }}">
                
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
            
            <!-- Login Links for Non-Authenticated Users -->
            @guest
                <div class="mt-4 text-center">
                    <p class="text-sm text-gray-600">
                        @if(request('type', 'user') === 'admin')
                            Not an admin? <a href="/login?type=user" class="text-green-600 hover:text-green-700 underline">Click here</a> for user login
                        @else
                            Not a user? <a href="/login?type=admin" class="text-orange-600 hover:text-orange-700 underline">Click here</a> for admin login
                        @endif
                    </p>
                </div>
            @endguest
        </div>
    </div>
</x-layout>
