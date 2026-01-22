<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Querentia') - Academic Publishing Platform</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <!-- Replaced custom CSS with Tailwind utilities. -->
</head>
<body class="bg-gray-50 font-sans antialiased" x-data="{ sidebarOpen: false, sidebarCollapsed: false }">
    <!-- Mobile Overlay -->
    <div class="mobile-overlay" 
         :class="{ 'active': sidebarOpen }"
         @click="sidebarOpen = false"
         x-show="sidebarOpen"
         x-transition>
    </div>

        <!-- Sidebar -->
        <aside class="sidebar fixed left-0 top-0 h-screen bg-white shadow-md transform transition-all duration-300 flex flex-col"
            :class="{ 'w-64': !sidebarCollapsed, 'w-16': sidebarCollapsed, 'translate-x-0': sidebarOpen, '-translate-x-full md:translate-x-0': !sidebarOpen }"
            x-show="true">
        
        <!-- Logo -->
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3" :class="{ 'justify-center': sidebarCollapsed }">
                    <div x-show="!sidebarCollapsed">
                        <h1 class="text-xl font-bold text-gray-900">Querentia</h1>
                        <p class="text-xs text-gray-500">From Inquiry to Impact</p>
                    </div>
                    <div x-show="sidebarCollapsed" class="text-center">
                        <span class="text-2xl font-bold text-purple-600">Q</span>
                    </div>
                </div>
                
                <!-- Collapse Toggle (Desktop) -->
                <button class="hidden md:block text-gray-500 hover:text-gray-700"
                        @click="sidebarCollapsed = !sidebarCollapsed"
                        x-show="!sidebarCollapsed">
                    <i class="fas fa-chevron-left"></i>
                </button>
            </div>
        </div>

        <!-- User Profile Summary -->
        <div class="p-4 border-b border-gray-200" x-show="!sidebarCollapsed">
            <div class="flex items-center space-x-3">
                <div class="relative">
                    @if(auth()->user()->profile_picture)
                        <img src="{{ asset('storage/' . auth()->user()->profile_picture) }}" 
                             alt="{{ auth()->user()->full_name }}"
                             class="w-12 h-12 rounded-full object-cover border-2 border-white shadow">
                    @else
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-purple-400 to-blue-500 flex items-center justify-center text-white font-bold">
                            {{ strtoupper(substr(auth()->user()->first_name, 0, 1) . substr(auth()->user()->last_name, 0, 1)) }}
                        </div>
                    @endif
                    <div class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-white"></div>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">{{ auth()->user()->full_name }}</h3>
                    <p class="text-xs text-gray-500">{{ auth()->user()->position }}</p>
                    <p class="text-xs text-gray-500">{{ auth()->user()->institution }}</p>
                </div>
            </div>
            
            <!-- Connection Count -->
            <div class="mt-4 bg-gray-50 rounded-lg p-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Connections</span>
                    <span class="font-bold text-gray-900">{{ auth()->user()->connection_count }}</span>
                </div>
                <div class="flex justify-between items-center mt-2">
                    <span class="text-sm text-gray-600">Publications</span>
                    <span class="font-bold text-gray-900">{{ auth()->user()->journals()->count() }}</span>
                </div>
            </div>
        </div>

        <!-- Navigation Menu -->
        <nav class="p-4 overflow-auto flex-1">
            <ul class="space-y-2">
                <!-- Home -->
                <li>
                    <a href="{{ route('dashboard') }}" 
                       class="nav-item flex items-center space-x-3 p-3 {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="fas fa-home text-gray-500 w-6 text-center"></i>
                        <span x-show="!sidebarCollapsed">Home</span>
                    </a>
                </li>

                <!-- My Writing -->
                <li>
                          <a href="{{ route('my-writings') }}" 
                              class="nav-item relative flex items-center space-x-3 p-3 {{ request()->routeIs('my-writings') ? 'active' : '' }}">
                        <i class="fas fa-edit text-gray-500 w-6 text-center"></i>
                        <span x-show="!sidebarCollapsed">My Writing</span>
                        <span class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full w-4 h-4 text-xs flex items-center justify-center"
                              x-show="!sidebarCollapsed && {{ auth()->user()->journals()->where('status', 'draft')->count() }} > 0">
                            {{ auth()->user()->journals()->where('status', 'draft')->count() }}
                        </span>
                    </a>
                </li>

                <!-- My Reviews -->
                <li>
                          <a href="{{ route('my-reviews') }}" 
                              class="nav-item relative flex items-center space-x-3 p-3 {{ request()->routeIs('my-reviews') ? 'active' : '' }}">
                        <i class="fas fa-star text-gray-500 w-6 text-center"></i>
                        <span x-show="!sidebarCollapsed">My Reviews</span>
                        <span class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full w-4 h-4 text-xs flex items-center justify-center"
                              x-show="!sidebarCollapsed && {{ auth()->user()->receivedConnections()->where('status', 'pending')->count() }} > 0">
                            {{ auth()->user()->receivedConnections()->where('status', 'pending')->count() }}
                        </span>
                    </a>
                </li>

                <!-- My Connections -->
                <li>
                    <a href="{{ route('my-connections') }}" 
                       class="nav-item flex items-center space-x-3 p-3 {{ request()->routeIs('my-connections') ? 'active' : '' }}">
                        <i class="fas fa-users text-gray-500 w-6 text-center"></i>
                        <span x-show="!sidebarCollapsed">My Connections</span>
                    </a>
                </li>

                <!-- Notifications -->
                <li>
                    <a href="{{ route('notifications') }}" 
                       class="nav-item flex items-center space-x-3 p-3 {{ request()->routeIs('notifications') ? 'active' : '' }}">
                        <i class="fas fa-bell text-gray-500 w-6 text-center"></i>
                        <span x-show="!sidebarCollapsed">Notifications</span>
                        <span class="notification-badge" x-show="!sidebarCollapsed && 5 > 0">5</span>
                    </a>
                </li>

                <!-- Blog -->
                <li>
                    <a href="{{ route('blog') }}" 
                       class="nav-item flex items-center space-x-3 p-3 {{ request()->routeIs('blog') ? 'active' : '' }}">
                        <i class="fas fa-newspaper text-gray-500 w-6 text-center"></i>
                        <span x-show="!sidebarCollapsed">Blog</span>
                    </a>
                </li>

                <!-- Groups -->
                <li>
                    <a href="{{ route('groups') }}" 
                       class="nav-item flex items-center space-x-3 p-3 {{ request()->routeIs('groups') ? 'active' : '' }}">
                        <i class="fas fa-layer-group text-gray-500 w-6 text-center"></i>
                        <span x-show="!sidebarCollapsed">Groups</span>
                    </a>
                </li>

                <!-- Submissions -->
                <li>
                    <a href="{{ route('submissions') }}" 
                       class="nav-item flex items-center space-x-3 p-3 {{ request()->routeIs('submissions') ? 'active' : '' }}">
                        <i class="fas fa-paper-plane text-gray-500 w-6 text-center"></i>
                        <span x-show="!sidebarCollapsed">Submissions</span>
                    </a>
                </li>

                <!-- Divider -->
                <li class="border-t border-gray-200 pt-4">
                    <p class="text-xs text-gray-500 uppercase font-semibold mb-2 px-3" x-show="!sidebarCollapsed">AI Tools</p>
                </li>

                <!-- AI Journal Studio -->
                <li>
                    <a href="{{ route('create_journal') }}" 
                       class="nav-item flex items-center space-x-3 p-3 {{ request()->routeIs('create_journal') ? 'active' : '' }}">
                        <i class="fas fa-robot text-purple-500 w-6 text-center"></i>
                        <span x-show="!sidebarCollapsed">AI Journal Studio</span>
                        <span class="bg-purple-100 text-purple-800 text-xs px-2 py-1 rounded" x-show="!sidebarCollapsed">
                            Pro
                        </span>
                    </a>
                </li>

                <!-- Upgrade to Pro -->
                <li x-show="!sidebarCollapsed && !auth()->user()->isPro()">
                    <a href="{{ route('subscription.upgrade') }}" 
                       class="nav-item flex items-center space-x-3 p-3 bg-gradient-to-r from-purple-600 to-blue-500 text-white rounded-lg">
                        <i class="fas fa-crown w-6 text-center"></i>
                        <span>Upgrade to Pro</span>
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Bottom Section 
        <div class="mt-auto p-4 border-t border-gray-200 bg-white" x-show="!sidebarCollapsed">
            <!-- Settings 
            <a href="{{ route('settings') }}" 
               class="nav-item flex items-center space-x-3 p-3 mb-2">
                <i class="fas fa-cog text-gray-500 w-6 text-center"></i>
                <span>Settings</span>
            </a>

            <!-- Logout 
            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <button type="submit" 
                        class="nav-item flex items-center space-x-3 p-3 w-full text-left text-red-600 hover:bg-red-50">
                    <i class="fas fa-sign-out-alt w-6 text-center"></i>
                    <span>Logout</span>
                </button>
            </form>
        </div>-->
    </aside>

    <!-- Main Content -->
    <main class="main-content min-h-screen transition-all duration-300" :class="{ 'md:ml-64': !sidebarCollapsed, 'md:ml-16': sidebarCollapsed }">
        <!-- Top Navigation Bar -->
        <header class="bg-white border-b border-gray-200 sticky top-0 z-50">
            <div class="flex items-center justify-between px-6 py-4">
                <!-- Left: Mobile Menu Toggle -->
                <div class="flex items-center space-x-4">
                    <button class="md:hidden text-gray-600"
                            @click="sidebarOpen = !sidebarOpen">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    
                    <!-- Search Bar -->
                    <div class="relative hidden md:block">
                        <input type="text" 
                               placeholder="Search researchers, publications..."
                               class="pl-10 pr-4 py-2 w-96 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                </div>

                <!-- Right: User Actions -->
                <div class="flex items-center space-x-4">
                    <!-- Mobile Search -->
                    <button class="md:hidden text-gray-600">
                        <i class="fas fa-search text-xl"></i>
                    </button>

                    <!-- Create Button -->
                    <button class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition">
                        <i class="fas fa-plus mr-2"></i>
                        <span class="hidden md:inline">Create</span>
                    </button>

                    <!-- Notifications -->
                    <div class="relative">
                        <button class="text-gray-600 hover:text-gray-900 relative">
                            <i class="fas fa-bell text-xl"></i>
                            <span class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full w-4 h-4 text-xs flex items-center justify-center">5</span>
                        </button>
                    </div>

                    <!-- Messages -->
                    <div class="relative">
                        <button class="text-gray-600 hover:text-gray-900 relative">
                            <i class="fas fa-envelope text-xl"></i>
                            <span class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full w-4 h-4 text-xs flex items-center justify-center">3</span>
                        </button>
                    </div>

                    <!-- User Menu -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" 
                                class="flex items-center space-x-2 focus:outline-none">
                            @if(auth()->user()->profile_picture)
                                <img src="{{ asset('storage/' . auth()->user()->profile_picture) }}" 
                                     alt="{{ auth()->user()->full_name }}"
                                     class="w-8 h-8 rounded-full object-cover">
                            @else
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-400 to-blue-500 flex items-center justify-center text-white font-bold text-sm">
                                    {{ strtoupper(substr(auth()->user()->first_name, 0, 1)) }}
                                </div>
                            @endif
                            <span class="hidden md:inline text-sm font-medium">{{ auth()->user()->first_name }}</span>
                            <i class="fas fa-chevron-down text-xs hidden md:inline"></i>
                        </button>

                        <!-- Dropdown Menu -->
                        <div x-show="open" 
                             @click.away="open = false"
                             class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50">
                            <a href="{{ route('profile.show') }}" 
                               class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-user mr-2"></i>Profile
                            </a>
                            <a href="{{ route('settings') }}" 
                               class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-cog mr-2"></i>Settings
                            </a>
                            <a href="{{ route('subscription.index') }}" 
                               class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-crown mr-2"></i>Subscription
                            </a>
                            <div class="border-t border-gray-200 mt-2 pt-2">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" 
                                            class="block w-full text-left px-4 py-2 text-red-600 hover:bg-red-50">
                                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile Search Bar (Hidden by default) -->
            <div class="md:hidden px-6 pb-4" x-show="false" x-transition>
                <div class="relative">
                    <input type="text" 
                           placeholder="Search researchers, publications..."
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <div class="p-6">
            @yield('content')
        </div>

        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200 mt-8 py-6 px-6">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="text-gray-600 text-sm mb-4 md:mb-0">
                    &copy; {{ date('Y') }} Querentia. Accelerating academic impact.
                </div>
                <div class="flex space-x-6">
                    <a href="#" class="text-gray-600 hover:text-purple-600 text-sm">Terms</a>
                    <a href="#" class="text-gray-600 hover:text-purple-600 text-sm">Privacy</a>
                    <a href="#" class="text-gray-600 hover:text-purple-600 text-sm">Help Center</a>
                    <a href="#" class="text-gray-600 hover:text-purple-600 text-sm">Contact</a>
                </div>
            </div>
        </footer>
    </main>

    <!-- Scripts -->
    <script>
        // Handle sidebar collapse on mobile
        document.addEventListener('DOMContentLoaded', function() {
            // Check localStorage for sidebar state
            const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            if (isCollapsed) {
                document.querySelector('[x-data]').__x.$data.sidebarCollapsed = true;
            }
            
            // Save state to localStorage
            document.querySelector('[x-data]').$watch('sidebarCollapsed', (value) => {
                localStorage.setItem('sidebarCollapsed', value);
            });
            
            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(event) {
                if (window.innerWidth <= 768) {
                    const sidebar = document.querySelector('.sidebar');
                    const toggleBtn = document.querySelector('[x-on:click*="sidebarOpen"]');
                    
                    if (sidebar && toggleBtn && !sidebar.contains(event.target) && !toggleBtn.contains(event.target)) {
                        document.querySelector('[x-data]').__x.$data.sidebarOpen = false;
                    }
                }
            });
        });
    </script>
</body>
</html>