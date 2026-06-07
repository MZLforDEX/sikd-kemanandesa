@extends('layouts.app')

@section('title', 'Jadwal & Log Ronda Anda')

@section('content')
<div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    
    <!-- Header -->
    <div class="flex justify-between items-center bg-white p-6 rounded-3xl border border-slate-200/60 shadow-premium-sm">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Jadwal & Laporan Ronda Anda</h1>
            <p class="text-sm text-slate-500 mt-1 font-normal">Lihat riwayat tugas ronda dan isi log hasil ronda setiap kali selesai ronda lapangan.</p>
        </div>
    </div>

    <!-- Schedules Grid/List -->
    <div class="bg-white border border-slate-200/60 rounded-3xl overflow-hidden shadow-premium-sm font-medium">
        
        @if($schedules->isEmpty())
            <div class="p-16 text-center text-slate-400">
                <i data-lucide="calendar" class="w-12 h-12 mx-auto mb-3 text-slate-350"></i>
                <h4 class="font-extrabold text-slate-700 text-sm">Tidak Ada Jadwal Terdaftar</h4>
                <p class="text-xs text-slate-500 mt-1 font-normal">Anda belum memiliki penugasan jadwal ronda dari perangkat desa.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-slate-50/50 text-slate-500 border-b border-slate-100 text-[10px] font-bold uppercase tracking-wider">
                            <th class="px-6 py-4">Tanggal Tugas</th>
                            <th class="px-6 py-4">Shift & Waktu</th>
                            <th class="px-6 py-4">Area Ronda</th>
                            <th class="px-6 py-4">Catatan Instruksi</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Laporan Lapangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @foreach($schedules as $sched)
                            <tr class="hover:bg-slate-50/25 transition text-slate-800">
                                <td class="px-6 py-4 whitespace-nowrap text-slate-900 font-bold">
                                    {{ $sched->patrol_date->format('d-m-Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-0.5 text-[9px] font-bold rounded-sm uppercase tracking-wide bg-indigo-50 text-indigo-700 inline-block border border-indigo-100">
                                        Shift {{ $sched->shift }}
                                    </span>
                                    <span class="text-[10px] text-slate-400 block mt-1 font-normal">{{ substr($sched->start_time, 0, 5) }} - {{ substr($sched->end_time, 0, 5) }} WIB</span>
                                </td>
                                <td class="px-6 py-4 text-slate-750 font-bold">
                                    {{ $sched->area }}
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-500 font-normal max-w-xs truncate">
                                    {{ $sched->notes ?? 'Tidak ada instruksi khusus.' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap font-bold">
                                    @if($sched->status === 'completed')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-100">Selesai</span>
                                    @elseif($sched->status === 'missed')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-800 border border-rose-100">Terlewat</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-100">Terjadwal</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    @if($sched->status === 'scheduled')
                                        <button type="button" 
                                            onclick="openPatrolLogModal({{ json_encode($sched) }})"
                                            class="inline-flex items-center space-x-1.5 text-xs font-bold bg-indigo-650 hover:bg-indigo-700 text-white px-3.5 py-2 rounded-xl transition-premium hover:-translate-y-0.5 shadow-premium-sm">
                                            <i data-lucide="plus-circle" class="w-4 h-4"></i>
                                            <span>Isi Laporan</span>
                                        </button>
                                    @else
                                        <span class="text-xs text-slate-400 italic font-normal">Sudah terlapor</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($schedules->hasPages())
                <div class="px-6 py-4 border-t border-slate-100">
                    {{ $schedules->links() }}
                </div>
            @endif
        @endif
        
    </div>

</div>

<!-- Modal Isi Log Patroli (hidden by default) -->
<div id="patrolLogModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-50 p-4">
    <div class="bg-white border border-slate-200/60 rounded-3xl w-full max-w-md shadow-2xl overflow-hidden font-medium">
        <div class="px-6 py-4 border-b border-slate-150 flex justify-between items-center bg-slate-50">
            <h3 class="font-extrabold text-slate-900 text-base flex items-center space-x-2">
                <i data-lucide="shield-check" class="w-5 h-5 text-indigo-650"></i>
                <span>Tulis Laporan Hasil Ronda</span>
            </h3>
            <button type="button" onclick="closePatrolLogModal()" class="text-slate-400 hover:text-slate-600 transition focus:outline-hidden">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>

        <form id="patrolLogForm" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
            @csrf
            
            <div class="bg-slate-50 p-3.5 rounded-2xl text-xs space-y-1 border border-slate-200/60">
                <p class="text-slate-400 font-bold uppercase tracking-wider">Tugas Ronda Terpilih:</p>
                <p class="text-slate-900 font-bold" id="selected_patrol_info"></p>
            </div>

            <!-- Checkpoint Checked -->
            <div class="space-y-1.5">
                <label for="location_checked" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Lokasi Cek Poin / Ronda</label>
                <input type="text" name="location_checked" id="location_checked" required 
                    placeholder="Contoh: Gapura RT 01, Rumah Kosong RT 03, Jembatan"
                    class="block w-full rounded-xl border border-slate-200 py-2.5 px-3 text-sm focus:border-indigo-500 focus:outline-hidden focus:ring-1 focus:ring-indigo-500 bg-white font-medium text-slate-800">
            </div>

            <!-- Condition -->
            <div class="space-y-1.5">
                <label for="condition" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Kondisi Lingkungan</label>
                <select name="condition" id="condition" required class="block w-full rounded-xl border border-slate-200 py-2.5 px-3 text-sm focus:border-indigo-500 bg-white font-medium text-slate-800">
                    <option value="aman">Aman & Terkendali</option>
                    <option value="mencurigakan">Mencurigakan / Butuh Atensi</option>
                    <option value="bahaya">Bahaya / Butuh Tindakan Darurat</option>
                </select>
            </div>

            <!-- Notes -->
            <div class="space-y-1.5">
                <label for="log_notes" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Laporan Hasil Pengamatan</label>
                <textarea name="notes" id="log_notes" rows="3" placeholder="Tuliskan temuan lapangan. Contoh: Pintu gerbang terkunci rapat, warga berkumpul ronda dengan baik, cuaca hujan."
                    class="block w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:border-indigo-500 font-medium text-slate-800"></textarea>
            </div>

            <!-- Upload Photo -->
            <div class="space-y-1.5">
                <label for="log_attachment" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Unggah Foto Kondisi Lapangan (Opsional)</label>
                <input type="file" name="attachment" id="log_attachment" accept="image/*"
                    class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition">
            </div>

            <!-- Submit -->
            <div class="flex items-center justify-end space-x-2 border-t border-slate-100 pt-4">
                <button type="button" onclick="closePatrolLogModal()" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-500 hover:bg-slate-50 transition-premium">Batal</button>
                <button type="submit" class="bg-indigo-650 hover:bg-indigo-700 text-white font-bold py-2.5 px-5 rounded-xl text-xs shadow-premium-sm transition-premium hover:-translate-y-0.5">Kirim Log Laporan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openPatrolLogModal(schedule) {
        const modal = document.getElementById('patrolLogModal');
        const form = document.getElementById('patrolLogForm');
        
        // Set Action URL
        form.action = "{{ route('warga.ronda.schedules.log', ':id') }}".replace(':id', schedule.id);
        
        // Populate display info
        const dateStr = schedule.patrol_date.split('T')[0];
        document.getElementById('selected_patrol_info').textContent = 
            `Tanggal ${dateStr} • Shift ${schedule.shift.toUpperCase()} • Area: ${schedule.area}`;
            
        modal.classList.remove('hidden');
        lucide.createIcons();
    }

    function closePatrolLogModal() {
        document.getElementById('patrolLogModal').classList.add('hidden');
    }
</script>
@endsection
