@extends('layouts.app')

@section('title', 'Tindak Lanjut Penanganan Kejadian')

@section('content')
<div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    
    <!-- Navigation Back Link -->
    <a href="{{ route('warga.ronda.incidents.index') }}" class="inline-flex items-center space-x-1.5 text-xs font-bold text-slate-500 hover:text-slate-900 transition-premium hover:-translate-x-0.5">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
        <span>Kembali ke Daftar Tugas</span>
    </a>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Left: Incident Information & History -->
        <div class="lg:col-span-7 space-y-6">
            
            <!-- Details Card -->
            <div class="bg-white border border-slate-200/60 rounded-3xl shadow-premium-sm overflow-hidden font-medium">
                <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tugas Penanganan Kejadian</span>
                    <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full border uppercase
                        {{ $incident->status === 'selesai' ? 'bg-emerald-50 border-emerald-100 text-emerald-800' : 'bg-rose-50 border-rose-100 text-rose-800' }}">
                        {{ $incident->status }}
                    </span>
                </div>

                <div class="p-6 space-y-6">
                    <div>
                        <div class="flex items-center space-x-2">
                            <h2 class="text-xl font-extrabold text-slate-900 leading-tight tracking-tight">{{ $incident->title }}</h2>
                            <span class="px-2 py-0.5 text-[9px] font-bold rounded-sm uppercase tracking-wider
                                {{ $incident->severity === 'tinggi' ? 'bg-rose-55 text-rose-805' : ($incident->severity === 'sedang' ? 'bg-amber-50 text-amber-808' : 'bg-slate-100 text-slate-800') }}">
                                {{ $incident->severity }}
                            </span>
                        </div>
                        <span class="text-xs text-slate-450 mt-1 block font-normal">Dilaporkan Pada: {{ $incident->incident_date->format('d-m-Y H:i') }} WIB</span>
                    </div>

                    <div class="grid grid-cols-2 gap-4 bg-slate-50 p-4 rounded-2xl text-xs">
                        <div>
                            <span class="font-bold text-slate-400 uppercase tracking-wider block">Kategori</span>
                            <span class="font-bold text-slate-800 block mt-0.5 capitalize">{{ $incident->category }}</span>
                        </div>
                        <div>
                            <span class="font-bold text-slate-400 uppercase tracking-wider block">Lokasi</span>
                            <span class="font-bold text-slate-800 block mt-0.5 leading-relaxed">{{ $incident->location }}</span>
                        </div>
                    </div>

                    <div class="space-y-1.5 text-sm border-t border-slate-100 pt-4">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Kronologi / Laporan Awal</span>
                        <p class="whitespace-pre-line leading-relaxed font-normal text-slate-700">{{ $incident->description }}</p>
                    </div>

                    @if($incident->attachments->isNotEmpty())
                        <div class="border-t border-slate-100 pt-4 space-y-3">
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Bukti Foto Lampiran</span>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                @foreach($incident->attachments as $attachment)
                                    <div class="border border-slate-200 rounded-2xl overflow-hidden shadow-premium-sm bg-slate-50">
                                        <img src="{{ asset('storage/' . $attachment->file_path) }}" alt="{{ $attachment->file_name }}" class="w-full h-40 object-cover">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Timeline of Handling Actions -->
            <div class="bg-white border border-slate-200/60 rounded-3xl p-6 shadow-premium-sm space-y-6 font-medium">
                <h3 class="font-extrabold text-slate-900 text-base border-b border-slate-100 pb-3">Riwayat Penanganan Anda</h3>
                
                @if($incident->handlingRecords->isEmpty())
                    <div class="text-center py-6 text-slate-400">
                        <i data-lucide="clipboard" class="w-10 h-10 mx-auto mb-2 text-slate-350"></i>
                        <p class="text-xs font-normal">Belum ada catatan progres penanganan lapangan.</p>
                    </div>
                @else
                    <div class="relative pl-6 border-l border-slate-200 space-y-8">
                        @foreach($incident->handlingRecords as $record)
                            <div class="relative font-medium text-slate-850">
                                @php
                                    $bulletColor = $record->status_after === 'selesai' ? 'bg-emerald-100 border-emerald-500 text-emerald-500' : 'bg-indigo-100 border-indigo-500 text-indigo-600';
                                    $dotColor = $record->status_after === 'selesai' ? 'bg-emerald-500' : 'bg-indigo-650';
                                 @endphp
                                <span class="absolute -left-[31px] top-1.5 flex h-4 w-4 items-center justify-center rounded-full {{ $bulletColor }} ring-4 ring-white border">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $dotColor }}"></span>
                                </span>
                                <div class="space-y-1">
                                    <h4 class="text-sm font-bold text-slate-900">
                                        @if(str_contains($record->action_taken, 'TUGAS BARU:'))
                                            Tugas Baru Diterima
                                        @else
                                            Laporan Kerja Lapangan
                                        @endif
                                    </h4>
                                    <p class="text-[10px] text-slate-450 font-normal">{{ $record->handled_at->format('d-m-Y H:i') }} WIB • Oleh: <strong>{{ $record->handler->name }}</strong></p>
                                    <p class="text-xs text-slate-605 leading-relaxed mt-1 font-normal">
                                        <strong>Tindakan:</strong> {{ str_replace('TUGAS BARU:', '', $record->action_taken) }}
                                    </p>
                                    @if($record->result)
                                        <div class="text-xs text-slate-700 bg-slate-50 border border-slate-200/60 p-3 rounded-2xl mt-2 leading-relaxed font-normal">
                                            <strong class="font-bold text-slate-900">Hasil / Kondisi Akhir:</strong> {{ $record->result }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>

        <!-- Right: Action Form -->
        <div class="lg:col-span-5 space-y-6">
            
            <div class="bg-white border border-slate-200/60 rounded-3xl p-6 shadow-premium-sm space-y-5 font-medium">
                <h3 class="font-extrabold text-slate-900 text-base border-b border-slate-100 pb-3 flex items-center space-x-1.5">
                    <i data-lucide="edit-3" class="w-5 h-5 text-indigo-650"></i>
                    <span>Tulis Tindak Lanjut Lapangan</span>
                </h3>

                <form action="{{ route('warga.ronda.incidents.handling', $incident->id) }}" method="POST" class="space-y-4">
                    @csrf

                    <!-- Action Taken -->
                    <div class="space-y-1.5">
                        <label for="action_taken" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Tindakan Lapangan Yang Dilakukan</label>
                        <textarea name="action_taken" id="action_taken" rows="3" required 
                            placeholder="Contoh: Mendatangi TKP, memeriksa saksi, mengamankan barang bukti, membubarkan kerumunan..."
                            class="block w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:border-indigo-500 font-medium text-slate-800"></textarea>
                    </div>

                    <!-- Result -->
                    <div class="space-y-1.5">
                        <label for="result" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Hasil / Perkembangan Kondisi (Opsional)</label>
                        <textarea name="result" id="result" rows="2" 
                            placeholder="Contoh: Situasi sudah kondusif, pelaku melarikan diri tapi teridentifikasi, barang curian sudah dikembalikan..."
                            class="block w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:border-indigo-500 font-medium text-slate-800"></textarea>
                    </div>

                    <!-- Status After -->
                    <div class="space-y-1.5">
                        <label for="status_after" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Status Setelah Penanganan</label>
                        <select name="status_after" id="status_after" required class="block w-full rounded-xl border border-slate-200 py-2.5 px-3 text-sm focus:border-indigo-500 bg-white font-medium text-slate-800">
                            <option value="diproses" {{ $incident->status === 'diproses' ? 'selected' : '' }}>Diproses (Sedang Penanganan lanjutan)</option>
                            <option value="ditangani" {{ $incident->status === 'ditangani' ? 'selected' : '' }}>Ditangani (Tindakan lapangan selesai)</option>
                            <option value="selesai" {{ $incident->status === 'selesai' ? 'selected' : '' }}>Selesai (Kasus ditutup/Selesai)</option>
                        </select>
                    </div>

                    <button type="submit" class="w-full bg-indigo-655 hover:bg-indigo-700 text-white font-bold py-2.5 px-4 rounded-xl shadow-premium-sm flex items-center justify-center space-x-2 transition-premium hover:-translate-y-0.5">
                        <i data-lucide="check-square" class="w-4 h-4"></i>
                        <span>Kirim Laporan Tindak Lanjut</span>
                    </button>
                </form>
            </div>

        </div>

    </div>

</div>
@endsection
