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

<div class="mx-auto max-w-4xl px-4 sm:px-6 py-12 sm:py-16 space-y-10">

    {{-- Hero --}}
    <div class="text-center space-y-5">
        <div class="inline-flex items-center justify-center w-14 h-14 bg-indigo-600 text-white rounded-2xl mx-auto">
            <i data-lucide="shield" class="w-7 h-7"></i>
        </div>
        <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
            Sistem Keamanan Desa Awa
        </h1>
        <p class="text-slate-500 text-sm sm:text-base max-w-lg mx-auto">
            Laporkan kejadian keamanan, pantau patroli ronda, dan kelola penanganan dari satu portal.
        </p>
        <div class="flex flex-wrap justify-center gap-3 pt-1">
            @auth
                <a href="{{ $dashboardUrl }}" class="inline-flex items-center gap-2 bg-indigo-650 hover:bg-indigo-700 text-white font-bold px-5 py-2.5 rounded-xl text-sm">
                    <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                    Dashboard
                </a>
            @else
                <a href="{{ route('login') }}" class="inline-flex items-center gap-2 bg-indigo-650 hover:bg-indigo-700 text-white font-bold px-5 py-2.5 rounded-xl text-sm">
                    Masuk
                </a>
                <a href="{{ route('register') }}" class="inline-flex items-center gap-2 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 font-bold px-5 py-2.5 rounded-xl text-sm">
                    Daftar Warga
                </a>
            @endauth
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-white border border-slate-200/60 rounded-2xl p-5 shadow-premium-sm">
        <div class="text-center">
            <p class="text-2xl font-extrabold text-slate-900">{{ $stats['laporan_baru'] }}</p>
            <p class="text-[10px] font-bold text-slate-400 uppercase mt-1">Laporan Baru</p>
        </div>
        <div class="text-center">
            <p class="text-2xl font-extrabold text-indigo-650">{{ $stats['laporan_diproses'] }}</p>
            <p class="text-[10px] font-bold text-slate-400 uppercase mt-1">Diproses</p>
        </div>
        <div class="text-center">
            <p class="text-2xl font-extrabold text-emerald-600">{{ $stats['laporan_selesai'] }}</p>
            <p class="text-[10px] font-bold text-slate-400 uppercase mt-1">Selesai</p>
        </div>
        <div class="text-center">
            <p class="text-2xl font-extrabold text-cyan-600">{{ $stats['total_patroli_hari_ini'] }}</p>
            <p class="text-[10px] font-bold text-slate-400 uppercase mt-1">Patroli Hari Ini</p>
        </div>
    </div>

    {{-- Demo accounts --}}
    @guest
    <div class="bg-white border border-slate-200/60 rounded-2xl shadow-premium-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50">
            <h2 class="font-bold text-slate-900 text-sm">Akun Demo</h2>
            <p class="text-xs text-slate-500 mt-0.5">Password semua akun: <strong>password</strong></p>
        </div>
        <div class="divide-y divide-slate-100">
            @foreach([
                ['Warga', 'warga@desa.id'],
                ['Warga Ronda', 'satpam@desa.id'],
                ['Perangkat Desa', 'perangkat@desa.id'],
                ['Kepala Desa', 'kades@desa.id'],
            ] as [$role, $email])
                <div class="flex items-center justify-between gap-4 px-5 py-3.5 text-sm">
                    <div>
                        <span class="font-bold text-slate-800">{{ $role }}</span>
                        <span class="text-slate-400 mx-2">·</span>
                        <span class="text-slate-500 font-mono text-xs">{{ $email }}</span>
                    </div>
                    <a href="{{ route('login') }}?email={{ urlencode($email) }}" class="text-xs font-bold text-indigo-650 hover:text-indigo-800 shrink-0">
                        Login →
                    </a>
                </div>
            @endforeach
        </div>
    </div>
    @endguest

</div>
@endsection
