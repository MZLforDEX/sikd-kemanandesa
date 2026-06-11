<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#4f46e5">
    <link rel="manifest" href="/manifest.json">
    <title>@yield('title', 'Sistem Keamanan Desa') - SIKD</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
        }

        @keyframes marker-pulse {
            0% {
                transform: scale(0.8);
                opacity: 0.5;
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
            }
            70% {
                transform: scale(1);
                opacity: 1;
                box-shadow: 0 0 0 8px rgba(239, 68, 68, 0);
            }
            100% {
                transform: scale(0.8);
                opacity: 0.5;
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0);
            }
        }

        .blinking-marker {
            background-color: #ef4444;
            border: 2px solid #ffffff;
            border-radius: 50%;
            width: 14px;
            height: 14px;
            box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
            animation: marker-pulse 1.4s infinite;
            display: inline-block;
        }
        
        .blinking-marker-container {
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Leaflet JS Map CDN (GIS Integration) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
</head>
<body class="h-full flex flex-col text-slate-800">

    <!-- Red-White Flag Stripe Ribbon -->
    <div class="red-white-stripe"></div>

    <!-- Navbar -->
    <header class="glass-panel sticky top-0 z-40 border-b border-slate-200/50 shadow-premium-sm backdrop-blur-md">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 justify-between items-center">
                
                <!-- Logo & Brand -->
                <div class="flex items-center space-x-3">
                    <a href="/" class="flex items-center space-x-3 group">
                        <div class="bg-indigo-600 text-white p-2.5 rounded-xl shadow-premium-sm flex items-center justify-center transition-premium group-hover:bg-indigo-700">
                            <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                        </div>
                        <div>
                            <span class="text-xs font-extrabold tracking-tight text-slate-900 block leading-tight uppercase font-sans">PEMERINTAH DESA AWA</span>
                            <span class="text-[9px] text-slate-500 font-bold tracking-wider uppercase block">SIKD · KECAMATAN SAMATURU</span>
                        </div>
                    </a>
                </div>

                <!-- Right Navigation -->
                <div class="flex items-center space-x-3">
                    @auth
                        <!-- Dashboard Links -->
                        <div class="hidden md:flex items-center space-x-1 mr-2">
                            @if(auth()->user()->hasRole('warga'))
                                <a href="{{ route('warga.dashboard') }}" class="px-3 py-2 rounded-xl text-xs font-bold flex items-center space-x-1.5 transition-premium {{ request()->routeIs('warga.dashboard*') ? 'text-indigo-650 bg-indigo-50/80' : 'text-slate-605 hover:text-indigo-650 hover:bg-slate-50/60' }}">
                                    <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                                    <span>Dashboard</span>
                                </a>
                                <a href="{{ route('warga.reports.create') }}" class="px-3 py-2 rounded-xl text-xs font-bold flex items-center space-x-1.5 transition-premium {{ request()->routeIs('warga.reports.create*') ? 'text-indigo-650 bg-indigo-50/80' : 'text-slate-605 hover:text-indigo-650 hover:bg-slate-50/60' }}">
                                    <i data-lucide="file-plus" class="w-4 h-4"></i>
                                    <span>Buat Laporan</span>
                                </a>
                                <a href="{{ route('warga.ronda.schedules') }}" class="px-3 py-2 rounded-xl text-xs font-bold flex items-center space-x-1.5 transition-premium {{ request()->routeIs('warga.ronda.schedules*') ? 'text-indigo-650 bg-indigo-50/80' : 'text-slate-605 hover:text-indigo-650 hover:bg-slate-50/60' }}">
                                    <i data-lucide="calendar" class="w-4 h-4"></i>
                                    <span>Jadwal Ronda</span>
                                </a>
                                <a href="{{ route('warga.ronda.incidents.index') }}" class="px-3 py-2 rounded-xl text-xs font-bold flex items-center space-x-1.5 transition-premium {{ request()->routeIs('warga.ronda.incidents.*') ? 'text-indigo-650 bg-indigo-50/80' : 'text-slate-605 hover:text-indigo-650 hover:bg-slate-50/60' }}">
                                    <i data-lucide="clipboard-list" class="w-4 h-4"></i>
                                    <span>Tugas Ronda</span>
                                </a>
                            @elseif(auth()->user()->hasRole('perangkat'))
                                <a href="{{ route('perangkat.dashboard') }}" class="px-3 py-2 rounded-xl text-xs font-bold flex items-center space-x-1.5 transition-premium {{ request()->routeIs('perangkat.dashboard*') ? 'text-indigo-650 bg-indigo-50/80' : 'text-slate-605 hover:text-indigo-650 hover:bg-slate-50/60' }}">
                                    <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                                    <span>Dashboard</span>
                                </a>
                                <a href="{{ route('perangkat.reports.index') }}" class="px-3 py-2 rounded-xl text-xs font-bold flex items-center space-x-1.5 transition-premium {{ request()->routeIs('perangkat.reports.*') ? 'text-indigo-650 bg-indigo-50/80' : 'text-slate-605 hover:text-indigo-650 hover:bg-slate-50/60' }}">
                                    <i data-lucide="file-text" class="w-4 h-4"></i>
                                    <span>Laporan</span>
                                </a>
                                <a href="{{ route('perangkat.incidents.index') }}" class="px-3 py-2 rounded-xl text-xs font-bold flex items-center space-x-1.5 transition-premium {{ request()->routeIs('perangkat.incidents.*') ? 'text-indigo-650 bg-indigo-50/80' : 'text-slate-605 hover:text-indigo-650 hover:bg-slate-50/60' }}">
                                    <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                                    <span>Kejadian</span>
                                </a>
                                <a href="{{ route('perangkat.schedules') }}" class="px-3 py-2 rounded-xl text-xs font-bold flex items-center space-x-1.5 transition-premium {{ request()->routeIs('perangkat.schedules*') ? 'text-indigo-650 bg-indigo-50/80' : 'text-slate-605 hover:text-indigo-650 hover:bg-slate-50/60' }}">
                                    <i data-lucide="calendar" class="w-4 h-4"></i>
                                    <span>Jadwal Patroli</span>
                                </a>
                                <a href="{{ route('perangkat.users') }}" class="px-3 py-2 rounded-xl text-xs font-bold flex items-center space-x-1.5 transition-premium {{ request()->routeIs('perangkat.users*') ? 'text-indigo-650 bg-indigo-50/80' : 'text-slate-605 hover:text-indigo-650 hover:bg-slate-50/60' }}">
                                    <i data-lucide="users" class="w-4 h-4"></i>
                                    <span>Pengguna</span>
                                </a>
                            @elseif(auth()->user()->hasRole('kades'))
                                <a href="{{ route('kades.dashboard') }}" class="px-3 py-2 rounded-xl text-xs font-bold flex items-center space-x-1.5 transition-premium {{ request()->routeIs('kades.dashboard*') ? 'text-indigo-650 bg-indigo-50/80' : 'text-slate-605 hover:text-indigo-650 hover:bg-slate-50/60' }}">
                                    <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                                    <span>Dashboard</span>
                                </a>
                                <a href="{{ route('kades.reports') }}" class="px-3 py-2 rounded-xl text-xs font-bold flex items-center space-x-1.5 transition-premium {{ request()->routeIs('kades.reports*') ? 'text-indigo-650 bg-indigo-50/80' : 'text-slate-605 hover:text-indigo-650 hover:bg-slate-50/60' }}">
                                    <i data-lucide="file-text" class="w-4 h-4"></i>
                                    <span>Laporan</span>
                                </a>
                                <a href="{{ route('kades.rekap') }}" class="px-3 py-2 rounded-xl text-xs font-bold flex items-center space-x-1.5 transition-premium {{ request()->routeIs('kades.rekap*') ? 'text-indigo-650 bg-indigo-50/80' : 'text-slate-605 hover:text-indigo-650 hover:bg-slate-50/60' }}">
                                    <i data-lucide="bar-chart-3" class="w-4 h-4"></i>
                                    <span>Rekap & Laporan</span>
                                </a>
                                <a href="{{ route('kades.tren') }}" class="px-3 py-2 rounded-xl text-xs font-bold flex items-center space-x-1.5 transition-premium {{ request()->routeIs('kades.tren*') ? 'text-indigo-650 bg-indigo-50/80' : 'text-slate-605 hover:text-indigo-650 hover:bg-slate-50/60' }}">
                                    <i data-lucide="trending-up" class="w-4 h-4"></i>
                                    <span>Analisis Tren</span>
                                </a>
                            @endif
                        </div>

                        <!-- Notification Dropdown -->
                        @php
                            $unreadNotifications = auth()->user()->notifications()->where('is_read', false)->orderBy('created_at', 'desc')->get();
                        @endphp
                        <div class="relative" id="notificationMenu">
                            <button type="button" onclick="toggleDropdown('notificationDropdown')" class="relative p-2 text-slate-500 hover:text-indigo-600 hover:bg-indigo-50/50 rounded-xl transition-premium focus:outline-hidden">
                                <i data-lucide="bell" class="w-5 h-5"></i>
                                @if($unreadNotifications->count() > 0)
                                    <span class="absolute top-1 right-1 flex h-4 w-4 items-center justify-center rounded-full bg-rose-500 text-[9px] font-bold text-white ring-2 ring-white">
                                        {{ $unreadNotifications->count() }}
                                    </span>
                                @endif
                            </button>

                            <!-- Dropdown Box -->
                            <div id="notificationDropdown" class="hidden absolute right-0 mt-3 w-80 bg-white border border-slate-200 rounded-2xl shadow-premium-lg z-50 overflow-hidden divide-y divide-slate-100 transform origin-top-right transition">
                                <div class="px-4 py-3 bg-slate-50 flex justify-between items-center">
                                    <span class="font-bold text-xs text-slate-805">Notifikasi</span>
                                    @if($unreadNotifications->count() > 0)
                                        <form action="{{ route('notifications.read-all') }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="text-[10px] text-indigo-600 hover:text-indigo-850 font-bold">Tandai semua dibaca</button>
                                        </form>
                                    @endif
                                </div>
                                <div class="max-h-72 overflow-y-auto">
                                    @forelse($unreadNotifications as $notif)
                                        <a href="{{ route('notifications.read', $notif->id) }}" class="block px-4 py-3 hover:bg-slate-50 transition border-l-2 border-indigo-500">
                                            <p class="text-xs font-bold text-slate-800">{{ $notif->title }}</p>
                                            <p class="text-xs text-slate-550 mt-0.5 line-clamp-2 font-normal">{{ $notif->message }}</p>
                                            <span class="text-[9px] text-slate-400 mt-1 block font-semibold">{{ $notif->created_at->diffForHumans() }}</span>
                                        </a>
                                    @empty
                                        <div class="px-4 py-8 text-center text-slate-400">
                                            <i data-lucide="bell-off" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                                            <span class="text-xs font-medium">Tidak ada notifikasi baru</span>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <!-- Quick Role Switcher for Simulation (Visible in Local Env) -->
                        @if(config('app.env') === 'local' || config('app.env') === 'testing')
                            <div class="relative" id="simulationMenu">
                                <button type="button" onclick="toggleDropdown('simulationDropdown')" class="hidden lg:flex items-center space-x-1.5 px-3 py-2 rounded-xl border border-amber-200 bg-amber-50/50 text-amber-805 text-xs font-bold transition-premium hover:bg-amber-100/60 focus:outline-hidden">
                                    <i data-lucide="shuffle" class="w-3.5 h-3.5 text-amber-500"></i>
                                    <span>Simulasi Peran</span>
                                </button>
                                <div id="simulationDropdown" class="hidden absolute right-0 mt-3 w-48 bg-white border border-slate-200 rounded-2xl shadow-premium-lg z-50 overflow-hidden divide-y divide-slate-100 transform origin-top-right transition">
                                    <div class="px-4 py-2.5 bg-slate-50">
                                        <p class="text-[9px] text-slate-405 font-bold uppercase tracking-wider">Pilih Peran Akun</p>
                                    </div>
                                    <a href="{{ route('simulasi.switch', 'warga') }}" class="block px-4 py-2.5 text-xs text-slate-700 hover:bg-slate-50 transition flex items-center justify-between">
                                        <span class="font-bold">Warga</span>
                                        <span class="text-[9px] bg-slate-100 px-1.5 py-0.5 rounded text-slate-500 font-bold">Warga</span>
                                    </a>
                                    <a href="{{ route('simulasi.switch', 'perangkat') }}" class="block px-4 py-2.5 text-xs text-slate-700 hover:bg-slate-50 transition flex items-center justify-between">
                                        <span class="font-bold">Perangkat Desa</span>
                                        <span class="text-[9px] bg-indigo-50 px-1.5 py-0.5 rounded text-indigo-700 font-bold">Admin</span>
                                    </a>
                                    <a href="{{ route('simulasi.switch', 'kades') }}" class="block px-4 py-2.5 text-xs text-slate-700 hover:bg-slate-50 transition flex items-center justify-between">
                                        <span class="font-bold">Kepala Desa</span>
                                        <span class="text-[9px] bg-amber-50 px-1.5 py-0.5 rounded text-amber-705 font-bold font-mono">Kades</span>
                                    </a>
                                </div>
                            </div>
                        @endif

                        <!-- User Profile Menu -->
                        <div class="relative" id="profileMenu">
                            <button type="button" onclick="toggleDropdown('profileDropdown')" class="flex items-center space-x-2 p-1 rounded-xl hover:bg-slate-100/60 transition-premium focus:outline-hidden">
                                <div class="w-8 h-8 rounded-full bg-indigo-550 text-white flex items-center justify-center font-extrabold text-xs shadow-premium-sm">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                                </div>
                                <div class="hidden sm:block text-left pr-1">
                                    <span class="text-xs font-bold block text-slate-900 leading-none">{{ auth()->user()->name }}</span>
                                    <span class="text-[9px] font-semibold text-slate-400 uppercase tracking-wider mt-0.5 block">{{ auth()->user()->role->display_name }}</span>
                                </div>
                                <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-400"></i>
                            </button>

                            <!-- Dropdown Box -->
                            <div id="profileDropdown" class="hidden absolute right-0 mt-3 w-48 bg-white border border-slate-200 rounded-2xl shadow-premium-lg z-50 overflow-hidden transform origin-top-right transition">
                                <div class="px-4 py-3 border-b border-slate-100 bg-slate-50">
                                    <p class="text-[9px] text-slate-400 font-bold uppercase tracking-wider">Akun Aktif</p>
                                    <p class="text-xs font-bold text-slate-700 truncate mt-0.5">{{ auth()->user()->email }}</p>
                                </div>
                                <a href="/" class="block px-4 py-2.5 text-xs text-slate-700 hover:bg-slate-50 transition flex items-center space-x-2 font-medium">
                                    <i data-lucide="home" class="w-4 h-4 text-slate-405"></i>
                                    <span>Halaman Utama</span>
                                </a>
                                <button type="button" id="push-toggle-btn" class="w-full text-left px-4 py-2.5 text-xs text-slate-700 hover:bg-slate-50 transition flex items-center space-x-2 font-medium border-t border-slate-100">
                                    <i data-lucide="bell-ring" class="w-4 h-4 text-slate-405" id="push-icon"></i>
                                    <span id="push-toggle-text">Notifikasi HP: Nonaktif</span>
                                </button>
                                <form action="{{ route('logout') }}" method="POST" class="block">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2.5 text-xs text-rose-600 hover:bg-rose-50/50 transition flex items-center space-x-2 font-bold border-t border-slate-100">
                                        <i data-lucide="log-out" class="w-4 h-4"></i>
                                        <span>Keluar</span>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Mobile Menu Trigger -->
                        <button type="button" onclick="toggleMobileMenu()" class="md:hidden p-2 text-slate-505 hover:text-indigo-650 hover:bg-slate-150 rounded-xl focus:outline-hidden transition-premium">
                            <i data-lucide="menu" class="w-5 h-5"></i>
                        </button>

                    @else
                        <!-- Guest Navigation -->
                        <a href="{{ route('login') }}" class="text-xs font-bold text-slate-650 hover:text-indigo-650 px-3.5 py-2 rounded-xl hover:bg-slate-100/50 transition-premium">Masuk</a>
                        <a href="{{ route('register') }}" class="text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 px-4 py-2 rounded-xl shadow-premium-sm transition-premium">Daftar</a>
                    @endauth
                </div>

            </div>
        </div>

        <!-- Mobile Navigation Menu -->
        @auth
            <div id="mobileMenu" class="hidden md:hidden border-t border-slate-200/50 bg-white/95 px-4 py-3 space-y-1 shadow-premium-lg backdrop-blur-md">
                @if(auth()->user()->hasRole('warga'))
                    <a href="{{ route('warga.dashboard') }}" class="block px-3 py-2.5 rounded-xl text-sm font-bold flex items-center space-x-2 transition {{ request()->routeIs('warga.dashboard*') ? 'text-indigo-655 bg-indigo-50/80' : 'text-slate-650 hover:bg-slate-50' }}">
                        <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="{{ route('warga.reports.create') }}" class="block px-3 py-2.5 rounded-xl text-sm font-bold flex items-center space-x-2 transition {{ request()->routeIs('warga.reports.create*') ? 'text-indigo-655 bg-indigo-50/80' : 'text-slate-650 hover:bg-slate-50' }}">
                        <i data-lucide="file-plus" class="w-4 h-4"></i>
                        <span>Buat Laporan</span>
                    </a>
                    <a href="{{ route('warga.ronda.schedules') }}" class="block px-3 py-2.5 rounded-xl text-sm font-bold flex items-center space-x-2 transition {{ request()->routeIs('warga.ronda.schedules*') ? 'text-indigo-655 bg-indigo-50/80' : 'text-slate-650 hover:bg-slate-50' }}">
                        <i data-lucide="calendar" class="w-4 h-4"></i>
                        <span>Jadwal Ronda Saya</span>
                    </a>
                    <a href="{{ route('warga.ronda.incidents.index') }}" class="block px-3 py-2.5 rounded-xl text-sm font-bold flex items-center space-x-2 transition {{ request()->routeIs('warga.ronda.incidents.*') ? 'text-indigo-655 bg-indigo-50/80' : 'text-slate-650 hover:bg-slate-50' }}">
                        <i data-lucide="clipboard-list" class="w-4 h-4"></i>
                        <span>Tugas Ronda Saya</span>
                    </a>
                @elseif(auth()->user()->hasRole('perangkat'))
                    <a href="{{ route('perangkat.dashboard') }}" class="block px-3 py-2.5 rounded-xl text-sm font-bold flex items-center space-x-2 transition {{ request()->routeIs('perangkat.dashboard*') ? 'text-indigo-655 bg-indigo-50/80' : 'text-slate-650 hover:bg-slate-50' }}">
                        <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="{{ route('perangkat.reports.index') }}" class="block px-3 py-2.5 rounded-xl text-sm font-bold flex items-center space-x-2 transition {{ request()->routeIs('perangkat.reports.*') ? 'text-indigo-655 bg-indigo-50/80' : 'text-slate-650 hover:bg-slate-50' }}">
                        <i data-lucide="file-text" class="w-4 h-4"></i>
                        <span>Laporan Masuk</span>
                    </a>
                    <a href="{{ route('perangkat.incidents.index') }}" class="block px-3 py-2.5 rounded-xl text-sm font-bold flex items-center space-x-2 transition {{ request()->routeIs('perangkat.incidents.*') ? 'text-indigo-655 bg-indigo-50/80' : 'text-slate-650 hover:bg-slate-50' }}">
                        <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                        <span>Pengelolaan Kejadian</span>
                    </a>
                    <a href="{{ route('perangkat.schedules') }}" class="block px-3 py-2.5 rounded-xl text-sm font-bold flex items-center space-x-2 transition {{ request()->routeIs('perangkat.schedules*') ? 'text-indigo-655 bg-indigo-50/80' : 'text-slate-650 hover:bg-slate-50' }}">
                        <i data-lucide="calendar" class="w-4 h-4"></i>
                        <span>Jadwal Patroli</span>
                    </a>
                    <a href="{{ route('perangkat.users') }}" class="block px-3 py-2.5 rounded-xl text-sm font-bold flex items-center space-x-2 transition {{ request()->routeIs('perangkat.users*') ? 'text-indigo-655 bg-indigo-50/80' : 'text-slate-650 hover:bg-slate-50' }}">
                        <i data-lucide="users" class="w-4 h-4"></i>
                        <span>Daftar Pengguna</span>
                    </a>
                @elseif(auth()->user()->hasRole('kades'))
                    <a href="{{ route('kades.dashboard') }}" class="block px-3 py-2.5 rounded-xl text-sm font-bold flex items-center space-x-2 transition {{ request()->routeIs('kades.dashboard*') ? 'text-indigo-655 bg-indigo-50/80' : 'text-slate-650 hover:bg-slate-50' }}">
                        <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="{{ route('kades.reports') }}" class="block px-3 py-2.5 rounded-xl text-sm font-bold flex items-center space-x-2 transition {{ request()->routeIs('kades.reports*') ? 'text-indigo-655 bg-indigo-50/80' : 'text-slate-650 hover:bg-slate-50' }}">
                        <i data-lucide="file-text" class="w-4 h-4"></i>
                        <span>Daftar Laporan</span>
                    </a>
                    <a href="{{ route('kades.rekap') }}" class="block px-3 py-2.5 rounded-xl text-sm font-bold flex items-center space-x-2 transition {{ request()->routeIs('kades.rekap*') ? 'text-indigo-655 bg-indigo-50/80' : 'text-slate-650 hover:bg-slate-50' }}">
                        <i data-lucide="bar-chart-3" class="w-4 h-4"></i>
                        <span>Rekap Keamanan</span>
                    </a>
                    <a href="{{ route('kades.tren') }}" class="block px-3 py-2.5 rounded-xl text-sm font-bold flex items-center space-x-2 transition {{ request()->routeIs('kades.tren*') ? 'text-indigo-655 bg-indigo-50/80' : 'text-slate-650 hover:bg-slate-50' }}">
                        <i data-lucide="trending-up" class="w-4 h-4"></i>
                        <span>Analisis Tren</span>
                    </a>
                @endif

                <!-- Quick Role Switcher for Mobile Simulation (Visible in Local Env) -->
                @if(config('app.env') === 'local' || config('app.env') === 'testing')
                    <div class="border-t border-slate-100 pt-3 mt-3">
                        <p class="text-[9px] text-slate-400 font-bold uppercase tracking-wider px-3 mb-2 flex items-center space-x-1">
                            <i data-lucide="shuffle" class="w-3 h-3 text-amber-500"></i>
                            <span>Simulasi Peran</span>
                        </p>
                        <div class="grid grid-cols-3 gap-2 px-3">
                            <a href="{{ route('simulasi.switch', 'warga') }}" class="block px-2.5 py-1.5 rounded-xl text-xs font-bold text-slate-700 bg-slate-50 border border-slate-200 text-center transition">Warga</a>
                            <a href="{{ route('simulasi.switch', 'perangkat') }}" class="block px-2.5 py-1.5 rounded-xl text-xs font-bold text-slate-700 bg-slate-50 border border-slate-200 text-center transition">Admin</a>
                            <a href="{{ route('simulasi.switch', 'kades') }}" class="block px-2.5 py-1.5 rounded-xl text-xs font-bold text-slate-700 bg-slate-50 border border-slate-200 text-center transition">Kades</a>
                        </div>
                    </div>
                @endif
            </div>
        @endauth
    </header>

    <!-- Main Content Area -->
    <main class="flex-1">
        
        <!-- Alerts/Flash Messages -->
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 mt-4">
            
            @if(session('success'))
                <div class="mb-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center space-x-3 shadow-xs">
                    <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600 flex-shrink-0"></i>
                    <span class="text-sm font-semibold">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center space-x-3 shadow-xs">
                    <i data-lucide="alert-octagon" class="w-5 h-5 text-rose-600 flex-shrink-0"></i>
                    <span class="text-sm font-semibold">{{ session('error') }}</span>
                </div>
            @endif

            @if(session('emergency_triggered'))
                <div class="mb-4 p-5 rounded-xl bg-rose-100 border-2 border-rose-500 text-rose-900 flex items-start space-x-4 shadow-md animate-pulse">
                    <i data-lucide="alert-triangle" class="w-6 h-6 text-rose-600 flex-shrink-0 mt-0.5"></i>
                    <div>
                        <h4 class="font-bold text-base">🚨 Sinyal Darurat Terkirim! 🚨</h4>
                        <p class="text-sm mt-1 font-semibold">{{ session('emergency_triggered') }}</p>
                    </div>
                </div>
            @endif

        </div>

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-indigo-950 text-slate-350 border-t border-indigo-900 mt-auto">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-10">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-sm">
                <!-- Info Layanan -->
                <div class="space-y-3">
                    <div class="flex items-center space-x-2 text-white">
                        <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                        <span class="font-extrabold text-base tracking-tight uppercase font-sans">SIKD DESA AWA</span>
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed font-normal">
                        Sistem Informasi Keamanan Desa terintegrasi untuk pelayanan pelaporan kejadian darurat warga secara real-time dan pemantauan ronda keamanan lingkungan.
                    </p>
                </div>
                <!-- Kontak Kantor Desa -->
                <div class="space-y-2.5">
                    <h4 class="text-white font-bold text-xs uppercase tracking-wider">Kontak & Layanan Darurat</h4>
                    <ul class="space-y-2 text-xs text-slate-400 font-normal">
                        <li class="flex items-start gap-2">
                            <i data-lucide="map-pin" class="w-4 h-4 text-rose-500 shrink-0 mt-0.5"></i>
                            <span>Kantor Kepala Desa Awa, Jl. Poros Kolaka-Mowewe, Kec. Samaturu, Kab. Kolaka, Sulawesi Tenggara 93551</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i data-lucide="phone" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                            <span>Hotline Kamtibmas: +62 823-4567-8910</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i data-lucide="mail" class="w-4 h-4 text-amber-500 shrink-0"></i>
                            <span>Email: aduan@desa-awa.go.id</span>
                        </li>
                    </ul>
                </div>
                <!-- Tautan Terkait -->
                <div class="space-y-2.5">
                    <h4 class="text-white font-bold text-xs uppercase tracking-wider">Tautan Resmi</h4>
                    <ul class="space-y-2 text-xs text-slate-400 font-normal">
                        <li><a href="https://www.kolakakab.go.id" target="_blank" class="hover:text-white transition">Portal Kabupaten Kolaka</a></li>
                        <li><a href="https://kemendagri.go.id" target="_blank" class="hover:text-white transition">Kementerian Dalam Negeri RI</a></li>
                        <li><a href="https://lapor.go.id" target="_blank" class="hover:text-white transition">SP4N-LAPOR! Layanan Aspirasi</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-indigo-900/60 mt-8 pt-6 flex flex-col sm:flex-row justify-between items-center text-[11px] text-slate-500 font-medium">
                <p>&copy; {{ date('Y') }} Pemerintah Desa Awa. Hak Cipta Dilindungi Undang-Undang.</p>
                <p class="mt-2 sm:mt-0 flex items-center space-x-1">
                    <span>Dikembangkan untuk</span>
                    <span class="text-slate-400 font-semibold">Keamanan & Ketertiban Masyarakat</span>
                    <i data-lucide="shield-check" class="w-3.5 h-3.5 text-emerald-600"></i>
                </p>
            </div>
        </div>
    </footer>

    <!-- Lucide Icons & Interactivity script -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        // Global Toast Notification Helper
        window.showToast = function(message, type = 'success') {
            const container = document.getElementById('global-toast-container') || (function() {
                const c = document.createElement('div');
                c.id = 'global-toast-container';
                c.className = "fixed bottom-5 right-5 z-[100] flex flex-col space-y-3";
                document.body.appendChild(c);
                return c;
            })();

            const toast = document.createElement('div');
            toast.className = `p-4 rounded-2xl shadow-2xl flex items-center space-x-3 max-w-sm border transition duration-300 transform translate-y-2 opacity-0 text-white ` + 
                (type === 'error' || type === 'danger' ? 'bg-rose-600 border-rose-500' : 
                 type === 'info' ? 'bg-indigo-650 border-indigo-500' : 'bg-emerald-600 border-emerald-500');

            const icon = type === 'error' || type === 'danger' ? 
                `<svg class="w-5 h-5 text-white shrink-0 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>` : 
                type === 'info' ?
                `<svg class="w-5 h-5 text-white shrink-0 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>` :
                `<svg class="w-5 h-5 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`;

            toast.innerHTML = `
                ${icon}
                <div class="flex-1">
                    <p class="text-xs font-extrabold tracking-tight">${message}</p>
                </div>
            `;
            container.appendChild(toast);
            setTimeout(() => {
                toast.classList.remove('translate-y-2', 'opacity-0');
            }, 100);

            setTimeout(() => {
                toast.classList.add('opacity-0');
                setTimeout(() => toast.remove(), 300);
            }, 6000);
        };

        // Initialize Lucide Icons
        document.addEventListener("DOMContentLoaded", function() {
            lucide.createIcons();
        });

        // Dropdown toggle helper
        function toggleDropdown(id) {
            const el = document.getElementById(id);
            const isHidden = el.classList.contains('hidden');
            
            // Close other dropdowns
            document.querySelectorAll('#notificationDropdown, #profileDropdown, #simulationDropdown').forEach(dropdown => {
                dropdown.classList.add('hidden');
            });

            if (isHidden) {
                el.classList.remove('hidden');
            }
        }

        // Close dropdowns on outside click
        window.addEventListener('click', function(e) {
            const profileMenu = document.getElementById('profileMenu');
            const notificationMenu = document.getElementById('notificationMenu');
            const simulationMenu = document.getElementById('simulationMenu');
            
            if (profileMenu && !profileMenu.contains(e.target)) {
                document.getElementById('profileDropdown')?.classList.add('hidden');
            }
            if (notificationMenu && !notificationMenu.contains(e.target)) {
                document.getElementById('notificationDropdown')?.classList.add('hidden');
            }
            if (simulationMenu && !simulationMenu.contains(e.target)) {
                document.getElementById('simulationDropdown')?.classList.add('hidden');
            }
        });

        // Mobile Menu toggle
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            menu.classList.toggle('hidden');
        }

        // Intercept global Fetch requests to handle Page Expired (419) automatically
        const { fetch: originalFetch } = window;
        window.fetch = async (...args) => {
            try {
                let response = await originalFetch(...args);
                if (response.status === 419) {
                    if (window.showToast) {
                        window.showToast("Sesi halaman telah berakhir (Page Expired). Memuat ulang halaman...", "danger");
                    } else {
                        alert("Sesi halaman telah berakhir (Page Expired). Halaman akan dimuat ulang.");
                    }
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                }
                return response;
            } catch (err) {
                throw err;
            }
        };

        // Keep-alive ping to prevent session/CSRF expiration while browser tab is active
        setInterval(() => {
            fetch('/up').catch(err => console.debug('Keep-alive ping failed:', err));
        }, 5 * 60 * 1000); // Every 5 minutes
    </script>
    
    @if(session('emergency_triggered'))
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                showToast("Pesan dikirim! Sinyal darurat telah dikirim ke petugas.", "danger");
            });
        </script>
    @endif
    @if(session('success'))
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                showToast("{{ session('success') }}", "success");
            });
        </script>
    @endif
    @if(session('error'))
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                showToast("{{ session('error') }}", "danger");
            });
        </script>
    @endif
    @if($errors->any())
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                showToast("{{ $errors->first() }}", "danger");
            });
        </script>
    @endif

    @auth
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const vapidPublicKey = "{{ env('VAPID_PUBLIC_KEY') }}";
            const pushToggleBtn = document.getElementById('push-toggle-btn');
            const pushToggleText = document.getElementById('push-toggle-text');
            const pushIcon = document.getElementById('push-icon');
            
            if (!pushToggleBtn) return;

            if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
                pushToggleText.textContent = 'Notifikasi HP: Tidak Didukung';
                pushToggleBtn.disabled = true;
                return;
            }

            if (!vapidPublicKey) {
                pushToggleText.textContent = 'Notifikasi HP: Belum Siap';
                pushToggleBtn.disabled = true;
                return;
            }

            let isSubscribed = false;
            let swRegistration = null;

            navigator.serviceWorker.register('/sw.js')
                .then(function(swReg) {
                    swRegistration = swReg;
                    initializeUI();
                })
                .catch(function(error) {
                    console.error('Service Worker Error', error);
                });

            function initializeUI() {
                pushToggleBtn.addEventListener('click', function() {
                    pushToggleBtn.disabled = true;
                    if (isSubscribed) {
                        unsubscribeUser();
                    } else {
                        subscribeUser();
                    }
                });

                swRegistration.pushManager.getSubscription()
                    .then(function(subscription) {
                        isSubscribed = !(subscription === null);
                        updateBtn();
                        if (!isSubscribed && Notification.permission !== 'denied') {
                            subscribeUser();
                        }
                    });
            }

            function updateBtn() {
                if (Notification.permission === 'denied') {
                    pushToggleText.textContent = 'Notifikasi HP: Diblokir';
                    pushToggleBtn.disabled = true;
                    return;
                }

                if (isSubscribed) {
                    pushToggleText.textContent = 'Notifikasi HP: Aktif';
                    pushToggleBtn.classList.add('bg-emerald-50/50', 'text-emerald-700');
                    pushIcon.classList.add('text-emerald-500');
                } else {
                    pushToggleText.textContent = 'Notifikasi HP: Nonaktif';
                    pushToggleBtn.classList.remove('bg-emerald-50/50', 'text-emerald-700');
                    pushIcon.classList.remove('text-emerald-500');
                }

                pushToggleBtn.disabled = false;
            }

            function urlB64ToUint8Array(base64String) {
                const padding = '='.repeat((4 - base64String.length % 4) % 4);
                const base64 = (base64String + padding)
                    .replace(/\-/g, '+')
                    .replace(/_/g, '/');

                const rawData = window.atob(base64);
                const outputArray = new Uint8Array(rawData.length);

                for (let i = 0; i < rawData.length; ++i) {
                    outputArray[i] = rawData.charCodeAt(i);
                }
                return outputArray;
            }

            function subscribeUser() {
                const applicationServerKey = urlB64ToUint8Array(vapidPublicKey);
                swRegistration.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: applicationServerKey
                })
                .then(function(subscription) {
                    updateSubscriptionOnServer(subscription);
                    isSubscribed = true;
                    updateBtn();
                    if (window.showToast) window.showToast('Notifikasi HP berhasil diaktifkan!');
                })
                .catch(function(err) {
                    console.error('Failed to subscribe the user: ', err);
                    updateBtn();
                });
            }

            function unsubscribeUser() {
                swRegistration.pushManager.getSubscription()
                    .then(function(subscription) {
                        if (subscription) {
                            subscription.unsubscribe().then(function() {
                                removeSubscriptionFromServer(subscription);
                                isSubscribed = false;
                                updateBtn();
                                if (window.showToast) window.showToast('Notifikasi HP telah dinonaktifkan.');
                            });
                        }
                    })
                    .catch(function(error) {
                        console.error('Error unsubscribing', error);
                    });
            }

            function updateSubscriptionOnServer(subscription) {
                const jsonSub = subscription.toJSON();
                fetch("{{ route('push.subscribe') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({
                        endpoint: subscription.endpoint,
                        keys: jsonSub.keys,
                        content_encoding: 'aes128gcm'
                    })
                });
            }

            function removeSubscriptionFromServer(subscription) {
                fetch("{{ route('push.unsubscribe') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({
                        endpoint: subscription.endpoint
                    })
                });
            }
        });
    </script>
    @endauth
</body>
</html>
