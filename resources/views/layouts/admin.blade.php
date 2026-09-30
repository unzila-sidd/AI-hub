<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} - POS</title>
    <link rel="stylesheet" href="{{ asset('libs/adminlte/vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('libs/adminlte/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pos.css') }}">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars">&#9776;</i></a>
            </li>
        </ul>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('dashboard') }}">Main App</a>
            </li>
            <li class="nav-item">
                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-link nav-link">Logout</button>
                </form>
            </li>
        </ul>
    </nav>

    <!-- Sidebar -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="{{ route('pos.index') }}" class="brand-link">
            <span class="brand-text font-weight-light">AI-Hub POS</span>
        </a>
        <div class="sidebar">
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                    @if (auth()->user()->hasPermission('sales.create'))
                    <li class="nav-item">
                        <a href="{{ route('pos.index') }}" class="nav-link {{ request()->routeIs('pos.index') ? 'active' : '' }}">
                            <i class="nav-icon">&#128722;</i>
                            <p>POS Terminal</p>
                        </a>
                    </li>
                    @endif
                    @if (auth()->user()->hasAnyPermission(['sales.view', 'sales.create']))
                    <li class="nav-item">
                        <a href="{{ route('pos.sales') }}" class="nav-link {{ request()->routeIs('pos.sales', 'pos.receipt') ? 'active' : '' }}">
                            <i class="nav-icon">&#128176;</i>
                            <p>Sales</p>
                        </a>
                    </li>
                    @endif
                    @if (auth()->user()->hasAnyPermission(['products.view', 'products.add', 'products.edit', 'products.delete']))
                    <li class="nav-item">
                        <a href="{{ route('pos.products.index') }}" class="nav-link {{ request()->routeIs('pos.products.*') ? 'active' : '' }}">
                            <i class="nav-icon">&#128230;</i>
                            <p>Products</p>
                        </a>
                    </li>
                    @endif
                    @if (auth()->user()->hasAnyPermission(['payments.view', 'payments.add', 'payments.edit', 'payments.delete']))
                    <li class="nav-item">
                        <a href="{{ route('pos.payments.index') }}" class="nav-link {{ request()->routeIs('pos.payments.*') ? 'active' : '' }}">
                            <i class="nav-icon">&#128179;</i>
                            <p>Payments</p>
                        </a>
                    </li>
                    @endif
                    @if (auth()->user()->hasPermission('reports.view'))
                    <li class="nav-item">
                        <a href="{{ route('pos.reports.index') }}" class="nav-link {{ request()->routeIs('pos.reports.*') ? 'active' : '' }}">
                            <i class="nav-icon">&#128202;</i>
                            <p>Reports</p>
                        </a>
                    </li>
                    @endif
                    @if (auth()->user()->hasPermission('roles.manage'))
                    <li class="nav-item">
                        <a href="{{ route('pos.roles.index') }}" class="nav-link {{ request()->routeIs('pos.roles.*') ? 'active' : '' }}">
                            <i class="nav-icon">&#128737;</i>
                            <p>Roles & Permissions</p>
                        </a>
                    </li>
                    @endif
                    @if (auth()->user()->hasPermission('users.manage'))
                    <li class="nav-item">
                        <a href="{{ route('pos.users.index') }}" class="nav-link {{ request()->routeIs('pos.users.*') ? 'active' : '' }}">
                            <i class="nav-icon">&#128101;</i>
                            <p>Users</p>
                        </a>
                    </li>
                    @endif
                </ul>
            </nav>
        </div>
    </aside>

    <!-- Content -->
    <div class="content-wrapper">
        @yield('content')
    </div>

    <footer class="main-footer">
        <strong>AI-Hub POS</strong> &middot; works offline, syncs when online.
    </footer>
</div>

<script src="{{ asset('libs/adminlte/vendor/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('libs/adminlte/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('libs/adminlte/js/adminlte.min.js') }}"></script>
@yield('scripts')
</body>
</html>