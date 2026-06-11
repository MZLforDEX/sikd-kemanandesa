@extends('layouts.app')

@section('title', 'Dashboard Kepala Desa')

@section('content')
<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6 sm:space-y-8">

    <x-dashboard.hero
        variant="emerald"
        badge="Portal Kepala Desa"
        :title="'Halo, Pak Kades ' . auth()->user()->name . '!'"
        subtitle="Pantau keamanan desa secara menyeluruh, analisis titik rawan, dan akses rekapitulasi laporan resmi."
    >
        <x-slot:actions>
            <a href="{{ route('kades.rekap') }}" class="btn-hero-primary">
                <i data-lucide="printer" class="w-4 h-4"></i>
                <span>Cetak Rekap</span>
            </a>
            <a href="{{ route('kades.tren') }}" class="btn-hero-secondary">
                <i data-lucide="line-chart" class="w-4 h-4"></i>
                <span>Analisis Tren</span>
            </a>
        </x-slot:actions>
    </x-dashboard.hero>

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4">
        <x-dashboard.stat label="Total Laporan" :value="$stats['total_reports']" icon="file-text" tone="slate" id="stats-total" />
        <x-dashboard.stat label="Terverifikasi" :value="$stats['verified_reports']" icon="shield-check" tone="blue" id="stats-diverifikasi" />
        <x-dashboard.stat label="Diproses" :value="$stats['processing_reports']" icon="loader" tone="amber" id="stats-diproses" />
        <x-dashboard.stat label="Selesai" :value="$stats['completed_reports']" icon="check-circle-2" tone="emerald" id="stats-selesai" />
        <x-dashboard.stat label="Ditolak" :value="$stats['rejected_reports']" icon="x-circle" tone="rose" id="stats-ditolak" class="col-span-2 sm:col-span-1" />
    </div>

    <x-dashboard.map-panel
        title="Peta Pemantauan Wilayah Desa Awa"
        subtitle="Visualisasi geospasial seluruh laporan kejadian keamanan desa"
    />

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            function escapeHtml(text) {
                if (!text) return '';
                return text
                    .replace(/&/g, "&amp;")
                    .replace(/</g, "&lt;")
                    .replace(/>/g, "&gt;")
                    .replace(/"/g, "&quot;")
                    .replace(/'/g, "&#039;");
            }

            var map = L.map('dashboard-map').setView([-3.946944, 121.351028], 15);
            L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community'
            }).addTo(map);



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
                        var detailUrl = "{{ route('kades.reports') }}";
                        
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
                                    <span>Oleh: ${escapeHtml(user)}</span>
                                    <a href="${detailUrl}" class="font-bold text-indigo-655 hover:text-indigo-850">Lihat Daftar &rarr;</a>
                                </div>
                            </div>
                        `;
                        marker.bindPopup(popupHtml);
                    })();
                @endif
            @endforeach

            var maxReportId = {{ \App\Models\Report::max('id') ?? 0 }};

            function formatFullDateTime(dateStr) {
                const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                const d = new Date(dateStr);
                const day = d.getDate();
                const month = months[d.getMonth()];
                const year = d.getFullYear();
                const hrs = String(d.getHours()).padStart(2, '0');
                const mins = String(d.getMinutes()).padStart(2, '0');
                return `${day} ${month} ${year}, ${hrs}:${mins} WIB`;
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
                        if (data.stats) {
                            document.getElementById('stats-total').textContent = data.stats.total;
                            document.getElementById('stats-diverifikasi').textContent = data.stats.diverifikasi;
                            document.getElementById('stats-diproses').textContent = data.stats.diproses;
                            document.getElementById('stats-selesai').textContent = data.stats.selesai;
                            document.getElementById('stats-ditolak').textContent = data.stats.ditolak;
                        }

                        if (data.reports && data.reports.length > 0) {
                            data.reports.forEach(report => {
                                if (report.id > maxReportId) maxReportId = report.id;

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

                                    var detailUrl = "{{ route('kades.dashboard') }}";
                                     var popupHtml = `
                                        <div class="p-1 space-y-1.5 min-w-[220px] text-xs">
                                            <div class="flex justify-between items-center gap-2">
                                                <span class="text-[10px] font-bold text-slate-400 uppercase">Laporan Warga</span>
                                                ${statusLabel}
                                            </div>
                                            <h4 class="font-bold text-slate-900 text-xs leading-tight">${escapeHtml(report.title)}</h4>
                                            <p class="text-[10px] text-slate-605 font-normal leading-normal mt-1">${escapeHtml(report.description)}</p>
                                            <p class="text-[10px] text-slate-550 font-medium mt-0.5">Lokasi: ${escapeHtml(report.location)}</p>
                                            <div class="border-t border-slate-100 pt-1.5 mt-2 flex justify-between items-center text-[9px] text-slate-450">
                                                <span>Oleh: ${escapeHtml(report.user_name)}</span>
                                                <a href="${detailUrl}" class="font-bold text-indigo-655 hover:text-indigo-850">Lihat Daftar &rarr;</a>
                                            </div>
                                        </div>
                                    `;
                                    marker.bindPopup(popupHtml);
                                }

                                const recentList = document.getElementById('recent-reports-list');
                                if (recentList) {
                                    const emptyRecent = document.getElementById('recent-reports-empty');
                                    if (emptyRecent) emptyRecent.classList.add('hidden');
                                    recentList.classList.remove('hidden');

                                    const item = document.createElement('div');
                                    item.className = "dashboard-list-row items-center";
                                    item.innerHTML = `
                                        <div class="space-y-1 min-w-0">
                                            <h3 class="font-bold text-slate-900 text-sm leading-snug">${escapeHtml(report.title)}</h3>
                                            <p class="text-xs text-slate-500">
                                                <strong>${escapeHtml(report.user_name)}</strong> · ${formatFullDateTime(report.reported_at)}
                                            </p>
                                            <p class="text-xs text-slate-605 truncate">${escapeHtml(report.location)}</p>
                                        </div>
                                        <div class="shrink-0">${statusBadge(report.status)}</div>
                                    `;
                                    recentList.insertBefore(item, recentList.firstChild);
                                }
                            });
                        }
                    })
                    .catch(err => {
                        clearTimeout(timeoutId);
                        console.error("Error fetching updates: ", err);
                    });
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

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 sm:gap-8">

        <div class="lg:col-span-5 space-y-6">
            <x-dashboard.panel title="Titik Rawan Wilayah" icon="map-pin" icon-color="text-rose-500" subtitle="Wilayah dengan intensitas kejadian tertinggi">
                <div class="p-6 space-y-4">
                    @forelse($hotspots as $area => $count)
                        @php
                            $maxCount = max(array_values($hotspots)) ?: 1;
                            $percentage = min(100, round(($count / $maxCount) * 100));
                            $barColor = $count >= 5 ? 'bg-rose-500' : ($count >= 3 ? 'bg-amber-500' : 'bg-indigo-500');
                            $textColor = $count >= 5 ? 'text-rose-700' : ($count >= 3 ? 'text-amber-700' : 'text-indigo-700');
                            $bgBadge = $count >= 5 ? 'bg-rose-50' : ($count >= 3 ? 'bg-amber-50' : 'bg-indigo-50');
                            $riskText = $count >= 5 ? 'Rawan Tinggi' : ($count >= 3 ? 'Rawan Sedang' : 'Rawan Rendah');
                        @endphp
                        <div class="space-y-2 font-medium">
                            <div class="flex justify-between items-center gap-2 text-xs">
                                <span class="text-slate-800 font-bold truncate">{{ $area }}</span>
                                <div class="flex items-center gap-2 shrink-0">
                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase {{ $bgBadge }} {{ $textColor }}">{{ $riskText }}</span>
                                    <span class="text-slate-900 font-bold tabular-nums">{{ $count }}</span>
                                </div>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                                <div class="h-full rounded-full {{ $barColor }} transition-all duration-700" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                    @empty
                        <x-dashboard.empty icon="map-pin" title="Belum Ada Titik Rawan" description="Data titik rawan akan muncul setelah ada kejadian tercatat." />
                    @endforelse
                </div>
            </x-dashboard.panel>

            @if($incidentsByCategory->isNotEmpty())
            <div class="bg-white border border-slate-200/60 rounded-3xl p-6 shadow-premium-sm space-y-4">
                <h3 class="font-extrabold text-slate-900 text-sm flex items-center gap-2">
                    <i data-lucide="pie-chart" class="w-4 h-4 text-indigo-600"></i>
                    Kategori Kejadian
                </h3>
                <div class="space-y-2">
                    @foreach($incidentsByCategory as $cat)
                        <div class="flex justify-between items-center text-xs p-3 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="font-bold text-slate-700 capitalize">{{ $cat->category }}</span>
                            <span class="font-extrabold text-indigo-650 tabular-nums">{{ $cat->total }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <div class="lg:col-span-7">
            <x-dashboard.panel title="Laporan Terakhir" icon="activity" icon-color="text-slate-500" subtitle="Aktivitas laporan keamanan warga terbaru">
                <x-slot:actions>
                    <a href="{{ route('kades.reports') }}" class="text-xs text-indigo-650 hover:text-indigo-850 font-bold transition-premium">Semua Laporan</a>
                </x-slot:actions>

                <div id="recent-reports-empty" class="{{ $recentReports->isEmpty() ? '' : 'hidden' }}">
                    <x-dashboard.empty icon="inbox" title="Belum Ada Laporan" description="Laporan dari warga akan muncul di sini." />
                </div>

                <div id="recent-reports-list" class="divide-y divide-slate-100 font-medium {{ $recentReports->isEmpty() ? 'hidden' : '' }}">
                    @foreach($recentReports as $report)
                        <div class="dashboard-list-row items-center">
                            <div class="space-y-1 min-w-0">
                                <h3 class="font-bold text-slate-900 text-sm leading-snug">{{ $report->title }}</h3>
                                <p class="text-xs text-slate-500">
                                    <strong>{{ $report->user->name }}</strong> · {{ $report->reported_at->format('d M Y, H:i') }} WIB
                                </p>
                                <p class="text-xs text-slate-600 truncate">{{ $report->location }}</p>
                            </div>
                            <div class="shrink-0">@include('partials.report-status-badge', ['status' => $report->status])</div>
                        </div>
                    @endforeach
                </div>
            </x-dashboard.panel>
        </div>

    </div>

</div>
@endsection
