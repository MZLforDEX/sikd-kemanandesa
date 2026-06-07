<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\Report;
use App\Models\Incident;
use App\Models\HandlingRecord;
use App\Models\PatrolSchedule;
use App\Models\PatrolLog;
use App\Models\Notification;
use App\Models\ActivityLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Roles
        $roles = [
            ['name' => 'warga', 'display_name' => 'Warga Desa'],
            ['name' => 'perangkat', 'display_name' => 'Perangkat Desa'],
            ['name' => 'kades', 'display_name' => 'Kepala Desa'],
        ];

        $roleModels = [];
        foreach ($roles as $role) {
            $roleModels[$role['name']] = Role::create($role);
        }

        // 2. Seed Users
        $usersData = [
            [
                'role_id' => $roleModels['warga']->id,
                'name' => 'amalaaa ',
                'email' => 'warga@desa.id',
                'password' => Hash::make('password'),
                'phone' => '081234567890',
                'address' => 'RT 01 / RW 01, Dusun Krajan',
            ],
            [
                'role_id' => $roleModels['warga']->id,
                'name' => 'putrii',
                'email' => 'warga2@desa.id',
                'password' => Hash::make('password'),
                'phone' => '081234567891',
                'address' => 'RT 02 / RW 01, Dusun Krajan',
            ],
            [
                'role_id' => $roleModels['perangkat']->id,
                'name' => 'anto keceee',
                'email' => 'perangkat@desa.id',
                'password' => Hash::make('password'),
                'phone' => '082134567890',
                'address' => 'RT 03 / RW 02, Dusun Mulyo',
            ],
            [
                'role_id' => $roleModels['kades']->id,
                'name' => 'sulfikar anjayyy',
                'email' => 'kades@desa.id',
                'password' => Hash::make('password'),
                'phone' => '083134567890',
                'address' => 'RT 01 / RW 02, Dusun Mulyo',
            ],
            [
                'role_id' => $roleModels['warga']->id,
                'name' => 'auliaa',
                'email' => 'satpam@desa.id',
                'password' => Hash::make('password'),
                'phone' => '084134567890',
                'address' => 'Pos Ronda Utama, RT 01 / RW 01',
            ],
            [
                'role_id' => $roleModels['warga']->id,
                'name' => 'siapaa??',
                'email' => 'satpam2@desa.id',
                'password' => Hash::make('password'),
                'phone' => '084134567891',
                'address' => 'Pos Ronda II, RT 04 / RW 02',
            ],
        ];

        $users = [];
        foreach ($usersData as $data) {
            $users[$data['email']] = User::create($data);
        }

        // 3. Seed Reports
        $reportsData = [
            [
                'user_id' => $users['warga@desa.id']->id,
                'title' => 'Pencurian Tabung Gas Melon',
                'description' => 'Ada pencurian tabung gas 3kg milik warung Bu Sri sekitar jam 2 dini hari. Pelaku terlihat memakai jaket hitam dan menggunakan motor matic.',
                'location' => 'Warung Bu Sri, RT 02 / RW 01',
                'latitude' => -3.94694400,
                'longitude' => 121.35102800,
                'status' => 'selesai',
                'reported_at' => Carbon::now()->subDays(5),
            ],
            [
                'user_id' => $users['warga2@desa.id']->id,
                'title' => 'Kebakaran Lahan Tebu',
                'description' => 'Terjadi kebakaran kecil di lahan tebu ujung barat desa. Api merembet cepat karena angin kencang. Butuh penanganan darurat!',
                'location' => 'Lahan Tebu Barat, RT 05 / RW 02',
                'latitude' => -3.94824400,
                'longitude' => 121.35262800,
                'status' => 'ditangani',
                'reported_at' => Carbon::now()->subDays(2),
            ],
            [
                'user_id' => $users['warga@desa.id']->id,
                'title' => 'Kerumunan Pemuda Mabuk-Mabukan',
                'description' => 'Ada sekelompok pemuda dari luar desa berkumpul di jembatan pinggir sungai sambil minum minuman keras dan berisik sampai larut malam.',
                'location' => 'Jembatan Sungai Krajan, RT 03 / RW 01',
                'latitude' => -3.94574400,
                'longitude' => 121.34852800,
                'status' => 'diproses',
                'reported_at' => Carbon::now()->subDays(1),
            ],
            [
                'user_id' => $users['warga2@desa.id']->id,
                'title' => 'Kehilangan Sepeda Motor Honda Beat',
                'description' => 'Telah hilang motor Honda Beat Hitam Nopol N 4567 AA, diparkir di depan rumah dalam keadaan kunci stang. Kejadian sekitar jam 19.30 WIB.',
                'location' => 'Rumah Ibu putrii, RT 02 / RW 01',
                'latitude' => -3.94424400,
                'longitude' => 121.35162800,
                'status' => 'baru',
                'reported_at' => Carbon::now()->subHours(4),
            ],
            [
                'user_id' => $users['warga@desa.id']->id,
                'title' => 'Permintaan Bantuan Evakuasi Ular Kobra',
                'description' => 'Ditemukan ular kobra masuk ke dalam dapur rumah warga. Warga ketakutan dan tidak berani mengevakuasi sendiri.',
                'location' => 'Rumah Pak amalaaa , RT 01 / RW 01',
                'latitude' => -3.94924400,
                'longitude' => 121.35002800,
                'status' => 'baru',
                'reported_at' => Carbon::now()->subMinutes(30),
            ],
        ];

        $reports = [];
        foreach ($reportsData as $rData) {
            $reports[] = Report::create($rData);
        }

        // 4. Seed Incidents (from reports)
        // Incident 1: Pencurian Gas
        $incident1 = Incident::create([
            'report_id' => $reports[0]->id,
            'title' => 'Pencurian Gas di Warung Bu Sri',
            'description' => $reports[0]->description,
            'category' => 'pencurian',
            'location' => $reports[0]->location,
            'incident_date' => $reports[0]->reported_at,
            'severity' => 'sedang',
            'status' => 'selesai',
        ]);

        // Incident 2: Kebakaran Lahan
        $incident2 = Incident::create([
            'report_id' => $reports[1]->id,
            'title' => 'Kebakaran Lahan Tebu Barat',
            'description' => $reports[1]->description,
            'category' => 'kebakaran',
            'location' => $reports[1]->location,
            'incident_date' => $reports[1]->reported_at,
            'severity' => 'tinggi',
            'status' => 'ditangani',
        ]);

        // Incident 3: Keributan Pemuda
        $incident3 = Incident::create([
            'report_id' => $reports[2]->id,
            'title' => 'Kerumunan Miras di Jembatan',
            'description' => $reports[2]->description,
            'category' => 'keributan',
            'location' => $reports[2]->location,
            'incident_date' => $reports[2]->reported_at,
            'severity' => 'sedang',
            'status' => 'diproses',
        ]);

        // 5. Seed Handling Records
        // Handling for Incident 1 (Pencurian Gas) - Selesai
        HandlingRecord::create([
            'incident_id' => $incident1->id,
            'handler_id' => $users['satpam@desa.id']->id,
            'action_taken' => 'Mengecek CCTV pos ronda terdekat, berkoordinasi dengan warga setempat, dan mengamankan rekaman wajah pelaku.',
            'result' => 'Kasus diselesaikan secara kekeluargaan setelah pelaku teridentifikasi warga setempat dan mengembalikan barang curian.',
            'handled_at' => Carbon::now()->subDays(4),
            'status_after' => 'selesai',
        ]);

        // Handling for Incident 2 (Kebakaran) - Ditangani
        HandlingRecord::create([
            'incident_id' => $incident2->id,
            'handler_id' => $users['satpam2@desa.id']->id,
            'action_taken' => 'Memadamkan api bersama warga menggunakan pompa air sawah dan alat pemadam kebakaran darurat (APAR) pos desa.',
            'result' => 'Api berhasil dipadamkan sebelum merembet ke pemukiman warga. Kondisi saat ini dalam pendinginan.',
            'handled_at' => Carbon::now()->subDays(1),
            'status_after' => 'ditangani',
        ]);

        // Handling for Incident 3 (Kerumunan Miras) - Diproses
        HandlingRecord::create([
            'incident_id' => $incident3->id,
            'handler_id' => $users['satpam@desa.id']->id,
            'action_taken' => 'Petugas patroli mendatangi lokasi jembatan untuk membubarkan para pemuda.',
            'result' => 'Para pemuda dibubarkan dan diberikan teguran keras. Identitas mereka dicatat.',
            'handled_at' => Carbon::now()->subHours(12),
            'status_after' => 'diproses',
        ]);

        // 6. Seed Patrol Schedules
        $schedulesData = [
            [
                'user_id' => $users['satpam@desa.id']->id,
                'shift' => 'malam',
                'start_time' => '22:00:00',
                'end_time' => '06:00:00',
                'patrol_date' => Carbon::now()->subDays(1),
                'area' => 'Dusun Krajan (RT 01 s/d RT 05)',
                'notes' => 'Fokus patroli rumah kosong dan pos ronda perbatasan.',
                'status' => 'completed',
            ],
            [
                'user_id' => $users['satpam2@desa.id']->id,
                'shift' => 'siang',
                'start_time' => '14:00:00',
                'end_time' => '22:00:00',
                'patrol_date' => Carbon::now()->subDays(1),
                'area' => 'Dusun Mulyo (RT 01 s/d RT 04)',
                'notes' => 'Pantau aktivitas pertokoan warga.',
                'status' => 'completed',
            ],
            // Today Schedules
            [
                'user_id' => $users['satpam@desa.id']->id,
                'shift' => 'malam',
                'start_time' => '22:00:00',
                'end_time' => '06:00:00',
                'patrol_date' => Carbon::now(),
                'area' => 'Dusun Krajan & Pintu Masuk Desa',
                'notes' => 'Lakukan patroli keliling berkala setiap 2 jam.',
                'status' => 'scheduled',
            ],
            [
                'user_id' => $users['satpam2@desa.id']->id,
                'shift' => 'pagi',
                'start_time' => '06:00:00',
                'end_time' => '14:00:00',
                'patrol_date' => Carbon::now(),
                'area' => 'Sekolah Desa & Kantor Desa',
                'notes' => 'Pantau kelancaran lalu lintas pagi dan keamanan lingkungan sekolah.',
                'status' => 'scheduled',
            ],
        ];

        $schedules = [];
        foreach ($schedulesData as $sData) {
            $schedules[] = PatrolSchedule::create($sData);
        }

        // 7. Seed Patrol Logs
        PatrolLog::create([
            'patrol_schedule_id' => $schedules[0]->id, // malam kemarin
            'user_id' => $users['satpam@desa.id']->id,
            'logged_at' => Carbon::now()->subDays(1)->setTime(23, 30, 0),
            'location_checked' => 'Pintu Masuk Desa RT 01',
            'condition' => 'aman',
            'notes' => 'Situasi gerbang masuk kondusif. Warga ronda malam lengkap.',
        ]);

        PatrolLog::create([
            'patrol_schedule_id' => $schedules[0]->id,
            'user_id' => $users['satpam@desa.id']->id,
            'logged_at' => Carbon::now()->subDays(1)->setTime(2, 15, 0),
            'location_checked' => 'Pos Ronda RT 03',
            'condition' => 'mencurigakan',
            'notes' => 'Menemukan kerumunan pemuda mabuk-mabukan di jembatan. Telah dibubarkan.',
        ]);

        PatrolLog::create([
            'patrol_schedule_id' => $schedules[1]->id, // siang kemarin
            'user_id' => $users['satpam2@desa.id']->id,
            'logged_at' => Carbon::now()->subDays(1)->setTime(16, 0, 0),
            'location_checked' => 'Bank Sampah & Kantor Desa',
            'condition' => 'aman',
            'notes' => 'Aktivitas pelayanan desa berjalan normal.',
        ]);

        // 8. Seed Notifications
        Notification::create([
            'user_id' => $users['perangkat@desa.id']->id,
            'title' => 'Laporan Baru Masuk',
            'message' => 'Warga melaporkan pencurian sepeda motor Honda Beat.',
            'is_read' => false,
            'link' => route('perangkat.reports.index'),
        ]);

        Notification::create([
            'user_id' => $users['satpam@desa.id']->id,
            'title' => 'Tugas Penanganan Kejadian',
            'message' => 'Anda ditugaskan menangani kasus kerumunan pemuda mabuk.',
            'is_read' => true,
            'link' => route('perangkat.incidents.index'),
        ]);

        // 9. Seed Activity Logs
        ActivityLog::log('Mendaftar ke dalam sistem', $users['warga@desa.id']->id);
        ActivityLog::log('Membuat laporan: Pencurian Tabung Gas Melon', $users['warga@desa.id']->id);
        ActivityLog::log('Memverifikasi laporan pencurian', $users['perangkat@desa.id']->id);
        ActivityLog::log('Menambahkan penanganan kejadian pencurian', $users['satpam@desa.id']->id);
    }
}
