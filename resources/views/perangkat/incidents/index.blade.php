@extends('layouts.app')

@section('title', 'Daftar Kejadian Keamanan')

@section('content')
<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-6 rounded-3xl border border-slate-200/60 shadow-premium-sm">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Pengelolaan Kejadian Keamanan Desa</h1>
            <p class="text-sm text-slate-500 mt-1 font-normal">Disposisi tugas penanganan kejadian kepada Satpam dan awasi progres tindak lanjut.</p>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="bg-white border border-slate-200/60 p-2.5 rounded-3xl flex flex-wrap gap-1.5 shadow-premium-sm">
        <a href="{{ route('perangkat.incidents.index') }}" 
            class="px-4 py-2 rounded-xl text-xs font-bold transition-premium hover:-translate-y-0.5 {{ !$status ? 'bg-indigo-600 text-white shadow-premium-sm' : 'text-slate-600 hover:bg-slate-50' }}">
            Semua Kejadian
        </a>
        <a href="{{ route('perangkat.incidents.index', ['status' => 'diverifikasi']) }}" 
            class="px-4 py-2 rounded-xl text-xs font-bold transition-premium hover:-translate-y-0.5 {{ $status === 'diverifikasi' ? 'bg-indigo-600 text-white shadow-premium-sm' : 'text-slate-600 hover:bg-slate-50' }}">
            Menunggu Petugas
        </a>
        <a href="{{ route('perangkat.incidents.index', ['status' => 'diproses']) }}" 
            class="px-4 py-2 rounded-xl text-xs font-bold transition-premium hover:-translate-y-0.5 {{ $status === 'diproses' ? 'bg-indigo-600 text-white shadow-premium-sm' : 'text-slate-600 hover:bg-slate-50' }}">
            Sedang Diproses
        </a>
        <a href="{{ route('perangkat.incidents.index', ['status' => 'ditangani']) }}" 
            class="px-4 py-2 rounded-xl text-xs font-bold transition-premium hover:-translate-y-0.5 {{ $status === 'ditangani' ? 'bg-indigo-600 text-white shadow-premium-sm' : 'text-slate-600 hover:bg-slate-50' }}">
            Ditangani Lapangan
        </a>
        <a href="{{ route('perangkat.incidents.index', ['status' => 'selesai']) }}" 
            class="px-4 py-2 rounded-xl text-xs font-bold transition-premium hover:-translate-y-0.5 {{ $status === 'selesai' ? 'bg-indigo-600 text-white shadow-premium-sm' : 'text-slate-600 hover:bg-slate-50' }}">
            Selesai
        </a>
    </div>

    <!-- List Grid/Table -->
    <div class="bg-white border border-slate-200/60 rounded-3xl overflow-hidden shadow-premium-sm">
        
        @if($incidents->isEmpty())
            <div class="p-16 text-center text-slate-400">
                <i data-lucide="alert-octagon" class="w-12 h-12 mx-auto mb-3 text-slate-350"></i>
                <h4 class="font-extrabold text-slate-700 text-base">Tidak Ada Kejadian</h4>
                <p class="text-xs text-slate-500 mt-1 font-normal">Tidak ada kejadian aktif yang cocok dengan filter saat ini.</p>
            </div>
        @else
            <div class="overflow-x-auto font-medium">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-slate-50/50 text-slate-500 border-b border-slate-100 text-[10px] font-bold uppercase tracking-wider">
                            <th class="px-6 py-4">Tanggal & Waktu</th>
                            <th class="px-6 py-4">Nama Kejadian</th>
                            <th class="px-6 py-4">Kategori</th>
                            <th class="px-6 py-4">Tingkat Keparahan</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @foreach($incidents as $incident)
                            <tr class="hover:bg-slate-50/25 transition text-slate-800">
                                <td class="px-6 py-4 text-slate-500 text-xs whitespace-nowrap">
                                    {{ $incident->incident_date->format('d-m-Y') }} <br>
                                    <span class="text-[10px] text-slate-400 font-normal">{{ $incident->incident_date->format('H:i') }} WIB</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-slate-900 block font-bold leading-tight">{{ $incident->title }}</span>
                                    <span class="text-xs text-slate-500 mt-1 flex items-center space-x-1 font-normal">
                                        <i data-lucide="map-pin" class="w-3.5 h-3.5 text-indigo-650 shrink-0"></i>
                                        <span>{{ $incident->location }}</span>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="capitalize px-2.5 py-0.5 bg-slate-100 border border-slate-200/50 text-slate-700 rounded-xl text-xs font-bold">
                                        {{ $incident->category }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($incident->severity === 'tinggi')
                                        <span class="inline-flex items-center space-x-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-800 border border-rose-100">Tinggi / Gawat</span>
                                    @elseif($incident->severity === 'sedang')
                                        <span class="inline-flex items-center space-x-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-100">Sedang</span>
                                    @else
                                        <span class="inline-flex items-center space-x-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-850 border border-slate-200/50">Rendah</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($incident->status === 'diverifikasi')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-100">Menunggu Petugas</span>
                                    @elseif($incident->status === 'diproses')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">Dalam Proses</span>
                                    @elseif($incident->status === 'ditangani')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-cyan-50 text-cyan-700 border border-cyan-100">Ditangani</span>
                                    @elseif($incident->status === 'selesai')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-100">Selesai</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-100 font-bold">Ditolak</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <a href="{{ route('perangkat.incidents.show', $incident->id) }}" class="inline-flex items-center space-x-1 text-xs font-bold bg-slate-105 hover:bg-slate-200 text-slate-700 px-3 py-1.5 rounded-xl transition-premium hover:-translate-y-0.5">
                                        <i data-lucide="clipboard-list" class="w-3.5 h-3.5"></i>
                                        <span>Kelola</span>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if($incidents->hasPages())
                <div class="px-6 py-4 border-t border-slate-100">
                    {{ $incidents->appends(request()->query())->links() }}
                </div>
            @endif
        @endif
        
    </div>

</div>
@endsection
