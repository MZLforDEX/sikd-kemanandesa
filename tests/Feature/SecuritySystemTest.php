<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Models\Report;
use App\Models\Incident;
use App\Models\PatrolSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecuritySystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles for testing
        Role::create(['name' => 'warga', 'display_name' => 'Warga']);
        Role::create(['name' => 'perangkat', 'display_name' => 'Perangkat Desa']);
        Role::create(['name' => 'kades', 'display_name' => 'Kepala Desa']);
    }

    /**
     * Test the entire business workflow of the village security information system.
     */
    public function test_complete_security_system_workflow(): void
    {
        // 1. REGISTER: Citizen registers a new account
        $registerData = [
            'name' => 'Budi Santoso',
            'email' => 'budi@desa.id',
            'phone' => '081234567890',
            'address' => 'RT 01 / RW 01',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/register', $registerData);
        $response->assertRedirect('/warga/dashboard');
        
        $this->assertDatabaseHas('users', [
            'email' => 'budi@desa.id',
            'name' => 'Budi Santoso',
        ]);

        $citizen = User::where('email', 'budi@desa.id')->first();
        $this->assertTrue($citizen->hasRole('warga'));

        // Create guard and patrol schedule for today so they are on duty
        $guard = User::factory()->create([
            'role_id' => Role::where('name', 'warga')->first()->id,
            'email' => 'satpam@desa.id',
        ]);

        $schedule = PatrolSchedule::create([
            'user_id' => $guard->id,
            'patrol_date' => now()->toDateString(),
            'shift' => 'malam',
            'start_time' => '22:00:00',
            'end_time' => '06:00:00',
            'area' => 'Dusun Krajan',
            'status' => 'scheduled',
        ]);

        // 2. REPORT: Citizen creates a new security report
        $reportData = [
            'title' => 'Pencurian Ayam di RT 01',
            'description' => 'Telah terjadi kehilangan ayam milik pak RT pada jam 2 malam.',
            'location' => 'Kandang Ayam RT 01',
        ];

        $response = $this->actingAs($citizen)->post('/warga/laporan', $reportData);
        $response->assertRedirect('/warga/dashboard');

        // Verify it is automatically verified and assigned to the active guard on duty
        $this->assertDatabaseHas('reports', [
            'title' => 'Pencurian Ayam di RT 01',
            'user_id' => $citizen->id,
            'status' => 'diproses',
        ]);

        $report = Report::first();

        $this->assertDatabaseHas('incidents', [
            'report_id' => $report->id,
            'status' => 'diproses',
        ]);

        $incident = Incident::first();

        $this->assertDatabaseHas('handling_records', [
            'incident_id' => $incident->id,
            'handler_id' => $guard->id,
            'status_after' => 'diproses',
        ]);

        // 3. FIELD HANDLING: Warga Ronda views task and logs follow-up actions
        $response = $this->actingAs($guard)->get('/warga/ronda/kejadian/' . $incident->id);
        $response->assertStatus(200);

        $handlingData = [
            'action_taken' => 'Mendatangi kandang ayam RT 01 dan mengamankan rekaman CCTV pos ronda.',
            'result' => 'Terduga pelaku teridentifikasi dalam rekaman.',
            'status_after' => 'ditangani',
        ];

        $response = $this->actingAs($guard)->post('/warga/ronda/kejadian/' . $incident->id . '/tindak-lanjut', $handlingData);
        $response->assertRedirect('/warga/ronda/kejadian/' . $incident->id);

        $this->assertDatabaseHas('handling_records', [
            'incident_id' => $incident->id,
            'handler_id' => $guard->id,
            'status_after' => 'ditangani',
            'result' => 'Terduga pelaku teridentifikasi dalam rekaman.',
        ]);

        $this->assertEquals('ditangani', $report->fresh()->status);
        $this->assertEquals('ditangani', $incident->fresh()->status);

        // 4. PATROL LOG: Ronda views schedules and submits a checkpoint log
        $logData = [
            'location_checked' => 'Gapura RT 01',
            'condition' => 'aman',
            'notes' => 'Patroli keliling aman, tidak ada tanda-tanda mencurigakan.',
        ];

        $response = $this->actingAs($guard)->post('/warga/ronda/jadwal/' . $schedule->id . '/log', $logData);
        $response->assertRedirect('/warga/ronda/jadwal');

        $this->assertDatabaseHas('patrol_logs', [
            'patrol_schedule_id' => $schedule->id,
            'user_id' => $guard->id,
            'location_checked' => 'Gapura RT 01',
            'condition' => 'aman',
        ]);

        $this->assertEquals('completed', $schedule->fresh()->status);

        // 7. MONITOR & REKAP: Kades logs in and views dashboard/rekapitulasi
        $kadesRole = Role::where('name', 'kades')->first();
        $kades = User::factory()->create([
            'role_id' => $kadesRole->id,
            'email' => 'kades@desa.id',
        ]);

        $response = $this->actingAs($kades)->get('/kades/dashboard');
        $response->assertStatus(200);

        $response = $this->actingAs($kades)->get('/kades/rekap?status=ditangani');
        $response->assertStatus(200);
        $response->assertSee('Pencurian Ayam di RT 01');

        // 8. SIMULATION: Test switching roles
        $wargaUser = User::factory()->create([
            'role_id' => Role::where('name', 'warga')->first()->id,
            'email' => 'warga@desa.id',
        ]);

        $response = $this->actingAs($kades)->get('/simulasi/switch/warga');
        $response->assertRedirect('/warga/dashboard');
        $this->assertTrue(auth()->user()->hasRole('warga'));
    }

    public function test_verified_report_cannot_be_verified_again(): void
    {
        $wargaRole = Role::where('name', 'warga')->first();
        $officerRole = Role::where('name', 'perangkat')->first();

        $citizen = User::factory()->create(['role_id' => $wargaRole->id]);
        $officer = User::factory()->create(['role_id' => $officerRole->id]);

        $report = Report::create([
            'user_id' => $citizen->id,
            'title' => 'Laporan Uji',
            'description' => 'Deskripsi uji',
            'location' => 'RT 01',
            'status' => 'diverifikasi',
            'reported_at' => now(),
        ]);

        Incident::create([
            'report_id' => $report->id,
            'title' => $report->title,
            'description' => $report->description,
            'category' => 'pencurian',
            'location' => $report->location,
            'incident_date' => $report->reported_at,
            'severity' => 'sedang',
            'status' => 'diverifikasi',
        ]);

        $response = $this->actingAs($officer)->post('/perangkat/laporan/' . $report->id . '/verifikasi', [
            'action' => 'verify',
            'category' => 'pencurian',
            'severity' => 'sedang',
        ]);

        $response->assertRedirect('/perangkat/laporan/' . $report->id);
        $response->assertSessionHas('error');
        $this->assertEquals(1, Incident::where('report_id', $report->id)->count());
    }

    public function test_emergency_requires_gps_coordinates(): void
    {
        $citizen = User::factory()->create([
            'role_id' => Role::where('name', 'warga')->first()->id,
        ]);

        $response = $this->actingAs($citizen)->post('/warga/darurat', []);

        $response->assertSessionHasErrors(['latitude', 'longitude']);
        $this->assertEquals(0, Report::count());
    }

    public function test_emergency_uses_device_gps_coordinates(): void
    {
        $citizen = User::factory()->create([
            'role_id' => Role::where('name', 'warga')->first()->id,
            'address' => 'RT 01 / RW 01',
        ]);

        $response = $this->actingAs($citizen)->post('/warga/darurat', [
            'latitude' => -3.94712345,
            'longitude' => 121.35198765,
            'accuracy' => 12,
        ]);

        $response->assertRedirect('/warga/dashboard');
        $response->assertSessionHas('emergency_triggered');

        $this->assertDatabaseHas('reports', [
            'user_id' => $citizen->id,
            'latitude' => -3.94712345,
            'longitude' => 121.35198765,
            'status' => 'diverifikasi',
        ]);

        $report = Report::first();
        $this->assertStringContainsString('GPS Perangkat', $report->location);
        $this->assertStringContainsString('-3.94712345', $report->description);
    }

    public function test_incident_cannot_be_assigned_to_non_warga_user(): void
    {
        $wargaRole = Role::where('name', 'warga')->first();
        $officerRole = Role::where('name', 'perangkat')->first();
        $kadesRole = Role::where('name', 'kades')->first();

        $officer = User::factory()->create(['role_id' => $officerRole->id]);
        $kades = User::factory()->create(['role_id' => $kadesRole->id]);

        $report = Report::create([
            'user_id' => User::factory()->create(['role_id' => $wargaRole->id])->id,
            'title' => 'Laporan Uji',
            'description' => 'Deskripsi uji',
            'location' => 'RT 01',
            'status' => 'diverifikasi',
            'reported_at' => now(),
        ]);

        $incident = Incident::create([
            'report_id' => $report->id,
            'title' => $report->title,
            'description' => $report->description,
            'category' => 'pencurian',
            'location' => $report->location,
            'incident_date' => $report->reported_at,
            'severity' => 'sedang',
            'status' => 'diverifikasi',
        ]);

        $response = $this->actingAs($officer)->post('/perangkat/kejadian/' . $incident->id . '/tugaskan', [
            'handler_id' => $kades->id,
            'instruction' => 'Tugas uji',
        ]);

        $response->assertSessionHasErrors('handler_id');
        $this->assertDatabaseMissing('handling_records', [
            'incident_id' => $incident->id,
            'handler_id' => $kades->id,
        ]);
    }

    public function test_patrol_warga_receives_correct_notification_link_and_can_access_emergency_report(): void
    {
        $wargaRole = Role::where('name', 'warga')->first();
        $officerRole = Role::where('name', 'perangkat')->first();
        $kadesRole = Role::where('name', 'kades')->first();

        $citizen = User::factory()->create(['role_id' => $wargaRole->id]);
        $guard = User::factory()->create(['role_id' => $wargaRole->id]);
        $officer = User::factory()->create(['role_id' => $officerRole->id]);
        $kades = User::factory()->create(['role_id' => $kadesRole->id]);

        // Guard has active patrol shift today
        PatrolSchedule::create([
            'user_id' => $guard->id,
            'shift' => 'malam',
            'start_time' => '22:00:00',
            'end_time' => '06:00:00',
            'patrol_date' => now()->toDateString(),
            'area' => 'Dusun Krajan',
            'status' => 'scheduled',
        ]);

        // Citizen triggers emergency
        $response = $this->actingAs($citizen)->post('/warga/darurat', [
            'latitude' => -3.94712,
            'longitude' => 121.35198,
            'accuracy' => 10,
        ]);

        $report = Report::first();
        $incident = Incident::first();

        // Verify guard's notification links to warga.ronda.incidents.show
        $this->assertDatabaseHas('notifications', [
            'user_id' => $guard->id,
            'link' => route('warga.ronda.incidents.show', $incident->id),
        ]);

        // Verify officer's notification links to perangkat.reports.show
        $this->assertDatabaseHas('notifications', [
            'user_id' => $officer->id,
            'link' => route('perangkat.reports.show', $report->id),
        ]);

        // Verify kades's notification links to kades.reports
        $this->assertDatabaseHas('notifications', [
            'user_id' => $kades->id,
            'link' => route('kades.reports'),
        ]);

        // Verify guard can view emergency report details
        $response = $this->actingAs($guard)->get('/warga/laporan/' . $report->id);
        $response->assertStatus(200);
    }

    public function test_cannot_create_patrol_schedule_for_non_warga_user(): void
    {
        $officerRole = Role::where('name', 'perangkat')->first();
        $officer = User::factory()->create(['role_id' => $officerRole->id]);
        $nonWarga = User::factory()->create(['role_id' => $officerRole->id]); // perangkat, not warga

        $scheduleData = [
            'user_ids' => [$nonWarga->id],
            'shift' => 'malam',
            'start_time' => '22:00',
            'end_time' => '06:00',
            'patrol_date' => now()->addDay()->toDateString(),
            'area' => 'Dusun Krajan',
            'notes' => 'Catatan uji',
        ];

        $response = $this->actingAs($officer)->post('/perangkat/jadwal', $scheduleData);
        $response->assertSessionHasErrors('user_ids.0');
        $this->assertEquals(0, PatrolSchedule::count());
    }

    public function test_can_create_patrol_schedule_for_multiple_warga_users(): void
    {
        $wargaRole = Role::where('name', 'warga')->first();
        $officerRole = Role::where('name', 'perangkat')->first();

        $officer = User::factory()->create(['role_id' => $officerRole->id]);
        $citizen1 = User::factory()->create(['role_id' => $wargaRole->id]);
        $citizen2 = User::factory()->create(['role_id' => $wargaRole->id]);

        $scheduleData = [
            'user_ids' => [$citizen1->id, $citizen2->id],
            'shift' => 'malam',
            'start_time' => '22:00',
            'end_time' => '06:00',
            'patrol_date' => now()->addDay()->toDateString(),
            'area' => 'Dusun Krajan',
            'notes' => 'Tugas kelompok ronda malam',
        ];

        $response = $this->actingAs($officer)->post('/perangkat/jadwal', $scheduleData);
        $response->assertRedirect('/perangkat/jadwal');
        $response->assertSessionHasNoErrors();

        $this->assertEquals(2, PatrolSchedule::count());
        $this->assertDatabaseHas('patrol_schedules', [
            'user_id' => $citizen1->id,
            'area' => 'Dusun Krajan',
            'shift' => 'malam',
        ]);
        $this->assertDatabaseHas('patrol_schedules', [
            'user_id' => $citizen2->id,
            'area' => 'Dusun Krajan',
            'shift' => 'malam',
        ]);

        // Verify both citizens received database notifications
        $this->assertDatabaseHas('notifications', [
            'user_id' => $citizen1->id,
            'title' => 'Jadwal Ronda Baru',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $citizen2->id,
            'title' => 'Jadwal Ronda Baru',
        ]);
    }

    public function test_cannot_create_overlapping_patrol_schedules_for_same_user(): void
    {
        $wargaRole = Role::where('name', 'warga')->first();
        $officerRole = Role::where('name', 'perangkat')->first();

        $officer = User::factory()->create(['role_id' => $officerRole->id]);
        $citizen = User::factory()->create(['role_id' => $wargaRole->id]);

        // Create initial patrol schedule
        PatrolSchedule::create([
            'user_id' => $citizen->id,
            'shift' => 'malam',
            'start_time' => '22:00',
            'end_time' => '06:00',
            'patrol_date' => now()->addDay()->toDateString(),
            'area' => 'Dusun Krajan',
            'status' => 'scheduled',
        ]);

        // Try creating overlapping schedule on same day/time
        $scheduleData = [
            'user_ids' => [$citizen->id],
            'shift' => 'malam',
            'start_time' => '22:00',
            'end_time' => '06:00',
            'patrol_date' => now()->addDay()->toDateString(),
            'area' => 'Dusun Krajan 2',
            'notes' => 'Tabrakan',
        ];

        $response = $this->actingAs($officer)->post('/perangkat/jadwal', $scheduleData);
        $response->assertSessionHasErrors('user_ids');
        $this->assertEquals(1, PatrolSchedule::count());
    }
}
