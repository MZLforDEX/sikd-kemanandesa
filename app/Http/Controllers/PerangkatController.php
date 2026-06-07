<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Incident;
use App\Models\HandlingRecord;
use App\Models\PatrolSchedule;
use App\Models\PatrolLog;
use App\Models\User;
use App\Models\Role;
use App\Models\Notification;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class PerangkatController extends Controller
{
    // ==========================================
    // DASHBOARD & OVERVIEW
    // ==========================================
    public function dashboard()
    {
        $stats = [
            'total_reports' => Report::count(),
            'pending_reports' => Report::where('status', 'baru')->count(),
            'active_incidents' => Incident::whereIn('status', ['diverifikasi', 'diproses', 'ditangani'])->count(),
            'total_patrols_today' => PatrolSchedule::whereDate('patrol_date', now()->toDateString())->count(),
        ];

        $recentReports = Report::with('user')->orderBy('created_at', 'desc')->take(5)->get();
        $recentLogs = PatrolLog::with(['user', 'patrolSchedule'])->orderBy('logged_at', 'desc')->take(5)->get();
        $activeIncidents = Incident::whereIn('status', ['baru', 'diverifikasi', 'diproses', 'ditangani'])
            ->orderBy('created_at', 'desc')->take(5)->get();

        $reportsWithCoordinates = Report::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        return view('perangkat.dashboard', compact('stats', 'recentReports', 'recentLogs', 'activeIncidents', 'reportsWithCoordinates'));
    }

    // ==========================================
    // REPORT MANAGEMENT
    // ==========================================
    public function reports(Request $request)
    {
        $status = $request->get('status');
        $query = Report::with('user')->orderBy('created_at', 'desc');
        
        if ($status) {
            $query->where('status', $status);
        }

        $reports = $query->paginate(15);
        return view('perangkat.reports.index', compact('reports', 'status'));
    }

    public function showReport(Report $report)
    {
        $report->load(['user', 'attachments', 'incident']);
        return view('perangkat.reports.show', compact('report'));
    }

    public function verifyReport(Request $request, Report $report)
    {
        if ($report->status !== 'baru') {
            return redirect()->route('perangkat.reports.show', $report->id)
                ->with('error', 'Laporan ini sudah diproses dan tidak dapat diverifikasi ulang.');
        }

        $request->validate([
            'action' => ['required', Rule::in(['verify', 'reject'])],
            'notes' => ['nullable', 'string'],
            // Incident fields if verified
            'category' => ['required_if:action,verify', 'nullable', 'string'],
            'severity' => ['required_if:action,verify', 'nullable', 'string', Rule::in(['rendah', 'sedang', 'tinggi'])],
        ]);

        DB::transaction(function () use ($request, $report) {
            if ($request->action === 'reject') {
                $report->update(['status' => 'ditolak']);
                ActivityLog::log('Menolak laporan: ' . $report->title);

                // Notify citizen
                Notification::create([
                    'user_id' => $report->user_id,
                    'title' => 'Laporan Ditolak',
                    'message' => 'Laporan Anda "' . $report->title . '" telah ditolak. Alasan: ' . ($request->notes ?? 'Tidak valid.'),
                    'link' => route('warga.reports.show', $report->id),
                ]);
            } else {
                $report->update(['status' => 'diverifikasi']);
                
                // Create Incident
                $incident = Incident::create([
                    'report_id' => $report->id,
                    'title' => $report->title,
                    'description' => $report->description,
                    'category' => $request->category,
                    'location' => $report->location,
                    'incident_date' => $report->reported_at,
                    'severity' => $request->severity,
                    'status' => 'diverifikasi',
                ]);

                // Copy report attachments to incident (since it's polymorphic, we could just associate them or let them query through report, but let's keep them accessible)
                foreach ($report->attachments as $att) {
                    $incident->attachments()->create([
                        'file_path' => $att->file_path,
                        'file_name' => $att->file_name,
                        'file_type' => $att->file_type,
                        'file_size' => $att->file_size,
                    ]);
                }

                ActivityLog::log('Memverifikasi laporan & membuat kejadian: ' . $incident->title);

                // Notify citizen
                Notification::create([
                    'user_id' => $report->user_id,
                    'title' => 'Laporan Terverifikasi',
                    'message' => 'Laporan Anda "' . $report->title . '" telah diverifikasi dan sedang ditangani.',
                    'link' => route('warga.reports.show', $report->id),
                ]);
            }
        });

        return redirect()->route('perangkat.reports.show', $report->id)->with('success', 'Status laporan berhasil diperbarui.');
    }

    // ==========================================
    // INCIDENT MANAGEMENT
    // ==========================================
    public function incidents(Request $request)
    {
        $status = $request->get('status');
        $query = Incident::with('report')->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        $incidents = $query->paginate(15);
        return view('perangkat.incidents.index', compact('incidents', 'status'));
    }

    public function showIncident(Incident $incident)
    {
        $incident->load(['report.user', 'attachments', 'handlingRecords.handler']);
        $satpams = User::whereHas('role', function ($q) {
            $q->where('name', 'warga');
        })->get();

        return view('perangkat.incidents.show', compact('incident', 'satpams'));
    }

    public function assignIncident(Request $request, Incident $incident)
    {
        $request->validate([
            'handler_id' => [
                'required',
                'exists:users,id',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $handler = User::with('role')->find($value);
                    if (!$handler || !$handler->hasRole('warga')) {
                        $fail('Petugas penanganan harus berperan sebagai warga.');
                    }
                },
            ],
            'instruction' => ['required', 'string'],
        ]);

        DB::transaction(function () use ($request, $incident) {
            $incident->update(['status' => 'diproses']);
            
            if ($incident->report) {
                $incident->report->update(['status' => 'diproses']);
            }

            // Create Handling Record
            $handling = HandlingRecord::create([
                'incident_id' => $incident->id,
                'handler_id' => $request->handler_id,
                'action_taken' => 'TUGAS BARU: ' . $request->instruction,
                'status_after' => 'diproses',
                'handled_at' => now(),
            ]);

            ActivityLog::log('Menugaskan kejadian ke warga petugas ronda: ' . $incident->title);

            // Notify citizen assigned
            Notification::create([
                'user_id' => $request->handler_id,
                'title' => 'Tugas Penanganan Baru',
                'message' => 'Anda ditugaskan menangani kejadian: ' . $incident->title,
                'link' => route('warga.ronda.incidents.show', $incident->id),
            ]);
        });

        return redirect()->route('perangkat.incidents.show', $incident->id)->with('success', 'Petugas berhasil ditugaskan.');
    }

    public function updateIncidentStatus(Request $request, Incident $incident)
    {
        $request->validate([
            'status' => ['required', Rule::in(['diverifikasi', 'diproses', 'ditangani', 'selesai', 'ditolak'])],
        ]);

        $allowedTransitions = [
            'diverifikasi' => ['diproses', 'ditolak'],
            'diproses' => ['ditangani', 'ditolak'],
            'ditangani' => ['selesai', 'ditolak'],
            'selesai' => [],
            'ditolak' => [],
        ];

        $currentStatus = $incident->status;
        $newStatus = $request->status;

        if (!in_array($newStatus, $allowedTransitions[$currentStatus] ?? [])) {
            return redirect()->route('perangkat.incidents.show', $incident->id)
                ->with('error', 'Transisi status tidak valid dari "' . $currentStatus . '" ke "' . $newStatus . '".');
        }

        DB::transaction(function () use ($request, $incident) {
            $incident->update(['status' => $request->status]);
            
            if ($incident->report) {
                $incident->report->update(['status' => $request->status]);
                
                // Notify citizen
                Notification::create([
                    'user_id' => $incident->report->user_id,
                    'title' => 'Update Status Kejadian',
                    'message' => 'Status laporan Anda "' . $incident->report->title . '" diperbarui menjadi: ' . $request->status,
                    'link' => route('warga.reports.show', $incident->report->id),
                ]);
            }

            ActivityLog::log('Memperbarui status kejadian "' . $incident->title . '" menjadi: ' . $request->status);
        });

        return redirect()->route('perangkat.incidents.show', $incident->id)->with('success', 'Status kejadian berhasil diperbarui.');
    }

    // ==========================================
    // PATROL SCHEDULES
    // ==========================================
    public function schedules()
    {
        $schedules = PatrolSchedule::with('user')->orderBy('patrol_date', 'desc')->paginate(15);
        $satpams = User::whereHas('role', function ($q) {
            $q->where('name', 'warga');
        })->get();

        return view('perangkat.schedules.index', compact('schedules', 'satpams'));
    }

    public function storeSchedule(Request $request)
    {
        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'shift' => ['required', Rule::in(['pagi', 'siang', 'malam'])],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'patrol_date' => ['required', 'date', 'after_or_equal:today'],
            'area' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $schedule = PatrolSchedule::create([
            'user_id' => $request->user_id,
            'shift' => $request->shift,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'patrol_date' => $request->patrol_date,
            'area' => $request->area,
            'notes' => $request->notes,
            'status' => 'scheduled',
        ]);

        ActivityLog::log('Membuat jadwal patroli ronda untuk ' . $schedule->user->name);

        // Notify Warga
        Notification::create([
            'user_id' => $schedule->user_id,
            'title' => 'Jadwal Ronda Baru',
            'message' => 'Jadwal ronda baru pada tanggal ' . $schedule->patrol_date->format('d-m-Y') . ' (' . $schedule->shift . ') di ' . $schedule->area,
            'link' => route('warga.ronda.schedules'),
        ]);

        return redirect()->route('perangkat.schedules')->with('success', 'Jadwal patroli berhasil ditambahkan.');
    }

    public function updateSchedule(Request $request, PatrolSchedule $schedule)
    {
        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'shift' => ['required', Rule::in(['pagi', 'siang', 'malam'])],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'patrol_date' => ['required', 'date'],
            'area' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['scheduled', 'completed', 'missed'])],
        ]);

        $schedule->update([
            'user_id' => $request->user_id,
            'shift' => $request->shift,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'patrol_date' => $request->patrol_date,
            'area' => $request->area,
            'notes' => $request->notes,
            'status' => $request->status,
        ]);

        ActivityLog::log('Memperbarui jadwal patroli ID: ' . $schedule->id);

        return redirect()->route('perangkat.schedules')->with('success', 'Jadwal patroli berhasil diperbarui.');
    }

    public function deleteSchedule(PatrolSchedule $schedule)
    {
        ActivityLog::log('Menghapus jadwal patroli ID: ' . $schedule->id);
        $schedule->delete();
        return redirect()->route('perangkat.schedules')->with('success', 'Jadwal patroli berhasil dihapus.');
    }

    // ==========================================
    // USER MANAGEMENT
    // ==========================================
    public function users()
    {
        $users = User::with('role')->paginate(15);
        $roles = Role::all();
        return view('perangkat.users.index', compact('users', 'roles'));
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'role_id' => ['required', 'exists:roles,id'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        ActivityLog::log('Membuat pengguna baru: ' . $user->name . ' (' . $user->role->display_name . ')');

        return redirect()->route('perangkat.users')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function updateUser(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'role_id' => ['required', 'exists:roles,id'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role_id' => $request->role_id,
            'phone' => $request->phone,
            'address' => $request->address,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        ActivityLog::log('Memperbarui data pengguna: ' . $user->name);

        return redirect()->route('perangkat.users')->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function deleteUser(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('perangkat.users')->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        ActivityLog::log('Menghapus pengguna: ' . $user->name);
        $user->delete();

        return redirect()->route('perangkat.users')->with('success', 'Pengguna berhasil dihapus.');
    }
}
