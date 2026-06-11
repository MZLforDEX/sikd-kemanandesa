@extends('layouts.app')

@section('title', 'Portal Keamanan Desa')

@section('content')
@php
    $dashboardUrl = match (auth()->user()?->role?->name) {
        'warga' => route('warga.dashboard'),
        'perangkat' => route('perangkat.dashboard'),
        'kades' => route('kades.dashboard'),
        default => route('login'),
    };
@endphp

<!-- Hero & Live Status Section -->
<div class="relative overflow-hidden bg-slate-900 border-b border-indigo-950 py-12 sm:py-16 text-white text-center">
    <!-- Subtle grid background pattern -->
    <div class="absolute inset-0 opacity-[0.08] pointer-events-none bg-[radial-gradient(#ffffff_1px,transparent_1px)] [background-size:16px_16px]"></div>
    
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 space-y-6 relative z-10">
        <div class="inline-flex items-center space-x-2 px-3.5 py-1.5 rounded-full bg-indigo-500/10 border border-indigo-400/20 text-indigo-300 text-[10px] font-extrabold uppercase tracking-widest mx-auto">
            <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
            <span>PORTAL LAYANAN ADUAN KAMTIBMAS</span>
        </div>
        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight leading-tight font-sans max-w-3xl mx-auto">
            Sistem Informasi Keamanan Desa <br class="hidden sm:inline">
            <span class="text-amber-500">(SIKD) Desa Awa</span>
        </h1>
        <p class="text-slate-350 text-xs sm:text-sm max-w-2xl leading-relaxed font-medium mx-auto font-sans">
            Layanan digital terpadu Pemerintah Desa Awa, Kecamatan Samaturu untuk pelaporan cepat kejadian darurat warga (Panic Button), pemantauan patroli ronda pos keamanan lingkungan (poskamling), dan pemetaan sebaran insiden secara real-time.
        </p>
        <div class="flex flex-wrap justify-center gap-3 pt-2">
            @auth
                <a href="{{ $dashboardUrl }}" class="inline-flex items-center justify-center gap-2 bg-amber-500 hover:bg-amber-600 text-slate-950 font-extrabold px-6 py-3.5 rounded-xl text-xs shadow-md transition-all hover:-translate-y-0.5">
                    <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                    <span>Masuk Dashboard Layanan</span>
                </a>
            @else
                <a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold px-6 py-3.5 rounded-xl text-xs shadow-md transition-all hover:-translate-y-0.5 border border-indigo-550">
                    <i data-lucide="log-in" class="w-4 h-4"></i>
                    <span>Masuk Portal Warga</span>
                </a>
                <a href="{{ route('register') }}" class="inline-flex items-center justify-center gap-2 bg-white/10 hover:bg-white/15 text-white font-extrabold px-6 py-3.5 rounded-xl text-xs shadow-md transition-all hover:-translate-y-0.5 border border-white/20">
                    <i data-lucide="user-plus" class="w-4 h-4 text-slate-300"></i>
                    <span>Daftar Akun Baru</span>
                </a>
            @endauth
        </div>
    </div>
</div>

<!-- Main content (Stats, Features & Incidents) -->
<div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 py-10 space-y-10">
    
    <!-- Core Features Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs hover:shadow-md transition">
            <div class="w-12 h-12 rounded-xl bg-rose-50 border border-rose-100 text-rose-600 flex items-center justify-center mb-5">
                <i data-lucide="bell-ring" class="w-6 h-6"></i>
            </div>
            <h3 class="font-extrabold text-slate-900 text-sm">Laporan & Sinyal Darurat</h3>
            <p class="text-slate-500 text-xs mt-2 leading-relaxed font-normal">
                Kirim sinyal aduan darurat beserta titik koordinat GPS secara instan ke pusat kendali petugas keamanan desa.
            </p>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs hover:shadow-md transition">
            <div class="w-12 h-12 rounded-xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center mb-5">
                <i data-lucide="map-pin" class="w-6 h-6"></i>
            </div>
            <h3 class="font-extrabold text-slate-900 text-sm">Pemetaan GIS Real-Time</h3>
            <p class="text-slate-500 text-xs mt-2 leading-relaxed font-normal">
                Visualisasi spasial peta kerawanan dan sebaran kejadian di wilayah Desa Awa untuk memudahkan rute patroli petugas.
            </p>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs hover:shadow-md transition">
            <div class="w-12 h-12 rounded-xl bg-amber-50 border border-amber-100 text-amber-600 flex items-center justify-center mb-5">
                <i data-lucide="calendar-days" class="w-6 h-6"></i>
            </div>
            <h3 class="font-extrabold text-slate-900 text-sm">Ronda & Patroli Digital</h3>
            <p class="text-slate-500 text-xs mt-2 leading-relaxed font-normal">
                Pengelolaan jadwal pos ronda poskamling warga secara transparan serta pemantauan check-in posko ronda digital.
            </p>
        </div>
    </div>

    <!-- Recent Security Incidents Feed (Compact) -->
    <div class="bg-white border border-slate-200/60 rounded-3xl shadow-premium-sm overflow-hidden">
        <div class="px-6 py-4.5 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
            <div class="flex items-center space-x-2">
                <div class="w-2.5 h-2.5 rounded-full bg-indigo-650 animate-ping-slow"></div>
                <h2 class="font-extrabold text-slate-900 text-sm">Feed Kejadian Keamanan Terbaru</h2>
            </div>
            <span class="text-[10px] bg-indigo-50 text-indigo-700 font-extrabold px-2 py-0.5 rounded-md">Live Update</span>
        </div>
        
        <div class="divide-y divide-slate-100">
            @forelse($recentIncidents as $incident)
                @php
                    // Map category styles
                    $catStyle = match($incident->category) {
                        'pencurian' => ['text-red-700 bg-red-50 border-red-100', 'shield-alert'],
                        'kebakaran' => ['text-orange-700 bg-orange-50 border-orange-100', 'flame'],
                        'kehilangan' => ['text-amber-700 bg-amber-50 border-amber-100', 'search'],
                        'keributan' => ['text-purple-700 bg-purple-50 border-purple-100', 'frown'],
                        'bencana alam' => ['text-blue-700 bg-blue-50 border-blue-100', 'wind'],
                        default => ['text-slate-700 bg-slate-50 border-slate-150', 'alert-circle'],
                    };

                    // Map severity styles
                    $sevStyle = match($incident->severity) {
                        'tinggi' => 'text-rose-700 bg-rose-50 border border-rose-150 font-bold',
                        'sedang' => 'text-amber-700 bg-amber-50 border border-amber-150 font-bold',
                        default => 'text-emerald-700 bg-emerald-50 border border-emerald-150 font-semibold',
                    };

                    // Map status styles
                    $statusStyle = match($incident->status) {
                        'baru' => 'bg-blue-50 text-blue-700 border-blue-150',
                        'diverifikasi' => 'bg-purple-50 text-purple-700 border-purple-150',
                        'diproses' => 'bg-amber-50 text-amber-800 border-amber-150',
                        'ditangani' => 'bg-cyan-50 text-cyan-700 border-cyan-150',
                        'selesai' => 'bg-emerald-50 text-emerald-800 border-emerald-150',
                        default => 'bg-slate-50 text-slate-600 border-slate-150',
                    };
                @endphp
                <div class="p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 transition hover:bg-slate-50/50">
                    <div class="space-y-1.5">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-xs font-bold text-slate-800">{{ $incident->title }}</span>
                            <span class="inline-flex items-center gap-1 text-[9px] font-bold px-2 py-0.5 rounded-full border {{ $catStyle[0] }}">
                                <i data-lucide="{{ $catStyle[1] }}" class="w-2.5 h-2.5"></i>
                                <span class="capitalize">{{ $incident->category }}</span>
                            </span>
                            <span class="text-[9px] uppercase tracking-wider px-2 py-0.5 rounded-md {{ $sevStyle }}">
                                {{ $incident->severity }}
                            </span>
                        </div>
                        <p class="text-slate-500 text-xs font-normal max-w-2xl line-clamp-1">
                            {{ $incident->description }}
                        </p>
                        <div class="flex items-center space-x-3 text-[10px] text-slate-400 font-semibold">
                            <span class="flex items-center gap-1">
                                <i data-lucide="map-pin" class="w-3 h-3 text-slate-350"></i>
                                <span>{{ $incident->location }}</span>
                            </span>
                            <span class="text-slate-200">•</span>
                            <span class="flex items-center gap-1">
                                <i data-lucide="clock" class="w-3 h-3 text-slate-350"></i>
                                <span>{{ \Carbon\Carbon::parse($incident->incident_date)->diffForHumans() }}</span>
                            </span>
                        </div>
                    </div>
                    
                    <div class="shrink-0 flex items-center justify-between sm:justify-end gap-2 border-t border-slate-50 pt-2 sm:border-0 sm:pt-0">
                        <span class="inline-flex items-center text-[10px] font-extrabold px-3 py-1 rounded-full border {{ $statusStyle }}">
                            <span class="capitalize">Status: {{ $incident->status }}</span>
                        </span>
                    </div>
                </div>
            @empty
                <div class="px-6 py-10 text-center text-slate-400">
                    <div class="w-12 h-12 rounded-full bg-emerald-50 border border-emerald-100 flex items-center justify-center mx-auto mb-3">
                        <i data-lucide="shield-check" class="w-6 h-6 text-emerald-600"></i>
                    </div>
                    <span class="text-xs font-bold text-slate-800 block">Kondisi Wilayah Sepenuhnya Aman</span>
                    <span class="text-[10px] text-slate-450 mt-1 block">Tidak ada laporan insiden/kejadian terbaru yang aktif.</span>
                </div>
            @endforelse
        </div>
    </div>

</div>
@endsection
