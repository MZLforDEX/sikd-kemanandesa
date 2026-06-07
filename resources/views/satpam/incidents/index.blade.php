@extends('layouts.app')

@section('title', 'Tugas Penanganan Kejadian Anda')

@section('content')
<div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    
    <!-- Header -->
    <div class="flex justify-between items-center bg-white p-6 rounded-3xl border border-slate-200/60 shadow-premium-sm">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Disposisi Tugas Penanganan Kejadian</h1>
            <p class="text-sm text-slate-500 mt-1 font-normal">Daftar kasus atau kejadian yang didelegasikan oleh perangkat desa untuk Anda tindaklanjuti di lapangan.</p>
        </div>
    </div>

    <!-- Tasks List -->
    <div class="bg-white border border-slate-200/60 rounded-3xl overflow-hidden shadow-premium-sm font-medium">
        
        @if($incidents->isEmpty())
            <div class="p-16 text-center text-slate-400">
                <i data-lucide="check-circle" class="w-12 h-12 mx-auto mb-3 text-emerald-500"></i>
                <h4 class="font-extrabold text-slate-700 text-sm">Tidak Ada Tugas Penanganan</h4>
                <p class="text-xs text-slate-500 mt-1 font-normal">Anda tidak memiliki tugas penanganan kejadian aktif saat ini.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-slate-50/50 text-slate-500 border-b border-slate-100 text-[10px] font-bold uppercase tracking-wider">
                            <th class="px-6 py-4">Tanggal Kejadian</th>
                            <th class="px-6 py-4">Nama Kejadian & Lokasi</th>
                            <th class="px-6 py-4">Kategori</th>
                            <th class="px-6 py-4">Keparahan</th>
                            <th class="px-6 py-4">Status Kerja</th>
                            <th class="px-6 py-4 text-right">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @foreach($incidents as $incident)
                            <tr class="hover:bg-slate-50/25 transition text-slate-800">
                                <td class="px-6 py-4 whitespace-nowrap text-slate-500 text-xs">
                                    {{ $incident->incident_date->format('d-m-Y') }} <br>
                                    <span class="text-[10px] text-slate-400 font-normal">{{ $incident->incident_date->format('H:i') }} WIB</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-slate-900 block font-bold">{{ $incident->title }}</span>
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
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-800 border border-rose-100">Gawat / Tinggi</span>
                                    @elseif($incident->severity === 'sedang')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-100">Sedang</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-850 border border-slate-200/50">Rendah</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($incident->status === 'diproses')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">Diproses</span>
                                    @elseif($incident->status === 'ditangani')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-cyan-50 text-cyan-700 border border-cyan-100">Ditangani</span>
                                    @elseif($incident->status === 'selesai')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-100">Selesai</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-800 border border-slate-200/60">Menunggu</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <a href="{{ route('warga.ronda.incidents.show', $incident->id) }}" class="inline-flex items-center space-x-1.5 text-xs font-bold bg-indigo-655 hover:bg-indigo-700 text-white px-3.5 py-2 rounded-xl transition-premium hover:-translate-y-0.5 shadow-premium-sm">
                                        <i data-lucide="clipboard-check" class="w-3.5 h-3.5"></i>
                                        <span>Progres Kerja</span>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($incidents->hasPages())
                <div class="px-6 py-4 border-t border-slate-100">
                    {{ $incidents->links() }}
                </div>
            @endif
        @endif
        
    </div>

</div>
@endsection
