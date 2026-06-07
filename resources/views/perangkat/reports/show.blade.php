@extends('layouts.app')

@section('title', 'Detail Laporan Warga')

@section('content')
<div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    
    <!-- Navigation Back Link -->
    <a href="{{ route('perangkat.reports.index') }}" class="inline-flex items-center space-x-1.5 text-xs font-bold text-slate-500 hover:text-slate-900 transition-premium hover:-translate-x-0.5">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
        <span>Kembali ke Daftar Laporan</span>
    </a>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Left Column: Report Information -->
        <div class="lg:col-span-7 space-y-6">
            <div class="bg-white border border-slate-200/60 rounded-3xl shadow-premium-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Laporan Keamanan</span>
                    @if($report->status === 'baru')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-100">Baru</span>
                    @elseif($report->status === 'diverifikasi')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-100">Terverifikasi</span>
                    @elseif($report->status === 'diproses')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">Diproses</span>
                    @elseif($report->status === 'ditangani')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-cyan-50 text-cyan-700 border border-cyan-100">Ditangani</span>
                    @elseif($report->status === 'selesai')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">Selesai</span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-100">Ditolak</span>
                    @endif
                </div>

                <div class="p-6 space-y-6">
                    <div>
                        <h2 class="text-xl font-extrabold text-slate-900 leading-tight tracking-tight">{{ $report->title }}</h2>
                        <span class="text-xs text-slate-450 mt-1 block font-normal">Dikirim oleh <strong>{{ $report->user->name }}</strong> • {{ $report->reported_at->format('d-m-Y H:i') }} WIB</span>
                    </div>

                    <!-- Reporter Profile -->
                    <div class="bg-slate-50 p-4 rounded-2xl grid grid-cols-2 gap-4 text-xs font-medium">
                        <div>
                            <span class="font-bold text-slate-400 uppercase tracking-wider block">No. Telepon</span>
                            <span class="font-bold text-slate-800 block mt-0.5">{{ $report->user->phone ?? 'Tidak ada' }}</span>
                        </div>
                        <div>
                            <span class="font-bold text-slate-400 uppercase tracking-wider block">Alamat Pelapor</span>
                            <span class="font-bold text-slate-800 block mt-0.5">{{ $report->user->address ?? 'Tidak ada' }}</span>
                        </div>
                    </div>

                    <div class="space-y-1.5 text-sm">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Lokasi Kejadian</span>
                        <p class="font-bold text-slate-800 flex items-center space-x-1">
                            <i data-lucide="map-pin" class="w-4 h-4 text-indigo-650"></i>
                            <span>{{ $report->location }}</span>
                        </p>
                    </div>

                    <div class="space-y-1.5 text-sm">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Peta Lokasi (GIS/Maps)</span>
                        <div id="map" class="h-44 w-full rounded-2xl border border-slate-200 mt-2 overflow-hidden z-10"></div>
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

                                 L.marker([lat, lng], { icon: markerIcon }).addTo(map)
                                     .bindPopup('<b>Aduan Warga:</b> {{ addslashes($report->location) }}')
                                     .openPopup();
                            });
                        </script>
                    </div>

                    <div class="space-y-2 text-sm border-t border-slate-100 pt-4">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Deskripsi Kejadian</span>
                        <p class="whitespace-pre-line leading-relaxed font-normal text-slate-700">{{ $report->description }}</p>
                    </div>

                    @if($report->attachments->isNotEmpty())
                        <div class="border-t border-slate-100 pt-4 space-y-3">
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Bukti File Pendukung</span>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                @foreach($report->attachments as $attachment)
                                    <div class="border border-slate-200 rounded-2xl overflow-hidden shadow-premium-sm bg-slate-50">
                                        <img src="{{ asset('storage/' . $attachment->file_path) }}" alt="{{ $attachment->file_name }}" class="w-full h-40 object-cover">
                                        <div class="p-3 bg-white border-t border-slate-100 text-xs truncate font-bold text-slate-700">
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

        <!-- Right Column: Verification Form / Linked Incident -->
        <div class="lg:col-span-5 space-y-6">
            
            @if($report->status === 'baru')
                <!-- Verification Form -->
                <div class="bg-white border border-slate-200/60 rounded-3xl p-6 shadow-premium-sm space-y-6">
                    <h3 class="font-extrabold text-slate-900 text-lg border-b border-slate-100 pb-3">Tindakan Verifikasi</h3>
                    
                    <form action="{{ route('perangkat.reports.verify', $report->id) }}" method="POST" class="space-y-4">
                        @csrf

                        <!-- Action selector -->
                        <div class="space-y-1.5">
                            <label for="action" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Pilih Tindakan</label>
                            <select name="action" id="action" onchange="toggleVerificationFields(this.value)" class="block w-full rounded-xl border border-slate-200 py-2.5 px-3 text-sm focus:border-indigo-500 focus:outline-hidden focus:ring-1 focus:ring-indigo-500 bg-white font-medium text-slate-800">
                                <option value="verify">Verifikasi & Jadikan Kejadian Resmi</option>
                                <option value="reject">Tolak Laporan (Tidak Valid)</option>
                            </select>
                        </div>

                        <!-- Notes -->
                        <div class="space-y-1.5">
                            <label for="notes" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Catatan / Alasan Penolakan</label>
                            <textarea name="notes" id="notes" rows="3" placeholder="Masukkan catatan opsional atau alasan penolakan jika laporan ditolak..."
                                class="block w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:outline-hidden focus:ring-1 focus:ring-indigo-500 font-medium text-slate-800"></textarea>
                        </div>

                        <!-- Incident Specific Fields (Only active when 'verify' is chosen) -->
                        <div id="incident-fields" class="space-y-4 border-t border-slate-100 pt-4">
                            
                            <div class="space-y-1.5">
                                <label for="category" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Kategori Kejadian</label>
                                <select name="category" id="category" class="block w-full rounded-xl border border-slate-200 py-2.5 px-3 text-sm focus:border-indigo-500 focus:outline-hidden focus:ring-1 focus:ring-indigo-500 bg-white font-medium text-slate-800">
                                    <option value="pencurian">Pencurian / Penjarahan</option>
                                    <option value="kebakaran">Kebakaran</option>
                                    <option value="kehilangan">Kehilangan Barang</option>
                                    <option value="keributan">Keributan / Perkelahian</option>
                                    <option value="bencana alam">Bencana Alam</option>
                                    <option value="lainnya">Lainnya</option>
                                </select>
                            </div>

                            <div class="space-y-1.5">
                                <label for="severity" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Tingkat Keparahan</label>
                                <select name="severity" id="severity" class="block w-full rounded-xl border border-slate-200 py-2.5 px-3 text-sm focus:border-indigo-500 focus:outline-hidden focus:ring-1 focus:ring-indigo-500 bg-white font-medium text-slate-800">
                                    <option value="rendah">Rendah (Low)</option>
                                    <option value="sedang" selected>Sedang (Medium)</option>
                                    <option value="tinggi">Tinggi (High / Emergency)</option>
                                </select>
                            </div>

                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="w-full bg-indigo-650 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-xl shadow-premium-sm flex items-center justify-center space-x-2 transition-premium hover:-translate-y-0.5">
                            <i data-lucide="shield-check" class="w-4 h-4"></i>
                            <span>Simpan Verifikasi</span>
                        </button>

                    </form>
                </div>
            @else
                <!-- Show Linked Incident / Status -->
                <div class="bg-white border border-slate-200/60 rounded-3xl p-6 shadow-premium-sm space-y-4">
                    <h3 class="font-extrabold text-slate-900 text-lg border-b border-slate-100 pb-3">Status Verifikasi</h3>
                    
                    @if($report->status === 'ditolak')
                        <div class="bg-rose-50 border border-rose-100 text-rose-900 p-4 rounded-2xl space-y-2 text-xs font-medium">
                            <h4 class="font-bold flex items-center space-x-1 text-rose-800">
                                <i data-lucide="alert-octagon" class="w-4 h-4 text-rose-600"></i>
                                <span>Laporan Ditolak</span>
                            </h4>
                            <p class="font-normal text-rose-700 leading-relaxed">Laporan warga ini telah ditolak oleh Perangkat Desa dan tidak diteruskan menjadi kejadian resmi.</p>
                        </div>
                    @else
                        <div class="bg-emerald-50 border border-emerald-100 text-emerald-950 p-4 rounded-2xl space-y-3 text-xs font-medium">
                            <h4 class="font-bold flex items-center space-x-1 text-emerald-800">
                                <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i>
                                <span>Telah Diverifikasi</span>
                            </h4>
                            <p class="font-normal text-emerald-700 leading-relaxed">Laporan ini sah dan telah diteruskan menjadi Kejadian Resmi Keamanan Desa.</p>
                            
                            @if($report->incident)
                                <a href="{{ route('perangkat.incidents.show', $report->incident->id) }}" class="mt-2 w-full inline-flex items-center justify-center space-x-1 bg-white hover:bg-slate-50 text-indigo-650 border border-slate-200/60 font-bold py-2 rounded-xl text-xs transition-premium hover:-translate-y-0.5 shadow-premium-sm">
                                    <span>Lihat Penanganan Kejadian</span>
                                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            @endif

        </div>

    </div>

</div>

<script>
    function toggleVerificationFields(val) {
        const fields = document.getElementById('incident-fields');
        if (val === 'reject') {
            fields.classList.add('hidden');
        } else {
            fields.classList.remove('hidden');
        }
    }
</script>
@endsection
