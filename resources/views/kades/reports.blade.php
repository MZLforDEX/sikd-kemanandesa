@extends('layouts.app')

@section('title', 'Pemantauan Laporan Keamanan')

@section('content')
<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-6 rounded-3xl border border-slate-200/60 shadow-premium-sm">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Pemantauan Seluruh Laporan Keamanan</h1>
            <p class="text-sm text-slate-500 mt-1 font-normal">Mengawasi seluruh status laporan warga dan riwayat penanganannya.</p>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="bg-white border border-slate-200/60 p-2.5 rounded-3xl flex flex-wrap gap-1.5 shadow-premium-sm">
        <a href="{{ route('kades.reports') }}" 
            class="px-4 py-2 rounded-xl text-xs font-bold transition-premium hover:-translate-y-0.5 {{ !$status ? 'bg-indigo-600 text-white shadow-premium-sm' : 'text-slate-600 hover:bg-slate-50' }}">
            Semua Laporan
        </a>
        <a href="{{ route('kades.reports', ['status' => 'baru']) }}" 
            class="px-4 py-2 rounded-xl text-xs font-bold transition-premium hover:-translate-y-0.5 {{ $status === 'baru' ? 'bg-indigo-600 text-white shadow-premium-sm' : 'text-slate-600 hover:bg-slate-50' }}">
            Baru
        </a>
        <a href="{{ route('kades.reports', ['status' => 'diverifikasi']) }}" 
            class="px-4 py-2 rounded-xl text-xs font-bold transition-premium hover:-translate-y-0.5 {{ $status === 'diverifikasi' ? 'bg-indigo-600 text-white shadow-premium-sm' : 'text-slate-600 hover:bg-slate-50' }}">
            Diverifikasi
        </a>
        <a href="{{ route('kades.reports', ['status' => 'diproses']) }}" 
            class="px-4 py-2 rounded-xl text-xs font-bold transition-premium hover:-translate-y-0.5 {{ $status === 'diproses' ? 'bg-indigo-600 text-white shadow-premium-sm' : 'text-slate-600 hover:bg-slate-50' }}">
            Diproses
        </a>
        <a href="{{ route('kades.reports', ['status' => 'ditangani']) }}" 
            class="px-4 py-2 rounded-xl text-xs font-bold transition-premium hover:-translate-y-0.5 {{ $status === 'ditangani' ? 'bg-indigo-600 text-white shadow-premium-sm' : 'text-slate-600 hover:bg-slate-50' }}">
            Ditangani
        </a>
        <a href="{{ route('kades.reports', ['status' => 'selesai']) }}" 
            class="px-4 py-2 rounded-xl text-xs font-bold transition-premium hover:-translate-y-0.5 {{ $status === 'selesai' ? 'bg-indigo-600 text-white shadow-premium-sm' : 'text-slate-600 hover:bg-slate-50' }}">
            Selesai
        </a>
        <a href="{{ route('kades.reports', ['status' => 'ditolak']) }}" 
            class="px-4 py-2 rounded-xl text-xs font-bold transition-premium hover:-translate-y-0.5 {{ $status === 'ditolak' ? 'bg-indigo-600 text-white shadow-premium-sm' : 'text-slate-600 hover:bg-slate-50' }}">
            Ditolak
        </a>
    </div>

    <!-- Table Card -->
    <div class="bg-white border border-slate-200/60 rounded-3xl overflow-hidden shadow-premium-sm">
        
        @if($reports->isEmpty())
            <div class="p-16 text-center text-slate-400">
                <i data-lucide="file-text" class="w-12 h-12 mx-auto mb-3 text-slate-350"></i>
                <h4 class="font-extrabold text-slate-700 text-sm">Tidak Ada Laporan ditemukan</h4>
                <p class="text-xs text-slate-500 mt-1 font-normal">Belum ada laporan dari warga untuk kategori filter status ini.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-slate-50/50 text-slate-500 border-b border-slate-100 text-[10px] font-bold uppercase tracking-wider">
                            <th class="px-6 py-4">Tanggal Lapor</th>
                            <th class="px-6 py-4">Pelapor & Kontak</th>
                            <th class="px-6 py-4">Detail Kejadian</th>
                            <th class="px-6 py-4">Lokasi</th>
                            <th class="px-6 py-4">Status Terakhir</th>
                            <th class="px-6 py-4 text-right">Penanggung Jawab</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @foreach($reports as $report)
                            <tr class="hover:bg-slate-50/25 transition text-slate-800">
                                <td class="px-6 py-4 whitespace-nowrap text-slate-500 text-xs">
                                    {{ $report->reported_at->format('d-m-Y') }} <br>
                                    <span class="text-[10px] text-slate-400 font-normal">{{ $report->reported_at->format('H:i') }} WIB</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-slate-900 block font-bold">{{ $report->user->name }}</span>
                                    <span class="text-xs text-slate-500 block font-normal mt-0.5">{{ $report->user->phone ?? '-' }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-slate-900 block font-bold leading-tight">{{ $report->title }}</span>
                                    <span class="text-xs text-slate-550 line-clamp-1 mt-0.5 font-normal">{{ $report->description }}</span>
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-600 font-normal">
                                    {{ $report->location }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($report->status === 'baru')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200/50">Baru</span>
                                    @elseif($report->status === 'diverifikasi')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-100">Diverifikasi</span>
                                    @elseif($report->status === 'diproses')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-100">Diproses</span>
                                    @elseif($report->status === 'ditangani')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-cyan-50 text-cyan-700 border border-cyan-100">Ditangani</span>
                                    @elseif($report->status === 'selesai')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">Selesai</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-100">Ditolak</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    @if($report->incident && $report->incident->handlingRecords->isNotEmpty())
                                        <span class="text-xs text-slate-700 block font-bold">{{ $report->incident->handlingRecords->last()->handler->name }}</span>
                                        <span class="text-[9px] text-slate-450 block mt-0.5">Satpam Lapangan</span>
                                    @else
                                        <span class="text-xs text-slate-400 italic font-normal">Belum ditugaskan</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($reports->hasPages())
                <div class="px-6 py-4 border-t border-slate-100">
                    {{ $reports->appends(request()->query())->links() }}
                </div>
            @endif
        @endif
        
    </div>

</div>
@endsection
