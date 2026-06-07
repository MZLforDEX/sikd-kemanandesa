@extends('layouts.app')

@section('title', 'Buat Laporan Keamanan')

@section('content')
<div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 py-8 space-y-6 font-medium">
    
    <!-- Navigation Back Link -->
    <a href="{{ route('warga.dashboard') }}" class="inline-flex items-center space-x-1.5 text-xs font-bold text-slate-500 hover:text-slate-900 transition-premium hover:-translate-x-0.5">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
        <span>Kembali ke Dashboard</span>
    </a>

    <!-- Header Card -->
    <div class="bg-white border border-slate-200/60 rounded-3xl p-6 shadow-premium-sm flex items-center space-x-4">
        <div class="p-3 bg-indigo-50 text-indigo-650 rounded-2xl">
            <i data-lucide="file-plus" class="w-6 h-6"></i>
        </div>
        <div>
            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Buat Laporan Keamanan Baru</h1>
            <p class="text-xs text-slate-500 mt-1 font-normal">Berikan informasi yang akurat demi memudahkan respon petugas.</p>
        </div>
    </div>

    <!-- Form Card -->
    <div class="bg-white border border-slate-200/60 rounded-3xl shadow-premium-sm overflow-hidden">
        <form action="{{ route('warga.reports.store') }}" method="POST" enctype="multipart/form-data" class="p-8 space-y-6">
            @csrf
            <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude', -3.946944) }}">
            <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude', 121.351028) }}">

            <!-- Title -->
            <div class="space-y-1.5">
                <label for="title" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Judul Laporan / Kejadian</label>
                <input type="text" name="title" id="title" required value="{{ old('title') }}" 
                    placeholder="Contoh: Pencurian Ban Sepeda Motor, Kebakaran Tong Sampah"
                    class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm focus:border-indigo-500 focus:outline-hidden focus:ring-1 focus:ring-indigo-500 transition-premium @error('title') border-rose-500 @enderror font-medium text-slate-850">
                @error('title')
                    <span class="text-xs font-semibold text-rose-600 block mt-1">{{ $message }}</span>
                @enderror
            </div>

            <!-- Location -->
            <div class="space-y-1.5">
                <label for="location" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Lokasi Kejadian (Keterangan Alamat)</label>
                <div class="relative rounded-xl shadow-xs">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <i data-lucide="map-pin" class="h-5 w-5 text-indigo-650"></i>
                    </div>
                    <input type="text" name="location" id="location" required value="{{ old('location') }}"
                        placeholder="Contoh: Gang Dahlia RT 02 / RW 01, samping Pos Ronda I"
                        class="block w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm focus:border-indigo-500 focus:outline-hidden focus:ring-1 focus:ring-indigo-500 transition-premium @error('location') border-rose-500 @enderror font-medium text-slate-850">
                </div>
                @error('location')
                    <span class="text-xs font-semibold text-rose-600 block mt-1">{{ $message }}</span>
                @enderror
            </div>

            <!-- Map Picker (GIS Integration) -->
            <div class="space-y-2">
                <label class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Tandai Lokasi Presisi di Peta (Satelit GIS)</label>
                <div class="p-3 bg-slate-50 border border-slate-200/60 rounded-2xl text-xs text-slate-500 flex items-start space-x-2">
                    <i data-lucide="info" class="w-4 h-4 text-indigo-650 shrink-0 mt-0.5"></i>
                    <span class="font-normal leading-relaxed">Klik pada peta satelit di bawah ini untuk menandai posisi kejadian dengan presisi. Pin marker juga dapat Anda geser (drag).</span>
                </div>
                <div id="select-map" class="h-64 w-full rounded-2xl border border-slate-200 overflow-hidden z-10"></div>
            </div>

            <!-- Description -->
            <div class="space-y-1.5">
                <label for="description" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Deskripsi Kronologi Kejadian</label>
                <textarea name="description" id="description" rows="5" required
                    placeholder="Tuliskan kronologi lengkap kejadian, ciri-ciri pelaku jika ada, estimasi waktu kejadian, serta detail pendukung lainnya..."
                    class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm focus:border-indigo-500 focus:outline-hidden focus:ring-1 focus:ring-indigo-500 transition-premium @error('description') border-rose-500 @enderror font-medium text-slate-850">{{ old('description') }}</textarea>
                @error('description')
                    <span class="text-xs font-semibold text-rose-600 block mt-1">{{ $message }}</span>
                @enderror
            </div>

            <!-- File Upload -->
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Unggah Bukti Pendukung (Foto/Gambar)</label>
                <div class="mt-1 flex justify-center rounded-2xl border-2 border-dashed border-slate-200 px-6 py-6 hover:border-indigo-500 transition">
                    <div class="space-y-1 text-center">
                        <i data-lucide="image" class="mx-auto h-10 w-10 text-slate-400"></i>
                        <div class="flex text-sm text-slate-600 justify-center">
                            <label for="attachment" class="relative cursor-pointer rounded-md bg-white font-semibold text-indigo-655 focus-within:outline-hidden focus-within:ring-2 focus-within:ring-indigo-500 focus-within:ring-offset-2 hover:text-indigo-800 transition">
                                <span>Pilih berkas foto</span>
                                <input id="attachment" name="attachment" type="file" accept="image/*" class="sr-only">
                            </label>
                            <p class="pl-1 font-normal">atau seret berkas ke sini</p>
                        </div>
                        <p class="text-xs text-slate-500 font-normal">PNG, JPG, JPEG hingga ukuran 5MB</p>
                        <span id="file-chosen" class="text-xs text-indigo-600 font-bold block mt-2"></span>
                    </div>
                </div>
                @error('attachment')
                    <span class="text-xs font-semibold text-rose-600 block mt-1">{{ $message }}</span>
                @enderror
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end space-x-3 border-t border-slate-100 pt-6">
                <a href="{{ route('warga.dashboard') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-bold text-slate-500 hover:bg-slate-50 transition-premium">Batal</a>
                <button type="submit" class="bg-indigo-650 hover:bg-indigo-700 text-white font-bold py-2.5 px-6 rounded-xl shadow-premium-sm flex items-center space-x-2 transition-premium hover:-translate-y-0.5">
                    <i data-lucide="send" class="w-4 h-4"></i>
                    <span>Kirim Laporan</span>
                </button>
            </div>

        </form>
    </div>

</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const fileInput = document.getElementById('attachment');
        const fileChosen = document.getElementById('file-chosen');
        
        fileInput.addEventListener('change', function(){
            if(this.files && this.files.length > 0) {
                fileChosen.textContent = "Berkas terpilih: " + this.files[0].name;
            } else {
                fileChosen.textContent = "";
            }
        });

        // Initialize Map Selection
        var defaultLat = parseFloat(document.getElementById('latitude').value);
        var defaultLng = parseFloat(document.getElementById('longitude').value);

        var map = L.map('select-map').setView([defaultLat, defaultLng], 16);
        
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

        // Marker
        var marker = L.marker([defaultLat, defaultLng], {
            draggable: true,
            icon: markerIcon
        }).addTo(map);

        // Auto-detect real-time location
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                var realLat = position.coords.latitude;
                var realLng = position.coords.longitude;
                
                // Update map view & marker
                map.setView([realLat, realLng], 16);
                marker.setLatLng([realLat, realLng]);
                
                // Update form inputs
                document.getElementById('latitude').value = realLat.toFixed(8);
                document.getElementById('longitude').value = realLng.toFixed(8);
                
                if (window.showToast) {
                    window.showToast('Lokasi GPS real-time berhasil dimuat!', 'success');
                }
            }, function(error) {
                console.warn("Geolocation failed or denied, using default coordinates: ", error);
            }, {
                enableHighAccuracy: true,
                timeout: 5000,
                maximumAge: 0
            });
        }

        // Click to place marker
        map.on('click', function(e) {
            marker.setLatLng(e.latlng);
            document.getElementById('latitude').value = e.latlng.lat.toFixed(8);
            document.getElementById('longitude').value = e.latlng.lng.toFixed(8);
        });

        // Drag marker
        marker.on('dragend', function(e) {
            var position = marker.getLatLng();
            document.getElementById('latitude').value = position.lat.toFixed(8);
            document.getElementById('longitude').value = position.lng.toFixed(8);
        });
    });
</script>
@endsection
