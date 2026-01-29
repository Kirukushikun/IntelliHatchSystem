@props(['hideDate' => false])

@auth
    <nav class="shadow-lg border-b border-gray-200 bg-white sticky top-0 z-30">
        <div class="mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Left side - Logo/Brand -->
                <div class="flex items-center space-x-3">
                    <!-- App Icon/Logo -->
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">
                            {{ $slot ?? 'Dashboard' }}
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
                </div>

                <!-- User Profile Dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <!-- Profile Button -->
                    <button 
                        @click="open = !open"
                        class="flex items-center space-x-3 text-gray-700 hover:bg-gray-100 focus:outline-none rounded-xl p-2 pr-3 transition-all duration-200"
                        aria-expanded="false"
                        aria-haspopup="true"
                    >
                        <!-- User Avatar Circle with Initials -->
                        <div class="relative">
                            <div class="w-10 h-10 bg-linear-to-br from-blue-500 via-blue-600 to-indigo-600 rounded-full flex items-center justify-center text-white font-semibold text-sm shadow-md">
                                {{ strtoupper(substr(auth()->user()->first_name ?? auth()->user()->username, 0, 1)) }}{{ strtoupper(substr(auth()->user()->last_name ?? '', 0, 1)) }}
                            </div>
                            <!-- Online Status Indicator -->
                            <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-white rounded-full"></span>
                        </div>
                        
                        <!-- User Name and Role -->
                        <div class="hidden sm:block text-left">
                            <div class="font-semibold text-gray-900 text-sm">
                                {{ auth()->user()->full_name }}
                            </div>
                        </div>
                        
                        <!-- Dropdown Arrow -->
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

                    <!-- Dropdown Menu -->
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
                        <!-- User Info Header -->
                        <div class="px-4 py-3 bg-linear-to-r from-blue-50 to-indigo-50 border-b border-gray-200">
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 bg-linear-to-br from-blue-500 via-blue-600 to-indigo-600 rounded-full flex items-center justify-center text-white font-bold shadow-md">
                                    {{ strtoupper(substr(auth()->user()->first_name ?? auth()->user()->username, 0, 1)) }}{{ strtoupper(substr(auth()->user()->last_name ?? '', 0, 1)) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 truncate">
                                        {{ auth()->user()->full_name }}
                                    </p>
                                    <p class="text-xs text-gray-600 truncate">
                                        {{ auth()->user()->email }}
                                    </p>
                                    <p class="text-xs text-gray-500 truncate">
                                        {{ auth()->user()->username }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Logout Section -->
                        <div class="border-t border-gray-200">
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

    <style>
        [x-cloak] {
            display: none !important;
        }
        
        /* Smooth scrollbar for dropdowns */
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
@endauth