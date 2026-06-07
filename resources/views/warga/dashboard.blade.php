@extends('layouts.app')

@section('title', 'Dashboard Warga')

@section('content')
<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6 sm:space-y-8">

    <x-dashboard.hero
        variant="indigo"
        badge="Portal Warga"
        :title="'Halo, ' . auth()->user()->name . '!'"
        subtitle="Laporkan kejadian keamanan, pantau status laporan Anda, dan kirim sinyal darurat bila diperlukan."
    >
        <x-slot:actions>
            <a href="{{ route('warga.reports.create') }}" class="btn-hero-primary">
                <i data-lucide="file-plus" class="w-4 h-4"></i>
                <span>Buat Laporan</span>
            </a>
            <form id="emergency-form" action="{{ route('warga.emergency') }}" method="POST" class="inline">
                @csrf
                <input type="hidden" name="latitude" id="emergency-lat" value="">
                <input type="hidden" name="longitude" id="emergency-lng" value="">
                <input type="hidden" name="accuracy" id="emergency-accuracy" value="">
                <button type="button" id="panic-button" class="btn-hero-danger animate-pulse-slow">
                    <i data-lucide="alert-octagon" class="w-4 h-4"></i>
                    <span>Panic Button</span>
                </button>
            </form>
        </x-slot:actions>
    </x-dashboard.hero>

    <!-- Feedback Alerts -->
    @if(session('emergency_triggered'))
        <div class="bg-rose-50 border-2 border-rose-100 text-rose-900 px-6 py-5 rounded-3xl flex items-start space-x-4 shadow-premium-sm animate-pulse">
            <div class="p-3 bg-rose-600 text-white rounded-2xl shrink-0">
                <i data-lucide="shield-alert" class="w-6 h-6 animate-spin"></i>
            </div>
            <div class="flex-1">
                <h4 class="font-extrabold text-base text-rose-800">🚨 LOKASI DARURAT ANDA BERHASIL DIKIRIM!</h4>
                <p class="text-sm text-rose-700 mt-1 font-medium">{{ session('emergency_triggered') }}</p>
                <div class="mt-3 flex items-center space-x-2">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-rose-200/80 text-rose-800 space-x-1.5">
                        <span class="h-2 w-2 rounded-full bg-rose-650 animate-ping"></span>
                        <span>Koordinat Anda telah dipetakan & Petugas sedang bergerak</span>
                    </span>
                </div>
            </div>
        </div>
    @endif

    @if($errors->has('latitude') || $errors->has('longitude'))
        <div class="bg-amber-50 border border-amber-200 text-amber-900 px-6 py-4 rounded-3xl flex items-start gap-3 shadow-premium-sm">
            <div class="p-2 bg-amber-500 text-white rounded-xl shrink-0">
                <i data-lucide="map-pin-off" class="w-5 h-5"></i>
            </div>
            <div>
                <h4 class="font-extrabold text-sm text-amber-900">Lokasi GPS Diperlukan</h4>
                <p class="text-sm text-amber-800 mt-1">{{ $errors->first('latitude') ?: $errors->first('longitude') }}</p>
            </div>
        </div>
    @endif

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200/60 text-emerald-900 px-6 py-4 rounded-3xl flex items-center space-x-3 shadow-premium-sm">
            <div class="p-2 bg-emerald-600 text-white rounded-xl">
                <i data-lucide="check-circle" class="w-5 h-5"></i>
            </div>
            <span class="text-sm font-bold text-emerald-800">{{ session('success') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <x-dashboard.stat label="Total Laporan" :value="$stats['total']" icon="folder" tone="slate" id="stats-total" />
        <x-dashboard.stat label="Laporan Baru" :value="$stats['baru']" icon="file-text" tone="indigo" id="stats-baru" />
        <x-dashboard.stat label="Sedang Diproses" :value="$stats['proses']" icon="loader" tone="amber" id="stats-proses" />
        <x-dashboard.stat label="Selesai" :value="$stats['selesai']" icon="check-circle-2" tone="emerald" id="stats-selesai" />
    </div>

    <x-dashboard.map-panel
        title="Peta Laporan Anda"
        subtitle="Lokasi laporan keamanan yang Anda kirimkan — diperbarui secara real-time"
    />

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Panic button GPS handled by resources/js/emergency-gps.js

            // Coordinate of Desa Awa: -3.946944, 121.351028
            var map = L.map('dashboard-map').setView([-3.946944, 121.351028], 15);
            L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community'
            }).addTo(map);

            // Add center pin for Desa Awa
            var villageCenter = L.marker([-3.946944, 121.351028], {
                icon: L.icon({
                    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-gold.png',
                    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                    iconSize: [18, 30],
                    iconAnchor: [9, 30],
                    popupAnchor: [1, -26],
                    shadowSize: [30, 30]
                })
            }).addTo(map);
            villageCenter.bindPopup("<b>Desa Awa</b><br>Kec. Samaturu, Kab. Kolaka, Sulawesi Tenggara");

            // Loop through reports and add markers
            @foreach($reportsWithCoordinates as $report)
                @if($report->latitude && $report->longitude)
                    (function() {
                        var lat = {{ $report->latitude }};
                        var lng = {{ $report->longitude }};
                        var title = "{{ addslashes($report->title) }}";
                        var status = "{{ $report->status }}";
                        var location = "{{ addslashes($report->location) }}";
                        
                        // Select color marker based on status
                        var markerColor = 'red';
                        if (status === 'selesai') markerColor = 'green';
                        else if (status === 'diproses' || status === 'diverifikasi' || status === 'ditangani') markerColor = 'orange';
                        else if (status === 'ditolak') markerColor = 'grey';

                        var markerIcon = L.icon({
                            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-' + markerColor + '.png',
                            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                            iconSize: [18, 30],
                            iconAnchor: [9, 30],
                            popupAnchor: [1, -26],
                            shadowSize: [30, 30]
                        });

                        var marker = L.marker([lat, lng], { icon: markerIcon }).addTo(map);
                        
                        var statusLabel = '<span class="px-2 py-0.5 text-[9px] font-bold rounded-full bg-slate-100 text-slate-800 border border-slate-200">Baru</span>';
                        if (status === 'selesai') {
                            statusLabel = '<span class="px-2 py-0.5 text-[9px] font-bold rounded-full bg-emerald-50 text-emerald-800 border border-emerald-250">Selesai</span>';
                        } else if (status === 'diproses' || status === 'diverifikasi' || status === 'ditangani') {
                            statusLabel = '<span class="px-2 py-0.5 text-[9px] font-bold rounded-full bg-amber-50 text-amber-805 border border-amber-250">Diproses</span>';
                        } else if (status === 'ditolak') {
                            statusLabel = '<span class="px-2 py-0.5 text-[9px] font-bold rounded-full bg-rose-50 text-rose-800 border border-rose-250">Ditolak</span>';
                        }

                        // Determine action link
                        var actionHtml = '<span>Awa Kondusif</span>';
                        @if($report->user_id === auth()->id())
                            var detailUrl = "{{ route('warga.reports.show', $report->id) }}";
                            actionHtml = `<a href="${detailUrl}" class="font-bold text-indigo-650 hover:text-indigo-850">Detail &rarr;</a>`;
                        @endif

                        var popupHtml = `
                            <div class="p-1 space-y-1.5 min-w-[200px] text-xs">
                                <div class="flex justify-between items-center gap-2">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase">Laporan</span>
                                    ${statusLabel}
                                </div>
                                <h4 class="font-bold text-slate-900 text-xs leading-tight">${title}</h4>
                                <p class="text-[10px] text-slate-550 font-medium mt-0.5">Lokasi: ${location}</p>
                                <div class="border-t border-slate-100 pt-1.5 mt-2 flex justify-between items-center text-[9px] text-slate-450">
                                    <span>Kec. Samaturu</span>
                                    ${actionHtml}
                                </div>
                            </div>
                        `;
                        marker.bindPopup(popupHtml);
                    })();
                @endif
            @endforeach

            // Real-time updates polling
            var maxReportId = {{ \App\Models\Report::max('id') ?? 0 }};
            var currentUserId = {{ auth()->id() }};

            function checkRealtimeUpdates() {
                fetch(`{{ route('reports.realtime_updates') }}?last_id=${maxReportId}`)
                    .then(response => response.json())
                    .then(data => {
                        // Update stats
                        if (data.stats) {
                            document.getElementById('stats-total').textContent = data.stats.total;
                            document.getElementById('stats-baru').textContent = data.stats.baru;
                            document.getElementById('stats-proses').textContent = data.stats.proses;
                            document.getElementById('stats-selesai').textContent = data.stats.selesai;
                        }

                        // Add new reports to map
                        if (data.reports && data.reports.length > 0) {
                            data.reports.forEach(report => {
                                if (report.id > maxReportId) {
                                    maxReportId = report.id;
                                }

                                if (report.latitude && report.longitude) {
                                    // Select color marker based on status
                                    var markerColor = 'red';
                                    if (report.status === 'selesai') markerColor = 'green';
                                    else if (report.status === 'diproses' || report.status === 'diverifikasi' || report.status === 'ditangani') markerColor = 'orange';
                                    else if (report.status === 'ditolak') markerColor = 'grey';

                                    var markerIcon = L.icon({
                                        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-' + markerColor + '.png',
                                        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                                        iconSize: [18, 30],
                                        iconAnchor: [9, 30],
                                        popupAnchor: [1, -26],
                                        shadowSize: [30, 30]
                                    });

                                    var marker = L.marker([report.latitude, report.longitude], { icon: markerIcon }).addTo(map);

                                    var statusLabel = '<span class="px-2 py-0.5 text-[9px] font-bold rounded-full bg-slate-100 text-slate-800 border border-slate-200">Baru</span>';
                                    if (report.status === 'selesai') {
                                        statusLabel = '<span class="px-2 py-0.5 text-[9px] font-bold rounded-full bg-emerald-50 text-emerald-800 border border-emerald-250">Selesai</span>';
                                    } else if (report.status === 'diproses' || report.status === 'diverifikasi' || report.status === 'ditangani') {
                                        statusLabel = '<span class="px-2 py-0.5 text-[9px] font-bold rounded-full bg-amber-50 text-amber-805 border border-amber-250">Diproses</span>';
                                    } else if (report.status === 'ditolak') {
                                        statusLabel = '<span class="px-2 py-0.5 text-[9px] font-bold rounded-full bg-rose-50 text-rose-800 border border-rose-250">Ditolak</span>';
                                    }

                                    var actionHtml = '<span>Awa Kondusif</span>';
                                    if (report.user_id === currentUserId) {
                                        actionHtml = `<a href="${report.detail_url_warga}" class="font-bold text-indigo-650 hover:text-indigo-850">Detail &rarr;</a>`;
                                    }

                                    var popupHtml = `
                                        <div class="p-1 space-y-1.5 min-w-[200px] text-xs">
                                            <div class="flex justify-between items-center gap-2">
                                                <span class="text-[10px] font-bold text-slate-400 uppercase">Laporan</span>
                                                ${statusLabel}
                                            </div>
                                            <h4 class="font-bold text-slate-900 text-xs leading-tight">${report.title}</h4>
                                            <p class="text-[10px] text-slate-550 font-medium mt-0.5">Lokasi: ${report.location}</p>
                                            <div class="border-t border-slate-100 pt-1.5 mt-2 flex justify-between items-center text-[9px] text-slate-450">
                                                <span>Kec. Samaturu</span>
                                                ${actionHtml}
                                            </div>
                                        </div>
                                    `;
                                    marker.bindPopup(popupHtml);
                                    
                                    // Play sound or notify if it's an emergency panic button
                                    if (report.title.includes('🚨') || report.title.toLowerCase().includes('darurat')) {
                                        showEmergencyToast(report);
                                    }
                                }
                            });
                        }
                    })
                    .catch(err => console.error("Error fetching updates: ", err));
            }

            // Function to show a floating real-time toast alert
            function showEmergencyToast(report) {
                const toastContainer = document.getElementById('toast-container') || createToastContainer();
                const toast = document.createElement('div');
                toast.className = "bg-rose-600 text-white p-4 rounded-2xl shadow-2xl flex items-start space-x-3 max-w-sm border border-rose-500 transition duration-300 transform translate-y-2 opacity-0";
                toast.innerHTML = `
                    <div class="p-1.5 bg-white/20 rounded-lg">
                        <svg class="w-6 h-6 text-white animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-extrabold text-sm">🚨 KONDISI DARURAT!</h4>
                        <p class="text-xs text-white/90 mt-0.5 font-medium">${report.user_name} membutuhkan bantuan segera di ${report.location}</p>
                        <a href="${report.detail_url_warga}" class="inline-block mt-2 text-[10px] font-bold bg-white text-rose-600 px-2.5 py-1 rounded-lg hover:bg-rose-50 transition">LIHAT LOKASI</a>
                    </div>
                `;
                toastContainer.appendChild(toast);
                setTimeout(() => {
                    toast.classList.remove('translate-y-2', 'opacity-0');
                }, 100);
                
                // Play sound
                try {
                    var audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                    var osc = audioCtx.createOscillator();
                    var gain = audioCtx.createGain();
                    osc.connect(gain);
                    gain.connect(audioCtx.destination);
                    osc.type = 'sawtooth';
                    osc.frequency.setValueAtTime(880, audioCtx.currentTime); // A5 note
                    gain.gain.setValueAtTime(0.5, audioCtx.currentTime);
                    osc.start();
                    osc.stop(audioCtx.currentTime + 0.3);
                } catch(e) {}

                // Remove after 8 seconds
                setTimeout(() => {
                    toast.classList.add('opacity-0');
                    setTimeout(() => toast.remove(), 300);
                }, 8000);
            }

            function createToastContainer() {
                const container = document.createElement('div');
                container.id = 'toast-container';
                container.className = "fixed bottom-5 right-5 z-50 flex flex-col space-y-3";
                document.body.appendChild(container);
                return container;
            }

            // Poll every 5 seconds
            setInterval(checkRealtimeUpdates, 5000);
        });
    </script>

    <x-dashboard.panel title="Riwayat Laporan Anda" icon="history" subtitle="Seluruh laporan keamanan yang pernah Anda kirimkan">
        @if($reports->isEmpty())
            <x-dashboard.empty
                icon="file-warning"
                title="Belum Ada Laporan"
                description='Klik "Buat Laporan" di atas untuk melaporkan kejadian keamanan di lingkungan Anda.'
            />
        @else
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-slate-50/75 text-slate-650 border-b border-slate-100 text-[10px] font-bold uppercase tracking-wider">
                            <th class="px-6 py-4">Tanggal</th>
                            <th class="px-6 py-4">Judul Kejadian</th>
                            <th class="px-6 py-4">Lokasi</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-800">
                        @foreach($reports as $report)
                            <tr class="hover:bg-slate-50/50 transition-premium">
                                <td class="px-6 py-4 text-slate-500 text-xs whitespace-nowrap">
                                    {{ $report->reported_at->format('d-m-Y') }}
                                    <span class="text-[10px] text-slate-400 block font-normal">{{ $report->reported_at->format('H:i') }} WIB</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-slate-900 block font-bold leading-tight">{{ $report->title }}</span>
                                    <span class="text-xs text-slate-500 mt-1 line-clamp-1 font-normal">{{ $report->description }}</span>
                                </td>
                                <td class="px-6 py-4 text-slate-600 font-normal text-xs">{{ $report->location }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">@include('partials.report-status-badge', ['status' => $report->status])</td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <a href="{{ route('warga.reports.show', $report->id) }}" class="inline-flex items-center gap-1 text-xs font-bold bg-indigo-50 hover:bg-indigo-100 text-indigo-700 px-3 py-1.5 rounded-xl transition-premium hover:-translate-y-0.5">
                                        <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                        <span>Detail</span>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="md:hidden divide-y divide-slate-100">
                @foreach($reports as $report)
                    <a href="{{ route('warga.reports.show', $report->id) }}" class="block p-5 hover:bg-slate-50/60 transition-premium">
                        <div class="flex justify-between items-start gap-3 mb-2">
                            <h3 class="font-bold text-slate-900 text-sm leading-snug">{{ $report->title }}</h3>
                            @include('partials.report-status-badge', ['status' => $report->status])
                        </div>
                        <p class="text-xs text-slate-500 line-clamp-2">{{ $report->description }}</p>
                        <div class="flex items-center justify-between mt-3 text-[10px] text-slate-400 font-semibold">
                            <span class="flex items-center gap-1"><i data-lucide="map-pin" class="w-3 h-3"></i> {{ $report->location }}</span>
                            <span>{{ $report->reported_at->format('d/m/Y') }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </x-dashboard.panel>

</div>
@endsection
