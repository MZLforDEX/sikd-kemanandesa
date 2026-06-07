<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Notification;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class WargaController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
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

        $reportsWithCoordinates = Report::where('user_id', $user->id)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        return view('warga.dashboard', compact('reports', 'stats', 'reportsWithCoordinates'));
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

        ActivityLog::log('Membuat laporan keamanan: ' . $report->title);

        // Notify officers (perangkat desa)
        $officers = User::whereHas('role', function ($q) {
            $q->where('name', 'perangkat');
        })->get();

        foreach ($officers as $officer) {
            Notification::create([
                'user_id' => $officer->id,
                'title' => 'Laporan Baru Masuk',
                'message' => 'Laporan dari ' . Auth::user()->name . ': ' . $report->title,
                'link' => route('perangkat.reports.show', $report->id),
            ]);
        }

        return redirect()->route('warga.dashboard')->with('success', 'Laporan berhasil dikirim dan sedang menunggu verifikasi.');
    }

    public function showReport(Report $report)
    {
        // Enforce owner-only access
        if ($report->user_id !== Auth::id()) {
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

        // Notify officers and active patrol wargas for today
        $todayStr = now()->toDateString();
        $responders = User::where(function ($q) use ($todayStr) {
            $q->whereHas('role', function ($r) {
                $r->where('name', 'perangkat');
            })->orWhereHas('patrolSchedules', function ($r) use ($todayStr) {
                $r->whereDate('patrol_date', $todayStr);
            });
        })->get();

        foreach ($responders as $responder) {
            Notification::create([
                'user_id' => $responder->id,
                'title' => '🚨 ALARM DARURAT WARGA! 🚨',
                'message' => $user->name . ' membutuhkan bantuan darurat di ' . $gpsLocation,
                'link' => route('perangkat.reports.show', $report->id),
            ]);
        }

        return redirect()->route('warga.dashboard')->with('emergency_triggered',
            'Sinyal darurat terkirim dengan lokasi GPS perangkat Anda (' . $latitude . ', ' . $longitude . '). Tetap tenang, petugas akan segera menghubungi atau mendatangi lokasi Anda.'
        );
    }

    public function realtimeUpdates(Request $request)
    {
        $lastId = intval($request->query('last_id', 0));
        $user = Auth::user();

        $query = Report::with('user')->where('id', '>', $lastId);

        // Warga only sees their own reports
        if ($user->hasRole('warga')) {
            $query->where('user_id', $user->id);
        }

        $newReports = $query->get()->map(function ($report) use ($user) {
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
                'reported_at' => $report->reported_at->format('d-m-Y H:i'),
            ];

            // Only expose detail URLs for authorized routes
            if ($user->hasRole('perangkat') || $user->hasRole('kades')) {
                $data['detail_url_perangkat'] = route('perangkat.reports.show', $report->id);
            }
            if ($user->id === $report->user_id) {
                $data['detail_url_warga'] = route('warga.reports.show', $report->id);
            }

            return $data;
        });

        $statsQuery = Report::query();
        if ($user->hasRole('warga')) {
            $statsQuery->where('user_id', $user->id);
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
