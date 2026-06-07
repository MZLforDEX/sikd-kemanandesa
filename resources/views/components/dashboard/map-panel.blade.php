@props([
    'title',
    'subtitle' => null,
])

<div class="bg-white border border-slate-200/60 rounded-3xl overflow-hidden shadow-premium-sm">
    <div class="px-6 py-5 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 bg-gradient-to-r from-slate-50/80 to-white">
        <div>
            <h2 class="font-extrabold text-slate-900 text-base flex items-center gap-2">
                <span class="p-1.5 rounded-lg bg-white shadow-premium-sm border border-slate-100">
                    <i data-lucide="map" class="w-4 h-4 text-indigo-600"></i>
                </span>
                <span>{{ $title }}</span>
                <span class="map-live-badge inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-rose-50 text-rose-600 text-[9px] font-bold uppercase tracking-wider border border-rose-100">
                    <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                    Live
                </span>
            </h2>
            @if($subtitle)
                <p class="text-xs text-slate-500 mt-1 ml-9 font-normal">{{ $subtitle }}</p>
            @endif
        </div>
        <div class="flex flex-wrap items-center gap-2 text-xs">
            <span class="map-legend-pill"><span class="h-2 w-2 rounded-full bg-rose-500"></span> Baru</span>
            <span class="map-legend-pill"><span class="h-2 w-2 rounded-full bg-amber-500"></span> Diproses</span>
            <span class="map-legend-pill"><span class="h-2 w-2 rounded-full bg-emerald-500"></span> Selesai</span>
        </div>
    </div>
    <div class="relative">
        <div id="dashboard-map" class="h-[28rem] sm:h-96 w-full z-10"></div>
        <div class="absolute bottom-4 left-4 z-20 hidden sm:flex items-center gap-2 px-3 py-2 rounded-xl bg-white/90 backdrop-blur-md border border-slate-200/60 shadow-premium-sm text-[10px] font-bold text-slate-600">
            <i data-lucide="map-pin" class="w-3.5 h-3.5 text-indigo-600"></i>
            Desa Awa · -3.9469, 121.3510
        </div>
    </div>
</div>
