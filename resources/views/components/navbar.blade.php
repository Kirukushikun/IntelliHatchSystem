@props(['hideDate' => false, 'includeSidebar' => false, 'user' => null, 'title' => null])

@auth
    @if($includeSidebar && $user)
        <div class="flex h-screen bg-gray-100">
            <!-- Sidebar -->
            <x-sidebar :user="$user" />
            
            <!-- Main Content Area -->
            <div class="flex-1 flex flex-col overflow-hidden">
                <!-- Navbar -->
                <nav class="shadow-lg border-b border-gray-200 bg-white sticky top-0 z-30">
                    <div class="mx-auto px-4 sm:px-6 lg:px-8">
                        <div class="flex justify-between items-center h-16">
                            <!-- Left side - Logo/Brand -->
                            <div class="flex items-center space-x-3 flex-1">
                                @if($title)
                                    <div>
                                        <h1 class="text-xl font-bold text-gray-900">
                                            {{ $title }}
                                        </h1>
                                        @if(!$hideDate)
                                            <p class="text-xs text-gray-500" 
                                            x-data="{ 
                                                date: new Date(),
                                                updateTime() {
                                                    this.date = new Date();
                                                }
                                            }" 
                                            x-init="setInterval(() => updateTime(), 1000)"
                                            x-text="date.toLocaleDateString() + ' ' + date.toLocaleTimeString('en-US', { hour12: false, hour: '2-digit', minute: '2-digit' })">
                                            </p>
                                        @endif
                                    </div>
                                @endif
                                
                                <!-- Navbar Actions (only show with sidebar) -->
                                @if(isset($navbarActions))
                                    <div class="ml-4">
                                        {{ $navbarActions }}
                                    </div>
                                @endif
                            </div>

                            <!-- Right side - User Profile Dropdown -->
                            <div class="relative" x-data="{ open: false }" x-init="$watch('open', value => value ? document.body.classList.add('overflow-hidden') : document.body.classList.remove('overflow-hidden'))">
                                <button 
                                    @click="open = !open"
                                    class="flex items-center space-x-3 text-gray-700 hover:bg-gray-100 focus:outline-none rounded-xl p-2 pr-3 transition-all duration-200"
                                    aria-expanded="false"
                                    aria-haspopup="true"
                                >
                                    <div class="relative">
                                        <div class="w-10 h-10 bg-linear-to-br from-blue-500 via-blue-600 to-indigo-600 rounded-full flex items-center justify-center text-white font-semibold text-sm shadow-md">
                                            {{ strtoupper(substr(auth()->user()->first_name ?? '', 0, 1)) }}{{ strtoupper(substr(auth()->user()->last_name ?? '', 0, 1)) }}
                                        </div>
                                        <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-white rounded-full"></span>
                                    </div>
                                    
                                    <div class="text-left">
                                        <div class="font-semibold text-gray-900 text-sm">
                                            {{ auth()->user()->full_name }}
                                        </div>
                                    </div>
                                    
                                    <svg 
                                        class="w-4 h-4 text-gray-500 transition-transform duration-200"
                                        :class="{ 'rotate-180': open }"
                                        fill="none" 
                                        stroke="currentColor" 
                                        viewBox="0 0 24 24"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                <div 
                                    x-show="open" 
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="transform opacity-0 scale-95 translate-y-1"
                                    x-transition:enter-end="transform opacity-100 scale-100 translate-y-0"
                                    x-transition:leave="transition ease-in duration-150"
                                    x-transition:leave-start="transform opacity-100 scale-100 translate-y-0"
                                    x-transition:leave-end="transform opacity-0 scale-95 translate-y-1"
                                    @click.away="open = false"
                                    class="absolute right-0 mt-3 w-64 bg-white rounded-xl shadow-2xl border border-gray-200 overflow-hidden z-50"
                                    x-cloak
                                >
                                    <div class="px-4 py-3 bg-linear-to-r from-blue-50 to-indigo-50 border-b border-gray-200">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-12 h-12 bg-linear-to-br from-blue-500 via-blue-600 to-indigo-600 rounded-full flex items-center justify-center text-white font-bold shadow-md">
                                                {{ strtoupper(substr(auth()->user()->first_name ?? '', 0, 1)) }}{{ strtoupper(substr(auth()->user()->last_name ?? '', 0, 1)) }}
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-semibold text-gray-900 truncate">
                                                    {{ auth()->user()->full_name }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="border-t border-gray-200">
                                        <a href="{{ route('admin.change-password') }}" class="flex items-center w-full px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition-colors duration-200">
                                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                            </svg>
                                            Change Password
                                        </a>
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button 
                                                type="submit"
                                                class="flex items-center w-full px-4 py-3 text-sm font-medium text-red-600 hover:bg-red-50 hover:text-red-700 transition-colors duration-200"
                                            >
                                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                                </svg>
                                                Logout
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </nav>
                
                <!-- Page Content -->
                <main class="flex-1 overflow-y-auto bg-gray-50">
                    {{ $slot }}
                </main>
            </div>
        </div>
    @else
        <!-- Navbar without Sidebar -->
        <nav class="shadow-lg border-b border-gray-200 bg-white sticky top-0 z-30">
            <div class="mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <!-- Left side - Logo/Brand -->
                    <div class="flex items-center space-x-3">
                        <div>
                            @if($title)
                                <h1 class="text-xl font-bold text-gray-900">
                                    {{ $title }}
                                </h1>
                            @endif
                            @if(!$hideDate)
                                <p class="text-xs text-gray-500" 
                                x-data="{ 
                                    date: new Date(),
                                    updateTime() {
                                        this.date = new Date();
                                    }
                                }" 
                                x-init="setInterval(() => updateTime(), 1000)"
                                x-text="date.toLocaleDateString() + ' ' + date.toLocaleTimeString('en-US', { hour12: false, hour: '2-digit', minute: '2-digit' })">
                                </p>
                            @endif
                        </div>
                    </div>

                    <!-- Right side - User Profile Dropdown -->
                    <div class="relative" x-data="{ open: false }" x-init="$watch('open', value => value ? document.body.classList.add('overflow-hidden') : document.body.classList.remove('overflow-hidden'))">
                        <button 
                            @click="open = !open"
                            class="flex items-center space-x-3 text-gray-700 hover:bg-gray-100 focus:outline-none rounded-xl p-2 pr-3 transition-all duration-200"
                            aria-expanded="false"
                            aria-haspopup="true"
                        >
                            <div class="relative">
                                <div class="w-10 h-10 bg-linear-to-br from-blue-500 via-blue-600 to-indigo-600 rounded-full flex items-center justify-center text-white font-semibold text-sm shadow-md">
                                    {{ strtoupper(substr(auth()->user()->first_name ?? '', 0, 1)) }}{{ strtoupper(substr(auth()->user()->last_name ?? '', 0, 1)) }}
                                </div>
                                <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-white rounded-full"></span>
                            </div>
                            
                            <div class="hidden sm:block text-left">
                                <div class="font-semibold text-gray-900 text-sm">
                                    {{ auth()->user()->full_name }}
                                </div>
                            </div>
                            
                            <svg 
                                class="w-4 h-4 text-gray-500 transition-transform duration-200"
                                :class="{ 'rotate-180': open }"
                                fill="none" 
                                stroke="currentColor" 
                                viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div 
                            x-show="open" 
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="transform opacity-0 scale-95 translate-y-1"
                            x-transition:enter-end="transform opacity-100 scale-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="transform opacity-100 scale-100 translate-y-0"
                            x-transition:leave-end="transform opacity-0 scale-95 translate-y-1"
                            @click.away="open = false"
                            class="absolute right-0 mt-3 w-64 bg-white rounded-xl shadow-2xl border border-gray-200 overflow-hidden z-50"
                            x-cloak
                        >
                            <div class="px-4 py-3 bg-linear-to-r from-blue-50 to-indigo-50 border-b border-gray-200">
                                <div class="flex items-center space-x-3">
                                    <div class="w-12 h-12 bg-linear-to-br from-blue-500 via-blue-600 to-indigo-600 rounded-full flex items-center justify-center text-white font-bold shadow-md">
                                        {{ strtoupper(substr(auth()->user()->first_name ?? '', 0, 1)) }}{{ strtoupper(substr(auth()->user()->last_name ?? '', 0, 1)) }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-gray-900 truncate">
                                            {{ auth()->user()->full_name }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="border-t border-gray-200">
                                <a href="{{ route('admin.change-password') }}" class="flex items-center w-full px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition-colors duration-200">
                                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                    </svg>
                                    Change Password
                                </a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button 
                                        type="submit"
                                        class="flex items-center w-full px-4 py-3 text-sm font-medium text-red-600 hover:bg-red-50 hover:text-red-700 transition-colors duration-200"
                                    >
                                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                        </svg>
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
        
        <!-- Page Content for non-sidebar layout -->
        <main class="bg-gray-50">
            {{ $slot }}
        </main>
    @endif
@endauth

<style>
    [x-cloak] {
        display: none !important;
    }
    
    .overflow-y-auto::-webkit-scrollbar {
        width: 6px;
    }
    
    .overflow-y-auto::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    
    .overflow-y-auto::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }
    
    .overflow-y-auto::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
</style>