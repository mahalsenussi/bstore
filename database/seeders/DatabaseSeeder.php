<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            \Database\Seeders\BrandSeeder::class,
        ]);

        $admin = User::firstOrCreate(
            ['email' => 'admin@bstore.com'],
            [
                'name' => 'Admin',
                'password' => 'password',
            ]
        );
        $admin->assignRole('super-admin');
    }
}
