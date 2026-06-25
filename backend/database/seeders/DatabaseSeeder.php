<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            SampleDataSeeder::class,
            SettingsSeeder::class,
        ]);

        $admin = \App\Models\Role::where('slug', 'admin')->first();
        if ($admin) {
            \App\Models\User::firstOrCreate(
                ['email' => 'admin@restaurantpos.com'],
                ['name' => 'Admin', 'password' => bcrypt('admin123'), 'role_id' => $admin->id, 'is_active' => true]
            );
        }
    }
}
