@props([
    'label',
    'value',
    'icon',
    'tone' => 'slate',
    'id' => null,
])

@php
    $tones = [
        'slate'   => ['bg' => 'bg-slate-50', 'icon' => 'text-slate-500', 'val' => 'text-slate-900', 'ring' => 'ring-slate-100'],
        'indigo'  => ['bg' => 'bg-indigo-50', 'icon' => 'text-indigo-600', 'val' => 'text-indigo-650', 'ring' => 'ring-indigo-100'],
        'amber'   => ['bg' => 'bg-amber-50', 'icon' => 'text-amber-600', 'val' => 'text-amber-600', 'ring' => 'ring-amber-100'],
        'rose'    => ['bg' => 'bg-rose-50', 'icon' => 'text-rose-600', 'val' => 'text-rose-600', 'ring' => 'ring-rose-100'],
        'emerald' => ['bg' => 'bg-emerald-50', 'icon' => 'text-emerald-600', 'val' => 'text-emerald-600', 'ring' => 'ring-emerald-100'],
        'cyan'    => ['bg' => 'bg-cyan-50', 'icon' => 'text-cyan-600', 'val' => 'text-cyan-600', 'ring' => 'ring-cyan-100'],
        'blue'    => ['bg' => 'bg-blue-50', 'icon' => 'text-blue-600', 'val' => 'text-blue-600', 'ring' => 'ring-blue-100'],
    ];
    $t = $tones[$tone] ?? $tones['slate'];
@endphp

<div {{ $attributes->merge(['class' => 'dashboard-stat group bg-white rounded-2xl border border-slate-200/60 p-5 shadow-premium-sm card-interactive']) }}>
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0 flex-1">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">{{ $label }}</span>
            <span @if($id) id="{{ $id }}" @endif class="text-2xl sm:text-3xl font-extrabold {{ $t['val'] }} mt-1 block tabular-nums">{{ $value }}</span>
        </div>
        <div class="p-3 rounded-xl {{ $t['bg'] }} {{ $t['icon'] }} ring-1 {{ $t['ring'] }} transition-premium group-hover:scale-110">
            <i data-lucide="{{ $icon }}" class="w-5 h-5"></i>
        </div>
    </div>
</div>
