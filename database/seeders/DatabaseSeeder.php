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

    }
}
