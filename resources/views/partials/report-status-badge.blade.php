@php
    $badges = [
        'baru' => 'bg-slate-100 text-slate-700 border-slate-200/50',
        'diverifikasi' => 'bg-blue-50 text-blue-700 border-blue-100',
        'diproses' => 'bg-amber-50 text-amber-700 border-amber-100',
        'ditangani' => 'bg-cyan-50 text-cyan-700 border-cyan-100',
        'selesai' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
        'ditolak' => 'bg-rose-50 text-rose-700 border-rose-100',
    ];
    $labels = [
        'baru' => 'Baru',
        'diverifikasi' => 'Terverifikasi',
        'diproses' => 'Sedang Diproses',
        'ditangani' => 'Ditangani Lapangan',
        'selesai' => 'Kasus Selesai',
        'ditolak' => 'Ditolak',
    ];
    $cls = $badges[$status] ?? $badges['baru'];
    $label = $labels[$status] ?? ucfirst($status);
@endphp
<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold border {{ $cls }}">{{ $label }}</span>
