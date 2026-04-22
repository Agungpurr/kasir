<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Shop') }} — @yield('title', 'Dashboard')</title>

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Bootstrap Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --sidebar-w: 240px;
            --primary:   #1D9E75;
            --primary-dk:#0F6E56;
            --sidebar-bg:#0f1923;
            --sidebar-tx:#a9b5c2;
            --topbar-h:  56px;
        }

        body { background: #f4f6f9; font-family: 'Segoe UI', sans-serif; }

        /* ── Sidebar ── */
        #sidebar {
            position: fixed; top: 0; left: 0;
            width: var(--sidebar-w); height: 100vh;
            background: var(--sidebar-bg);
            display: flex; flex-direction: column;
            z-index: 1040; transition: transform .25s;
        }
        .sidebar-brand {
            height: var(--topbar-h); display: flex; align-items: center;
            gap: 10px; padding: 0 20px;
            border-bottom: 1px solid rgba(255,255,255,.07);
        }
        .brand-icon {
            width: 32px; height: 32px; border-radius: 8px;
            background: var(--primary); display: flex;
            align-items: center; justify-content: center;
            font-size: 16px; color: #fff; flex-shrink: 0;
        }
        .brand-name { color: #fff; font-weight: 600; font-size: 15px; }

        .sidebar-nav { flex: 1; overflow-y: auto; padding: 12px 0; }
        .nav-section {
            font-size: 10px; font-weight: 600; letter-spacing: .08em;
            color: rgba(255,255,255,.3); padding: 14px 20px 6px;
            text-transform: uppercase;
        }
        .sidebar-link {
            display: flex; align-items: center; gap: 10px;
            padding: 9px 20px; color: var(--sidebar-tx);
            text-decoration: none; font-size: 13.5px;
            transition: all .15s; border-radius: 0;
            position: relative;
        }
        .sidebar-link:hover  { color: #fff; background: rgba(255,255,255,.06); }
        .sidebar-link.active { color: #fff; background: rgba(29,158,117,.18); }
        .sidebar-link.active::before {
            content: ''; position: absolute; left: 0; top: 0;
            width: 3px; height: 100%; background: var(--primary);
            border-radius: 0 3px 3px 0;
        }
        .sidebar-link i { font-size: 16px; width: 20px; text-align: center; }

        .sidebar-footer {
            padding: 14px 20px;
            border-top: 1px solid rgba(255,255,255,.07);
        }
        .user-card {
            display: flex; align-items: center; gap: 10px;
        }
        .user-avatar {
            width: 34px; height: 34px; border-radius: 50%;
            background: var(--primary); color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 600; flex-shrink: 0;
        }
        .user-name  { color: #fff; font-size: 13px; font-weight: 500; }
        .user-role  { color: var(--sidebar-tx); font-size: 11px; }

        /* ── Main area ── */
        #main-wrap {
            margin-left: var(--sidebar-w);
            min-height: 100vh;
            display: flex; flex-direction: column;
        }
        .topbar {
            height: var(--topbar-h); background: #fff;
            border-bottom: 1px solid #e9ecef;
            display: flex; align-items: center;
            justify-content: space-between;
            padding: 0 24px; position: sticky; top: 0; z-index: 100;
        }
        .topbar-title { font-weight: 600; font-size: 15px; color: #1a1f2e; }
        .main-content { padding: 24px; flex: 1; }

        /* ── Alert / Flash ── */
        .flash-alert { border-radius: 10px; font-size: 14px; }

        /* ── Card ── */
        .card { border: none; border-radius: 12px; box-shadow: 0 1px 4px rgba(0,0,0,.07); }
        .card-header { background: #fff; border-bottom: 1px solid #f0f0f0; border-radius: 12px 12px 0 0 !important; }

        /* ── Stat card ── */
        .stat-card {
            background: #fff; border-radius: 12px;
            padding: 20px; display: flex; align-items: center; gap: 16px;
            box-shadow: 0 1px 4px rgba(0,0,0,.07);
        }
        .stat-icon {
            width: 48px; height: 48px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px; flex-shrink: 0;
        }
        .stat-label { font-size: 12px; color: #6c757d; margin-bottom: 2px; }
        .stat-value { font-size: 22px; font-weight: 700; color: #1a1f2e; line-height: 1; }
        .stat-sub   { font-size: 11px; color: var(--primary); margin-top: 3px; }

        /* ── Badge stok ── */
        .badge-ok   { background: #e6f9f2; color: #0f6e56; }
        .badge-low  { background: #fff8e6; color: #a16207; }
        .badge-out  { background: #fef2f2; color: #b91c1c; }

        /* ── Tombol utama ── */
        .btn-primary  { background: var(--primary); border-color: var(--primary); }
        .btn-primary:hover { background: var(--primary-dk); border-color: var(--primary-dk); }
        .btn-outline-primary { color: var(--primary); border-color: var(--primary); }
        .btn-outline-primary:hover { background: var(--primary); border-color: var(--primary); }

        /* ── Table ── */
        .table > thead { background: #f8f9fa; }
        .table > thead th { font-size: 12px; font-weight: 600; color: #6c757d; text-transform: uppercase; letter-spacing: .04em; border: none; }
        .table > tbody td { font-size: 13.5px; vertical-align: middle; }

        /* ── Responsive ── */
        @media(max-width:768px) {
            #sidebar { transform: translateX(-100%); }
            #sidebar.open { transform: translateX(0); }
            #main-wrap { margin-left: 0; }
        }
    </style>
    @stack('styles')
</head>
<body>

{{-- ═══════════════ SIDEBAR ═══════════════ --}}
<nav id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon"><i class="bi bi-bag-check"></i></div>
        <span class="brand-name">Shop</span>
    </div>

    <div class="sidebar-nav">
        <div class="nav-section">Utama</div>

        <a href="{{ route('dashboard') }}"
           class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid"></i> Dashboard
        </a>

        <a href="{{ route('transactions.index') }}"
           class="sidebar-link {{ request()->routeIs('transactions.*') ? 'active' : '' }}">
            <i class="bi bi-cart3"></i> Kasir
        </a>

        <a href="{{ route('transactions.history') }}"
           class="sidebar-link {{ request()->routeIs('transactions.history') ? 'active' : '' }}">
            <i class="bi bi-clock-history"></i> Riwayat Transaksi
        </a>

        {{-- Menu khusus admin --}}
        @auth
            @if(auth()->user() && auth()->user()->role === 'admin')
                <div class="nav-section">Manajemen</div>
                
                <a href="{{ route('products.index') }}"
                   class="sidebar-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
                    <i class="bi bi-box-seam"></i> Produk
                </a>

                <a href="{{ route('categories.index') }}"
                   class="sidebar-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                    <i class="bi bi-tags"></i> Kategori
                </a>

                <div class="nav-section">Laporan</div>

                <a href="{{ route('reports.daily') }}"
                   class="sidebar-link {{ request()->routeIs('reports.daily') ? 'active' : '' }}">
                    <i class="bi bi-calendar-day"></i> Harian
                </a>

                <a href="{{ route('reports.monthly') }}"
                   class="sidebar-link {{ request()->routeIs('reports.monthly') ? 'active' : '' }}">
                    <i class="bi bi-calendar-month"></i> Bulanan
                </a>

                <a href="{{ route('reports.best-selling') }}"
                   class="sidebar-link {{ request()->routeIs('reports.best-selling') ? 'active' : '' }}">
                    <i class="bi bi-trophy"></i> Produk Terlaris
                </a>
            @endif
        @endauth
    </div>

    @auth
    <div class="sidebar-footer">
        <div class="user-card">
            <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div>
            <div>
                <div class="user-name">{{ auth()->user()->name }}</div>
                <div class="user-role">{{ ucfirst(auth()->user()->role) }}</div>
            </div>
            <a href="{{ route('logout') }}" class="ms-auto text-secondary"
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
               title="Logout"><i class="bi bi-box-arrow-right"></i></a>
        </div>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
    </div>
    @endauth
</nav>

{{-- ═══════════════ MAIN ═══════════════ --}}
<div id="main-wrap">
    <div class="topbar">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-sm d-md-none" id="sidebarToggle">
                <i class="bi bi-list fs-5"></i>
            </button>
            <span class="topbar-title">@yield('title', 'Dashboard')</span>
        </div>
        <div class="d-flex align-items-center gap-2 text-muted" style="font-size:13px">
            <i class="bi bi-clock"></i>
            <span id="clock"></span>
        </div>
    </div>

    <div class="main-content">
        {{-- Flash messages --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible flash-alert mb-3" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible flash-alert mb-3" role="alert">
                <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Jam real-time
    function updateClock() {
        const now = new Date();
        const clockEl = document.getElementById('clock');
        if(clockEl) {
            clockEl.textContent = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        }
    }
    updateClock(); 
    setInterval(updateClock, 1000);

    // Sidebar toggle mobile
    const toggleBtn = document.getElementById('sidebarToggle');
    if(toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            document.getElementById('sidebar').classList.toggle('open');
        });
    }
</script>
@stack('scripts')
</body>
</html>