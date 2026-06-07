@extends('layouts.app')

@section('title', 'Jadwal Ronda Warga')

@section('content')
<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8 space-y-8">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-6 rounded-3xl border border-slate-200/60 shadow-premium-sm">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Jadwal Ronda Keamanan Desa</h1>
            <p class="text-sm text-slate-500 mt-1 font-normal">Mengatur shift, area ronda, dan memantau realisasi log ronda warga.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Left: Form Tambah Jadwal -->
        <div class="lg:col-span-4 bg-white border border-slate-200/60 rounded-3xl p-6 shadow-premium-sm space-y-5">
            <h3 class="font-extrabold text-slate-900 text-base border-b border-slate-100 pb-3 flex items-center space-x-1.5">
                <i data-lucide="calendar-plus" class="w-5 h-5 text-indigo-650"></i>
                <span>Jadwalkan Ronda Baru</span>
            </h3>

            <form action="{{ route('perangkat.schedules.store') }}" method="POST" class="space-y-4">
                @csrf

                <!-- Satpam -->
                <div class="space-y-1.5">
                    <label for="user_id" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Pilih Warga Petugas Ronda</label>
                    <select name="user_id" id="user_id" required class="block w-full rounded-xl border border-slate-200 py-2.5 px-3 text-sm focus:border-indigo-500 focus:outline-hidden focus:ring-1 focus:ring-indigo-500 bg-white font-medium text-slate-800">
                        @foreach($satpams as $satpam)
                            <option value="{{ $satpam->id }}">{{ $satpam->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Date -->
                <div class="space-y-1.5">
                    <label for="patrol_date" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Tanggal Ronda</label>
                    <input type="date" name="patrol_date" id="patrol_date" required min="{{ date('Y-m-d') }}"
                        class="block w-full rounded-xl border border-slate-200 py-2.5 px-3 text-sm focus:border-indigo-500 focus:outline-hidden focus:ring-1 focus:ring-indigo-500 bg-white font-medium text-slate-800">
                </div>

                <!-- Shift & Time -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label for="shift" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Shift</label>
                        <select name="shift" id="shift" required onchange="fillShiftHours(this.value)" class="block w-full rounded-xl border border-slate-200 py-2.5 px-3 text-sm focus:border-indigo-500 focus:outline-hidden focus:ring-1 focus:ring-indigo-500 bg-white font-medium text-slate-800">
                            <option value="pagi">Pagi</option>
                            <option value="siang">Siang</option>
                            <option value="malam" selected>Malam</option>
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label for="area" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Area Ronda</label>
                        <input type="text" name="area" id="area" required placeholder="Dusun Krajan"
                            class="block w-full rounded-xl border border-slate-200 py-2.5 px-3 text-sm focus:border-indigo-500 focus:outline-hidden focus:ring-1 focus:ring-indigo-500 font-medium text-slate-800">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label for="start_time" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Jam Mulai</label>
                        <input type="time" name="start_time" id="start_time" required value="22:00"
                            class="block w-full rounded-xl border border-slate-200 py-2.5 px-3 text-sm focus:border-indigo-500 focus:outline-hidden focus:ring-1 focus:ring-indigo-500 bg-white font-medium text-slate-800">
                    </div>

                    <div class="space-y-1.5">
                        <label for="end_time" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Jam Selesai</label>
                        <input type="time" name="end_time" id="end_time" required value="06:00"
                            class="block w-full rounded-xl border border-slate-200 py-2.5 px-3 text-sm focus:border-indigo-500 focus:outline-hidden focus:ring-1 focus:ring-indigo-500 bg-white font-medium text-slate-800">
                    </div>
                </div>

                <!-- Notes -->
                <div class="space-y-1.5">
                    <label for="notes" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Catatan / Instruksi</label>
                    <textarea name="notes" id="notes" rows="2" placeholder="Fokus pada daerah sepi penduduk..."
                        class="block w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:outline-hidden focus:ring-1 focus:ring-indigo-500 font-medium text-slate-800"></textarea>
                </div>

                <button type="submit" class="w-full bg-indigo-650 hover:bg-indigo-700 text-white font-bold py-2.5 px-4 rounded-xl shadow-premium-sm flex items-center justify-center space-x-2 transition-premium hover:-translate-y-0.5">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    <span>Tambah ke Jadwal Ronda</span>
                </button>
            </form>
        </div>

        <!-- Right: Daftar Jadwal Aktif & History -->
        <div class="lg:col-span-8 bg-white border border-slate-200/60 rounded-3xl shadow-premium-sm overflow-hidden font-medium">
            <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <h2 class="font-extrabold text-slate-900 text-base">Riwayat & Daftar Jadwal</h2>
                <span class="text-xs text-slate-450 font-bold">Urut berdasarkan tanggal ronda</span>
            </div>

            @if($schedules->isEmpty())
                <div class="p-16 text-center text-slate-400">
                    <i data-lucide="calendar" class="w-12 h-12 mx-auto mb-3 text-slate-350"></i>
                    <h4 class="font-extrabold text-slate-700 text-sm">Belum Ada Jadwal Ronda</h4>
                    <p class="text-xs text-slate-500 mt-1 font-normal">Gunakan formulir di sebelah kiri untuk menjadwalkan warga petugas ronda.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-slate-50/50 text-slate-500 border-b border-slate-100 text-[10px] font-bold uppercase tracking-wider">
                                <th class="px-6 py-4">Petugas</th>
                                <th class="px-6 py-4">Tanggal & Waktu</th>
                                <th class="px-6 py-4">Shift & Area</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @foreach($schedules as $schedule)
                                <tr class="hover:bg-slate-50/25 transition text-slate-800">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-slate-900 block font-bold">{{ $schedule->user->name }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-slate-800 block text-xs font-bold">{{ $schedule->patrol_date->format('d-m-Y') }}</span>
                                        <span class="text-[10px] text-slate-400 block mt-0.5 font-normal">{{ substr($schedule->start_time, 0, 5) }} - {{ substr($schedule->end_time, 0, 5) }} WIB</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-0.5 text-[9px] font-bold rounded-sm uppercase tracking-wide bg-indigo-50 text-indigo-700 inline-block border border-indigo-100">
                                            {{ $schedule->shift }}
                                        </span>
                                        <span class="text-slate-650 text-xs font-normal block mt-1">Area: <strong>{{ $schedule->area }}</strong></span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($schedule->status === 'completed')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-100">Selesai</span>
                                        @elseif($schedule->status === 'missed')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-800 border border-rose-100">Terlewat</span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-100">Terjadwal</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right whitespace-nowrap space-x-2">
                                        <!-- Edit trigger button -->
                                        <button type="button" 
                                            onclick="openEditScheduleModal({{ json_encode($schedule) }})"
                                            class="inline-flex items-center p-1.5 bg-slate-105 hover:bg-slate-200 text-slate-700 rounded-xl transition-premium hover:-translate-y-0.5">
                                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                                        </button>
                                        <!-- Delete action -->
                                        <form action="{{ route('perangkat.schedules.delete', $schedule->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jadwal ronda ini?')" class="inline-block">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center p-1.5 bg-rose-50 text-rose-700 hover:bg-rose-100 rounded-xl transition-premium hover:-translate-y-0.5 border border-rose-100">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
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

</div>

<!-- Modal Edit Jadwal (hidden by default) -->
<div id="editScheduleModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-50 p-4">
    <div class="bg-white border border-slate-200/60 rounded-3xl w-full max-w-md shadow-2xl overflow-hidden font-medium">
        <div class="px-6 py-4 border-b border-slate-150 flex justify-between items-center bg-slate-50">
            <h3 class="font-extrabold text-slate-900 text-base flex items-center space-x-2">
                <i data-lucide="calendar" class="w-5 h-5 text-indigo-650"></i>
                <span>Edit Jadwal Ronda</span>
            </h3>
            <button type="button" onclick="closeEditScheduleModal()" class="text-slate-400 hover:text-slate-600 transition focus:outline-hidden">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>

        <form id="editScheduleForm" method="POST" class="p-6 space-y-4">
            @csrf
            
            <!-- Satpam -->
            <div class="space-y-1.5">
                <label for="edit_user_id" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Pilih Warga Petugas Ronda</label>
                <select name="user_id" id="edit_user_id" required class="block w-full rounded-xl border border-slate-200 py-2.5 px-3 text-sm focus:border-indigo-500 focus:outline-hidden focus:ring-1 focus:ring-indigo-500 bg-white font-medium text-slate-800">
                    @foreach($satpams as $satpam)
                        <option value="{{ $satpam->id }}">{{ $satpam->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Date -->
            <div class="space-y-1.5">
                <label for="edit_patrol_date" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Tanggal Ronda</label>
                <input type="date" name="patrol_date" id="edit_patrol_date" required
                    class="block w-full rounded-xl border border-slate-200 py-2.5 px-3 text-sm focus:border-indigo-500 focus:outline-hidden focus:ring-1 focus:ring-indigo-500 bg-white font-medium text-slate-800">
            </div>

            <!-- Shift & Area -->
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label for="edit_shift" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Shift</label>
                    <select name="shift" id="edit_shift" required class="block w-full rounded-xl border border-slate-200 py-2.5 px-3 text-sm focus:border-indigo-500 focus:outline-hidden focus:ring-1 focus:ring-indigo-500 bg-white font-medium text-slate-800">
                        <option value="pagi">Pagi</option>
                        <option value="siang">Siang</option>
                        <option value="malam">Malam</option>
                    </select>
                </div>

                <div class="space-y-1.5">
                    <label for="edit_area" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Area Ronda</label>
                    <input type="text" name="area" id="edit_area" required
                        class="block w-full rounded-xl border border-slate-200 py-2.5 px-3 text-sm focus:border-indigo-500 font-medium text-slate-800">
                </div>
            </div>

            <!-- Time -->
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label for="edit_start_time" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Jam Mulai</label>
                    <input type="time" name="start_time" id="edit_start_time" required
                        class="block w-full rounded-xl border border-slate-200 py-2.5 px-3 text-sm focus:border-indigo-500 bg-white font-medium text-slate-800">
                </div>

                <div class="space-y-1.5">
                    <label for="edit_end_time" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Jam Selesai</label>
                    <input type="time" name="end_time" id="edit_end_time" required
                        class="block w-full rounded-xl border border-slate-200 py-2.5 px-3 text-sm focus:border-indigo-500 bg-white font-medium text-slate-800">
                </div>
            </div>

            <!-- Status -->
            <div class="space-y-1.5">
                <label for="edit_status" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Status Realisasi</label>
                <select name="status" id="edit_status" required class="block w-full rounded-xl border border-slate-200 py-2.5 px-3 text-sm focus:border-indigo-500 focus:outline-hidden focus:ring-1 focus:ring-indigo-500 bg-white font-medium text-slate-800">
                    <option value="scheduled">Terjadwal</option>
                    <option value="completed">Selesai</option>
                    <option value="missed">Terlewat</option>
                </select>
            </div>

            <!-- Notes -->
            <div class="space-y-1.5">
                <label for="edit_notes" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Instruksi Khusus</label>
                <textarea name="notes" id="edit_notes" rows="2"
                    class="block w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:border-indigo-500 font-medium text-slate-800"></textarea>
            </div>

            <!-- Submit -->
            <div class="flex items-center justify-end space-x-2 border-t border-slate-100 pt-4">
                <button type="button" onclick="closeEditScheduleModal()" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-500 hover:bg-slate-50 transition-premium">Batal</button>
                <button type="submit" class="bg-indigo-650 hover:bg-indigo-700 text-white font-bold py-2.5 px-4 rounded-xl text-xs shadow-premium-sm transition-premium hover:-translate-y-0.5">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function fillShiftHours(shift) {
        const start = document.getElementById('start_time');
        const end = document.getElementById('end_time');
        if (shift === 'pagi') {
            start.value = '06:00';
            end.value = '14:00';
        } else if (shift === 'siang') {
            start.value = '14:00';
            end.value = '22:00';
        } else if (shift === 'malam') {
            start.value = '22:00';
            end.value = '06:00';
        }
    }

    function openEditScheduleModal(schedule) {
        const modal = document.getElementById('editScheduleModal');
        const form = document.getElementById('editScheduleForm');
        
        // Set Action URL
        form.action = `/perangkat/jadwal/${schedule.id}/update`;
        
        // Populate fields
        document.getElementById('edit_user_id').value = schedule.user_id;
        document.getElementById('edit_patrol_date').value = schedule.patrol_date.split('T')[0];
        document.getElementById('edit_shift').value = schedule.shift;
        document.getElementById('edit_area').value = schedule.area;
        document.getElementById('edit_start_time').value = schedule.start_time.substring(0,5);
        document.getElementById('edit_end_time').value = schedule.end_time.substring(0,5);
        document.getElementById('edit_status').value = schedule.status;
        document.getElementById('edit_notes').value = schedule.notes || '';
        
        modal.classList.remove('hidden');
        lucide.createIcons();
    }

    function closeEditScheduleModal() {
        document.getElementById('editScheduleModal').classList.add('hidden');
    }
</script>
@endsection
