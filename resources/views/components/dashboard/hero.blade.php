@props([
    'title',
    'subtitle' => null,
    'badge' => null,
    'variant' => 'indigo',
])

@php
    $variants = [
        'indigo' => 'from-indigo-600 via-indigo-650 to-violet-700',
        'violet' => 'from-violet-600 via-indigo-650 to-indigo-800',
        'emerald' => 'from-emerald-600 via-teal-600 to-cyan-700',
        'cyan' => 'from-cyan-600 via-indigo-600 to-indigo-750',
    ];
    $gradient = $variants[$variant] ?? $variants['indigo'];
@endphp

<div class="dashboard-hero relative overflow-hidden rounded-3xl bg-gradient-to-br {{ $gradient }} p-6 sm:p-8 text-white shadow-premium-lg">
    <div class="dashboard-hero-pattern absolute inset-0 opacity-[0.12]"></div>
    <div class="dashboard-hero-glow absolute -right-16 -top-16 h-48 w-48 rounded-full bg-white/20 blur-3xl"></div>
    <div class="dashboard-hero-glow absolute -bottom-20 -left-10 h-40 w-40 rounded-full bg-white/10 blur-2xl"></div>

    <div class="relative flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
        <div class="space-y-2 min-w-0">
            @if($badge)
                <span class="inline-flex items-center gap-1.5 rounded-full bg-white/15 px-3 py-1 text-[10px] font-bold uppercase tracking-widest backdrop-blur-sm border border-white/20">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-300 animate-pulse"></span>
                    {{ $badge }}
                </span>
            @endif
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight leading-tight">{{ $title }}</h1>
            @if($subtitle)
                <p class="text-sm text-white/80 max-w-xl font-medium leading-relaxed">{{ $subtitle }}</p>
            @endif
            <p class="text-[11px] text-white/50 font-semibold">
                {{ now()->translatedFormat('l, d F Y') }} · Desa Awa, Kec. Samaturu
            </p>
        </div>
        @if(isset($actions))
            <div class="flex flex-wrap gap-2 shrink-0">{{ $actions }}</div>
        @endif
    </div>
</div>
