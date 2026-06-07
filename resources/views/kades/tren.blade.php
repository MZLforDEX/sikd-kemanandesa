@extends('layouts.app')

@section('title', 'Tren & Analisis Keamanan Desa')

@section('content')
<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8 space-y-8">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-6 rounded-3xl border border-slate-200/60 shadow-premium-sm">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Analisis Tren & Log Keamanan</h1>
            <p class="text-sm text-slate-500 mt-1 font-normal">Laporan analitis mengenai tipe kriminalitas, intensitas kejadian, dan audit log aktivitas sistem.</p>
        </div>
        <a href="{{ route('kades.dashboard') }}" class="inline-flex items-center space-x-1.5 text-xs font-bold text-slate-500 hover:text-slate-950 transition-premium hover:-translate-x-0.5 bg-slate-50 border border-slate-200/60 px-4 py-2.5 rounded-xl">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Kembali ke Dashboard</span>
        </a>
    </div>

    <!-- Analytics Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        
        <!-- Incidents by Category Card -->
        <div class="bg-white border border-slate-200/60 rounded-3xl p-6 shadow-premium-sm space-y-6">
            <div>
                <h3 class="font-extrabold text-slate-900 text-base flex items-center space-x-2">
                    <i data-lucide="pie-chart" class="w-5 h-5 text-indigo-650"></i>
                    <span>Distribusi Berdasarkan Kategori</span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5 font-normal">Jumlah kejadian keamanan dikelompokkan berdasarkan klasifikasi.</p>
            </div>

            <div class="space-y-4">
                @forelse($categories as $category => $count)
                    @php
                        $maxCat = max(array_values($categories)) ?: 1;
                        $percent = min(100, round(($count / $maxCat) * 100));
                    @endphp
                    <div class="space-y-1.5 font-medium">
                        <div class="flex justify-between items-center text-xs">
                            <span class="capitalize font-bold text-slate-800">{{ $category }}</span>
                            <span class="font-extrabold text-indigo-650">{{ $count }} Kejadian</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                            <div class="bg-indigo-600 h-full rounded-full" style="width: {{ $percent }}%"></div>
                        </div>
                    </div>
                @empty
                    <div class="py-6 text-center text-slate-400">
                        <i data-lucide="folder" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                        <span class="text-xs">Belum ada data kategori kejadian.</span>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Incidents by Severity Card -->
        <div class="bg-white border border-slate-200/60 rounded-3xl p-6 shadow-premium-sm space-y-6">
            <div>
                <h3 class="font-extrabold text-slate-900 text-base flex items-center space-x-2">
                    <i data-lucide="bar-chart-2" class="w-5 h-5 text-indigo-655"></i>
                    <span>Tingkat Keparahan Kasus</span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5 font-normal">Persentase kejadian berdasarkan klasifikasi tingkat urgensi.</p>
            </div>

            <div class="space-y-4">
                @forelse($severities as $severity => $count)
                    @php
                        $maxSev = max(array_values($severities)) ?: 1;
                        $percent = min(100, round(($count / $maxSev) * 100));
                        $color = $severity === 'tinggi' ? 'bg-rose-500' : ($severity === 'sedang' ? 'bg-amber-500' : 'bg-slate-550');
                    @endphp
                    <div class="space-y-1.5 font-medium">
                        <div class="flex justify-between items-center text-xs">
                            <span class="capitalize font-bold text-slate-800">Urgensi {{ $severity }}</span>
                            <span class="font-extrabold text-slate-900">{{ $count }} Kejadian</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                            <div class="{{ $color }} h-full rounded-full" style="width: {{ $percent }}%"></div>
                        </div>
                    </div>
                @empty
                    <div class="py-6 text-center text-slate-400">
                        <i data-lucide="shield" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                        <span class="text-xs">Belum ada data keparahan kejadian.</span>
                    </div>
                @endforelse
            </div>
        </div>

    </div>

    <!-- Audit Logs Section (Activity Logs) -->
    <div class="bg-white border border-slate-200/60 rounded-3xl overflow-hidden shadow-premium-sm">
        <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <div>
                <h2 class="font-extrabold text-slate-900 text-base flex items-center space-x-2">
                    <i data-lucide="history" class="w-5 h-5 text-indigo-650"></i>
                    <span>Log Aktivitas Sistem Keamanan Desa</span>
                </h2>
                <p class="text-xs text-slate-500 mt-0.5 font-normal">Audit trail real-time mencatat aktivitas krusial seperti login, pelaporan, dan verifikasi.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/30 text-slate-500 border-b border-slate-100 font-bold uppercase tracking-wider text-[10px]">
                        <th class="px-6 py-4">Waktu</th>
                        <th class="px-6 py-4">Pengguna</th>
                        <th class="px-6 py-4">Aktivitas Tindakan</th>
                        <th class="px-6 py-4">IP Address</th>
                        <th class="px-6 py-4">User Agent</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50/25 transition text-slate-800">
                            <td class="px-6 py-4 whitespace-nowrap text-slate-500">
                                {{ $log->created_at->format('d-m-Y H:i:s') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-bold text-slate-800">
                                {{ $log->user ? $log->user->name : 'Sistem Otomatis' }}
                                @if($log->user)
                                    <span class="text-[9px] font-normal text-slate-400 block mt-0.5">{{ $log->user->role->display_name }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-slate-900 leading-relaxed font-normal">
                                {{ $log->activity }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-mono text-slate-600">
                                {{ $log->ip_address }}
                            </td>
                            <td class="px-6 py-4 truncate max-w-xs text-slate-450 font-normal">
                                {{ $log->user_agent }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-10 text-center text-slate-400 font-normal">
                                <i data-lucide="database" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                                <span class="text-xs">Belum ada log aktivitas tercatat.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
