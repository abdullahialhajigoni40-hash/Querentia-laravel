<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Network') | Querentia</title>

    

    {{-- Tailwind CSS --}}

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Tailwind CDN --}}

    <script src="https://cdn.tailwindcss.com"></script>



    <!-- Font Awesome for Icons -->

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    

    <!-- Alpine.js x-cloak styling -->

    <style>

        [x-cloak] { display: none !important; }

    </style>

    

    <!-- Alpine.js for interactivity -->

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

</head>

<body class="bg-gray-100 text-gray-800 font-sans antialiased">



    <!-- ================= Desktop / Tablet Top Navigation ================= -->

    <nav class="flex fixed top-0 w-full bg-white border-b z-50">

        <div class="max-w-7xl mx-auto w-full px-6 flex items-center justify-between h-16">

            <!-- Logo -->

            <div class="flex items-center gap-3 mr-4 md:mr-0">

                <a href="{{ route('network.home') }}" class="text-xl font-bold text-blue-600 hover:text-blue-700">Querentia</a>

            </div>



            <!-- Search Bar -->

            <div class="hidden md:flex flex-1 max-w-2xl mx-8">

                <div class="relative w-full">

                    <input type="text" 

                           placeholder="Search researchers, journals, topics..."

                           class="w-full pl-10 pr-4 py-2 bg-gray-100 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">

                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>

                </div>

            </div>



            <!-- Navigation Links -->

            <div class="flex items-center gap-4 md:gap-8 text-sm font-medium">

                <button onclick="document.getElementById('journal-modal').style.display='flex'" 

                        class="flex items-center gap-2 hover:text-blue-600 transition">

                    <i class="fas fa-pen w-5 text-center"></i>

                    <span>My Writing</span>

                </button>

                

                <!-- Journal Modal -->

                <div id="journal-modal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50 flex items-center justify-center p-4">

                    <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full">

                        <div class="bg-gradient-to-r from-blue-600 to-purple-500 px-6 py-4 rounded-t-lg">

                            <h3 class="text-lg font-semibold text-white">✍️ Start Writing</h3>

                            <p class="text-blue-100 text-sm mt-1">Enter a title for your writing journal</p>

                        </div>

                        <form action="{{ route('journal.create.with.title') }}" method="POST" class="p-6" id="journal-form">

                            @csrf

                            <div class="mb-4">

                                <label class="block text-sm font-medium text-gray-700 mb-2">

                                    Journal Title <span class="text-red-500">*</span>

                                </label>

                                <input type="text" 

                                       name="title"

                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"

                                       placeholder="Enter your journal title..."

                                       maxlength="500"

                                       required>

                                <input type="hidden" name="is_ai_journal" value="0">

                                <p class="text-xs text-gray-500 mt-1">Maximum 500 characters</p>

                            </div>

                            <div class="flex justify-end gap-3">

                                <button type="button" onclick="document.getElementById('journal-modal').style.display='none'" 

                                        class="px-4 py-2 text-gray-600 hover:text-gray-800">

                                    Cancel

                                </button>

                                <button type="submit" 

                                        class="px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-500 text-white rounded-lg hover:opacity-90">

                                    Continue to Editor

                                </button>

                            </div>

                        </form>

                    </div>

                </div>

                

                <!-- User Dropdown -->

                <div class="relative" x-data="{ open: false }">

                    <button @click="open = !open" 

                            class="flex items-center gap-2 focus:outline-none">

                        @if(auth()->user()->profile_picture)

                            <img src="{{ asset('storage/' . auth()->user()->profile_picture) }}" 

                                 class="w-8 h-8 rounded-full object-cover border">

                        @else

                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold">

                                {{ strtoupper(substr(auth()->user()->first_name, 0, 1)) }}

                            </div>

                        @endif

                        <span class="hidden lg:inline">{{ auth()->user()->first_name }}</span>

                        <i class="fas fa-chevron-down text-xs"></i>

                    </button>

                    

                    <!-- Dropdown Menu -->

                    <div x-show="open" 

                         @click.away="open = false"

                         class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border py-2 z-50">

                        <a href="{{ route('profile.view', auth()->user()->id) }}" class="block px-4 py-2 hover:bg-gray-100">

                            <i class="fas fa-user mr-2"></i>My Profile

                        </a>

                        <a href="{{ route('create_journal') }}" class="block px-4 py-2 hover:bg-gray-100">

                            <i class="fas fa-robot mr-2"></i>AI Journal Enhancer

                        </a>

                        <div class="border-t mt-2 pt-2">

                            <form method="POST" action="{{ route('logout') }}">

                                @csrf

                                <button type="submit" class="block w-full text-left px-4 py-2 hover:bg-red-50 text-red-600">

                                    <i class="fas fa-sign-out-alt mr-2"></i>Logout

                                </button>

                            </form>

                        </div>

                    </div>

                </div>

                

                <!-- Journal submission script -->

                

            </div>

        </div>

    </nav>



    <!-- ================= Page Content ================= -->

    <main class="pt-24 pb-24 max-w-7xl mx-auto px-4 grid grid-cols-1 lg:grid-cols-4 gap-6">



        <!-- ================= Left Sidebar (Desktop) ================= -->

        <aside class="hidden lg:block bg-white rounded-xl shadow p-4 h-fit">

            <!-- User Profile Card -->

            <div class="mb-6">

                <div class="flex items-center gap-3 mb-4">

                    @if(auth()->user()->profile_picture)

                        <img src="{{ asset('storage/' . auth()->user()->profile_picture) }}" 

                             class="w-12 h-12 rounded-full object-cover border-2 border-white shadow">

                    @else

                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold text-lg">

                            {{ strtoupper(substr(auth()->user()->first_name, 0, 1) . substr(auth()->user()->last_name, 0, 1)) }}

                        </div>

                    @endif

                    <div>

                        <h3 class="font-semibold">{{ auth()->user()->full_name }}</h3>

                        <p class="text-sm text-gray-500">{{ auth()->user()->position }}</p>

                    </div>

                </div>

                <div class="bg-gray-50 rounded-lg p-3 mb-4">

                    <div class="flex justify-between mb-2">

                        <span class="text-sm text-gray-600">Connections</span>

                        <span class="font-semibold">{{ auth()->user()->connection_count }}</span>

                    </div>

                    <div class="flex justify-between">

                        <span class="text-sm text-gray-600">Publications</span>

                        <span class="font-semibold">{{ auth()->user()->journals()->count() }}</span>

                    </div>

                </div>

            </div>



            <!-- Navigation -->

            <h3 class="font-semibold mb-4 text-gray-700">Manage Network</h3>

            <!-- Navigation -->

<nav class="p-4">

    <ul class="space-y-3 text-sm">

        <!-- Home -->

        <li>

            <a href="{{ route('network.home') }}" 

               class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-100 {{ request()->routeIs('network.home') ? 'bg-blue-50 text-blue-600' : 'text-gray-600' }}">

                <i class="fas fa-home w-5 text-center"></i>

                <span>Home</span>

            </a>

        </li>



        <!-- My Writing -->

        <li>

            <a href="#" onclick="document.getElementById('journal-modal').style.display='flex'; return false;"

               class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-100 {{ request()->routeIs('my-writings') ? 'bg-blue-50 text-blue-600' : 'text-gray-600' }}">

                <i class="fas fa-edit w-5 text-center"></i>

                <span>My Writing</span>

                @php

                    $draftCount = auth()->user()->journals()->where('status', 'draft')->count();

                @endphp

                @if($draftCount > 0)

                <span class="ml-auto bg-yellow-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">

                    {{ $draftCount }}

                </span>

                @endif

            </a>

        </li>



        <!-- My Reviews -->

        <li>

            <a href="{{ route('my-reviews') }}" 

               class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-100 {{ request()->routeIs('my-reviews') ? 'bg-blue-50 text-blue-600' : 'text-gray-600' }}">

                <i class="fas fa-star w-5 text-center"></i>

                <span>My Reviews</span>

                @php

                    $pendingReviews = auth()->user()->journals()->where('status', 'in_review')->count();

                @endphp

                @if($pendingReviews > 0)

                <span class="ml-auto bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">

                    {{ $pendingReviews }}

                </span>

                @endif

            </a>

        </li>



        <!-- My Connections -->

        <li>

            <a href="{{ route('my-connections') }}"

               class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-100 {{ request()->routeIs('my-connections') ? 'bg-blue-50 text-blue-600' : 'text-gray-600' }}">

                <i class="fas fa-users w-5 text-center"></i>

                <span>My Connections</span>

                @if(auth()->user()->pendingConnections()->count() > 0)

                <span class="ml-auto bg-blue-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">

                    {{ auth()->user()->pendingConnections()->count() }}

                </span>

                @endif

            </a>

        </li>



        <!-- Notification -->

        <li>

            <a href="{{ route('notifications') }}"

               class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-100 {{ request()->routeIs('notifications') ? 'bg-blue-50 text-blue-600' : 'text-gray-600' }}">

                <i class="fas fa-bell w-5 text-center"></i>

                <span>Notification</span>

                @php

                    $unreadNotifications = \App\Models\Notification::where('user_id', auth()->id())

                        ->whereNull('read_at')

                        ->count();

                @endphp

                @if($unreadNotifications > 0)

                <span class="ml-auto bg-purple-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">

                    {{ $unreadNotifications > 9 ? '9+' : $unreadNotifications }}

                </span>

                @endif

            </a>

        </li>



        <!-- Blog -->

        <li>

            <a href="{{ route('blogs.index') }}" 

               class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-100 {{ request()->routeIs('blogs.*') ? 'bg-blue-50 text-blue-600' : 'text-gray-600' }}">

                <i class="fas fa-newspaper w-5 text-center"></i>

                <span>Blog</span>

            </a>

        </li>



        <!-- Group -->

        <li>

            <a href="{{ route('groups.index') }}"

               class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-100 {{ request()->routeIs('groups.*') ? 'bg-blue-50 text-blue-600' : 'text-gray-600' }}">

                <i class="fas fa-layer-group w-5 text-center"></i>

                <span>Group</span>

                @php

                    $unreadGroupMessages = auth()->user()->getUnreadGroupMessagesCount();

                @endphp

                @if($unreadGroupMessages > 0)

                <span class="ml-auto bg-green-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">

                    {{ $unreadGroupMessages > 9 ? '9+' : $unreadGroupMessages }}

                </span>

                @endif

            </a>

        </li>



        <!-- Divider 

        <li class="pt-4 mt-4 border-t border-gray-200">

            <p class="text-xs font-semibold text-gray-500 uppercase mb-2 px-2">AI Tools</p>

        </li>



        <!-- AI Journal 

        <li>

            <a href="{{ route('create_journal') }}" 

               class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-100 {{ request()->routeIs('create_journal') ? 'bg-purple-50 text-purple-600' : 'text-gray-600' }}">

                <i class="fas fa-robot w-5 text-center"></i>

                <span>AI Journal</span>

                @if(!auth()->user()->isPro())

                <span class="ml-auto bg-purple-100 text-purple-800 text-xs px-2 py-1 rounded">

                    Pro

                </span>

                @endif

            </a>



            

        </li>



        <!-- Upgrade Button 

        <li>

            <a href="#" 

               class="flex items-center gap-3 p-2 rounded-lg bg-gradient-to-r from-purple-600 to-blue-500 text-white hover:opacity-90">

                <i class="fas fa-crown w-5 text-center"></i>

                <span>Upgrade to Pro</span>

            </a>

        </li>-->

    </ul>

    

    <!-- Recent Groups Section -->

    <div class="mt-8">

        <h3 class="text-xs font-semibold text-gray-500 uppercase px-2 mb-2">Your Groups</h3>

        <ul class="space-y-2">

            @php

                $userGroups = auth()->user()->joinedGroups()->take(3)->get();

            @endphp

            @if($userGroups->count() > 0)

                @foreach($userGroups as $group)

                    <li>

                        <a href="{{ route('groups.show', $group->slug) }}" class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-100 text-gray-600">

                            @if($group->avatar)

                                <img src="{{ asset('storage/' . $group->avatar) }}" 

                                     alt="{{ $group->name }}" 

                                     class="w-8 h-8 rounded-lg object-cover">

                            @else

                                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-purple-400 to-blue-500 flex items-center justify-center text-white text-sm">

                                    <i class="fas fa-layer-group"></i>

                                </div>

                            @endif

                            <span class="text-sm">{{ Str::limit($group->name, 20) }}</span>

                            @if($group->getUnreadMessageCount(auth()->id()) > 0)

                                <span class="ml-auto bg-red-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center">

                                    {{ $group->getUnreadMessageCount(auth()->id()) }}

                                </span>

                            @endif

                        </a>

                    </li>

                @endforeach

            @else

                <li>

                    <div class="flex items-center gap-3 p-2 text-gray-400">

                        <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center">

                            <i class="fas fa-layer-group text-gray-400 text-sm"></i>

                                </div>

                        <span class="text-sm">No groups yet</span>

                    </div>

                </li>

            @endif

        </ul>

        @if($userGroups->count() >= 3)

            <div class="mt-2 px-2">

                <a href="{{ route('groups.index') }}" class="text-xs text-purple-600 hover:text-purple-800">

                    View all groups →

                </a>

            </div>

        @endif

    </div>

</nav>

        </aside>



        <!-- ================= Main Feed ================= -->

        <section class="lg:col-span-3 space-y-6">

            @yield('content')

        </section>

    </main>



    <!-- ================= Mobile Bottom Navigation ================= -->

    <nav class="fixed bottom-0 w-full bg-white border-t flex justify-around items-center h-16 md:hidden z-50">

    <!-- Home -->

    <a href="{{ route('network.home') }}" 

       class="flex flex-col items-center text-xs {{ request()->routeIs('network.home') ? 'text-blue-600' : 'text-gray-500 hover:text-blue-600' }}">

        <i class="fas fa-home w-5 h-5 mb-1"></i>

        <span class="text-[10px]">Home</span>

    </a>



    <!-- My Writing -->

    <a href="{{ route('create_journal') }}" 

       class="flex flex-col items-center text-xs {{ request()->routeIs('my-writings') ? 'text-blue-600' : 'text-gray-500 hover:text-blue-600' }}">

        <i class="fas fa-edit w-5 h-5 mb-1"></i>

        <span class="text-[10px]">Writing</span>

    </a>



    <!-- My Reviews -->

    <a href="{{ route('my-reviews') }}"

       class="flex flex-col items-center text-xs {{ request()->routeIs('my-reviews') ? 'text-blue-600' : 'text-gray-500 hover:text-blue-600' }}">

        <i class="fas fa-star w-5 h-5 mb-1"></i>

        <span class="text-[10px]">Reviews</span>

        @php

            $pendingReviews = auth()->user()->journals()->where('status', 'in_review')->count();

        @endphp

        @if($pendingReviews > 0)

        <span class="absolute top-1 right-3 bg-red-500 text-white text-[8px] rounded-full w-3 h-3 flex items-center justify-center">

            {{ $pendingReviews }}

        </span>

        @endif

    </a>



    <!-- Connections -->

    <a href="{{ route('my-connections') }}"

       class="flex flex-col items-center text-xs relative {{ request()->routeIs('my-connections') ? 'text-blue-600' : 'text-gray-500 hover:text-blue-600' }}">

        <i class="fas fa-users w-5 h-5 mb-1"></i>

        <span class="text-[10px]">Network</span>

        @if(auth()->user()->pendingConnections()->count() > 0)

        <span class="absolute top-1 right-3 bg-blue-500 text-white text-[8px] rounded-full w-3 h-3 flex items-center justify-center">

            {{ auth()->user()->pendingConnections()->count() > 9 ? '9+' : auth()->user()->pendingConnections()->count() }}

        </span>

        @endif

    </a>



    <!-- Notification -->

    <a href="{{ route('notifications') }}"

       class="flex flex-col items-center text-xs relative {{ request()->routeIs('notifications') ? 'text-blue-600' : 'text-gray-500 hover:text-blue-600' }}">

        <i class="fas fa-bell w-5 h-5 mb-1"></i>

        <span class="text-[10px]">Alerts</span>

        @php

            $unreadCount = \App\Models\Notification::where('user_id', auth()->id())

                ->whereNull('read_at')

                ->count();

        @endphp

        @if($unreadCount > 0)

        <span class="absolute top-1 right-3 bg-purple-500 text-white text-[8px] rounded-full w-3 h-3 flex items-center justify-center">

            {{ $unreadCount > 9 ? '9+' : $unreadCount }}

        </span>

        @endif

    </a>



    <!-- Profile 

    <a href="{{ route('profile.show') }}" 

       class="flex flex-col items-center text-xs {{ request()->routeIs('profile.show') ? 'text-blue-600' : 'text-gray-500 hover:text-blue-600' }}">

        <i class="fas fa-user w-5 h-5 mb-1"></i>

        <span class="text-[10px]">Me</span>

    </a>-->

</nav>



    <!-- JavaScript -->

    <script>

        // Mobile search toggle

        document.addEventListener('DOMContentLoaded', function() {

            // Handle mobile search

            const searchBtn = document.querySelector('.mobile-search-btn');

            const searchBar = document.querySelector('.mobile-search-bar');

            

            if (searchBtn && searchBar) {

                searchBtn.addEventListener('click', function() {

                    searchBar.classList.toggle('hidden');

                });

            }

            

            // Handle notification clicks

            document.querySelectorAll('.notification-item').forEach(item => {

                item.addEventListener('click', function() {

                    const notificationId = this.dataset.notificationId;

                    if (notificationId) {

                        fetch(`/api/notifications/${notificationId}/read`, {

                            method: 'POST',

                            headers: {

                                'X-CSRF-TOKEN': '{{ csrf_token() }}'

                            }

                        });

                    }

                });

            });

        });

    </script>

    

    <script>

        // Journal form submission handler

        if (document.getElementById('journal-form')) {

            document.getElementById('journal-form').addEventListener('submit', function(e) {

                e.preventDefault();

                const formData = new FormData(this);

                

                fetch(this.action, {

                    method: 'POST',

                    body: formData,

                    headers: {

                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')

                    }

                })

                .then(response => response.json())

                .then(data => {

                    if (data.success) {

                        window.location.href = data.redirect_url;

                    } else {

                        alert(data.message || 'Failed to create journal');

                    }

                })

                .catch(error => {

                    alert('Network error. Please try again.');

                });

            });

        }

    </script>

    @include('components.journal-title-modal')

</body>

</html>