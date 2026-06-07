@extends('layouts.app')

@section('title', 'Dashboard Perangkat Desa')

@section('content')
<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6 sm:space-y-8">

    <x-dashboard.hero
        variant="violet"
        badge="Panel Perangkat Desa"
        :title="'Halo, ' . auth()->user()->name . '!'"
        subtitle="Kelola laporan warga, verifikasi kejadian, jadwalkan patroli ronda, dan pantau aktivitas keamanan desa."
    >
        <x-slot:actions>
            <a href="{{ route('perangkat.reports.index') }}?status=baru" class="btn-hero-primary">
                <i data-lucide="inbox" class="w-4 h-4"></i>
                <span>Verifikasi Laporan</span>
            </a>
            <a href="{{ route('perangkat.schedules') }}" class="btn-hero-secondary">
                <i data-lucide="calendar" class="w-4 h-4"></i>
                <span>Jadwal Ronda</span>
            </a>
        </x-slot:actions>
    </x-dashboard.hero>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <x-dashboard.stat label="Total Laporan" :value="$stats['total_reports']" icon="file-text" tone="slate" id="stats-total" />
        <x-dashboard.stat label="Menunggu Verifikasi" :value="$stats['pending_reports']" icon="alert-circle" tone="amber" id="stats-baru" />
        <x-dashboard.stat label="Kejadian Aktif" :value="$stats['active_incidents']" icon="alert-triangle" tone="rose" id="stats-proses" />
        <x-dashboard.stat label="Patroli Hari Ini" :value="$stats['total_patrols_today']" icon="shield" tone="cyan" />
    </div>

    <x-dashboard.map-panel
        title="Peta Pemantauan Keamanan Desa Awa"
        subtitle="Integrasi geospasial real-time seluruh laporan keamanan warga"
    />

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var map = L.map('dashboard-map').setView([-3.946944, 121.351028], 15);
            L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community'
            }).addTo(map);

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
            villageCenter.bindPopup("<b>Kantor Desa Awa</b><br>Kec. Samaturu, Kab. Kolaka, Sulawesi Tenggara");

            @foreach($reportsWithCoordinates as $report)
                @if($report->latitude && $report->longitude)
                    (function() {
                        var lat = {{ $report->latitude }};
                        var lng = {{ $report->longitude }};
                        var title = "{{ addslashes($report->title) }}";
                        var status = "{{ $report->status }}";
                        var location = "{{ addslashes($report->location) }}";
                        var user = "{{ addslashes($report->user->name) }}";
                        var detailUrl = "{{ route('perangkat.reports.show', $report->id) }}";
                        
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

                        var popupHtml = `
                            <div class="p-1 space-y-1.5 min-w-[200px] text-xs">
                                <div class="flex justify-between items-center gap-2">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase">Laporan Warga</span>
                                    ${statusLabel}
                                </div>
                                <h4 class="font-bold text-slate-900 text-xs leading-tight">${title}</h4>
                                <p class="text-[10px] text-slate-550 font-medium mt-0.5">Lokasi: ${location}</p>
                                <div class="border-t border-slate-100 pt-1.5 mt-2 flex justify-between items-center text-[9px] text-slate-450">
                                    <span>Oleh: ${user}</span>
                                    <a href="${detailUrl}" class="font-bold text-indigo-650 hover:text-indigo-850">Detail &rarr;</a>
                                </div>
                            </div>
                        `;
                        marker.bindPopup(popupHtml);
                    })();
                @endif
            @endforeach

            var maxReportId = {{ \App\Models\Report::max('id') ?? 0 }};

            function checkRealtimeUpdates() {
                fetch(`{{ route('reports.realtime_updates') }}?last_id=${maxReportId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.stats) {
                            document.getElementById('stats-total').textContent = data.stats.total;
                            document.getElementById('stats-baru').textContent = data.stats.baru;
                            document.getElementById('stats-proses').textContent = data.stats.proses;
                        }

                        if (data.reports && data.reports.length > 0) {
                            const pendingList = document.getElementById('pending-reports-list');
                            const noPending = document.getElementById('no-pending-reports');

                            data.reports.forEach(report => {
                                if (report.id > maxReportId) maxReportId = report.id;

                                if (report.latitude && report.longitude) {
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

                                    var popupHtml = `
                                        <div class="p-1 space-y-1.5 min-w-[200px] text-xs">
                                            <div class="flex justify-between items-center gap-2">
                                                <span class="text-[10px] font-bold text-slate-400 uppercase">Laporan Warga</span>
                                                ${statusLabel}
                                            </div>
                                            <h4 class="font-bold text-slate-900 text-xs leading-tight">${report.title}</h4>
                                            <p class="text-[10px] text-slate-550 font-medium mt-0.5">Lokasi: ${report.location}</p>
                                            <div class="border-t border-slate-100 pt-1.5 mt-2 flex justify-between items-center text-[9px] text-slate-450">
                                                <span>Oleh: ${report.user_name}</span>
                                                <a href="${report.detail_url_perangkat}" class="font-bold text-indigo-650 hover:text-indigo-850">Detail &rarr;</a>
                                            </div>
                                        </div>
                                    `;
                                    marker.bindPopup(popupHtml);
                                }

                                if (report.status === 'baru' && pendingList) {
                                    if (noPending) noPending.remove();

                                    const item = document.createElement('div');
                                    item.className = "p-6 hover:bg-slate-50/25 transition flex justify-between items-start gap-4";
                                    item.innerHTML = `
                                        <div class="space-y-1">
                                            <h3 class="font-bold text-slate-900 text-sm leading-snug">${report.title}</h3>
                                            <p class="text-xs text-slate-500 flex items-center space-x-1">
                                                <span>Oleh: <strong>${report.user_name}</strong></span>
                                                <span>•</span>
                                                <span>Baru saja</span>
                                            </p>
                                            <p class="text-xs text-slate-605 line-clamp-2 mt-2 font-normal">${report.description}</p>
                                        </div>
                                        <a href="${report.detail_url_perangkat}" class="inline-flex items-center space-x-1 text-xs font-bold bg-indigo-50 hover:bg-indigo-100/85 text-indigo-700 px-3 py-1.5 rounded-xl transition-premium hover:-translate-y-0.5 shrink-0">
                                            <span>Verifikasi</span>
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                        </a>
                                    `;
                                    pendingList.insertBefore(item, pendingList.firstChild);
                                }
                            });
                        }
                    })
                    .catch(err => console.error("Error fetching updates: ", err));
            }

            setInterval(checkRealtimeUpdates, 5000);
        });
    </script>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 sm:gap-8">

        <div class="lg:col-span-7 space-y-6">
            <x-dashboard.panel title="Laporan Baru Warga" icon="inbox" icon-color="text-amber-600" subtitle="Laporan yang menunggu verifikasi perangkat desa">
                <x-slot:actions>
                    <a href="{{ route('perangkat.reports.index') }}?status=baru" class="text-xs text-indigo-650 hover:text-indigo-850 font-bold transition-premium">Lihat Semua</a>
                </x-slot:actions>

                <div id="pending-reports-list" class="divide-y divide-slate-100 font-medium">
                    @php $pendingReports = $recentReports->where('status', 'baru'); @endphp
                    @forelse($pendingReports as $report)
                        <div class="dashboard-list-row">
                            <div class="space-y-1.5 min-w-0 flex-1">
                                <h3 class="font-bold text-slate-900 text-sm leading-snug">{{ $report->title }}</h3>
                                <p class="text-xs text-slate-500">
                                    <span>Oleh <strong>{{ $report->user->name }}</strong></span>
                                    <span class="mx-1">·</span>
                                    <span>{{ $report->reported_at->diffForHumans() }}</span>
                                </p>
                                <p class="text-xs text-slate-600 line-clamp-2 font-normal">{{ $report->description }}</p>
                            </div>
                            <a href="{{ route('perangkat.reports.show', $report->id) }}" class="inline-flex items-center gap-1 text-xs font-bold bg-indigo-650 hover:bg-indigo-700 text-white px-3.5 py-2 rounded-xl transition-premium hover:-translate-y-0.5 shrink-0 shadow-premium-sm">
                                <span>Verifikasi</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>
                    @empty
                        <div id="no-pending-reports">
                            <x-dashboard.empty icon="smile" title="Semua Laporan Terverifikasi" description="Tidak ada laporan baru yang menunggu tindakan verifikasi." />
                        </div>
                    @endforelse
                </div>
            </x-dashboard.panel>

            <x-dashboard.panel title="Kejadian Aktif" icon="alert-triangle" icon-color="text-rose-500" subtitle="Kejadian yang sedang dalam proses penanganan">
                <x-slot:actions>
                    <a href="{{ route('perangkat.incidents.index') }}" class="text-xs text-indigo-650 hover:text-indigo-850 font-bold transition-premium">Lihat Semua</a>
                </x-slot:actions>

                <div class="divide-y divide-slate-100">
                    @forelse($activeIncidents as $incident)
                        <div class="dashboard-list-row items-center">
                            <div class="space-y-1.5 min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-bold text-slate-900 text-sm">{{ $incident->title }}</span>
                                    <span class="px-2 py-0.5 text-[9px] font-bold rounded-full border uppercase
                                        {{ $incident->severity === 'tinggi' ? 'bg-rose-50 border-rose-100 text-rose-700' : ($incident->severity === 'sedang' ? 'bg-amber-50 border-amber-100 text-amber-700' : 'bg-slate-50 border-slate-200 text-slate-700') }}">
                                        {{ $incident->severity }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-500">
                                    <strong>{{ $incident->location }}</strong> · <span class="capitalize">{{ $incident->category }}</span>
                                </p>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                @include('partials.report-status-badge', ['status' => $incident->status])
                                <a href="{{ route('perangkat.incidents.show', $incident->id) }}" class="p-2 bg-slate-100 hover:bg-indigo-50 text-slate-600 hover:text-indigo-650 rounded-xl transition-premium">
                                    <i data-lucide="chevron-right" class="w-4 h-4"></i>
                                </a>
                            </div>
                        </div>
                    @empty
                        <x-dashboard.empty icon="check-circle" title="Tidak Ada Kejadian Aktif" description="Seluruh kejadian keamanan telah berhasil ditangani." />
                    @endforelse
                </div>
            </x-dashboard.panel>
        </div>

        <div class="lg:col-span-5">
            <x-dashboard.panel title="Log Patroli Ronda" icon="shield-check" icon-color="text-indigo-600" subtitle="Aktivitas patroli terbaru dari petugas ronda">
                <div class="p-6 space-y-6">
                    <div class="relative pl-5 border-l-2 border-slate-100 space-y-6 font-medium">
                        @forelse($recentLogs as $log)
                            @php
                                $dotColor = $log->condition === 'aman' ? 'bg-emerald-500' : ($log->condition === 'mencurigakan' ? 'bg-amber-500' : 'bg-rose-500');
                                $borderColor = $log->condition === 'aman' ? 'border-emerald-500 bg-emerald-50' : ($log->condition === 'mencurigakan' ? 'border-amber-500 bg-amber-50' : 'border-rose-500 bg-rose-50');
                            @endphp
                            <div class="relative">
                                <span class="absolute -left-[27px] top-1 flex h-4 w-4 items-center justify-center rounded-full {{ $borderColor }} border-2 ring-4 ring-white">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $dotColor }}"></span>
                                </span>
                                <div class="bg-slate-50/50 rounded-2xl p-4 border border-slate-100/80 space-y-1.5">
                                    <div class="flex justify-between items-start gap-2">
                                        <h4 class="text-xs font-bold text-slate-900">{{ $log->user->name }}</h4>
                                        <span class="text-[9px] text-slate-400 shrink-0">{{ $log->logged_at->diffForHumans() }}</span>
                                    </div>
                                    <p class="text-xs text-slate-700 font-bold">📍 {{ $log->location_checked }}</p>
                                    <p class="text-[11px] text-slate-550 leading-relaxed">{{ $log->notes ?? 'Aman terkendali.' }}</p>
                                </div>
                            </div>
                        @empty
                            <x-dashboard.empty icon="compass" title="Belum Ada Log Patroli" description="Log patroli akan muncul setelah petugas ronda mengisi laporan." />
                        @endforelse
                    </div>
                    <div class="border-t border-slate-100 pt-4 text-center">
                        <a href="{{ route('perangkat.schedules') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-indigo-650 hover:text-indigo-850 transition-premium">
                            <span>Lihat Penjadwalan & Log</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    </div>
                </div>
            </x-dashboard.panel>
        </div>

    </div>

</div>
@endsection
