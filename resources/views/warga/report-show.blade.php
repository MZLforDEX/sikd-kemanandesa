@extends('layouts.app')

@section('title', 'Detail Laporan Keamanan')

@section('content')
<div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    
    <!-- Navigation Back Link -->
    <a href="{{ route('warga.dashboard') }}" class="inline-flex items-center space-x-1.5 text-xs font-bold text-slate-500 hover:text-slate-900 transition-premium hover:-translate-x-0.5">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
        <span>Kembali ke Dashboard</span>
    </a>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- Left Side: Report Details & Attachment -->
        <div class="lg:col-span-7 space-y-6">
            
            <div class="bg-white border border-slate-200/60 rounded-3xl shadow-premium-sm overflow-hidden font-medium">
                <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Detail Laporan</span>
                    
                    @if($report->status === 'baru')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-800 border border-slate-200/60">Baru</span>
                    @elseif($report->status === 'diverifikasi')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-100 font-bold">Terverifikasi</span>
                    @elseif($report->status === 'diproses')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-100 font-bold">Sedang Diproses</span>
                    @elseif($report->status === 'ditangani')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-cyan-50 text-cyan-700 border border-cyan-100 font-bold">Ditangani Lapangan</span>
                    @elseif($report->status === 'selesai')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-100 font-bold">Kasus Selesai</span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-800 border border-rose-100 font-bold">Ditolak</span>
                    @endif
                </div>

                <div class="p-6 space-y-5">
                    <div>
                        <h2 class="text-xl font-extrabold text-slate-900 tracking-tight leading-tight">{{ $report->title }}</h2>
                        <span class="text-xs text-slate-450 mt-1 block font-normal">Dilaporkan pada {{ $report->reported_at->format('d-M-Y H:i') }} WIB</span>
                    </div>

                    <div class="space-y-1 text-sm text-slate-650">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Lokasi</span>
                        <p class="font-bold text-slate-800 flex items-center space-x-1">
                            <i data-lucide="map-pin" class="w-4 h-4 text-indigo-650"></i>
                            <span>{{ $report->location }}</span>
                        </p>
                    </div>

                    <div class="space-y-1.5 text-sm border-t border-slate-100 pt-4">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Deskripsi Kronologi</span>
                        <p class="whitespace-pre-line leading-relaxed font-normal text-slate-700">{{ $report->description }}</p>
                    </div>

                    <div class="space-y-1.5 text-sm border-t border-slate-100 pt-4">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Peta Lokasi Kejadian (GIS/Maps)</span>
                        <div id="map" class="h-48 w-full rounded-2xl border border-slate-200 mt-2 overflow-hidden z-10"></div>
                        <script>
                            document.addEventListener("DOMContentLoaded", function() {
                                var lat = {{ $report->latitude ?? -3.946944 }};
                                var lng = {{ $report->longitude ?? 121.351028 }};
                                var map = L.map('map').setView([lat, lng], 15);
                                
                                L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                                    attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community'
                                }).addTo(map);

                                 var markerIcon = L.icon({
                                     iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
                                     shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                                     iconSize: [18, 30],
                                     iconAnchor: [9, 30],
                                     popupAnchor: [1, -26],
                                     shadowSize: [30, 30]
                                 });

                                 var popupHtml = '<b>Lokasi:</b> ' + {!! json_encode(e($report->location)) !!} + '<br><span class="text-slate-400 font-light text-[11px]">Sektor Pengawasan Keamanan</span>';
                                 L.marker([lat, lng], { icon: markerIcon }).addTo(map)
                                     .bindPopup(popupHtml)
                                     .openPopup();
                            });
                        </script>
                    </div>

                    @if($report->attachments->isNotEmpty())
                        <div class="border-t border-slate-100 pt-4 space-y-2">
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Bukti Foto</span>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                @foreach($report->attachments as $attachment)
                                    <div class="border border-slate-200 rounded-2xl overflow-hidden shadow-premium-sm group cursor-zoom-in bg-slate-50">
                                        <img src="{{ asset('storage/' . $attachment->file_path) }}" alt="{{ $attachment->file_name }}" class="w-full h-40 object-cover group-hover:scale-105 transition duration-300">
                                        <div class="p-2.5 bg-white border-t border-slate-100 text-xs truncate font-bold text-slate-700">
                                            {{ $attachment->file_name }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                </div>
            </div>

        </div>

        <!-- Right Side: Incident Handling Timeline -->
        <div class="lg:col-span-5 space-y-6">
            
            <div class="bg-white border border-slate-200/60 rounded-3xl shadow-premium-sm p-6 space-y-6 font-medium">
                <h3 class="font-extrabold text-slate-900 text-lg border-b border-slate-100 pb-3">Progres Penanganan</h3>
                
                @if(!$report->incident)
                    <div class="flex flex-col items-center justify-center py-6 text-center text-slate-400 space-y-2 font-medium">
                        <i data-lucide="clock" class="w-8 h-8 text-slate-350 animate-pulse"></i>
                        <h4 class="font-extrabold text-sm text-slate-700">Menunggu Verifikasi</h4>
                        <p class="text-xs text-slate-500 max-w-[200px] leading-relaxed font-normal">Perangkat desa sedang meneliti laporan Anda untuk diverifikasi.</p>
                        
                        @php
                            $isPatrolActiveToday = false;
                            if (auth()->user()->hasRole('warga')) {
                                $isPatrolActiveToday = auth()->user()->patrolSchedules()->whereDate('patrol_date', now()->toDateString())->exists();
                            }
                        @endphp
                        @if($isPatrolActiveToday)
                            <div class="pt-4 border-t border-slate-100 w-full mt-3">
                                <form action="{{ route('warga.reports.proses', $report->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-4 rounded-xl text-xs transition duration-300 shadow-md">
                                        <i data-lucide="shield" class="w-4 h-4"></i>
                                        <span>Proses & Tangani Langsung</span>
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                @else
                    <!-- Vertical Timeline -->
                    <div class="relative pl-6 border-l border-slate-200 space-y-8 font-medium">
                        
                        <!-- Step 1: Laporan Dibuat -->
                        <div class="relative text-slate-800">
                            <!-- Bullet -->
                            <span class="absolute -left-[31px] top-1.5 flex h-4 w-4 items-center justify-center rounded-full bg-emerald-100 ring-4 ring-white border border-emerald-500">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                            </span>
                            <div>
                                <h4 class="text-sm font-bold text-slate-900">Laporan Diterima</h4>
                                <p class="text-[10px] text-slate-450 mt-0.5 font-normal">{{ $report->created_at->format('d-m-Y H:i') }} WIB</p>
                                <p class="text-xs text-slate-500 mt-1 leading-relaxed font-normal">Laporan berhasil terkirim ke dalam database desa.</p>
                            </div>
                        </div>

                        <!-- Step 2: Laporan Diverifikasi -->
                        <div class="relative text-slate-800">
                            <span class="absolute -left-[31px] top-1.5 flex h-4 w-4 items-center justify-center rounded-full bg-blue-100 ring-4 ring-white border border-blue-500">
                                <span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span>
                            </span>
                            <div>
                                <h4 class="text-sm font-bold text-slate-900">Diverifikasi Perangkat</h4>
                                <p class="text-[10px] text-slate-450 mt-0.5 font-normal">{{ $report->incident->created_at->format('d-m-Y H:i') }} WIB</p>
                                <p class="text-xs text-slate-500 mt-1 leading-relaxed font-normal">Laporan diverifikasi sah. Status laporan diubah menjadi Kejadian Resmi Keamanan Desa.</p>
                            </div>
                        </div>

                        <!-- Step 3: Tindak Lanjut Satpam -->
                        @php
                            $handlingRecords = $report->incident->handlingRecords;
                        @endphp

                        @forelse($handlingRecords as $index => $record)
                            <div class="relative text-slate-800">
                                @php
                                    $isLast = $index === $handlingRecords->count() - 1;
                                    $bulletColor = $record->status_after === 'selesai' ? 'bg-emerald-100 border-emerald-500 text-emerald-500' : 'bg-indigo-50 border-indigo-500 text-indigo-700';
                                    $dotColor = $record->status_after === 'selesai' ? 'bg-emerald-500' : 'bg-indigo-650';
                                @endphp
                                <span class="absolute -left-[31px] top-1.5 flex h-4 w-4 items-center justify-center rounded-full {{ $bulletColor }} ring-4 ring-white border">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $dotColor }}"></span>
                                </span>
                                <div>
                                    <h4 class="text-sm font-bold text-slate-900">
                                        @if(str_contains($record->action_taken, 'TUGAS BARU:'))
                                            Tugas Diserahkan Ke Satpam
                                        @else
                                            Tindak Lanjut Lapangan
                                        @endif
                                    </h4>
                                    <p class="text-[10px] text-slate-450 mt-0.5 font-normal">{{ $record->handled_at->format('d-m-Y H:i') }} WIB</p>
                                    <p class="text-xs text-slate-500 mt-1 leading-relaxed font-normal">
                                        <strong>Petugas:</strong> {{ $record->handler->name }} <br>
                                        <strong>Tindakan:</strong> {{ str_replace('TUGAS BARU:', '', $record->action_taken) }}
                                    </p>
                                    @if($record->result)
                                        <p class="text-xs text-emerald-700 bg-emerald-50 border border-emerald-100/60 p-2.5 rounded-2xl mt-2 leading-relaxed font-normal">
                                            <strong>Hasil:</strong> {{ $record->result }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="relative text-slate-800">
                                <span class="absolute -left-[31px] top-1.5 flex h-4 w-4 items-center justify-center rounded-full bg-amber-100 ring-4 ring-white border border-amber-500">
                                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500 animate-ping"></span>
                                </span>
                                <div>
                                    <h4 class="text-sm font-bold text-slate-900">Penjadwalan Petugas</h4>
                                    <p class="text-xs text-slate-500 mt-1 leading-relaxed font-normal">Kejadian sedang dalam proses disposisi tugas kepada anggota patroli/satpam desa.</p>
                                    
                                    @php
                                        $isPatrolActiveToday = false;
                                        if (auth()->user()->hasRole('warga')) {
                                            $isPatrolActiveToday = auth()->user()->patrolSchedules()->whereDate('patrol_date', now()->toDateString())->exists();
                                        }
                                    @endphp
                                    @if($isPatrolActiveToday)
                                        <div class="pt-4 mt-2">
                                            <form action="{{ route('warga.reports.proses', $report->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-3.5 rounded-xl text-xs transition duration-300 shadow-md">
                                                    <i data-lucide="shield" class="w-4 h-4"></i>
                                                    <span>Proses & Tangani Laporan</span>
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforelse

                    </div>
                @endif
            </div>

        </div>

    </div>

</div>
@endsection
