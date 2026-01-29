<x-layout>
    <!-- Toast notifications -->
    <x-toast :messages="array_filter([
        'error' => session('error'),
        'success' => session('success'),
        'warning' => session('warning')
    ])" />
    
    <div class="min-h-screen flex items-center justify-center bg-linear-to-br from-white via-orange-150 to-orange-300 p-4">
        <div class="w-full max-w-md bg-white rounded-xl shadow-lg px-8 pt-6 pb-8">
            
            <x-title subtitle="{{ request()->is('admin/login') ? 'Log in to your admin account' : 'Log in to your user account' }}">
                IntelliHatch System
            </x-title>

            <!-- Login Form -->
            <form action="{{ request()->is('admin/login') ? route('admin.login.submit') : route('login.submit') }}" method="POST">
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
                
                <div class="text-center mt-3">
                    <x-button variant="link" size="sm" href="{{ request()->is('admin/login') ? '/login' : '/admin/login' }}">
                        {{ request()->is('admin/login') ? 'Login as User' : 'Login as Admin' }}
                    </x-button>
                </div>
            </form>
        </div>
    </div>
</x-layout>
