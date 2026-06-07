<?php

namespace App\Http\Controllers;

use App\Models\PatrolSchedule;
use App\Models\PatrolLog;
use App\Models\Incident;
use App\Models\HandlingRecord;
use App\Models\Notification;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SatpamController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        
        $schedules = PatrolSchedule::where('user_id', $user->id)
            ->orderBy('patrol_date', 'desc')
            ->take(5)
            ->get();

        $assignedIncidents = Incident::whereHas('handlingRecords', function ($q) use ($user) {
                $q->where('handler_id', $user->id);
            })
            ->whereIn('status', ['diproses', 'diverifikasi'])
            ->orderBy('created_at', 'desc')
            ->get();

        $stats = [
            'schedules_count' => PatrolSchedule::where('user_id', $user->id)->count(),
            'pending_tasks' => $assignedIncidents->count(),
            'logs_count' => PatrolLog::where('user_id', $user->id)->count(),
        ];

        return view('satpam.dashboard', compact('schedules', 'assignedIncidents', 'stats'));
    }

    // ==========================================
    // PATROL SCHEDULES & LOGS
    // ==========================================
    public function schedules()
    {
        $user = Auth::user();
        $schedules = PatrolSchedule::where('user_id', $user->id)
            ->orderBy('patrol_date', 'desc')
            ->paginate(15);

        return view('satpam.schedules', compact('schedules'));
    }

    public function storePatrolLog(Request $request, PatrolSchedule $schedule)
    {
        // Enforce owner-only scheduling access
        if ($schedule->user_id !== Auth::id()) {
            abort(403, 'Akses ditolak.');
        }

        if ($schedule->status !== 'scheduled') {
            return redirect()->route('warga.ronda.schedules')->with('error', 'Jadwal ini sudah memiliki laporan patroli.');
        }

        // Only allow logging for today's schedules
        if ($schedule->patrol_date->format('Y-m-d') !== now()->format('Y-m-d')) {
            return redirect()->route('warga.ronda.schedules')->with('error', 'Anda hanya dapat mengisi log patroli untuk jadwal hari ini.');
        }

        $request->validate([
            'location_checked' => ['required', 'string', 'max:255'],
            'condition' => ['required', Rule::in(['aman', 'mencurigakan', 'bahaya'])],
            'notes' => ['nullable', 'string'],
            'attachment' => ['nullable', 'file', 'image', 'max:5120'],
        ]);

        DB::transaction(function () use ($request, $schedule) {
            $log = PatrolLog::create([
                'patrol_schedule_id' => $schedule->id,
                'user_id' => Auth::id(),
                'logged_at' => now(),
                'location_checked' => $request->location_checked,
                'condition' => $request->condition,
                'notes' => $request->notes,
            ]);

            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $path = $file->store('patrol_attachments', 'public');
                $log->attachments()->create([
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }

            // Mark schedule as completed if it was just scheduled
            if ($schedule->status === 'scheduled') {
                $schedule->update(['status' => 'completed']);
            }

            ActivityLog::log('Mengisi log patroli di: ' . $log->location_checked);

            // If danger condition is reported, alert Perangkat
            if ($request->condition === 'bahaya' || $request->condition === 'mencurigakan') {
                $officers = User::whereHas('role', function ($q) {
                    $q->where('name', 'perangkat');
                })->get();

                foreach ($officers as $officer) {
                    Notification::create([
                        'user_id' => $officer->id,
                        'title' => '⚠️ Temuan Patroli Ronda: ' . strtoupper($request->condition),
                        'message' => 'Warga ' . Auth::user()->name . ' melaporkan kondisi ' . $request->condition . ' di ' . $request->location_checked . '. Catatan: ' . ($request->notes ?? '-'),
                        'link' => route('perangkat.dashboard'), // Redirect to dashboard or patrol section
                    ]);
                }
            }
        });

        return redirect()->route('warga.ronda.schedules')->with('success', 'Log patroli berhasil disimpan.');
    }

    // ==========================================
    // INCIDENT TASKS & HANDLING
    // ==========================================
    public function incidents()
    {
        $user = Auth::user();
        
        $incidents = Incident::whereHas('handlingRecords', function ($q) use ($user) {
                $q->where('handler_id', $user->id);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('satpam.incidents.index', compact('incidents'));
    }

    public function showIncident(Incident $incident)
    {
        $user = Auth::user();
        
        // Enforce user is assigned to this incident
        $isAssigned = $incident->handlingRecords()->where('handler_id', $user->id)->exists();
        if (!$isAssigned) {
            abort(403, 'Anda tidak ditugaskan untuk menangani kejadian ini.');
        }

        $incident->load(['report.user', 'attachments', 'handlingRecords.handler']);
        return view('satpam.incidents.show', compact('incident'));
    }

    public function storeHandlingRecord(Request $request, Incident $incident)
    {
        $user = Auth::user();
        
        $isAssigned = $incident->handlingRecords()->where('handler_id', $user->id)->exists();
        if (!$isAssigned) {
            abort(403, 'Akses ditolak.');
        }

        $request->validate([
            'action_taken' => ['required', 'string'],
            'result' => ['nullable', 'string'],
            'status_after' => ['required', Rule::in(['diproses', 'ditangani', 'selesai'])],
        ]);

        $allowedTransitions = [
            'diverifikasi' => ['diproses'],
            'diproses' => ['ditangani'],
            'ditangani' => ['selesai'],
            'selesai' => [],
            'ditolak' => [],
        ];

        $currentStatus = $incident->status;
        $newStatus = $request->status_after;

        if (!in_array($newStatus, $allowedTransitions[$currentStatus] ?? [])) {
            return redirect()->route('warga.ronda.incidents.show', $incident->id)
                ->with('error', 'Transisi status tidak valid dari "' . $currentStatus . '" ke "' . $newStatus . '".');
        }

        DB::transaction(function () use ($request, $incident, $user) {
            $incident->update(['status' => $request->status_after]);
            
            if ($incident->report) {
                $incident->report->update(['status' => $request->status_after]);
            }

            // Create Handling Record
            HandlingRecord::create([
                'incident_id' => $incident->id,
                'handler_id' => $user->id,
                'action_taken' => $request->action_taken,
                'result' => $request->result,
                'status_after' => $request->status_after,
                'handled_at' => now(),
            ]);

            ActivityLog::log('Menambahkan tindak lanjut penanganan kejadian: ' . $incident->title);

            // Notify citizen if attached to a report
            if ($incident->report) {
                Notification::create([
                    'user_id' => $incident->report->user_id,
                    'title' => 'Perkembangan Penanganan Laporan',
                    'message' => 'Laporan Anda "' . $incident->report->title . '" ditindaklanjuti dengan status: ' . $request->status_after,
                    'link' => route('warga.reports.show', $incident->report->id),
                ]);
            }

            // Notify Perangkat Desa
            $officers = User::whereHas('role', function ($q) {
                $q->where('name', 'perangkat');
            })->get();

            foreach ($officers as $officer) {
                Notification::create([
                    'user_id' => $officer->id,
                    'title' => 'Update Penanganan Kejadian',
                    'message' => 'Petugas ronda ' . $user->name . ' memperbarui penanganan kejadian "' . $incident->title . '" menjadi: ' . $request->status_after,
                    'link' => route('perangkat.incidents.show', $incident->id),
                ]);
            }
        });

        return redirect()->route('warga.ronda.incidents.show', $incident->id)->with('success', 'Tindak lanjut berhasil dicatat.');
    }
}
