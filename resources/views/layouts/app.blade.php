<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tsena Mora — @yield('title', 'Dashboard')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <style>
        :root {
            --sidebar-bg:#1B5E20;
            --sidebar-hover:#2E7D32;
            --sidebar-active:#388E3C;
            --accent:#FFB300;
        }

    body {
    background:#F1F8E9;
    min-height:100vh;
    margin:0;
    overflow-x:hidden;
}

*{
    box-sizing:border-box;
}
.pagination-icon{
    width:14px;
    height:14px;
}
        /* ===== LAYOUT FIX (IMPORTANT) ===== */
    .layout-wrapper{
    min-height:100vh;
}

        /* ===== SIDEBAR ===== */
        .sidebar {
            width:230px;
            background:var(--sidebar-bg);
            color:#fff;
            position:fixed;
            top:0;
            left:0;
            bottom:0;
            overflow-y:auto;
            box-shadow:2px 0 8px rgba(0,0,0,.15);
            z-index:100;
        }

        .sidebar .brand {
            padding:1.25rem;
            border-bottom:1px solid rgba(255,255,255,.12);
        }

        .sidebar .brand h5 {
            color:var(--accent);
            font-weight:700;
            margin:0;
        }

        .sidebar .brand small {
            color:rgba(255,255,255,.55);
        }

        .sidebar .nav-label {
            padding:.9rem 1.25rem .2rem;
            font-size:.63rem;
            text-transform:uppercase;
            letter-spacing:.1em;
            color:rgba(255,255,255,.38);
            font-weight:700;
        }

        .sidebar .nav-link {
            color:rgba(255,255,255,.8);
            padding:.6rem 1.25rem;
            display:flex;
            align-items:center;
            gap:.55rem;
            font-size:.88rem;
            transition:.2s;
        }

        .sidebar .nav-link:hover {
            background:var(--sidebar-hover);
            color:#fff;
        }

        .sidebar .nav-link.active {
            background:var(--sidebar-active);
            color:#fff;
            border-left:3px solid var(--accent);
        }

        .sidebar .user-card {
            margin:.75rem;
            padding:.7rem 1rem;
            background:rgba(255, 255, 255, 0.29);
            border-radius:10px;
        }

        .sidebar .avatar {
            width:30px;height:30px;
            background:var(--accent);
            border-radius:50%;
            display:flex;
            align-items:center;
            justify-content:center;
            font-weight:700;
            color:#1B5E20;
        }

        /* ===== MAIN CONTENT FIX ===== */
.main-content {
    margin-left:230px;
    width:calc(100% - 230px);
    padding:1.5rem;
    min-height:100vh;
}

        /* ===== TOPBAR ===== */
        .topbar {
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin-bottom:1.25rem;
            padding-bottom:.85rem;
            border-bottom:1px solid #C8E6C9;
        }

        .notif-badge {
            position:relative;
        }

        .notif-badge .badge {
            position:absolute;
            top:-4px;
            right:-4px;
            font-size:.6rem;
        }

        /* ===== MOBILE FIX ===== */
      @media(max-width:768px){

    .sidebar{
        position:fixed;
        left:-230px;
        width:230px;
        transition:all .3s ease;
    }

    .sidebar.show{
        left:0;
    }

    .main-content{
        margin-left:0;
        width:100%;
        padding:1rem;
    }

    .topbar{
        flex-direction:column;
        align-items:flex-start;
        gap:10px;
    }
}
    </style>
</head>

<body>

<div class="layout-wrapper">

    {{-- SIDEBAR --}}
    @auth
    <div class="sidebar">

        <div class="brand">
            <h5><i class="bi bi-bag-heart-fill me-1"></i>Tsena Mora</h5>
            <small>Marketplace Malgache</small>
        </div>

        <div class="user-card d-flex align-items-center gap-2">
            <div class="avatar">
                {{ strtoupper(substr(auth()->user()->name,0,1)) }}
            </div>
            <div>
                <div style="font-weight:600;font-size:.82rem;">
                    {{ auth()->user()->name }}
                </div>
                <div style="font-size:.68rem;color:rgba(255,255,255,.5);">
                    {{ ucfirst(auth()->user()->role) }}
                </div>
            </div>
        </div>

        <div class="nav-label">Boutique</div>

        <nav class="nav flex-column">
            <a class="nav-link {{ request()->routeIs('vendor.dashboard') ? 'active':'' }}"
               href="{{ route('vendor.dashboard') }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>

            <a class="nav-link {{ request()->routeIs('vendor.products.*') ? 'active':'' }}"
               href="{{ route('vendor.products.index') }}">
                <i class="bi bi-box-seam"></i> Produits
            </a>

            <a class="nav-link {{ request()->routeIs('vendor.orders.*') ? 'active':'' }}"
               href="{{ route('vendor.orders.index') }}">
                <i class="bi bi-receipt"></i> Commandes
            </a>

            <a class="nav-link {{ request()->routeIs('vendor.statistics') ? 'active':'' }}"
               href="{{ route('vendor.statistics') }}">
                <i class="bi bi-bar-chart-line"></i> Statistiques
            </a>
        </nav>

        @if(auth()->user()->isAdmin())
        <div class="nav-label">Admin</div>
        <nav class="nav flex-column">
            <a class="nav-link {{ request()->routeIs('admin.reviews.*') ? 'active':'' }}"
               href="{{ route('admin.reviews.index') }}">
                <i class="bi bi-shield-check"></i> Modération avis
            </a>
        </nav>
        @endif

        <div class="nav-label">Compte</div>
        <nav class="nav flex-column mb-4">
            <a class="nav-link" href="{{ route('logout') }}"
               onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                <i class="bi bi-box-arrow-left"></i> Déconnexion
            </a>
        </nav>

        <form id="logout-form" method="POST" action="{{ route('logout') }}" class="d-none">
            @csrf
        </form>

    </div>
    @endauth

    {{-- MAIN --}}
    <div class="main-content">

        @auth
        <div class="topbar">

            <div>
                <h5 class="mb-0 fw-bold text-success">
                    @yield('page-title','Dashboard')
                </h5>
                <small class="text-muted">
                    @yield('page-subtitle','')
                </small>
            </div>

            <div class="d-flex align-items-center gap-3">

                {{-- NOTIFICATIONS --}}
                @php
                    $unread = auth()->user()->unreadNotifications()->count();
                @endphp

                <div class="dropdown notif-badge">
                    <button class="btn btn-sm btn-light border position-relative" data-bs-toggle="dropdown">
                        <i class="bi bi-bell"></i>
                        @if($unread > 0)
                        <span class="badge bg-danger rounded-pill">{{ $unread }}</span>
                        @endif
                    </button>

                    <ul class="dropdown-menu dropdown-menu-end shadow"
                        style="min-width:300px;max-height:350px;overflow-y:auto;">

                        <li class="px-3 py-2 border-bottom">
                            <small class="fw-bold text-muted text-uppercase">
                                Notifications
                            </small>
                        </li>

                        @forelse(auth()->user()->notifications()->latest()->take(8)->get() as $notif)
                        <li>
                            <a class="dropdown-item py-2 {{ is_null($notif->read_at) ? 'fw-semibold' : '' }}">
                                <div style="font-size:.82rem;">
                                    {{ $notif->data['message'] ?? 'Notification' }}
                                </div>
                                <small class="text-muted">
                                    {{ $notif->created_at->diffForHumans() }}
                                </small>
                            </a>
                        </li>
                        @empty
                        <li class="text-center py-3 text-muted">
                            Aucune notification
                        </li>
                        @endforelse
                    </ul>
                </div>

                <span class="badge bg-success-subtle text-success border">
                    <i class="bi bi-circle-fill me-1" style="font-size:.45rem;"></i>
                    En ligne
                </span>

            </div>
        </div>
        @endauth

        @yield('content')

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

@yield('scripts')

</body>
</html>