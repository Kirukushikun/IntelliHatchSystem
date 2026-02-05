@props([
    'user' => null,
    'currentPage' => null
])

@php
    // Define admin sidebar items
    $sidebarItems = [
        [
            'label' => 'Dashboard',
            'href' => '/admin/dashboard',
            'icon' => 'dashboard',
            'active' => 'admin/dashboard*',
            'customActive' => function() {
                return request()->is('admin/dashboard*') || request()->is('admin/incubator-routine-dashboard*');
            }
        ],
        [
            'label' => 'Users',
            'href' => '/admin/users',
            'icon' => 'users',
            'active' => 'admin/users*'
        ],
        [
            'label' => 'Incubators',
            'href' => '/admin/incubators',
            'icon' => 'incubator',
            'active' => 'admin/incubators*'
        ],
        [
            'label' => 'Forms',
            'href' => '/admin/forms',
            'icon' => 'forms',
            'active' => 'admin/forms*'
        ]
    ];
@endphp

<div x-data="{ 
    isOpen: false,
    isCollapsed: localStorage.getItem('sidebar-collapsed') !== 'false',
    toggleSidebar() {
        this.isCollapsed = !this.isCollapsed;
        localStorage.setItem('sidebar-collapsed', this.isCollapsed);
    },
    toggleMobile() {
        this.isOpen = !this.isOpen;
    },
    closeMobile() {
        this.isOpen = false;
    }
}" 
@toggle-sidebar.window="toggleSidebar()"
@toggle-mobile.window="toggleMobile()"
class="relative h-screen"
x-cloak>

    <!-- Mobile overlay -->
    <div x-show="isOpen" 
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-600/75 z-40 lg:hidden"
         @click="closeMobile()">
    </div>

    <!-- Sidebar -->
    <aside :class="[
        'fixed inset-y-0 left-0 z-50 flex flex-col bg-white shadow-xl transform transition-all duration-300 ease-in-out lg:relative lg:translate-x-0',
        isOpen ? 'translate-x-0' : '-translate-x-full',
        isCollapsed ? 'lg:w-16' : 'lg:w-64',
        'w-64'
    ]" 
    class="h-screen overflow-hidden"
    @click.stop>
        
        <!-- Header -->
        <div class="flex items-center h-16 px-4 border-b border-gray-200 shrink-0 bg-white">
            <!-- Logo/Brand - Show when NOT collapsed OR in mobile view -->
            <div x-show="!isCollapsed || window.innerWidth < 1024" class="flex items-center flex-1">
                <div class="w-8 h-8 bg-orange-500 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <span x-transition:enter="transition ease-in-out duration-200"
                      x-transition:enter-start="opacity-0 transform scale-95"
                      x-transition:enter-end="opacity-100 transform scale-100"
                      class="ml-3 text-xl font-bold text-gray-900">IntelliHatch</span>
            </div>

            <!-- Mobile close button -->
            <button @click="closeMobile()" 
                    class="lg:hidden ml-auto p-2 rounded-lg hover:bg-gray-100 transition-colors cursor-pointer">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
            
            <!-- Toggle button - Show when collapsed, positioned where logo was -->
            <div x-show="isCollapsed" class="hidden lg:flex items-center justify-center w-full">
                <button @click="toggleSidebar()" 
                        class="p-2 rounded-lg hover:bg-gray-100 transition-colors cursor-pointer">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path>
                    </svg>
                </button>
            </div>

            <!-- Toggle button - Normal position when NOT collapsed (desktop only) -->
            <button x-show="!isCollapsed" @click="toggleSidebar()" 
                    class="hidden lg:block ml-auto p-2 rounded-lg hover:bg-gray-100 transition-colors cursor-pointer">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
                </svg>
            </button>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-2 py-4 space-y-1 overflow-y-auto bg-white" x-cloak>
            @foreach($sidebarItems as $item)
                <a href="{{ $item['href'] }}" 
                   x-data="{ 
                       showTooltip: false,
                       tooltipPosition: { x: 0, y: 0 },
                       updatePosition() {
                           const rect = this.$el.getBoundingClientRect();
                           this.tooltipPosition = {
                               x: rect.right + 8,
                               y: rect.top + (rect.height / 2)
                           };
                       }
                   }"
                   @mouseenter="if(isCollapsed) { showTooltip = true; updatePosition(); }"
                   @mouseleave="showTooltip = false"
                   @click="closeMobile()"
                   class="group relative flex items-center px-3 py-3 text-sm font-medium rounded-lg transition-all duration-200
                          {{ isset($item['customActive']) && $item['customActive']() 
                              ? 'bg-orange-100 text-orange-700 shadow-sm' 
                              : (request()->is($item['active'] ?? $item['href']) 
                                  ? 'bg-orange-100 text-orange-700 shadow-sm' 
                                  : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900') }}"
                   :class="isCollapsed && window.innerWidth >= 1024 ? 'justify-center' : ''">
                    
                    <!-- Icon -->
                    <div class="shrink-0 w-6 h-6 flex items-center justify-center">
                        @if($item['icon'] === 'dashboard')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                        @elseif($item['icon'] === 'incubator')
                            <svg class="w-5 h-5" fill="currentColor" viewBox="-2 -3.5 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d='M11.843 12.37A4 4 0 0 0 18 9c0-1.238-.623-3.136-1.58-4.698C15.513 2.822 14.524 2 14 2s-1.513.822-2.42 2.302a12.214 12.214 0 0 0-.935 1.884 12.584 12.584 0 0 0-1.277-2.024C10.522 1.91 12.26 0 14 0c3 0 6 5.686 6 9a6 6 0 0 1-8.943 5.23c.36-.563.63-1.19.786-1.86zM6 17a6 6 0 0 1-6-6c0-3.314 3-9 6-9s6 5.686 6 9a6 6 0 0 1-6 6zm0-2a4 4 0 0 0 4-4c0-1.238-.623-3.136-1.58-4.698C7.513 4.822 6.524 4 6 4s-1.513.822-2.42 2.302C2.623 7.864 2 9.762 2 11a4 4 0 0 0 4 4z'/>
                            </svg>
                        @elseif($item['icon'] === 'forms')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        @elseif($item['icon'] === 'users')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                        @endif
                    </div>
                    
                    <!-- Text -->
                    <span class="ml-3 whitespace-nowrap lg:hidden">{{ $item['label'] }}</span>
                    <span x-show="!isCollapsed" 
                          x-transition:enter="transition ease-in-out duration-200"
                          x-transition:enter-start="opacity-0 transform scale-95"
                          x-transition:enter-end="opacity-100 transform scale-100"
                          class="ml-3 whitespace-nowrap hidden lg:block">{{ $item['label'] }}</span>
                    
                    <!-- Tooltip for collapsed state - teleported to body -->
                    <template x-teleport="body">
                        <div x-show="isCollapsed && showTooltip && window.innerWidth >= 1024" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-90"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-90"
                             :style="`position: fixed; left: ${tooltipPosition.x}px; top: ${tooltipPosition.y}px; transform: translateY(-50%);`"
                             class="px-2 py-1 bg-gray-900 text-white text-sm rounded-md whitespace-nowrap pointer-events-none z-50">
                            {{ $item['label'] }}
                            <div class="absolute left-0 top-1/2 transform -translate-y-1/2 -translate-x-1 rotate-45 w-2 h-2 bg-gray-900"></div>
                        </div>
                    </template>
                </a>
            @endforeach
        </nav>
    </aside>

    <!-- Mobile menu button -->
    <button @click="toggleMobile()" 
            x-show="!isOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-90"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-90"
            class="lg:hidden fixed bottom-4 right-4 z-50 p-3 bg-orange-500 text-white rounded-full shadow-lg hover:bg-orange-600 transition-colors cursor-pointer">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
    </button>

    <style>
        /* Prevent flash of unstyled content */
        [x-cloak] {
            display: none !important;
        }
        
        /* Custom scrollbar styles */
        .scrollbar-thin::-webkit-scrollbar {
            width: 6px;
        }
        
        .scrollbar-track-transparent::-webkit-scrollbar-track {
            background: transparent;
        }
        
        .scrollbar-thumb-gray-300::-webkit-scrollbar-thumb {
            background-color: #d1d5db;
            border-radius: 3px;
        }
        
        .scrollbar-thumb-gray-300::-webkit-scrollbar-thumb:hover {
            background-color: #9ca3af;
        }

        /* Prevent body scroll when mobile menu is open */
        body:has([x-data] [x-show="isOpen"]) {
            overflow: hidden;
        }
    </style>
</div>