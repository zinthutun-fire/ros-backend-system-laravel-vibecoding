<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') — Cashier</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        .sidebar { width: 250px; }
        .sidebar .nav-link { color: #6c757d; border-radius: 0.375rem; padding: 0.5rem 0.75rem; font-size: 0.9rem; }
        .sidebar .nav-link:hover { background: #f8f9fa; color: #212529; }
        .sidebar .nav-link.active { background: #e9ecef; color: #0d6efd; font-weight: 500; }
        .sidebar .nav-link i { font-size: 1.1rem; }
        @media (max-width: 767.98px) {
            .sidebar { position: fixed !important; top: 0; left: 0; height: 100vh; z-index: 1040; transform: translateX(-100%); transition: transform 0.3s; }
            .sidebar.show { transform: translateX(0); }
        }
        .stat-card { border-left: 4px solid; }
        .table-card { transition: box-shadow 0.2s; cursor: pointer; }
        .table-card:hover { box-shadow: 0 0.25rem 0.5rem rgba(0,0,0,0.08); }
        .badge-status { font-size: 0.75rem; font-weight: 500; padding: 0.25em 0.6em; }
        .page-title { font-size: 1.25rem; font-weight: 600; }
        .rotate-180 { transform: rotate(180deg); }
        .overlay { display: none; }
        .overlay.show { display: block; position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 1035; }
        @media print {
            .no-print { display: none !important; }
            .receipt, .receipt * { visibility: visible; }
            .receipt { position: absolute; left: 0; top: 0; width: 80mm; font-size: 10px; }
        }
    </style>
    @stack('styles')
</head>
<body>
<div x-data="{ sidebarOpen: false }" class="d-flex min-vh-100">
    {{-- Overlay --}}
    <div :class="sidebarOpen ? 'show' : ''" class="overlay" @click="sidebarOpen = false"></div>

    {{-- Sidebar --}}
    <aside :class="sidebarOpen ? 'show' : ''" class="sidebar bg-white border-end vh-100 overflow-auto d-flex flex-column flex-shrink-0">
        <div class="p-3 border-bottom">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-shop fs-4 text-primary"></i>
                <div>
                    <h6 class="mb-0 fw-bold">RMS Cashier</h6>
                    <small class="text-muted">Point of Sale</small>
                </div>
            </div>
        </div>
        <nav class="p-2 flex-grow-1">
            <small class="text-muted text-uppercase fw-semibold px-2 py-1 d-block" style="font-size:0.7rem;">Menu</small>
            <a href="{{ route('cashier.dashboard') }}" class="nav-link d-flex align-items-center gap-2 {{ request()->routeIs('cashier.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a href="{{ route('cashier.orders') }}" class="nav-link d-flex align-items-center gap-2 {{ request()->routeIs('cashier.orders*') ? 'active' : '' }}">
                <i class="bi bi-receipt"></i> Orders
            </a>
            <a href="{{ route('cashier.tables') }}" class="nav-link d-flex align-items-center gap-2 {{ request()->routeIs('cashier.tables*') ? 'active' : '' }}">
                <i class="bi bi-grid-3x3-gap"></i> Tables
            </a>
            <div x-data="{ open: {{ request()->routeIs('cashier.reports*') ? 'true' : 'false' }} }">
                <a href="#" @click.prevent="open = !open" class="nav-link d-flex align-items-center gap-2 {{ request()->routeIs('cashier.reports*') ? 'active' : '' }}">
                    <i class="bi bi-bar-chart-line"></i>
                    <span class="flex-grow-1">Reports</span>
                    <i class="bi bi-chevron-down small transition" :class="open ? 'rotate-180' : ''" style="transition:transform 0.2s;"></i>
                </a>
                <div x-show="open" class="ps-4">
                    <a href="{{ route('cashier.reports.daily') }}" class="nav-link d-flex align-items-center gap-2 small {{ request()->routeIs('cashier.reports.daily') ? 'active' : '' }}">
                        <i class="bi bi-dot"></i> Daily Report
                    </a>
                    <a href="{{ route('cashier.reports.monthly') }}" class="nav-link d-flex align-items-center gap-2 small {{ request()->routeIs('cashier.reports.monthly') ? 'active' : '' }}">
                        <i class="bi bi-dot"></i> Monthly Report
                    </a>
                </div>
            </div>
        </nav>
        <div class="p-3 border-top">
            <div class="d-flex align-items-center gap-2">
                <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;font-size:0.9rem;">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="flex-grow-1 min-w-0">
                    <div class="small fw-semibold text-truncate">{{ auth()->user()->name }}</div>
                    <small class="text-muted">Cashier</small>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button class="btn btn-sm btn-outline-secondary border-0" title="Logout"><i class="bi bi-box-arrow-right"></i></button>
                </form>
            </div>
        </div>
    </aside>

    {{-- Main Content --}}
    <div class="flex-grow-1 d-flex flex-column min-vh-100 overflow-x-hidden">
        {{-- Top Navbar --}}
        <nav class="navbar navbar-light bg-white border-bottom px-3 py-2 no-print">
            <div class="d-flex align-items-center gap-3">
                <button @click="sidebarOpen = !sidebarOpen" class="btn btn-sm btn-outline-secondary border-0 d-md-none">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <button @click="sidebarOpen = !sidebarOpen" class="btn btn-sm btn-outline-secondary border-0 d-none d-md-flex">
                    <i class="bi bi-layout-sidebar fs-5"></i>
                </button>
                <div class="d-none d-sm-block">
                    <span class="page-title">@yield('title', 'Dashboard')</span>
                    <span class="text-muted ms-2 small">{{ now()->format('l, F j, Y') }}</span>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-success bg-opacity-10 text-success px-2 py-1">
                    <span class="spinner-grow spinner-grow-sm me-1" style="width:0.5rem;height:0.5rem;" role="status"></span>
                    Online
                </span>
            </div>
        </nav>

        {{-- Page Content --}}
        <main class="flex-grow-1 p-4 bg-light">
            @yield('content')
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
