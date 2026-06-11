@extends('layouts.app')

@section('title', 'Kelola Kejadian Keamanan')

@section('content')
<div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    
    <!-- Navigation Back Link -->
    <a href="{{ route('perangkat.incidents.index') }}" class="inline-flex items-center space-x-1.5 text-xs font-bold text-slate-500 hover:text-slate-900 transition-premium hover:-translate-x-0.5">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
        <span>Kembali ke Daftar Kejadian</span>
    </a>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Left: Incident Information & Timeline -->
        <div class="lg:col-span-7 space-y-6">
            
            <!-- Details Card -->
            <div class="bg-white border border-slate-200/60 rounded-3xl shadow-premium-sm overflow-hidden font-medium">
                <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Kejadian Keamanan Resmi</span>
                    <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full border uppercase
                        {{ $incident->status === 'selesai' ? 'bg-emerald-50 border-emerald-100 text-emerald-800' : 'bg-rose-50 border-rose-100 text-rose-800' }}">
                        {{ $incident->status }}
                    </span>
                </div>

                <div class="p-6 space-y-6">
                    <div>
                        <div class="flex items-center space-x-2">
                            <h2 class="text-xl font-extrabold text-slate-900 leading-tight tracking-tight">{{ $incident->title }}</h2>
                            <span class="px-2 py-0.5 text-[9px] font-bold rounded-sm uppercase tracking-wider
                                {{ $incident->severity === 'tinggi' ? 'bg-rose-55 text-rose-800' : ($incident->severity === 'sedang' ? 'bg-amber-50 text-amber-800' : 'bg-slate-100 text-slate-800') }}">
                                {{ $incident->severity }}
                            </span>
                        </div>
                        <span class="text-xs text-slate-450 mt-1 block font-normal">Tercatat Pada: {{ $incident->incident_date->format('d-m-Y H:i') }} WIB</span>
                    </div>

                    <div class="grid grid-cols-2 gap-4 bg-slate-50 p-4 rounded-2xl text-xs">
                        <div>
                            <span class="font-bold text-slate-400 uppercase tracking-wider block">Kategori</span>
                            <span class="font-bold text-slate-800 block mt-0.5 capitalize">{{ $incident->category }}</span>
                        </div>
                        <div>
                            <span class="font-bold text-slate-400 uppercase tracking-wider block">Lokasi</span>
                            <span class="font-bold text-slate-800 block mt-0.5 leading-relaxed">{{ $incident->location }}</span>
                        </div>
                    </div>

                    <div class="space-y-1.5 text-sm">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Peta Lokasi Kejadian (GIS/Maps)</span>
                        <div id="map" class="h-44 w-full rounded-2xl border border-slate-200 mt-2 overflow-hidden z-10"></div>
                        <script>
                            document.addEventListener("DOMContentLoaded", function() {
                                var lat = {{ $incident->report->latitude ?? -3.946944 }};
                                var lng = {{ $incident->report->longitude ?? 121.351028 }};
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

                                 var popupHtml = '<b>Lokasi Kejadian:</b> ' + {!! json_encode($incident->location) !!} + '<br><span class="text-rose-500 font-bold text-[10px] uppercase">Zona Rawan Keamanan</span>';
                                 L.marker([lat, lng], { icon: markerIcon }).addTo(map)
                                     .bindPopup(popupHtml)
                                     .openPopup();
                            });
                        </script>
                    </div>

                    <div class="space-y-1.5 text-sm border-t border-slate-100 pt-4">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Deskripsi Kronologi Kejadian</span>
                        <p class="whitespace-pre-line leading-relaxed font-normal text-slate-700">{{ $incident->description }}</p>
                    </div>

                    @if($incident->attachments->isNotEmpty())
                        <div class="border-t border-slate-100 pt-4 space-y-3">
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Bukti Foto / Gambar</span>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                @foreach($incident->attachments as $attachment)
                                    <div class="border border-slate-200 rounded-2xl overflow-hidden shadow-premium-sm bg-slate-50">
                                        <img src="{{ asset('storage/' . $attachment->file_path) }}" alt="{{ $attachment->file_name }}" class="w-full h-40 object-cover">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Timeline of Handling Actions -->
            <div class="bg-white border border-slate-200/60 rounded-3xl p-6 shadow-premium-sm space-y-6 font-medium">
                <h3 class="font-extrabold text-slate-900 text-base border-b border-slate-100 pb-3">Riwayat Penanganan Lapangan</h3>
                
                @if($incident->handlingRecords->isEmpty())
                    <div class="text-center py-6 text-slate-400">
                        <i data-lucide="clipboard" class="w-10 h-10 mx-auto mb-2 text-slate-350"></i>
                        <p class="text-xs font-normal">Belum ada catatan penanganan lapangan untuk kejadian ini.</p>
                    </div>
                @else
                    <div class="relative pl-6 border-l border-slate-200 space-y-8">
                        @foreach($incident->handlingRecords as $record)
                            <div class="relative font-medium text-slate-800">
                                @php
                                    $bulletColor = $record->status_after === 'selesai' ? 'bg-emerald-100 border-emerald-500 text-emerald-500' : 'bg-indigo-100 border-indigo-500 text-indigo-600';
                                    $dotColor = $record->status_after === 'selesai' ? 'bg-emerald-500' : 'bg-indigo-650';
                                @endphp
                                <span class="absolute -left-[31px] top-1.5 flex h-4 w-4 items-center justify-center rounded-full {{ $bulletColor }} ring-4 ring-white border">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $dotColor }}"></span>
                                </span>
                                <div class="space-y-1">
                                    <h4 class="text-sm font-bold text-slate-900">
                                        @if(str_contains($record->action_taken, 'TUGAS BARU:'))
                                            Tugas Baru Diserahkan
                                        @else
                                            Update Penanganan Lapangan
                                        @endif
                                    </h4>
                                    <p class="text-[10px] text-slate-450 font-normal">{{ $record->handled_at->format('d-m-Y H:i') }} WIB • Oleh: <strong>{{ $record->handler->name }}</strong></p>
                                    <p class="text-xs text-slate-605 leading-relaxed mt-1 font-normal">
                                        <strong>Tindakan:</strong> {{ str_replace('TUGAS BARU:', '', $record->action_taken) }}
                                    </p>
                                    @if($record->result)
                                        <div class="text-xs text-slate-700 bg-slate-50 border border-slate-200/70 p-3 rounded-2xl mt-2 leading-relaxed font-normal">
                                            <strong class="font-bold text-slate-900">Hasil/Hasil Akhir:</strong> {{ $record->result }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>

        <!-- Right: Actions Panel (Assign Officer & Update Status) -->
        <div class="lg:col-span-5 space-y-6">
            
            <!-- Assign Guard Form -->
            <div class="bg-white border border-slate-200/60 rounded-3xl p-6 shadow-premium-sm space-y-5 font-medium">
                <h3 class="font-extrabold text-slate-900 text-base border-b border-slate-100 pb-3 flex items-center space-x-1.5">
                    <i data-lucide="user-check" class="w-5 h-5 text-indigo-650"></i>
                    <span>Tugaskan Warga Ronda</span>
                </h3>

                <form action="{{ route('perangkat.incidents.assign', $incident->id) }}" method="POST" class="space-y-4">
                    @csrf

                    <div class="space-y-1.5">
                        <label for="handler_id" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Pilih Warga Petugas Ronda</label>
                        <select name="handler_id" id="handler_id" class="block w-full rounded-xl border border-slate-200 py-2.5 px-3 text-sm focus:border-indigo-500 focus:outline-hidden focus:ring-1 focus:ring-indigo-500 bg-white font-medium text-slate-800">
                            @foreach($satpams as $satpam)
                                <option value="{{ $satpam->id }}">{{ $satpam->name }} ({{ $satpam->phone ?? '-' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label for="instruction" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Instruksi Tugas</label>
                        <textarea name="instruction" id="instruction" rows="3" required placeholder="Tuliskan instruksi penanganan lapangan. Contoh: Hubungi warga di lokasi, amankan barang bukti, lakukan ronda malam ekstra."
                            class="block w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:outline-hidden focus:ring-1 focus:ring-indigo-500 font-medium text-slate-800"></textarea>
                    </div>

                    <button type="submit" class="w-full bg-indigo-650 hover:bg-indigo-700 text-white font-bold py-2.5 px-4 rounded-xl shadow-premium-sm flex items-center justify-center space-x-2 transition-premium hover:-translate-y-0.5">
                        <i data-lucide="send" class="w-4 h-4"></i>
                        <span>Disposisi Tugas</span>
                    </button>
                </form>
            </div>

            <!-- Update Status Form -->
            <div class="bg-white border border-slate-200/60 rounded-3xl p-6 shadow-premium-sm space-y-4 font-medium">
                <h3 class="font-extrabold text-slate-900 text-base border-b border-slate-100 pb-3 flex items-center space-x-1.5">
                    <i data-lucide="activity" class="w-5 h-5 text-indigo-650"></i>
                    <span>Perbarui Status Kejadian</span>
                </h3>

                @if($incident->status === 'diproses' || $incident->status === 'ditangani')
                <form action="{{ route('perangkat.incidents.status', $incident->id) }}" method="POST">
                    @csrf
                    @if($incident->status === 'diproses')
                        <input type="hidden" name="status" value="ditangani">
                        <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 px-4 rounded-xl shadow-premium-sm flex items-center justify-center space-x-2 transition-premium hover:-translate-y-0.5">
                            <i data-lucide="shield-check" class="w-4 h-4"></i>
                            <span>Ubah Status ke: Ditangani</span>
                        </button>
                    @elseif($incident->status === 'ditangani')
                        <input type="hidden" name="status" value="selesai">
                        <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 px-4 rounded-xl shadow-premium-sm flex items-center justify-center space-x-2 transition-premium hover:-translate-y-0.5">
                            <i data-lucide="check-circle" class="w-4 h-4"></i>
                            <span>Tandai Kejadian Selesai</span>
                        </button>
                    @endif
                </form>
                <div class="relative flex py-2 items-center">
                    <div class="flex-grow border-t border-slate-200"></div>
                    <span class="flex-shrink mx-4 text-slate-400 text-[10px] font-bold uppercase tracking-wider">Atau Manual</span>
                    <div class="flex-grow border-t border-slate-200"></div>
                </div>
                @endif

                <form action="{{ route('perangkat.incidents.status', $incident->id) }}" method="POST" class="space-y-4">
                    @csrf
                    
                    <div class="space-y-1.5">
                        <label for="status" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Pilih Status</label>
                        <select name="status" id="status" class="block w-full rounded-xl border border-slate-200 py-2.5 px-3 text-sm focus:border-indigo-550 focus:outline-hidden focus:ring-1 focus:ring-indigo-500 bg-white font-medium text-slate-800">
                            <option value="diverifikasi" {{ $incident->status === 'diverifikasi' ? 'selected' : '' }}>Diverifikasi (Menunggu Petugas)</option>
                            <option value="diproses" {{ $incident->status === 'diproses' ? 'selected' : '' }}>Diproses (Sedang Ditangani)</option>
                            <option value="ditangani" {{ $incident->status === 'ditangani' ? 'selected' : '' }}>Ditangani Lapangan</option>
                            <option value="selesai" {{ $incident->status === 'selesai' ? 'selected' : '' }}>Selesai / Dituntaskan</option>
                            <option value="ditolak" {{ $incident->status === 'ditolak' ? 'selected' : '' }}>Dibatalkan / Ditolak</option>
                        </select>
                    </div>

                    <button type="submit" class="w-full bg-slate-800 hover:bg-slate-900 text-white font-bold py-2.5 px-4 rounded-xl shadow-premium-sm flex items-center justify-center space-x-2 transition-premium hover:-translate-y-0.5">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        <span>Perbarui Status</span>
                    </button>
                </form>
            </div>

        </div>

    </div>

</div>
@endsection
