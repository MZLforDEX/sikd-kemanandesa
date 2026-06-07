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

        // 2. REPORT: Citizen creates a new security report
        $reportData = [
            'title' => 'Pencurian Ayam di RT 01',
            'description' => 'Telah terjadi kehilangan ayam milik pak RT pada jam 2 malam.',
            'location' => 'Kandang Ayam RT 01',
        ];

        $response = $this->actingAs($citizen)->post('/warga/laporan', $reportData);
        $response->assertRedirect('/warga/dashboard');

        $this->assertDatabaseHas('reports', [
            'title' => 'Pencurian Ayam di RT 01',
            'user_id' => $citizen->id,
            'status' => 'baru',
        ]);

        $report = Report::first();

        // 3. VERIFY: Village Officer logs in, views the report, and verifies it to an Incident
        $officerRole = Role::where('name', 'perangkat')->first();
        $officer = User::factory()->create([
            'role_id' => $officerRole->id,
            'email' => 'perangkat@desa.id',
        ]);

        $response = $this->actingAs($officer)->get('/perangkat/laporan/' . $report->id);
        $response->assertStatus(200);

        // Verify and convert to incident
        $verifyData = [
            'action' => 'verify',
            'notes' => 'Laporan valid, kejadian pencurian terkonfirmasi.',
            'category' => 'pencurian',
            'severity' => 'sedang',
        ];

        $response = $this->actingAs($officer)->post('/perangkat/laporan/' . $report->id . '/verifikasi', $verifyData);
        $response->assertRedirect('/perangkat/laporan/' . $report->id);

        $this->assertDatabaseHas('reports', [
            'id' => $report->id,
            'status' => 'diverifikasi',
        ]);

        $this->assertDatabaseHas('incidents', [
            'report_id' => $report->id,
            'category' => 'pencurian',
            'severity' => 'sedang',
            'status' => 'diverifikasi',
        ]);

        $incident = Incident::first();

        // 4. ASSIGN: Officer assigns a citizen to the incident ronda task
        $guard = User::factory()->create([
            'role_id' => Role::where('name', 'warga')->first()->id,
            'email' => 'satpam@desa.id',
        ]);

        $assignData = [
            'handler_id' => $guard->id,
            'instruction' => 'Silakan cek ke lokasi RT 01 dan koordinasikan dengan warga.',
        ];

        $response = $this->actingAs($officer)->post('/perangkat/kejadian/' . $incident->id . '/tugaskan', $assignData);
        $response->assertRedirect('/perangkat/kejadian/' . $incident->id);

        $this->assertDatabaseHas('handling_records', [
            'incident_id' => $incident->id,
            'handler_id' => $guard->id,
            'status_after' => 'diproses',
        ]);

        // Check if report status is updated to 'diproses'
        $this->assertEquals('diproses', $report->fresh()->status);
        $this->assertEquals('diproses', $incident->fresh()->status);

        // 5. FIELD HANDLING: Warga Ronda views task and logs follow-up actions
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

        // 6. PATROL LOG: Ronda views schedules and submits a checkpoint log
        $schedule = PatrolSchedule::create([
            'user_id' => $guard->id,
            'patrol_date' => now()->toDateString(),
            'shift' => 'malam',
            'start_time' => '22:00:00',
            'end_time' => '06:00:00',
            'area' => 'Dusun Krajan',
            'status' => 'scheduled',
        ]);

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
            'status' => 'baru',
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
}
