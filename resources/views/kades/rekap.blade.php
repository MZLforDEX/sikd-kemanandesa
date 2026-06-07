@extends('layouts.app')

@section('title', 'Cetak Rekapitulasi Laporan Keamanan')

@section('content')
<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    
    <!-- Printable Header Banner (Only visible in Print) -->
    <div class="hidden print:block text-center border-b-2 border-slate-900 pb-5 mb-8">
        <h1 class="text-2xl font-extrabold text-slate-900 uppercase">Pemerintah Kabupaten Kolaka</h1>
        <h2 class="text-xl font-bold text-slate-800 uppercase mt-0.5">Sistem Informasi Keamanan Desa (SIKD)</h2>
        <p class="text-xs text-slate-500 mt-1">Kecamatan Samaturu, Desa Awa, Sulawesi Tenggara</p>
        <p class="text-xs font-bold text-slate-800 mt-3">REKAPITULASI LAPORAN KEJADIAN KEAMANAN DESA</p>
        <p class="text-[11px] text-slate-500 mt-0.5">Filter Periode: {{ request('start_date', 'Semua Waktu') }} s.d. {{ request('end_date', 'Semua Waktu') }}</p>
    </div>

    <!-- Header / Top Bar (Hidden in Print) -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 print:hidden bg-white p-6 rounded-3xl border border-slate-200/60 shadow-premium-sm">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Rekapitulasi Data Keamanan Desa</h1>
            <p class="text-sm text-slate-500 mt-1 font-normal">Gunakan saringan waktu untuk membuat laporan rekapitulasi keamanan desa resmi.</p>
        </div>
        <button type="button" onclick="window.print()" class="inline-flex items-center space-x-1.5 bg-indigo-655 hover:bg-indigo-700 text-white font-bold px-5 py-2.5 rounded-xl shadow-premium-sm transition-premium hover:-translate-y-0.5 text-sm">
            <i data-lucide="printer" class="w-4 h-4"></i>
            <span>Cetak Rekap (PDF / Kertas)</span>
        </button>
    </div>

    <!-- Filter Form Card (Hidden in Print) -->
    <div class="bg-white border border-slate-200/60 rounded-3xl p-6 shadow-premium-sm print:hidden">
        <form action="{{ route('kades.rekap') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <!-- Start Date -->
            <div class="space-y-1.5">
                <label for="start_date" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Tanggal Mulai</label>
                <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}"
                    class="block w-full rounded-xl border border-slate-200 py-2 px-3 text-sm focus:border-indigo-500 focus:outline-hidden focus:ring-1 focus:ring-indigo-500 bg-white font-medium text-slate-800">
            </div>

            <!-- End Date -->
            <div class="space-y-1.5">
                <label for="end_date" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Tanggal Selesai</label>
                <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}"
                    class="block w-full rounded-xl border border-slate-200 py-2 px-3 text-sm focus:border-indigo-500 focus:outline-hidden focus:ring-1 focus:ring-indigo-500 bg-white font-medium text-slate-800">
            </div>

            <!-- Status -->
            <div class="space-y-1.5">
                <label for="status" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Status Laporan</label>
                <select name="status" id="status" class="block w-full rounded-xl border border-slate-200 py-2 px-3 text-sm focus:border-indigo-500 bg-white font-medium text-slate-800">
                    <option value="">Semua Status</option>
                    <option value="baru" {{ request('status') === 'baru' ? 'selected' : '' }}>Baru</option>
                    <option value="diverifikasi" {{ request('status') === 'diverifikasi' ? 'selected' : '' }}>Diverifikasi</option>
                    <option value="diproses" {{ request('status') === 'diproses' ? 'selected' : '' }}>Diproses</option>
                    <option value="ditangani" {{ request('status') === 'ditangani' ? 'selected' : '' }}>Ditangani</option>
                    <option value="selesai" {{ request('status') === 'selesai' ? 'selected' : '' }}>Selesai</option>
                    <option value="ditolak" {{ request('status') === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-2">
                <button type="submit" class="flex-1 bg-slate-900 hover:bg-slate-950 text-white font-bold py-2.5 px-4 rounded-xl text-sm transition-premium hover:-translate-y-0.5 shadow-premium-sm">
                    Terapkan Filter
                </button>
                <a href="{{ route('kades.rekap') }}" class="px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-bold text-slate-600 hover:bg-slate-50 transition-premium hover:-translate-y-0.5 flex items-center justify-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="bg-white border border-slate-200/60 rounded-3xl overflow-hidden shadow-premium-sm print:border-none print:shadow-none">
        
        @if($reports->isEmpty())
            <div class="p-16 text-center text-slate-400 print:py-8">
                <i data-lucide="file-warning" class="w-12 h-12 mx-auto mb-3 text-slate-355 print:hidden"></i>
                <h4 class="font-extrabold text-slate-700 text-sm">Tidak Ada Data Rekapitulasi</h4>
                <p class="text-xs text-slate-500 mt-1 font-normal">Silakan sesuaikan filter waktu Anda atau tambahkan laporan kejadian baru.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-slate-50/50 text-slate-500 border-b border-slate-100 text-[10px] font-bold uppercase tracking-wider print:bg-slate-100">
                            <th class="px-6 py-4">No.</th>
                            <th class="px-6 py-4">Tanggal</th>
                            <th class="px-6 py-4">Pelapor</th>
                            <th class="px-6 py-4">Nama Laporan / Kronologi</th>
                            <th class="px-6 py-4">Lokasi</th>
                            <th class="px-6 py-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @foreach($reports as $index => $report)
                            <tr class="hover:bg-slate-50/25 transition print:break-inside-avoid">
                                <td class="px-6 py-4 text-xs text-slate-400">
                                    {{ $index + 1 }}
                                </td>
                                <td class="px-6 py-4 text-slate-500 text-xs whitespace-nowrap">
                                    {{ $report->reported_at->format('d-m-Y') }} <br>
                                    <span class="text-[10px] text-slate-400 font-normal">{{ $report->reported_at->format('H:i') }} WIB</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-slate-900 block font-bold text-xs">{{ $report->user->name }}</span>
                                    <span class="text-[10px] text-slate-500 block font-normal mt-0.5">{{ $report->user->phone ?? '-' }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-slate-900 block font-bold text-xs leading-snug">{{ $report->title }}</span>
                                    <p class="text-[11px] text-slate-500 mt-1 font-normal leading-relaxed max-w-md">{{ $report->description }}</p>
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-600 font-normal">
                                    {{ $report->location }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border capitalize 
                                        {{ $report->status === 'selesai' ? 'bg-emerald-50 border-emerald-100 text-emerald-800' : ($report->status === 'ditolak' ? 'bg-rose-50 border-rose-100 text-rose-800' : 'bg-amber-50 border-amber-100 text-amber-800') }}">
                                        {{ $report->status }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($reports->hasPages())
                <div class="px-6 py-4 border-t border-slate-100 print:hidden">
                    {{ $reports->links() }}
                </div>
            @endif

            <!-- Print Signature Page Block (Only visible in Print) -->
            <div class="hidden print:grid grid-cols-3 gap-6 mt-16 text-xs text-center font-semibold">
                <div></div>
                <div></div>
                <div class="space-y-16">
                    <div>
                        <p>Mengetahui,</p>
                        <p class="font-bold uppercase mt-1">Kepala Desa Awa</p>
                    </div>
                    <div>
                        <p class="font-bold underline">{{ auth()->user()->name }}</p>
                        <p class="text-[10px] text-slate-550">NIP. 19800801 200501 1 002</p>
                    </div>
                </div>
            </div>
        @endif
        
    </div>

</div>

<!-- Printable Styles CSS -->
<style>
    @media print {
        body {
            background-color: white !important;
            color: black !important;
            font-size: 12px !important;
        }
        nav, header, footer, .print\:hidden {
            display: none !important;
        }
        main {
            margin: 0 !important;
            padding: 0 !important;
        }
        .container {
            max-width: 100% !important;
            width: 100% !important;
            padding: 0 !important;
        }
    }
</style>
@endsection
