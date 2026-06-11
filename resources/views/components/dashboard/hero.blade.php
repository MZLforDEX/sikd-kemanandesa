@props([
    'title',
    'subtitle' => null,
    'badge' => null,
    'variant' => 'indigo',
])

@php
    $variants = [
        'indigo' => 'from-slate-900 via-indigo-950 to-indigo-900',
        'violet' => 'from-indigo-950 via-slate-900 to-indigo-900',
        'emerald' => 'from-emerald-950 via-slate-900 to-emerald-900',
        'cyan' => 'from-indigo-950 via-indigo-900 to-slate-950',
    ];
    $gradient = $variants[$variant] ?? $variants['indigo'];
@endphp

<div class="dashboard-hero relative overflow-hidden rounded-3xl bg-gradient-to-br {{ $gradient }} p-6 sm:p-8 text-white shadow-premium-lg border border-slate-800">
    <div class="dashboard-hero-pattern absolute inset-0 opacity-[0.08]"></div>

    <div class="relative flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
        <div class="space-y-2 min-w-0">
            @if($badge)
                <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1 text-[10px] font-bold uppercase tracking-widest border border-white/10 backdrop-blur-xs">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    {{ $badge }}
                </span>
            @endif
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight leading-tight">{{ $title }}</h1>
            @if($subtitle)
                <p class="text-sm text-white/80 max-w-xl font-medium leading-relaxed">{{ $subtitle }}</p>
            @endif
            <p class="text-[10px] text-white/60 font-bold uppercase tracking-wider flex items-center gap-1.5">
                <i data-lucide="calendar" class="w-3.5 h-3.5 text-white/70"></i>
                <span>{{ now()->translatedFormat('l, d F Y') }}</span>
                <span>·</span>
                <i data-lucide="map-pin" class="w-3.5 h-3.5 text-white/70"></i>
                <span>Pemerintah Desa Awa, Kec. Samaturu</span>
            </p>
        </div>
        @if(isset($actions))
            <div class="flex flex-wrap gap-2 shrink-0">{{ $actions }}</div>
        @endif
    </div>
</div>
