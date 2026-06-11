<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Incident;
use App\Models\PatrolLog;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KadesController extends Controller
{
    public function dashboard()
    {
        $now = Carbon::now();

        // General stats expected by view
        $stats = [
            'total_reports' => Report::count(),
            'verified_reports' => Report::where('status', 'diverifikasi')->count(),
            'processing_reports' => Report::where('status', 'diproses')->count(),
            'completed_reports' => Report::where('status', 'selesai')->count(),
            'rejected_reports' => Report::where('status', 'ditolak')->count(),
        ];

        // Incidents by location (Vulnerability mapping / Titik Rawan) mapped to array
        $hotspots = Incident::select('location', DB::raw('count(*) as total'))
            ->whereNotNull('location')
            ->groupBy('location')
            ->orderBy('total', 'desc')
            ->take(5)
            ->get()
            ->pluck('total', 'location')
            ->toArray();

        // Incidents by category
        $incidentsByCategory = Incident::select('category', DB::raw('count(*) as total'))
            ->groupBy('category')
            ->get();

        $driver = DB::connection()->getDriverName();
        $monthExpr = $driver === 'sqlite' 
            ? "strftime('%m', incident_date)" 
            : "MONTH(incident_date)";

        // Monthly trends for the current year
        $monthlyTrends = Incident::select(
                DB::raw("{$monthExpr} as month"),
                DB::raw('count(*) as total')
            )
            ->whereYear('incident_date', $now->year)
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get()
            ->map(function ($item) {
                $item->month_name = Carbon::create()->month((int)$item->month)->translatedFormat('F');
                return $item;
            });

        $recentReports = Report::with('user')->orderBy('created_at', 'desc')->take(5)->get();
        $recentLogs = PatrolLog::with(['user', 'patrolSchedule'])->orderBy('logged_at', 'desc')->take(5)->get();

        $reportsWithCoordinates = Report::with('user')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        return view('kades.dashboard', compact(
            'stats',
            'hotspots',
            'incidentsByCategory',
            'monthlyTrends',
            'recentReports',
            'recentLogs',
            'reportsWithCoordinates'
        ));
    }

    public function reports(Request $request)
    {
        $status = $request->get('status');
        $query = Report::with(['user', 'incident.handlingRecords.handler'])->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        $reports = $query->paginate(15);
        return view('kades.reports', compact('reports', 'status'));
    }

    public function rekap(Request $request)
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $status = $request->get('status');

        $query = Report::with(['user', 'incident.handlingRecords.handler']);

        if ($startDate) {
            $query->whereDate('reported_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('reported_at', '<=', $endDate);
        }
        if ($status) {
            $query->where('status', $status);
        }

        $reports = $query->orderBy('reported_at', 'desc')->paginate(50);

        return view('kades.rekap', compact('reports'));
    }

    public function tren()
    {
        // 1. Incidents count by category mapped to associative array
        $categories = Incident::select('category', DB::raw('count(*) as total'))
            ->groupBy('category')
            ->get()
            ->pluck('total', 'category')
            ->toArray();

        // 2. Incident count by severity mapped to associative array
        $severities = Incident::select('severity', DB::raw('count(*) as total'))
            ->groupBy('severity')
            ->get()
            ->pluck('total', 'severity')
            ->toArray();

        // 3. Paginated Activity Logs for Audit
        $logs = ActivityLog::with('user.role')->orderBy('created_at', 'desc')->paginate(20);

        return view('kades.tren', compact('categories', 'severities', 'logs'));
    }
}
