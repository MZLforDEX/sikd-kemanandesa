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

    @if($todayRonda)
        <div class="bg-gradient-to-r from-amber-50 to-orange-50 border-2 border-amber-200/60 rounded-3xl p-6 sm:p-8 shadow-premium flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-start gap-4">
                <div class="p-4 bg-amber-500 text-white rounded-2xl shadow-md shrink-0">
                    <i data-lucide="shield-check" class="w-6 h-6 animate-pulse"></i>
                </div>
                <div class="space-y-1">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-200 text-amber-900 uppercase tracking-wider">Tugas Ronda Hari Ini</span>
                    <h3 class="text-slate-900 font-extrabold text-base leading-tight">Anda Memiliki Jadwal Ronda Hari Ini!</h3>
                    <p class="text-xs text-slate-600 font-medium">
                        Silakan jalankan tugas patroli Anda di wilayah <strong class="text-amber-800 font-extrabold">{{ $todayRonda->area }}</strong>.
                    </p>
                    <div class="flex flex-wrap gap-x-4 gap-y-1 mt-2 text-xs text-slate-500 font-semibold">
                        <span class="flex items-center gap-1.5"><i data-lucide="calendar" class="w-3.5 h-3.5 text-slate-400"></i> Tanggal: {{ $todayRonda->patrol_date->format('d-m-Y') }}</span>
                        <span class="flex items-center gap-1.5"><i data-lucide="clock" class="w-3.5 h-3.5 text-slate-400"></i> Waktu: {{ substr($todayRonda->start_time, 0, 5) }} - {{ substr($todayRonda->end_time, 0, 5) }} WIB (Shift {{ ucfirst($todayRonda->shift) }})</span>
                    </div>
                </div>
            </div>
            <div class="shrink-0 flex items-center gap-3">
                <a href="{{ route('warga.ronda.dashboard') }}" class="inline-flex items-center gap-2 bg-slate-900 hover:bg-slate-800 text-white font-bold px-5 py-3 rounded-2xl transition duration-300 shadow-md hover:-translate-y-0.5">
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    <span>Portal Ronda</span>
                </a>
            </div>
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

            function escapeHtml(text) {
                if (!text) return '';
                return text
                    .replace(/&/g, "&amp;")
                    .replace(/</g, "&lt;")
                    .replace(/>/g, "&gt;")
                    .replace(/"/g, "&quot;")
                    .replace(/'/g, "&#039;");
            }

            // Coordinate of Desa Awa: -3.946944, 121.351028
            var map = L.map('dashboard-map').setView([-3.946944, 121.351028], 15);
            L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community'
            }).addTo(map);



            // Loop through reports and add markers
            @foreach($reportsWithCoordinates as $report)
                @if($report->latitude && $report->longitude)
                    (function() {
                        var lat = {{ $report->latitude }};
                        var lng = {{ $report->longitude }};
                        var title = {!! json_encode($report->title) !!};
                        var description = {!! json_encode($report->description) !!};
                        var status = {!! json_encode($report->status) !!};
                        var location = {!! json_encode($report->location) !!};
                        var user = {!! json_encode($report->user->name) !!};
                        
                        // Select marker based on status (blinking red for baru, green for selesai, red for others)
                        var markerIcon;
                        if (status === 'baru') {
                            markerIcon = L.divIcon({
                                className: 'blinking-marker-container',
                                html: '<div class="blinking-marker"></div>',
                                iconSize: [16, 16],
                                iconAnchor: [8, 8],
                                popupAnchor: [0, -8]
                            });
                        } else {
                            var markerColor = 'red';
                            if (status === 'selesai') markerColor = 'green';

                            markerIcon = L.icon({
                                iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-' + markerColor + '.png',
                                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                                iconSize: [18, 30],
                                iconAnchor: [9, 30],
                                popupAnchor: [1, -26],
                                shadowSize: [30, 30]
                            });
                        }

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
                        var detailUrl = "{{ route('warga.reports.show', $report->id) }}";
                        var canView = false;
                        @if($report->user_id === auth()->id() || ($todayRonda && $report->reported_at->toDateString() === now()->toDateString()))
                            canView = true;
                        @endif

                        var actionHtml = '<span>Desa Awa</span>';
                        if (canView) {
                            actionHtml = `<a href="${detailUrl}" class="font-bold text-indigo-650 hover:text-indigo-850">Detail &rarr;</a>`;
                        }

                        var popupHtml = `
                            <div class="p-1 space-y-1.5 min-w-[220px] text-xs">
                                <div class="flex justify-between items-center gap-2">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase">Laporan</span>
                                    ${statusLabel}
                                </div>
                                <h4 class="font-bold text-slate-900 text-xs leading-tight">${escapeHtml(title)}</h4>
                                <p class="text-[10px] text-slate-605 font-normal leading-normal mt-1">${escapeHtml(description)}</p>
                                <p class="text-[10px] text-slate-550 font-medium mt-0.5">Lokasi: ${escapeHtml(location)}</p>
                                <div class="border-t border-slate-100 pt-1.5 mt-2 flex justify-between items-center text-[9px] text-slate-450">
                                    <span>Pelapor: ${escapeHtml(user)}</span>
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

            function formatTime(dateStr) {
                const d = new Date(dateStr);
                const hrs = String(d.getHours()).padStart(2, '0');
                const mins = String(d.getMinutes()).padStart(2, '0');
                return `${hrs}:${mins}`;
            }

            function formatDate(dateStr) {
                const d = new Date(dateStr);
                const day = String(d.getDate()).padStart(2, '0');
                const month = String(d.getMonth() + 1).padStart(2, '0');
                const year = d.getFullYear();
                return `${day}-${month}-${year}`;
            }

            function statusBadge(status) {
                const badges = {
                    'baru': 'bg-slate-100 text-slate-700 border-slate-200/50',
                    'diverifikasi': 'bg-blue-50 text-blue-700 border-blue-100',
                    'diproses': 'bg-amber-50 text-amber-700 border-amber-100',
                    'ditangani': 'bg-cyan-50 text-cyan-700 border-cyan-100',
                    'selesai': 'bg-emerald-50 text-emerald-700 border-emerald-100',
                    'ditolak': 'bg-rose-50 text-rose-700 border-rose-100'
                };
                const labels = {
                    'baru': 'Baru',
                    'diverifikasi': 'Terverifikasi',
                    'diproses': 'Sedang Diproses',
                    'ditangani': 'Ditangani Lapangan',
                    'selesai': 'Kasus Selesai',
                    'ditolak': 'Ditolak'
                };
                const cls = badges[status] || badges['baru'];
                const label = labels[status] || status;
                return `<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold border ${cls}">${label}</span>`;
            }

            function checkRealtimeUpdates() {
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 10000);

                return fetch(`{{ route('reports.realtime_updates') }}?last_id=${maxReportId}`, { signal: controller.signal })
                    .then(response => response.json())
                    .then(data => {
                        clearTimeout(timeoutId);
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
                                    var markerIcon;
                                    if (report.status === 'baru') {
                                        markerIcon = L.divIcon({
                                            className: 'blinking-marker-container',
                                            html: '<div class="blinking-marker"></div>',
                                            iconSize: [16, 16],
                                            iconAnchor: [8, 8],
                                            popupAnchor: [0, -8]
                                        });
                                    } else {
                                        var markerColor = 'red';
                                        if (report.status === 'selesai') markerColor = 'green';

                                        markerIcon = L.icon({
                                            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-' + markerColor + '.png',
                                            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                                            iconSize: [18, 30],
                                            iconAnchor: [9, 30],
                                            popupAnchor: [1, -26],
                                            shadowSize: [30, 30]
                                        });
                                    }

                                    var marker = L.marker([report.latitude, report.longitude], { icon: markerIcon }).addTo(map);

                                    var statusLabel = '<span class="px-2 py-0.5 text-[9px] font-bold rounded-full bg-slate-100 text-slate-800 border border-slate-200">Baru</span>';
                                    if (report.status === 'selesai') {
                                        statusLabel = '<span class="px-2 py-0.5 text-[9px] font-bold rounded-full bg-emerald-50 text-emerald-800 border border-emerald-250">Selesai</span>';
                                    } else if (report.status === 'diproses' || report.status === 'diverifikasi' || report.status === 'ditangani') {
                                        statusLabel = '<span class="px-2 py-0.5 text-[9px] font-bold rounded-full bg-amber-50 text-amber-805 border border-amber-250">Diproses</span>';
                                    } else if (report.status === 'ditolak') {
                                        statusLabel = '<span class="px-2 py-0.5 text-[9px] font-bold rounded-full bg-rose-50 text-rose-800 border border-rose-250">Ditolak</span>';
                                    }

                                    var actionHtml = '<span>Desa Awa</span>';
                                    if (report.detail_url_warga) {
                                        actionHtml = `<a href="${report.detail_url_warga}" class="font-bold text-indigo-650 hover:text-indigo-850">Detail &rarr;</a>`;
                                    }

                                    var popupHtml = `
                                        <div class="p-1 space-y-1.5 min-w-[220px] text-xs">
                                            <div class="flex justify-between items-center gap-2">
                                                <span class="text-[10px] font-bold text-slate-400 uppercase">Laporan</span>
                                                ${statusLabel}
                                            </div>
                                            <h4 class="font-bold text-slate-900 text-xs leading-tight">${escapeHtml(report.title)}</h4>
                                            <p class="text-[10px] text-slate-605 font-normal leading-normal mt-1">${escapeHtml(report.description)}</p>
                                            <p class="text-[10px] text-slate-550 font-medium mt-0.5">Lokasi: ${escapeHtml(report.location)}</p>
                                            <div class="border-t border-slate-100 pt-1.5 mt-2 flex justify-between items-center text-[9px] text-slate-450">
                                                <span>Pelapor: ${escapeHtml(report.user_name)}</span>
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

                                // 1. Prepend to today reports list (if active ronda citizen today)
                                const todayReportsTbody = document.getElementById('today-reports-tbody');
                                if (todayReportsTbody) {
                                    const emptyToday = document.getElementById('today-reports-empty');
                                    const tableToday = document.getElementById('today-reports-table-container');
                                    
                                    if (emptyToday) emptyToday.classList.add('hidden');
                                    if (tableToday) tableToday.classList.remove('hidden');

                                    const tr = document.createElement('tr');
                                    tr.className = "hover:bg-slate-50/50 transition-premium";
                                    tr.innerHTML = `
                                        <td class="px-6 py-4 text-slate-500 whitespace-nowrap">
                                            <span class="font-bold">${formatTime(report.reported_at)} WIB</span>
                                            <span class="text-[10px] text-slate-400 block font-normal">${formatDate(report.reported_at)}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap font-semibold text-slate-900">
                                            ${escapeHtml(report.user_name)}
                                        </td>
                                        <td class="px-6 py-4 max-w-xs">
                                            <span class="text-slate-900 block font-bold leading-tight">${escapeHtml(report.title)}</span>
                                            <span class="text-xs text-slate-500 mt-0.5 line-clamp-1 font-normal">${escapeHtml(report.description)}</span>
                                        </td>
                                        <td class="px-6 py-4 text-slate-600 font-normal">${escapeHtml(report.location)}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            ${statusBadge(report.status)}
                                        </td>
                                        <td class="px-6 py-4 text-right whitespace-nowrap">
                                            ${report.incident_id ? `
                                                <a href="/warga/ronda/kejadian/${report.incident_id}" class="inline-flex items-center gap-1.5 text-[11px] font-bold bg-indigo-650 hover:bg-indigo-700 text-white px-3 py-1.5 rounded-xl transition-premium hover:-translate-y-0.5 shadow-sm">
                                                    <i data-lucide="shield" class="w-3.5 h-3.5"></i>
                                                    <span>Tindak Lanjut</span>
                                                </a>
                                            ` : `
                                                <a href="/warga/laporan/${report.id}" class="inline-flex items-center gap-1 text-[11px] font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 px-3 py-1.5 rounded-xl transition-premium hover:-translate-y-0.5">
                                                    <i data-lucide="eye" class="w-3.5 h-3.5 text-slate-400"></i>
                                                    <span>Detail</span>
                                                </a>
                                            `}
                                        </td>
                                    `;
                                    todayReportsTbody.insertBefore(tr, todayReportsTbody.firstChild);
                                }

                                // 2. Prepend to current user's reports list (if authored by logged-in warga)
                                if (report.user_id === currentUserId) {
                                    const myReportsTbody = document.getElementById('my-reports-tbody');
                                    const myReportsMobile = document.getElementById('my-reports-mobile');
                                    const emptyMy = document.getElementById('my-reports-empty');
                                    const containerMy = document.getElementById('my-reports-container');

                                    if (emptyMy) emptyMy.classList.add('hidden');
                                    if (containerMy) containerMy.classList.remove('hidden');

                                    if (myReportsTbody) {
                                        const tr = document.createElement('tr');
                                        tr.className = "hover:bg-slate-50/50 transition-premium";
                                        tr.innerHTML = `
                                            <td class="px-6 py-4 text-slate-500 text-xs whitespace-nowrap">
                                                ${formatDate(report.reported_at)}
                                                <span class="text-[10px] text-slate-400 block font-normal">${formatTime(report.reported_at)} WIB</span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="text-slate-900 block font-bold leading-tight">${escapeHtml(report.title)}</span>
                                                <span class="text-xs text-slate-500 mt-1 line-clamp-1 font-normal">${escapeHtml(report.description)}</span>
                                            </td>
                                            <td class="px-6 py-4 text-slate-600 font-normal text-xs">${escapeHtml(report.location)}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                ${statusBadge(report.status)}
                                            </td>
                                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                                <a href="/warga/laporan/${report.id}" class="inline-flex items-center gap-1 text-xs font-bold bg-indigo-50 hover:bg-indigo-100 text-indigo-700 px-3 py-1.5 rounded-xl transition-premium hover:-translate-y-0.5">
                                                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                                    <span>Detail</span>
                                                </a>
                                            </td>
                                        `;
                                        myReportsTbody.insertBefore(tr, myReportsTbody.firstChild);
                                    }

                                    if (myReportsMobile) {
                                        const a = document.createElement('a');
                                        a.href = `/warga/laporan/${report.id}`;
                                        a.className = "block p-5 hover:bg-slate-50/60 transition-premium";
                                        a.innerHTML = `
                                            <div class="flex justify-between items-start gap-3 mb-2">
                                                <h3 class="font-bold text-slate-900 text-sm leading-snug">${escapeHtml(report.title)}</h3>
                                                ${statusBadge(report.status)}
                                            </div>
                                            <p class="text-xs text-slate-500 line-clamp-2">${escapeHtml(report.description)}</p>
                                            <div class="flex items-center justify-between mt-3 text-[10px] text-slate-400 font-semibold">
                                                <span class="flex items-center gap-1"><i data-lucide="map-pin" class="w-3 h-3"></i> ${escapeHtml(report.location)}</span>
                                                <span>${formatDate(report.reported_at)}</span>
                                            </div>
                                        `;
                                        myReportsMobile.insertBefore(a, myReportsMobile.firstChild);
                                    }
                                }

                                // Re-initialize icons
                                if (window.lucide) {
                                    window.lucide.createIcons();
                                }
                            });
                        }
                    })
                    .catch(err => {
                        clearTimeout(timeoutId);
                        console.error("Error fetching updates: ", err);
                    });
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
                        <p class="text-xs text-white/90 mt-0.5 font-medium">${escapeHtml(report.user_name)} membutuhkan bantuan segera di ${escapeHtml(report.location)}</p>
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

            // Resilient polling using setTimeout to avoid request stacking on poor networks
            function scheduleNextCheck() {
                setTimeout(() => {
                    checkRealtimeUpdates().finally(scheduleNextCheck);
                }, 15000);
            }
            scheduleNextCheck();
        });
    </script>

    @if($todayRonda)
        <x-dashboard.panel title="Laporan Warga Hari Ini" icon="alert-triangle" iconColor="text-rose-600" subtitle="Daftar laporan kejadian dari warga pada hari ronda Anda">
            <div id="today-reports-empty" class="{{ $todayReports->isEmpty() ? '' : 'hidden' }}">
                <x-dashboard.empty
                    icon="check-circle"
                    title="Aman & Kondusif"
                    description="Belum ada laporan dari warga untuk hari ini. Tetap pantau lingkungan sekitar Anda."
                />
            </div>
            <div id="today-reports-table-container" class="overflow-x-auto {{ $todayReports->isEmpty() ? 'hidden' : '' }}">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50/75 text-slate-650 border-b border-slate-100 text-[10px] font-bold uppercase tracking-wider">
                            <th class="px-6 py-4">Waktu</th>
                            <th class="px-6 py-4">Pelapor</th>
                            <th class="px-6 py-4">Laporan / Kejadian</th>
                            <th class="px-6 py-4">Lokasi</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="today-reports-tbody" class="divide-y divide-slate-100 font-medium text-slate-800">
                        @foreach($todayReports as $report)
                            <tr class="hover:bg-slate-50/50 transition-premium">
                                <td class="px-6 py-4 text-slate-500 whitespace-nowrap">
                                    <span class="font-bold">{{ $report->reported_at->format('H:i') }} WIB</span>
                                    <span class="text-[10px] text-slate-400 block font-normal">{{ $report->reported_at->format('d/m/Y') }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap font-semibold text-slate-900">
                                    {{ $report->user->name }}
                                </td>
                                <td class="px-6 py-4 max-w-xs">
                                    <span class="text-slate-900 block font-bold leading-tight">{{ $report->title }}</span>
                                    <span class="text-xs text-slate-500 mt-0.5 line-clamp-1 font-normal">{{ $report->description }}</span>
                                </td>
                                <td class="px-6 py-4 text-slate-600 font-normal">{{ $report->location }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @include('partials.report-status-badge', ['status' => $report->status])
                                </td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    @if($report->incident)
                                        <a href="{{ route('warga.ronda.incidents.show', $report->incident->id) }}" class="inline-flex items-center gap-1.5 text-[11px] font-bold bg-indigo-650 hover:bg-indigo-700 text-white px-3 py-1.5 rounded-xl transition-premium hover:-translate-y-0.5 shadow-sm">
                                            <i data-lucide="shield" class="w-3.5 h-3.5"></i>
                                            <span>Tindak Lanjut</span>
                                        </a>
                                    @else
                                        <a href="{{ route('warga.reports.show', $report->id) }}" class="inline-flex items-center gap-1 text-[11px] font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 px-3 py-1.5 rounded-xl transition-premium hover:-translate-y-0.5">
                                            <i data-lucide="eye" class="w-3.5 h-3.5 text-slate-400"></i>
                                            <span>Detail</span>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-dashboard.panel>
    @endif

    @if($upcomingRonda)
        <x-dashboard.panel title="Jadwal Ronda Mendatang" icon="calendar-days" subtitle="Informasi penugasan ronda Anda berikutnya">
            <div class="p-6 bg-gradient-to-br from-indigo-50/50 to-white">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-700 uppercase tracking-wider">Shift {{ ucfirst($upcomingRonda->shift) }}</span>
                            <span class="text-xs text-slate-400 font-semibold">{{ $upcomingRonda->patrol_date->diffForHumans() }}</span>
                        </div>
                        <h4 class="font-extrabold text-slate-800 text-sm">Patroli di Wilayah: <span class="text-indigo-650 font-black">{{ $upcomingRonda->area }}</span></h4>
                        <div class="flex flex-wrap gap-x-4 gap-y-1 mt-1 text-xs text-slate-500 font-medium">
                            <span class="flex items-center gap-1.5"><i data-lucide="calendar" class="w-3.5 h-3.5 text-slate-400"></i> {{ $upcomingRonda->patrol_date->format('d-m-Y') }}</span>
                            <span class="flex items-center gap-1.5"><i data-lucide="clock" class="w-3.5 h-3.5 text-slate-400"></i> Jam: {{ substr($upcomingRonda->start_time, 0, 5) }} - {{ substr($upcomingRonda->end_time, 0, 5) }} WIB</span>
                        </div>
                        @if($upcomingRonda->notes)
                            <p class="text-xs text-slate-500 bg-slate-50 p-2.5 rounded-xl border border-slate-100 mt-2 font-medium">
                                <strong>Catatan:</strong> {{ $upcomingRonda->notes }}
                            </p>
                        @endif
                    </div>
                    <div class="shrink-0">
                        <a href="{{ route('warga.ronda.schedules') }}" class="inline-flex items-center gap-1.5 text-xs font-bold bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-4 py-2.5 rounded-xl shadow-premium-sm transition-premium hover:-translate-y-0.5">
                            <i data-lucide="calendar" class="w-3.5 h-3.5 text-slate-400"></i>
                            <span>Lihat Semua Jadwal</span>
                        </a>
                    </div>
                </div>
            </div>
        </x-dashboard.panel>
    @endif

    <x-dashboard.panel title="Riwayat Laporan Anda" icon="history" subtitle="Seluruh laporan keamanan yang pernah Anda kirimkan">
        <div id="my-reports-empty" class="{{ $reports->isEmpty() ? '' : 'hidden' }}">
            <x-dashboard.empty
                icon="file-warning"
                title="Belum Ada Laporan"
                description='Klik "Buat Laporan" di atas untuk melaporkan kejadian keamanan di lingkungan Anda.'
            />
        </div>
        <div id="my-reports-container" class="{{ $reports->isEmpty() ? 'hidden' : '' }}">
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
                    <tbody id="my-reports-tbody" class="divide-y divide-slate-100 font-medium text-slate-800">
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
            <div id="my-reports-mobile" class="md:hidden divide-y divide-slate-100">
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
        </div>
    </x-dashboard.panel>

</div>
@endsection
