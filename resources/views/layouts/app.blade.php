<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PowerTrack — @yield('title', 'Tableau de bord')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary:  { DEFAULT:'#1A3A5C', light:'#2E5F8A', dark:'#0D1F33' },
                        accent:   { DEFAULT:'#F39C12', light:'#FAD7A0' },
                        success:  { DEFAULT:'#1E8449', light:'#D5F5E3' },
                        warning:  { DEFAULT:'#E67E22', light:'#FDEBD0' },
                        danger:   { DEFAULT:'#E74C3C', light:'#FADBD8' },
                        info:     { DEFAULT:'#2980B9', light:'#D6EAF8' },
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: system-ui, sans-serif; }
        .sidebar-link {
            display:flex; align-items:center; gap:10px;
            padding:16px 24px; border-radius:10px;
            font-size:.9rem; font-weight:600; color:rgba(255,255,255,0.92);
            transition:all .12s ease; min-height:84px; position:relative;
        }
        /* icons doubled from current small size (4.5px -> 9px) */
        .sidebar-link > svg, .sidebar-link .icon { width:9px !important; height:9px !important; flex-shrink:0; opacity:.95 }
        .sidebar-link .nav-label { display:inline-block; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .sidebar-link:hover { background: rgba(255,255,255,0.05); color: #fff; transform:translateX(1px); }
        .sidebar-link.active {
            background: linear-gradient(90deg, rgba(255,255,255,0.08), rgba(255,255,255,0.03));
            color: #fff; box-shadow: inset 4px 0 0 0 rgba(243,156,18,0.95);
        }
        /* Left accent bar for active link */
        .sidebar-link.active::before {
            content:''; position:absolute; left:0; top:6px; bottom:6px; width:6px; border-radius:6px; background:linear-gradient(180deg,#F39C12,#FAD7A0);
        }
        /* Make sidebar nav scrollable and compact */
        aside { min-width:16rem; }
        aside nav { max-height: calc(100vh - 220px); overflow-y:auto; padding-right:6px; }
        aside nav::-webkit-scrollbar { width:12px; }
        aside nav::-webkit-scrollbar-thumb { background:rgba(255,255,255,0.07); border-radius:6px; }
        .card { background:white; border-radius:1rem; box-shadow:0 1px 3px rgba(0,0,0,.07); border:1px solid #f0f0f0; padding:1.25rem; }
        .btn-primary { background:#1A3A5C; color:white; padding:.6rem 1.2rem; border-radius:.75rem; font-weight:600; font-size:.875rem; transition:opacity .2s; cursor:pointer; }
        .btn-primary:hover { opacity:.9; }
        .btn-secondary { border:2px solid #1A3A5C; color:#1A3A5C; padding:.55rem 1.2rem; border-radius:.75rem; font-weight:600; font-size:.875rem; transition:all .2s; cursor:pointer; background:transparent; }
        .btn-secondary:hover { background:#1A3A5C; color:white; }
        .btn-danger { background:#E74C3C; color:white; padding:.6rem 1.2rem; border-radius:.75rem; font-weight:600; font-size:.875rem; transition:opacity .2s; cursor:pointer; }
        .btn-danger:hover { opacity:.9; }
        .input-field { width:100%; border:1.5px solid #e5e7eb; border-radius:.75rem; padding:.625rem 1rem; font-size:.875rem; outline:none; transition:border .2s; }
        .input-field:focus { border-color:#1A3A5C; box-shadow:0 0 0 3px rgba(26,58,92,.1); }
        .badge-success { display:inline-flex; align-items:center; background:#D5F5E3; color:#1E8449; font-size:.7rem; font-weight:700; padding:.2rem .6rem; border-radius:9999px; }
        .badge-warning { display:inline-flex; align-items:center; background:#FDEBD0; color:#E67E22; font-size:.7rem; font-weight:700; padding:.2rem .6rem; border-radius:9999px; }
        .badge-danger  { display:inline-flex; align-items:center; background:#FADBD8; color:#E74C3C; font-size:.7rem; font-weight:700; padding:.2rem .6rem; border-radius:9999px; }
        .badge-info    { display:inline-flex; align-items:center; background:#D6EAF8; color:#2980B9; font-size:.7rem; font-weight:700; padding:.2rem .6rem; border-radius:9999px; }
        ::-webkit-scrollbar { width:5px; } ::-webkit-scrollbar-thumb { background:#d1d5db; border-radius:3px; }
    </style>
</head>
<body class="bg-gray-50">
<div class="flex h-screen overflow-hidden">

    <!-- Sidebar -->
    <aside class="w-64 bg-primary-dark flex flex-col flex-shrink-0 overflow-y-auto">
        <!-- Logo -->
        <div class="flex items-center gap-2 px-5 py-5 border-b border-white/10">
            <svg class="w-7 h-7 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
            </svg>
            <span class="text-white font-extrabold text-xl tracking-tight">PowerTrack</span>
        </div>

        <!-- User -->
        <div class="px-5 py-4 border-b border-white/10">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-amber-400/20 flex items-center justify-center">
                    <span class="text-amber-400 font-bold text-sm">{{ strtoupper(substr(auth()->user()->nom,0,1)) }}</span>
                </div>
                <div class="overflow-hidden">
                    <p class="text-white text-sm font-semibold truncate">{{ auth()->user()->nom }}</p>
                    <p class="text-white/50 text-xs">{{ auth()->user()->type_compte }}</p>
                </div>
            </div>
        </div>

        <!-- Nav -->
        <nav class="flex-1 px-3 py-4 space-y-1">
            @php $route = request()->path(); @endphp
            <a href="/"             class="sidebar-link {{ $route === '/' ? 'active' : '' }}">
                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span class="nav-label">Dashboard</span>
            </a>
            <a href="/recepteurs"   class="sidebar-link {{ str_starts_with($route,'recepteurs') ? 'active' : '' }}">
                <svg class="w-4.5 h-4.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/></svg>
                <span class="nav-label">Récepteurs</span>
            </a>
            <a href="/saisie"       class="sidebar-link {{ $route === 'saisie' ? 'active' : '' }}">
                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span class="nav-label">Saisie</span>
            </a>
            <a href="/analyse"      class="sidebar-link {{ $route === 'analyse' ? 'active' : '' }}">
                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span class="nav-label">Analyse</span>
            </a>
            <a href="/prediction"   class="sidebar-link {{ $route === 'prediction' ? 'active' : '' }}">
                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                <span class="nav-label">Prédiction</span>
            </a>
            <a href="/alertes"      class="sidebar-link {{ $route === 'alertes' ? 'active' : '' }}">
                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                <span class="nav-label">Alertes</span>
                @if(auth()->user()->alertes()->count() > 0)
                    <span class="ml-auto bg-red-500 text-white text-xs rounded-full px-1.5 py-0.5">{{ auth()->user()->alertes()->count() }}</span>
                @endif
            </a>
            <a href="/profil"       class="sidebar-link {{ $route === 'profil' ? 'active' : '' }}">
                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                <span class="nav-label">Profil</span>
            </a>
        </nav>

        <!-- Logout -->
        <div class="p-3 border-t border-white/10">
            <form method="POST" action="/logout">
                @csrf
                <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold text-white/60 hover:bg-white/10 hover:text-red-400 transition-all">
                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Se déconnecter
                </button>
            </form>
        </div>
    </aside>

    <!-- Contenu principal -->
    <div class="flex flex-col flex-1 overflow-hidden">
        <!-- Topbar -->
        <header class="bg-white border-b border-gray-100 px-6 py-4 flex items-center justify-between flex-shrink-0">
            <h1 class="text-lg font-bold text-gray-900">@yield('title', 'Dashboard')</h1>
            <div class="flex items-center gap-4">
                <span class="text-sm text-gray-400">{{ auth()->user()->cout_kwh }} {{ auth()->user()->devise }}/kWh</span>
                <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center">
                    <span class="text-primary text-sm font-bold">{{ strtoupper(substr(auth()->user()->nom,0,1)) }}</span>
                </div>
            </div>
        </header>

        <!-- Flash messages -->
        <div class="px-6 pt-4">
            @if(session('success'))
                <div class="flex items-center gap-2 bg-success-light text-success text-sm font-semibold px-4 py-3 rounded-xl mb-2">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    {{ session('success') }}
                </div>
            @endif
            @if($errors->any())
                <div class="bg-danger-light text-danger text-sm font-semibold px-4 py-3 rounded-xl mb-2">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <!-- Page content -->
        <main class="flex-1 overflow-y-auto px-6 pb-6">
            @yield('content')
        </main>
    </div>
</div>
@yield('scripts')
</body>
</html>