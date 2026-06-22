<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Kitchen') - Kitchen Display</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="h-full">
<div x-data="{ sidebarOpen: true }" class="min-h-full flex">
    <aside x-show="sidebarOpen" class="w-56 bg-gray-800 text-gray-100 flex-shrink-0">
        <div class="h-16 flex items-center px-4 border-b border-gray-700">
            <h1 class="text-lg font-bold">{{ auth()->user()->kitchen?->name ?? 'Kitchen' }}</h1>
        </div>
        <nav class="mt-4 space-y-1 px-2">
            <a href="{{ route('kitchen.dashboard') }}" class="flex items-center px-3 py-3 rounded-lg text-sm {{ request()->routeIs('kitchen.dashboard') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Dashboard
            </a>
            <a href="{{ route('kitchen.orders') }}" class="flex items-center px-3 py-3 rounded-lg text-sm {{ request()->routeIs('kitchen.orders') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                Orders
                <span id="pending-badge" class="ml-auto bg-red-500 text-white text-xs px-2 py-0.5 rounded-full hidden">0</span>
            </a>
            <a href="{{ route('kitchen.display') }}" class="flex items-center px-3 py-3 rounded-lg text-sm {{ request()->routeIs('kitchen.display') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                Display Mode
            </a>
        </nav>
    </aside>

    <div class="flex-1 min-w-0 flex flex-col">
        <header class="h-16 bg-gray-800 border-b border-gray-700 flex items-center justify-between px-6">
            <button @click="sidebarOpen = !sidebarOpen" class="text-gray-400 hover:text-gray-200">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <div class="flex items-center space-x-4">
                <span class="text-sm text-gray-300">{{ auth()->user()->name }}</span>
                <span class="px-2 py-1 text-xs rounded-full bg-yellow-800 text-yellow-200">kitchen</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-red-400 hover:text-red-300">Logout</button>
                </form>
            </div>
        </header>
        <main class="flex-1 p-6 overflow-auto bg-gray-900">
            @yield('content')
        </main>
    </div>
</div>

@stack('scripts')
</body>
</html>
