@extends('layouts.app')

@section('title', 'Dashboard Satpam / Petugas Patroli')

@section('content')
<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6 sm:space-y-8">

    <x-dashboard.hero
        variant="cyan"
        badge="Portal Petugas Ronda"
        :title="'Halo, ' . auth()->user()->name . '!'"
        subtitle="Pantau jadwal patroli, kerjakan tugas penanganan kejadian, dan isi log hasil ronda lapangan."
    >
        <x-slot:actions>
            <a href="{{ route('warga.ronda.schedules') }}" class="btn-hero-primary">
                <i data-lucide="compass" class="w-4 h-4"></i>
                <span>Isi Log Patroli</span>
            </a>
            <a href="{{ route('warga.ronda.incidents.index') }}" class="btn-hero-secondary">
                <i data-lucide="clipboard-list" class="w-4 h-4"></i>
                <span>Semua Tugas</span>
            </a>
        </x-slot:actions>
    </x-dashboard.hero>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
        <x-dashboard.stat label="Total Penugasan" :value="$stats['schedules_count']" icon="calendar" tone="slate" />
        <x-dashboard.stat label="Tugas Aktif" :value="$stats['pending_tasks']" icon="alert-triangle" tone="rose" />
        <x-dashboard.stat label="Log Terisi" :value="$stats['logs_count']" icon="check-square" tone="emerald" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 sm:gap-8">

        <div class="lg:col-span-7">
            <x-dashboard.panel title="Tugas Penanganan Kejadian" icon="clipboard-list" icon-color="text-indigo-600" subtitle="Disposisi kejadian yang ditugaskan kepada Anda">
                <x-slot:actions>
                    <a href="{{ route('warga.ronda.incidents.index') }}" class="text-xs text-indigo-650 hover:text-indigo-850 font-bold transition-premium">Semua Tugas</a>
                </x-slot:actions>

                <div class="divide-y divide-slate-100">
                    @forelse($assignedIncidents as $incident)
                        <div class="dashboard-list-row">
                            <div class="space-y-1.5 min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="font-bold text-slate-900 text-sm leading-snug">{{ $incident->title }}</h3>
                                    <span class="px-2 py-0.5 text-[9px] font-bold rounded-full uppercase
                                        {{ $incident->severity === 'tinggi' ? 'bg-rose-50 text-rose-700 border border-rose-100' : 'bg-amber-50 text-amber-700 border border-amber-100' }}">
                                        {{ $incident->severity }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-500">
                                    <strong>{{ $incident->location }}</strong> · {{ $incident->incident_date->format('d-m-Y H:i') }} WIB
                                </p>
                                <p class="text-xs text-slate-600 line-clamp-2 font-normal">{{ $incident->description }}</p>
                            </div>
                            <a href="{{ route('warga.ronda.incidents.show', $incident->id) }}" class="inline-flex items-center gap-1 text-xs font-bold bg-indigo-650 hover:bg-indigo-700 text-white px-3.5 py-2 rounded-xl transition-premium hover:-translate-y-0.5 shrink-0 shadow-premium-sm">
                                <span>Tindak Lanjut</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>
                    @empty
                        <x-dashboard.empty icon="check-circle" title="Tidak Ada Tugas Tertunda" description="Semua disposisi penanganan kejadian telah selesai ditangani." />
                    @endforelse
                </div>
            </x-dashboard.panel>
        </div>

        <div class="lg:col-span-5">
            <x-dashboard.panel title="Jadwal Patroli" icon="calendar" icon-color="text-indigo-600" subtitle="Jadwal ronda terdekat yang ditugaskan kepada Anda">
                <div class="p-6 space-y-4">
                    @forelse($schedules->take(3) as $sched)
                        <div class="rounded-2xl p-4 border border-slate-200/60 bg-gradient-to-r from-slate-50/80 to-white hover:from-indigo-50/30 hover:to-white transition-premium card-interactive">
                            <div class="flex justify-between items-start gap-3">
                                <div class="space-y-1.5 min-w-0">
                                    <span class="text-sm font-extrabold text-slate-900 block">{{ $sched->patrol_date->format('d M Y') }}</span>
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-indigo-50 text-indigo-700 text-[10px] font-bold uppercase border border-indigo-100">
                                        <i data-lucide="clock" class="w-3 h-3"></i>
                                        Shift {{ $sched->shift }} · {{ substr($sched->start_time, 0, 5) }}–{{ substr($sched->end_time, 0, 5) }}
                                    </span>
                                    <p class="text-xs text-slate-600 mt-1"><i data-lucide="map-pin" class="w-3 h-3 inline text-indigo-500"></i> {{ $sched->area }}</p>
                                </div>
                                <div class="shrink-0">
                                    @if($sched->status === 'completed')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">Selesai</span>
                                    @elseif($sched->status === 'missed')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-100">Terlewat</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Terjadwal</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <x-dashboard.empty icon="calendar" title="Tidak Ada Jadwal" description="Belum ada penugasan jadwal ronda dari perangkat desa." />
                    @endforelse

                    <div class="border-t border-slate-100 pt-4 text-center">
                        <a href="{{ route('warga.ronda.schedules') }}" class="inline-flex items-center gap-1 text-xs font-bold text-indigo-650 hover:text-indigo-850 transition-premium">
                            <span>Lihat Seluruh Jadwal</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    </div>
                </div>
            </x-dashboard.panel>

            {{-- Quick tips card --}}
            <div class="mt-6 rounded-3xl bg-gradient-to-br from-indigo-50 to-cyan-50 border border-indigo-100/60 p-6 shadow-premium-sm">
                <h3 class="font-extrabold text-slate-900 text-sm flex items-center gap-2 mb-3">
                    <i data-lucide="lightbulb" class="w-4 h-4 text-amber-500"></i>
                    Panduan Singkat
                </h3>
                <ul class="space-y-2 text-xs text-slate-600 font-medium">
                    <li class="flex items-start gap-2"><span class="text-indigo-600 font-bold">1.</span> Cek jadwal patroli hari ini sebelum berangkat</li>
                    <li class="flex items-start gap-2"><span class="text-indigo-600 font-bold">2.</span> Isi log patroli setelah selesai ronda</li>
                    <li class="flex items-start gap-2"><span class="text-indigo-600 font-bold">3.</span> Laporkan kondisi mencurigakan atau bahaya segera</li>
                </ul>
            </div>
        </div>

    </div>

</div>
@endsection
