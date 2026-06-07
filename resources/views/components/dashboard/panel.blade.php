@props([
    'title',
    'icon' => null,
    'iconColor' => 'text-indigo-600',
    'subtitle' => null,
])

<div {{ $attributes->merge(['class' => 'bg-white border border-slate-200/60 rounded-3xl shadow-premium-sm overflow-hidden']) }}>
    <div class="px-6 py-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 bg-gradient-to-r from-slate-50/80 to-white">
        <div>
            <h2 class="font-extrabold text-slate-900 text-base flex items-center gap-2">
                @if($icon)
                    <span class="p-1.5 rounded-lg bg-white shadow-premium-sm border border-slate-100">
                        <i data-lucide="{{ $icon }}" class="w-4 h-4 {{ $iconColor }}"></i>
                    </span>
                @endif
                <span>{{ $title }}</span>
            </h2>
            @if($subtitle)
                <p class="text-xs text-slate-500 mt-0.5 font-normal ml-9">{{ $subtitle }}</p>
            @endif
        </div>
        @if(isset($actions))
            <div class="shrink-0">{{ $actions }}</div>
        @endif
    </div>
    <div>{{ $slot }}</div>
</div>
