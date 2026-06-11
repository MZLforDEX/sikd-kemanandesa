<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Disable foreign keys to safely truncate all tables
        Schema::disableForeignKeyConstraints();

        DB::table('push_subscriptions')->truncate();
        DB::table('activity_logs')->truncate();
        DB::table('notifications')->truncate();
        DB::table('patrol_logs')->truncate();
        DB::table('patrol_schedules')->truncate();
        DB::table('handling_records')->truncate();
        DB::table('incidents')->truncate();
        DB::table('reports')->truncate();
        DB::table('users')->truncate();
        DB::table('roles')->truncate();

        Schema::enableForeignKeyConstraints();

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

        // 2. Seed Admin & Aparat Users
        User::create([
            'role_id' => $roleModels['perangkat']->id,
            'name' => 'Perangkat Desa (Admin)',
            'email' => 'perangkat@desa.id',
            'password' => Hash::make('password'),
            'phone' => '082134567890',
            'address' => 'Kantor Desa Awa',
        ]);

        User::create([
            'role_id' => $roleModels['kades']->id,
            'name' => 'Kepala Desa (Aparat)',
            'email' => 'kades@desa.id',
            'password' => Hash::make('password'),
            'phone' => '083134567890',
            'address' => 'Rumah Dinas Kades, Desa Awa',
        ]);

        // 3. Seed Warga Dummy Users
        $wargaNames = [
            'Ahmad Fauzi', 'Budi Santoso', 'Chandra Wijaya', 'Dedi Kurniawan', 'Eko Prasetyo',
            'Fajar Hidayat', 'Guntur Wibowo', 'Hendra Setiawan', 'Indra Lesmana', 'Joko Susilo',
            'Kartika Sari', 'Larasati Putri', 'Mega Utami', 'Novi Rahmawati', 'Oki Saputra',
            'Pratama Putra', 'Qori Amelia', 'Rian Hidayat', 'Siti Aminah', 'Taufik Hidayat',
            'Umar Syarif', 'Vina Panduwinata', 'Wahyu Hidayat', 'Xena Princess', 'Yayan Ruhian',
            'Zainal Abidin', 'Adi Wijaya', 'Bambang Pamungkas', 'Cahyo Utomo', 'Dewi Lestari'
        ];

        foreach ($wargaNames as $index => $name) {
            User::create([
                'role_id' => $roleModels['warga']->id,
                'name' => $name,
                'email' => 'warga' . ($index + 1) . '@desa.id',
                'password' => Hash::make('password'),
                'phone' => '0812' . str_pad($index + 1, 8, '0', STR_PAD_LEFT),
                'address' => 'RT 0' . (($index % 5) + 1) . ' / RW 0' . (($index % 2) + 1) . ', Dusun Desa Awa',
            ]);
        }
    }
}
