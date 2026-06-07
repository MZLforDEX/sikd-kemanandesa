<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\WargaController;
use App\Http\Controllers\PerangkatController;
use App\Http\Controllers\SatpamController;
use App\Http\Controllers\KadesController;
use App\Http\Controllers\NotificationController;
use App\Models\Incident;
use App\Models\Report;
use App\Models\PatrolSchedule;
use App\Models\PatrolLog;

// ==========================================
// PUBLIC LANDING PAGE & STATS
// ==========================================
Route::get('/', function () {
    $stats = [
        'laporan_baru' => Report::where('status', 'baru')->count(),
        'laporan_diproses' => Report::whereIn('status', ['diverifikasi', 'diproses', 'ditangani'])->count(),
        'laporan_selesai' => Report::where('status', 'selesai')->count(),
        'total_patroli_hari_ini' => PatrolSchedule::whereDate('patrol_date', now()->toDateString())->count(),
    ];

    $recentIncidents = Incident::orderBy('incident_date', 'desc')->take(3)->get();
    
    return view('welcome', compact('stats', 'recentIncidents'));
});

// ==========================================
// GUEST AUTHENTICATION
// ==========================================
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// ==========================================
// AUTHENTICATED SHARED ROUTES
// ==========================================
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Notifications
    Route::get('/notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
    
    // Simulation Role Switcher
    Route::get('/simulasi/switch/{role}', [AuthController::class, 'switchRole'])->name('simulasi.switch');

    // Real-time Updates API
    Route::get('/reports/realtime-updates', [WargaController::class, 'realtimeUpdates'])->name('reports.realtime_updates');
});

// ==========================================
// 1. CITIZEN (WARGA) PORTAL
// ==========================================
Route::middleware(['auth', 'role:warga'])->prefix('warga')->name('warga.')->group(function () {
    Route::get('/dashboard', [WargaController::class, 'dashboard'])->name('dashboard');
    Route::get('/laporan/buat', [WargaController::class, 'createReport'])->name('reports.create');
    Route::post('/laporan', [WargaController::class, 'storeReport'])->name('reports.store');
    Route::get('/laporan/{report}', [WargaController::class, 'showReport'])->name('reports.show');
    Route::post('/darurat', [WargaController::class, 'triggerEmergency'])->name('emergency')->middleware('throttle:3,1');

    // Patroli & Ronda (Warga yang ditugaskan)
    Route::prefix('ronda')->name('ronda.')->group(function () {
        Route::get('/dashboard', [SatpamController::class, 'dashboard'])->name('dashboard');
        Route::get('/jadwal', [SatpamController::class, 'schedules'])->name('schedules');
        Route::post('/jadwal/{schedule}/log', [SatpamController::class, 'storePatrolLog'])->name('schedules.log');
        Route::get('/kejadian', [SatpamController::class, 'incidents'])->name('incidents.index');
        Route::get('/kejadian/{incident}', [SatpamController::class, 'showIncident'])->name('incidents.show');
        Route::post('/kejadian/{incident}/tindak-lanjut', [SatpamController::class, 'storeHandlingRecord'])->name('incidents.handling');
    });
});

// ==========================================
// 2. VILLAGE OFFICER (PERANGKAT) PORTAL
// ==========================================
Route::middleware(['auth', 'role:perangkat'])->prefix('perangkat')->name('perangkat.')->group(function () {
    Route::get('/dashboard', [PerangkatController::class, 'dashboard'])->name('dashboard');
    
    // Reports
    Route::get('/laporan', [PerangkatController::class, 'reports'])->name('reports.index');
    Route::get('/laporan/{report}', [PerangkatController::class, 'showReport'])->name('reports.show');
    Route::post('/laporan/{report}/verifikasi', [PerangkatController::class, 'verifyReport'])->name('reports.verify');
    
    // Incidents
    Route::get('/kejadian', [PerangkatController::class, 'incidents'])->name('incidents.index');
    Route::get('/kejadian/{incident}', [PerangkatController::class, 'showIncident'])->name('incidents.show');
    Route::post('/kejadian/{incident}/tugaskan', [PerangkatController::class, 'assignIncident'])->name('incidents.assign');
    Route::post('/kejadian/{incident}/status', [PerangkatController::class, 'updateIncidentStatus'])->name('incidents.status');
    
    // Patrol Schedules
    Route::get('/jadwal', [PerangkatController::class, 'schedules'])->name('schedules');
    Route::post('/jadwal', [PerangkatController::class, 'storeSchedule'])->name('schedules.store');
    Route::post('/jadwal/{schedule}/update', [PerangkatController::class, 'updateSchedule'])->name('schedules.update');
    Route::post('/jadwal/{schedule}/delete', [PerangkatController::class, 'deleteSchedule'])->name('schedules.delete');
    
    // User Management
    Route::get('/pengguna', [PerangkatController::class, 'users'])->name('users');
    Route::post('/pengguna', [PerangkatController::class, 'storeUser'])->name('users.store');
    Route::post('/pengguna/{user}/update', [PerangkatController::class, 'updateUser'])->name('users.update');
    Route::post('/pengguna/{user}/delete', [PerangkatController::class, 'deleteUser'])->name('users.delete');
});

// ==========================================
// 4. VILLAGE HEAD (KEPALA DESA) PORTAL
// ==========================================
Route::middleware(['auth', 'role:kades'])->prefix('kades')->name('kades.')->group(function () {
    Route::get('/dashboard', [KadesController::class, 'dashboard'])->name('dashboard');
    Route::get('/laporan', [KadesController::class, 'reports'])->name('reports');
    Route::get('/rekap', [KadesController::class, 'rekap'])->name('rekap');
    Route::get('/tren', [KadesController::class, 'tren'])->name('tren');
});
