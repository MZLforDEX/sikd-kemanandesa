<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Models\Report;
use App\Models\Incident;
use App\Models\PatrolSchedule;
use App\Models\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatrolDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected $wargaRole;
    protected $perangkatRole;
    protected $citizen;
    protected $officer;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles for testing
        $this->wargaRole = Role::create(['name' => 'warga', 'display_name' => 'Warga']);
        $this->perangkatRole = Role::create(['name' => 'perangkat', 'display_name' => 'Perangkat Desa']);
        Role::create(['name' => 'kades', 'display_name' => 'Kepala Desa']);

        $this->citizen = User::factory()->create([
            'role_id' => $this->wargaRole->id,
            'name' => 'Budi Santoso',
            'email' => 'budi@desa.id',
        ]);

        $this->officer = User::factory()->create([
            'role_id' => $this->perangkatRole->id,
            'email' => 'perangkat@desa.id',
        ]);
    }

    /**
     * Test citizen dashboard when they have no patrol schedules.
     */
    public function test_dashboard_with_no_schedules(): void
    {
        $response = $this->actingAs($this->citizen)->get('/warga/dashboard');

        $response->assertStatus(200);
        $response->assertDontSee('Tugas Ronda Hari Ini');
        $response->assertDontSee('Jadwal Ronda Mendatang');
        $response->assertDontSee('Laporan Warga Hari Ini');
    }

    /**
     * Test citizen dashboard when they have an upcoming patrol schedule.
     */
    public function test_dashboard_shows_upcoming_schedule(): void
    {
        // Schedule for tomorrow
        $tomorrow = now()->addDay()->toDateString();
        PatrolSchedule::create([
            'user_id' => $this->citizen->id,
            'patrol_date' => $tomorrow,
            'shift' => 'malam',
            'start_time' => '22:00:00',
            'end_time' => '06:00:00',
            'area' => 'RT 02 Dusun Krajan',
            'status' => 'scheduled',
        ]);

        $response = $this->actingAs($this->citizen)->get('/warga/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Jadwal Ronda Mendatang');
        $response->assertSee('RT 02 Dusun Krajan');
        $response->assertSee('Shift Malam');
        $response->assertDontSee('Tugas Ronda Hari Ini');
    }

    /**
     * Test citizen dashboard when they are scheduled for patrol today.
     */
    public function test_dashboard_shows_active_duty_today_and_citizen_reports(): void
    {
        // Schedule for today
        $today = now()->toDateString();
        $schedule = PatrolSchedule::create([
            'user_id' => $this->citizen->id,
            'patrol_date' => $today,
            'shift' => 'malam',
            'start_time' => '22:00:00',
            'end_time' => '06:00:00',
            'area' => 'Pos Ronda 1',
            'status' => 'scheduled',
        ]);

        // Create reports: one by another citizen today, one from yesterday
        $otherCitizen = User::factory()->create(['role_id' => $this->wargaRole->id]);
        
        $reportToday = Report::create([
            'user_id' => $otherCitizen->id,
            'title' => 'Maling Mangga RT 01',
            'description' => 'Ada orang mencurigakan memanjat pohon mangga.',
            'location' => 'Depan Masjid',
            'status' => 'baru',
            'reported_at' => now(),
        ]);

        $reportYesterday = Report::create([
            'user_id' => $otherCitizen->id,
            'title' => 'Ban Bocor',
            'description' => 'Ban bocor ditinggal pemilik.',
            'location' => 'Pinggir jalan',
            'status' => 'baru',
            'reported_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($this->citizen)->get('/warga/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Tugas Ronda Hari Ini');
        $response->assertSee('Pos Ronda 1');
        $response->assertSee('Laporan Warga Hari Ini');
        
        // Active patrol user should see today's report from the other citizen
        $response->assertSee('Maling Mangga RT 01');
        
        // Active patrol user should NOT see yesterday's report from other citizen in the today list
        $response->assertDontSee('Ban Bocor');

        // Check if database notification "Jadwal Ronda Hari Ini" was created automatically
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->citizen->id,
            'title' => 'Jadwal Ronda Hari Ini',
        ]);
    }

    /**
     * Test that schedule update triggers a notification.
     */
    public function test_schedule_update_triggers_notification(): void
    {
        // Create initial patrol schedule
        $schedule = PatrolSchedule::create([
            'user_id' => $this->citizen->id,
            'patrol_date' => now()->addDays(2)->toDateString(),
            'shift' => 'malam',
            'start_time' => '22:00:00',
            'end_time' => '06:00:00',
            'area' => 'RT 01',
            'status' => 'scheduled',
        ]);

        // Clear initial notifications if any
        Notification::where('user_id', $this->citizen->id)->delete();

        // Update the schedule via Perangkat
        $response = $this->actingAs($this->officer)->post('/perangkat/jadwal/' . $schedule->id . '/update', [
            'user_id' => $this->citizen->id,
            'shift' => 'siang',
            'start_time' => '13:00',
            'end_time' => '17:00',
            'patrol_date' => now()->addDays(2)->toDateString(),
            'area' => 'RT 02 Wilayah Baru',
            'status' => 'scheduled',
        ]);

        $response->assertRedirect('/perangkat/jadwal');
        
        // Assert notification exists
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->citizen->id,
            'title' => 'Pembaruan Jadwal Ronda',
        ]);
    }

    /**
     * Test that active patrol citizens can view reports from today even if not owned.
     */
    public function test_active_patrol_citizen_can_view_today_reports(): void
    {
        $today = now()->toDateString();
        
        // 1. Un-patrolled citizen cannot view other's report
        $otherCitizen = User::factory()->create(['role_id' => $this->wargaRole->id]);
        $report = Report::create([
            'user_id' => $otherCitizen->id,
            'title' => 'Laporan Rahasia',
            'description' => 'Hanya untuk yang berkepentingan.',
            'location' => 'RT 05',
            'status' => 'baru',
            'reported_at' => now(),
        ]);

        $response = $this->actingAs($this->citizen)->get('/warga/laporan/' . $report->id);
        $response->assertStatus(403);

        // 2. Scheduled citizen for today CAN view other's report from today
        PatrolSchedule::create([
            'user_id' => $this->citizen->id,
            'patrol_date' => $today,
            'shift' => 'malam',
            'start_time' => '22:00:00',
            'end_time' => '06:00:00',
            'area' => 'RT 05',
            'status' => 'scheduled',
        ]);

        $response = $this->actingAs($this->citizen)->get('/warga/laporan/' . $report->id);
        $response->assertStatus(200);
        $response->assertSee('Laporan Rahasia');
    }

    /**
     * Test that active patrol citizens cannot view reports from other days.
     */
    public function test_active_patrol_citizen_cannot_view_past_reports(): void
    {
        $today = now()->toDateString();
        
        PatrolSchedule::create([
            'user_id' => $this->citizen->id,
            'patrol_date' => $today,
            'shift' => 'malam',
            'start_time' => '22:00:00',
            'end_time' => '06:00:00',
            'area' => 'RT 05',
            'status' => 'scheduled',
        ]);

        $otherCitizen = User::factory()->create(['role_id' => $this->wargaRole->id]);
        $oldReport = Report::create([
            'user_id' => $otherCitizen->id,
            'title' => 'Kejadian Kemarin',
            'description' => 'Maling tertangkap kemarin.',
            'location' => 'RT 05',
            'status' => 'baru',
            'reported_at' => now()->subDays(2),
        ]);

        $response = $this->actingAs($this->citizen)->get('/warga/laporan/' . $oldReport->id);
        $response->assertStatus(403);
    }

    /**
     * Test that real-time updates API sends today's reports to active patrol warga.
     */
    public function test_realtime_updates_contains_today_reports_on_duty(): void
    {
        $today = now()->toDateString();
        
        PatrolSchedule::create([
            'user_id' => $this->citizen->id,
            'patrol_date' => $today,
            'shift' => 'malam',
            'start_time' => '22:00:00',
            'end_time' => '06:00:00',
            'area' => 'RT 05',
            'status' => 'scheduled',
        ]);

        $otherCitizen = User::factory()->create(['role_id' => $this->wargaRole->id]);
        $report = Report::create([
            'user_id' => $otherCitizen->id,
            'title' => 'Kebakaran Lahan',
            'description' => 'Asap tebal di lahan kosong.',
            'location' => 'RT 05',
            'status' => 'baru',
            'reported_at' => now(),
        ]);

        $response = $this->actingAs($this->citizen)->get(route('reports.realtime_updates', ['last_id' => 0]));
        $response->assertStatus(200);
        
        $data = $response->json();
        $this->assertNotEmpty($data['reports']);
        
        $titles = collect($data['reports'])->pluck('title');
        $this->assertTrue($titles->contains('Kebakaran Lahan'));
    }

    /**
     * Test that real-time updates API sends all reports to regular warga not on duty.
     */
    public function test_realtime_updates_contains_all_reports_for_regular_warga(): void
    {
        $otherCitizen = User::factory()->create(['role_id' => $this->wargaRole->id]);
        $report = Report::create([
            'user_id' => $otherCitizen->id,
            'title' => 'Maling Sepeda',
            'description' => 'Sepeda hilang di teras.',
            'location' => 'RT 02',
            'status' => 'baru',
            'reported_at' => now(),
        ]);

        $response = $this->actingAs($this->citizen)->get(route('reports.realtime_updates', ['last_id' => 0]));
        $response->assertStatus(200);
        
        $data = $response->json();
        $this->assertNotEmpty($data['reports']);
        
        $titles = collect($data['reports'])->pluck('title');
        $this->assertTrue($titles->contains('Maling Sepeda'));
    }

    /**
     * Test that active patrol citizens can process/verify a new report directly.
     */
    public function test_active_patrol_citizen_can_process_report_directly(): void
    {
        $today = now()->toDateString();
        
        PatrolSchedule::create([
            'user_id' => $this->citizen->id,
            'patrol_date' => $today,
            'shift' => 'malam',
            'start_time' => '22:00:00',
            'end_time' => '06:00:00',
            'area' => 'RT 05',
            'status' => 'scheduled',
        ]);

        $otherCitizen = User::factory()->create(['role_id' => $this->wargaRole->id]);
        $report = Report::create([
            'user_id' => $otherCitizen->id,
            'title' => '🚨 Maling Motor',
            'description' => 'Ada pencuri motor tertangkap basah.',
            'location' => 'RT 05',
            'status' => 'baru',
            'reported_at' => now(),
        ]);

        // Assert report has no incident initially
        $this->assertNull($report->incident);

        // Post to process route
        $response = $this->actingAs($this->citizen)->post(route('warga.reports.proses', $report->id));
        $response->assertRedirect();
        
        // Assert incident was created and status updated to diproses
        $report = $report->fresh();
        $this->assertEquals('diproses', $report->status);
        $this->assertNotNull($report->incident);
        $this->assertEquals('diproses', $report->incident->status);

        // Assert handling record was created for citizen
        $this->assertDatabaseHas('handling_records', [
            'incident_id' => $report->incident->id,
            'handler_id' => $this->citizen->id,
            'status_after' => 'diproses',
        ]);
    }
}
