<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Notification;
use App\Models\ActivityLog;
use App\Models\User;
use App\Models\Incident;
use App\Models\HandlingRecord;
use App\Models\PatrolSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class WargaController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        $todayStr = now()->toDateString();

        // Get today's patrol schedule for this user
        $todayRonda = PatrolSchedule::where('user_id', $user->id)
            ->whereDate('patrol_date', $todayStr)
            ->first();

        // Get nearest upcoming patrol schedule
        $upcomingRonda = PatrolSchedule::where('user_id', $user->id)
            ->where('patrol_date', '>', $todayStr)
            ->where('status', 'scheduled')
            ->orderBy('patrol_date', 'asc')
            ->first();

        // Handle notifications and reports if today is patrol day
        $todayReports = collect();
        if ($todayRonda) {
            // Check if reminder notification already exists for today's patrol
            $hasTodayNotification = Notification::where('user_id', $user->id)
                ->where('title', 'Jadwal Ronda Hari Ini')
                ->whereDate('created_at', $todayStr)
                ->exists();

            if (!$hasTodayNotification) {
                Notification::create([
                    'user_id' => $user->id,
                    'title' => 'Jadwal Ronda Hari Ini',
                    'message' => 'Hari ini Anda mendapat jadwal ronda (' . $todayRonda->shift . ') di ' . $todayRonda->area . '. Silakan bersiap!',
                    'link' => route('warga.ronda.dashboard'),
                ]);
            }

            // Patrol officer sees all citizen reports from today to monitor
            $todayReports = Report::with(['user', 'incident'])
                ->whereDate('reported_at', $todayStr)
                ->orderBy('reported_at', 'desc')
                ->get();
        }

        $reports = Report::with('incident.handlingRecords')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $stats = [
            'total' => $reports->count(),
            'baru' => $reports->where('status', 'baru')->count(),
            'proses' => $reports->whereIn('status', ['diverifikasi', 'diproses', 'ditangani'])->count(),
            'selesai' => $reports->where('status', 'selesai')->count(),
        ];

        // Map coordinates: display all reports that have coordinates
        $reportsWithCoordinates = Report::with('user')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        return view('warga.dashboard', compact(
            'reports', 
            'stats', 
            'reportsWithCoordinates', 
            'todayRonda', 
            'upcomingRonda', 
            'todayReports'
        ));
    }

    public function createReport()
    {
        return view('warga.report-create');
    }

    public function storeReport(Request $request)
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'location' => ['required', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'attachment' => ['nullable', 'file', 'image', 'max:5120'], // Max 5MB images
        ]);

        // Default or random offset around Desa Awa coordinates
        $latitude = $request->filled('latitude') ? $request->latitude : (-3.94694400 + (rand(-150, 150) / 100000.0));
        $longitude = $request->filled('longitude') ? $request->longitude : (121.35102800 + (rand(-150, 150) / 100000.0));

        $report = null;

        DB::transaction(function () use ($request, &$report, $latitude, $longitude) {
            $report = Report::create([
                'user_id' => Auth::id(),
                'title' => $request->title,
                'description' => $request->description,
                'location' => $request->location,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'status' => 'baru',
                'reported_at' => now(),
            ]);

            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $path = $file->store('attachments', 'public');
                
                $report->attachments()->create([
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }

            // Create Incident
            $incident = Incident::create([
                'report_id' => $report->id,
                'title' => $report->title,
                'description' => $report->description,
                'category' => 'lainnya',
                'location' => $report->location,
                'incident_date' => $report->reported_at,
                'severity' => 'sedang',
                'status' => 'diverifikasi',
            ]);

            // Copy attachments
            if ($report->attachments()->exists()) {
                foreach ($report->attachments as $att) {
                    $incident->attachments()->create([
                        'file_path' => $att->file_path,
                        'file_name' => $att->file_name,
                        'file_type' => $att->file_type,
                        'file_size' => $att->file_size,
                    ]);
                }
            }

            $report->update(['status' => 'diverifikasi']);

            // Find active patrol wargas for today
            $todayStr = now()->toDateString();
            $activePatrolUserIds = PatrolSchedule::whereDate('patrol_date', $todayStr)
                ->pluck('user_id')
                ->unique();

            if ($activePatrolUserIds->isNotEmpty()) {
                $report->update(['status' => 'diproses']);
                $incident->update(['status' => 'diproses']);

                foreach ($activePatrolUserIds as $handlerId) {
                    // Create Handling Record
                    HandlingRecord::create([
                        'incident_id' => $incident->id,
                        'handler_id' => $handlerId,
                        'action_taken' => 'TUGAS OTOMATIS: Laporan baru masuk dan langsung diteruskan ke petugas ronda hari ini.',
                        'status_after' => 'diproses',
                        'handled_at' => now(),
                    ]);

                    // Notify handler
                    Notification::create([
                        'user_id' => $handlerId,
                        'title' => 'Tugas Ronda Baru (Laporan Masuk)',
                        'message' => 'Laporan masuk dari ' . Auth::user()->name . ': ' . $report->title . '. Silakan tindak lanjuti.',
                        'link' => route('warga.ronda.incidents.show', $incident->id),
                    ]);
                }
            }
        });

        ActivityLog::log('Membuat laporan keamanan otomatis terverifikasi: ' . $report->title);

        // Notify officers (perangkat desa)
        $officers = User::whereHas('role', function ($q) {
            $q->where('name', 'perangkat');
        })->get();

        foreach ($officers as $officer) {
            Notification::create([
                'user_id' => $officer->id,
                'title' => 'Laporan Baru Masuk (Terverifikasi Otomatis)',
                'message' => 'Laporan dari ' . Auth::user()->name . ': ' . $report->title . ' telah otomatis diteruskan ke petugas ronda.',
                'link' => route('perangkat.reports.show', $report->id),
            ]);
        }

        return redirect()->route('warga.dashboard')->with('success', 'Laporan berhasil dikirim dan otomatis diproses oleh petugas ronda.');
    }

    public function showReport(Report $report)
    {
        $user = Auth::user();
        $isPatrolActiveToday = false;
        $todayStr = now()->toDateString();
        
        if ($user->hasRole('warga')) {
            $isPatrolActiveToday = $user->patrolSchedules()->whereDate('patrol_date', $todayStr)->exists();
        }

        // Allow viewing if owner, or if on active patrol today and report is from today
        $canView = ($report->user_id === $user->id) || ($isPatrolActiveToday && $report->reported_at->toDateString() === $todayStr);

        if (!$canView) {
            abort(403, 'Anda tidak memiliki hak untuk melihat laporan ini.');
        }

        $report->load(['incident.handlingRecords.handler', 'attachments']);
        return view('warga.report-show', compact('report'));
    }

    public function triggerEmergency(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'accuracy' => ['nullable', 'numeric', 'min:0'],
        ], [
            'latitude.required' => 'Koordinat GPS wajib diambil dari perangkat. Izinkan akses lokasi lalu coba lagi.',
            'longitude.required' => 'Koordinat GPS wajib diambil dari perangkat. Izinkan akses lokasi lalu coba lagi.',
        ]);

        $latitude = $validated['latitude'];
        $longitude = $validated['longitude'];
        $accuracyText = isset($validated['accuracy'])
            ? ' (akurasi GPS ±' . round($validated['accuracy']) . ' meter)'
            : '';

        $gpsLocation = sprintf('GPS Perangkat: %s, %s%s', $latitude, $longitude, $accuracyText);

        $report = null;
        $todayStr = now()->toDateString();

        // Automate Verification and Assignment
        DB::transaction(function () use (&$report, $user, $gpsLocation, $latitude, $longitude, $todayStr) {
            $report = Report::create([
                'user_id' => $user->id,
                'title' => '🚨 BUTUH BANTUAN DARURAT!',
                'description' => 'Warga ' . $user->name . ' memicu bantuan darurat. ' . $gpsLocation
                    . '. Alamat terdaftar: ' . $user->address,
                'location' => $gpsLocation,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'status' => 'baru',
                'reported_at' => now(),
            ]);

            ActivityLog::log('Memicu tombol bantuan darurat!');

            // Create Incident
            $incident = Incident::create([
                'report_id' => $report->id,
                'title' => $report->title,
                'description' => $report->description,
                'category' => 'darurat',
                'location' => $report->location,
                'incident_date' => $report->reported_at,
                'severity' => 'tinggi',
                'status' => 'diverifikasi',
            ]);

            $report->update(['status' => 'diverifikasi']);

            // Find active patrol wargas for today
            $activePatrolUserIds = PatrolSchedule::whereDate('patrol_date', $todayStr)
                ->pluck('user_id')
                ->unique();

            if ($activePatrolUserIds->isNotEmpty()) {
                $report->update(['status' => 'diproses']);
                $incident->update(['status' => 'diproses']);

                foreach ($activePatrolUserIds as $handlerId) {
                    // Create Handling Record
                    HandlingRecord::create([
                        'incident_id' => $incident->id,
                        'handler_id' => $handlerId,
                        'action_taken' => 'TUGAS OTOMATIS: Alarm darurat aktif, langsung ditugaskan ke petugas ronda hari ini.',
                        'status_after' => 'diproses',
                        'handled_at' => now(),
                    ]);
                }
            }
        });

        // Notify officers, kades, and active patrol wargas for today
        $responders = User::where(function ($q) use ($todayStr) {
            $q->whereHas('role', function ($r) {
                $r->whereIn('name', ['perangkat', 'kades']);
            })->orWhereHas('patrolSchedules', function ($r) use ($todayStr) {
                $r->whereDate('patrol_date', $todayStr);
            });
        })->get();

        foreach ($responders as $responder) {
            if ($responder->hasRole('perangkat')) {
                $link = route('perangkat.reports.show', $report->id);
            } elseif ($responder->hasRole('kades')) {
                $link = route('kades.reports');
            } else {
                $incident = Incident::where('report_id', $report->id)->first();
                $link = $incident ? route('warga.ronda.incidents.show', $incident->id) : route('warga.reports.show', $report->id);
            }

            Notification::create([
                'user_id' => $responder->id,
                'title' => '🚨 ALARM DARURAT WARGA! 🚨',
                'message' => $user->name . ' membutuhkan bantuan darurat di ' . $gpsLocation,
                'link' => $link,
            ]);
        }

        return redirect()->route('warga.dashboard')->with('emergency_triggered',
            'Sinyal darurat terkirim dengan lokasi GPS perangkat Anda (' . $latitude . ', ' . $longitude . '). Tetap tenang, petugas akan segera menghubungi atau mendatangi lokasi Anda.'
        );
    }

    public function prosesReport(Report $report)
    {
        $user = Auth::user();
        $todayStr = now()->toDateString();

        // Ensure the user is on active patrol today
        $isPatrolActive = $user->patrolSchedules()->whereDate('patrol_date', $todayStr)->exists();
        if (!$isPatrolActive) {
            abort(403, 'Hanya petugas ronda aktif hari ini yang dapat memproses laporan.');
        }

        DB::transaction(function () use ($report, $user) {
            // Find or create incident
            $incident = $report->incident;
            if (!$incident) {
                $incident = Incident::create([
                    'report_id' => $report->id,
                    'title' => $report->title,
                    'description' => $report->description,
                    'category' => 'lainnya',
                    'location' => $report->location,
                    'incident_date' => $report->reported_at,
                    'severity' => 'sedang',
                    'status' => 'diproses',
                ]);

                // Copy attachments
                if ($report->attachments()->exists()) {
                    foreach ($report->attachments as $att) {
                        $incident->attachments()->create([
                            'file_path' => $att->file_path,
                            'file_name' => $att->file_name,
                            'file_type' => $att->file_type,
                            'file_size' => $att->file_size,
                        ]);
                    }
                }
            } else {
                $incident->update(['status' => 'diproses']);
            }

            $report->update(['status' => 'diproses']);

            // Create Handling Record for this patrol officer
            HandlingRecord::firstOrCreate([
                'incident_id' => $incident->id,
                'handler_id' => $user->id,
            ], [
                'action_taken' => 'Petugas ronda hari ini mengambil alih penanganan laporan secara langsung.',
                'status_after' => 'diproses',
                'handled_at' => now(),
            ]);

            // Create activity log
            ActivityLog::log('Mengambil alih penanganan laporan keamanan: ' . $report->title);
        });

        return redirect()->back()->with('success', 'Laporan berhasil diambil alih dan langsung diproses oleh Anda.');
    }

    public function realtimeUpdates(Request $request)
    {
        $lastId = intval($request->query('last_id', 0));
        $user = Auth::user();
        $todayStr = now()->toDateString();

        $query = Report::with(['user', 'incident'])->where('id', '>', $lastId);

        if ($user->hasRole('warga')) {
            $query->where(function ($q) use ($user, $todayStr) {
                $q->where('user_id', $user->id)
                  ->orWhereDate('reported_at', $todayStr);
            });
        }

        $newReports = $query->get()->map(function ($report) use ($user, $todayStr) {
            $data = [
                'id' => $report->id,
                'title' => $report->title,
                'description' => $report->description,
                'location' => $report->location,
                'latitude' => $report->latitude,
                'longitude' => $report->longitude,
                'status' => $report->status,
                'user_name' => $report->user->name,
                'user_id' => $report->user_id,
                'reported_at' => $report->reported_at->toIso8601String(),
                'incident_id' => $report->incident ? $report->incident->id : null,
            ];

            // Only expose detail URLs for authorized routes
            if ($user->hasRole('perangkat') || $user->hasRole('kades')) {
                $data['detail_url_perangkat'] = route('perangkat.reports.show', $report->id);
            }
            
            $isPatrolActiveToday = PatrolSchedule::where('user_id', $user->id)
                ->whereDate('patrol_date', $todayStr)
                ->exists();

            if ($user->id === $report->user_id || ($isPatrolActiveToday && $report->reported_at->toDateString() === $todayStr)) {
                $data['detail_url_warga'] = route('warga.reports.show', $report->id);
            }

            return $data;
        });

        $statsQuery = Report::query();
        if ($user->hasRole('warga')) {
            $isPatrolActiveToday = PatrolSchedule::where('user_id', $user->id)
                ->whereDate('patrol_date', $todayStr)
                ->exists();

            if ($isPatrolActiveToday) {
                $statsQuery->where(function ($q) use ($user, $todayStr) {
                    $q->where('user_id', $user->id)
                      ->orWhereDate('reported_at', $todayStr);
                });
            } else {
                $statsQuery->where('user_id', $user->id);
            }
        }

        $stats = [
            'total' => (clone $statsQuery)->count(),
            'baru' => (clone $statsQuery)->where('status', 'baru')->count(),
            'diverifikasi' => (clone $statsQuery)->where('status', 'diverifikasi')->count(),
            'diproses' => (clone $statsQuery)->where('status', 'diproses')->count(),
            'ditangani' => (clone $statsQuery)->where('status', 'ditangani')->count(),
            'selesai' => (clone $statsQuery)->where('status', 'selesai')->count(),
            'ditolak' => (clone $statsQuery)->where('status', 'ditolak')->count(),
            'proses' => (clone $statsQuery)->whereIn('status', ['diverifikasi', 'diproses', 'ditangani'])->count(),
        ];

        return response()->json([
            'reports' => $newReports,
            'stats' => $stats
        ]);
    }
}
