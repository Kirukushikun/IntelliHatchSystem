<x-layout>
    <div class="min-h-screen flex items-center justify-center bg-linear-to-br from-white via-orange-150 to-orange-300 p-4">
        <div class="w-full max-w-md bg-white rounded-xl shadow-lg px-8 pt-6 pb-8">
            
            <x-title subtitle="Login to your account">
                IntelliHatch System
            </x-title>

            <!-- Login Form -->
            <form action="{{ route('login.submit') }}" method="POST">
                @csrf
                
                <x-text-input 
                    label="Username" 
                    name="username" 
                    placeholder="Enter your username" 
                    required 
                    value="{{ old('username') }}"
                    class="-mt-2"
                />

                <x-text-input 
                    label="Password" 
                    name="password" 
                    type="password" 
                    placeholder="Enter your password" 
                    required 
                />
                
                <div class="flex justify-end mt-6">
                    <x-button variant="primary" type="submit" class="mt-1" fullWidth>
                        Login
                    </x-button>
                </div>
                
                <div class="text-center mt-3">
                    <x-button variant="link" size="sm" href="{{ request()->get('admin') ? '/login' : '/login?admin=true' }}">
                        {{ request()->get('admin') ? 'Login as User' : 'Login as Admin' }}
                    </x-button>
                </div>
            </form>
        </div>
    </div>
</x-layout>
