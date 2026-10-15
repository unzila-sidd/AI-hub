<x-app-layout>
    @php
        $user = auth()->user();

        $hour = (int) now()->format('G');
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');

        $canSales = $user->hasAnyPermission(['sales.view', 'sales.create']);
        $canProducts = $user->hasAnyPermission(['products.view', 'products.add', 'products.edit', 'products.delete']);
        $canPayments = $user->hasPermission('payments.view');
        $canReports = $user->hasPermission('reports.view');
        $canRoles = $user->hasPermission('roles.manage');
        $canUsers = $user->hasPermission('users.manage');

        $todaySales = $canSales ? App\Models\Sale::whereDate('created_at', today())->count() : null;
        $todayRevenue = $canSales ? (float) App\Models\Sale::whereDate('created_at', today())->sum('total') : null;
        $productsCount = $canProducts ? App\Models\Product::count() : null;
        $lowStockCount = $canProducts ? App\Models\Product::lowStock()->count() : null;
    @endphp

    <x-slot name="header">
        <h2 class="h5 fw-bold mb-0">{{ $greeting }}, {{ $user->name }}!</h2>
        <span class="badge rounded-pill text-bg-primary ms-2">{{ $user->role?->name ?? 'User' }}</span>
    </x-slot>

    <style>
        .stat-card {
            background: #fff;
            border: 1px solid rgba(15, 23, 42, .06);
            border-radius: 1rem;
            padding: 1rem 1.25rem;
            height: 100%;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .stat-card .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            color: #fff;
            flex-shrink: 0;
        }
        .stat-card .stat-value { font-size: 1.4rem; font-weight: 700; line-height: 1.1; color: #0f172a; }
        .stat-card .stat-label { font-size: .8rem; color: #64748b; }

        .hub-tile {
            display: block;
            background: #fff;
            border: 1px solid rgba(15, 23, 42, .06);
            border-radius: 1rem;
            padding: 1.25rem;
            height: 100%;
            text-decoration: none;
            color: #0f172a;
            transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease;
        }
        .hub-tile:hover {
            transform: translateY(-3px);
            box-shadow: 0 14px 28px -10px rgba(15, 23, 42, .18);
            border-color: rgba(37, 99, 235, .25);
            color: #0f172a;
        }
        .hub-tile .tile-icon {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            color: #fff;
            margin-bottom: .9rem;
        }
        .hub-tile .tile-title { font-weight: 600; margin-bottom: .15rem; }
        .hub-tile .tile-desc { font-size: .8rem; color: #64748b; margin-bottom: 0; }
        .hub-tile .bi-arrow-right { color: #94a3b8; font-size: .85rem; }
    </style>

    <div class="container-fluid py-4 px-4">
        <div class="row g-3 mb-4">
            @if ($canSales)
                <div class="col-md-6 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #0ea5e9, #2563eb);">
                            <i class="bi bi-bag-check"></i>
                        </div>
                        <div>
                            <div class="stat-value">{{ $todaySales }}</div>
                            <div class="stat-label">Sales today</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #22c55e, #16a34a);">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                        <div>
                            <div class="stat-value">${{ number_format($todayRevenue, 2) }}</div>
                            <div class="stat-label">Revenue today</div>
                        </div>
                    </div>
                </div>
            @endif

            @if ($canProducts)
                <div class="col-md-6 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #8b5cf6, #6366f1);">
                            <i class="bi bi-box-seam"></i>
                        </div>
                        <div>
                            <div class="stat-value">{{ $productsCount }}</div>
                            <div class="stat-label">Products</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #f59e0b, #dc2626);">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                        <div>
                            <div class="stat-value">{{ $lowStockCount }}</div>
                            <div class="stat-label">Low stock alerts</div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <h2 class="h6 text-uppercase text-muted fw-semibold mb-3">Quick access</h2>
        <div class="row g-3">
            <div class="col-md-6 col-lg-4 col-xxl-3">
                <a href="{{ route('chat') }}" class="hub-tile">
                    <div class="tile-icon" style="background: linear-gradient(135deg, #06b6d4, #2563eb);">
                        <i class="bi bi-chat-dots"></i>
                    </div>
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="tile-title">AI Chat</div>
                            <p class="tile-desc">Chat with the AI assistant</p>
                        </div>
                        <i class="bi bi-arrow-right"></i>
                    </div>
                </a>
            </div>

            @if ($canSales)
                <div class="col-md-6 col-lg-4 col-xxl-3">
                    <a href="{{ route('pos.index') }}" class="hub-tile">
                        <div class="tile-icon" style="background: linear-gradient(135deg, #22c55e, #16a34a);">
                            <i class="bi bi-bag-check"></i>
                        </div>
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="tile-title">POS Terminal</div>
                                <p class="tile-desc">Create a new sale at the counter</p>
                            </div>
                            <i class="bi bi-arrow-right"></i>
                        </div>
                    </a>
                </div>
                <div class="col-md-6 col-lg-4 col-xxl-3">
                    <a href="{{ route('pos.sales') }}" class="hub-tile">
                        <div class="tile-icon" style="background: linear-gradient(135deg, #14b8a6, #0d9488);">
                            <i class="bi bi-receipt"></i>
                        </div>
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="tile-title">Sales History</div>
                                <p class="tile-desc">Browse past invoices</p>
                            </div>
                            <i class="bi bi-arrow-right"></i>
                        </div>
                    </a>
                </div>
            @endif

            @if ($canProducts)
                <div class="col-md-6 col-lg-4 col-xxl-3">
                    <a href="{{ route('pos.products.index') }}" class="hub-tile">
                        <div class="tile-icon" style="background: linear-gradient(135deg, #8b5cf6, #6366f1);">
                            <i class="bi bi-box-seam"></i>
                        </div>
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="tile-title">Products</div>
                                <p class="tile-desc">Manage inventory and stock</p>
                            </div>
                            <i class="bi bi-arrow-right"></i>
                        </div>
                    </a>
                </div>
            @endif

            @if ($canPayments)
                <div class="col-md-6 col-lg-4 col-xxl-3">
                    <a href="{{ route('pos.payments.index') }}" class="hub-tile">
                        <div class="tile-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                            <i class="bi bi-credit-card"></i>
                        </div>
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="tile-title">Payments</div>
                                <p class="tile-desc">Record and review payments</p>
                            </div>
                            <i class="bi bi-arrow-right"></i>
                        </div>
                    </a>
                </div>
            @endif

            @if ($canReports)
                <div class="col-md-6 col-lg-4 col-xxl-3">
                    <a href="{{ route('pos.reports.index') }}" class="hub-tile">
                        <div class="tile-icon" style="background: linear-gradient(135deg, #ef4444, #b91c1c);">
                            <i class="bi bi-bar-chart"></i>
                        </div>
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="tile-title">Reports</div>
                                <p class="tile-desc">Sales, top products, methods</p>
                            </div>
                            <i class="bi bi-arrow-right"></i>
                        </div>
                    </a>
                </div>
            @endif

            @if ($canRoles)
                <div class="col-md-6 col-lg-4 col-xxl-3">
                    <a href="{{ route('pos.roles.index') }}" class="hub-tile">
                        <div class="tile-icon" style="background: linear-gradient(135deg, #64748b, #334155);">
                            <i class="bi bi-person-gear"></i>
                        </div>
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="tile-title">Roles &amp; Permissions</div>
                                <p class="tile-desc">Control what each role can do</p>
                            </div>
                            <i class="bi bi-arrow-right"></i>
                        </div>
                    </a>
                </div>
            @endif

            @if ($canUsers)
                <div class="col-md-6 col-lg-4 col-xxl-3">
                    <a href="{{ route('pos.users.index') }}" class="hub-tile">
                        <div class="tile-icon" style="background: linear-gradient(135deg, #06b6d4, #0e7490);">
                            <i class="bi bi-people"></i>
                        </div>
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="tile-title">Users</div>
                                <p class="tile-desc">Add staff and assign roles</p>
                            </div>
                            <i class="bi bi-arrow-right"></i>
                        </div>
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>