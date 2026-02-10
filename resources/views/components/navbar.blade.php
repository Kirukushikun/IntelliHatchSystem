@props(['hideDate' => false, 'includeSidebar' => false, 'user' => null, 'title' => null])

@php
    $isAdmin = ($user && ((int) $user->user_type) === 0);
@endphp

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
                            <!-- Left side - Breadcrumbs -->
                            <div class="flex items-center space-x-3 flex-1">
                                <!-- Breadcrumb Navigation -->
                                <nav class="flex items-center space-x-1 text-sm" aria-label="Breadcrumb">
                                    <ol class="flex items-center space-x-1">
                                        <li>
                                            <a href="{{ $user && ((int) $user->user_type) === 0 ? '/admin/dashboard' : '/user/forms' }}" class="text-gray-500 hover:text-gray-700 transition-colors">
                                                @if($user && ((int) $user->user_type) === 0)
                                                    <!-- Admin: Dashboard icon -->
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                                    </svg>
                                                @else
                                                    <!-- Regular User: Forms icon -->
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                    </svg>
                                                @endif
                                            </a>
                                        </li>
                                        
                                        <!-- Admin Dashboard breadcrumb for admin dashboard pages (except when already on admin dashboard) -->
                                        @if($user && ((int) $user->user_type) === 0 && request()->is('admin/*dashboard*') && !request()->is('admin/dashboard'))
                                            <li class="flex items-center">
                                                <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                                </svg>
                                                <a href="/admin/dashboard" class="ml-1 text-gray-500 hover:text-gray-700 transition-colors">Admin Dashboard</a>
                                            </li>
                                        @endif
                                        
                                        @if($title)
                                            <li class="flex items-center">
                                                <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                                </svg>
                                                <span class="ml-1 text-gray-900 font-medium">{{ $title }}</span>
                                            </li>
                                        @endif
                                    </ol>
                                </nav>
                                
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
                                        <div class="w-10 h-10 bg-linear-to-br {{ $isAdmin ? 'from-blue-500 via-blue-600 to-indigo-600' : 'from-green-500 via-green-600 to-emerald-600' }} rounded-full flex items-center justify-center text-white font-semibold text-sm shadow-md">
                                            {{ strtoupper(substr(auth()->user()->first_name ?? '', 0, 1)) }}{{ strtoupper(substr(auth()->user()->last_name ?? '', 0, 1)) }}
                                        </div>
                                        <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-white rounded-full"></span>
                                    </div>
                                    
                                    <div class="text-left">
                                        <div class="font-semibold text-gray-900 text-sm hidden sm:block">
                                            {{ auth()->user()->full_name }}
                                        </div>
                                    </div>
                                    
                                    <svg 
                                        class="w-4 h-4 text-gray-500 transition-transform duration-200 hidden sm:block"
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
                                    <div class="px-4 py-3 bg-linear-to-r {{ $isAdmin ? 'from-blue-50 to-indigo-50' : 'from-green-50 to-emerald-50' }} border-b border-gray-200">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-12 h-12 bg-linear-to-br {{ $isAdmin ? 'from-blue-500 via-blue-600 to-indigo-600' : 'from-green-500 via-green-600 to-emerald-600' }} rounded-full flex items-center justify-center text-white font-bold shadow-md">
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
                                        <a href="{{ $isAdmin ? route('admin.change-password') : route('user.change-password') }}" class="flex items-center w-full px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition-colors duration-200">
                                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                            </svg>
                                            Change Password
                                        </a>
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button 
                                                type="submit"
                                                class="flex items-center w-full px-4 py-3 text-sm font-medium text-red-600 hover:bg-red-50 hover:text-red-700 transition-colors duration-200 cursor-pointer"
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
                    <!-- Left side - Breadcrumbs -->
                    <div class="flex items-center space-x-3">
                        <!-- Breadcrumb Navigation -->
                        <nav class="flex items-center space-x-1 text-sm" aria-label="Breadcrumb">
                            <ol class="flex items-center space-x-1">
                                <li>
                                    <a href="{{ $user && ((int) $user->user_type) === 0 ? '/admin/dashboard' : '/user/forms' }}" class="text-gray-500 hover:text-gray-700 transition-colors">
                                        @if($user && ((int) $user->user_type) === 0)
                                            <!-- Admin: Dashboard icon -->
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                            </svg>
                                        @else
                                            <!-- Regular User: Forms icon -->
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                        @endif
                                    </a>
                                </li>
                                
                                <!-- Admin Dashboard breadcrumb for admin dashboard pages (except when already on admin dashboard) -->
                                @if($user && ((int) $user->user_type) === 0 && request()->is('admin/*dashboard*') && !request()->is('admin/dashboard'))
                                    <li class="flex items-center">
                                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                        <a href="/admin/dashboard" class="ml-1 text-gray-500 hover:text-gray-700 transition-colors">Admin Dashboard</a>
                                    </li>
                                @endif
                                
                                @if($title)
                                    <li class="flex items-center">
                                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                        <span class="ml-1 text-gray-900 font-medium">{{ $title }}</span>
                                    </li>
                                @endif
                            </ol>
                        </nav>
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
                                <a href="{{ $isAdmin ? route('admin.change-password') : route('user.change-password') }}" class="flex items-center w-full px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition-colors duration-200">
                                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                    </svg>
                                    Change Password
                                </a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button 
                                        type="submit"
                                        class="flex items-center w-full px-4 py-3 text-sm font-medium text-red-600 hover:bg-red-50 hover:text-red-700 transition-colors duration-200 cursor-pointer"
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