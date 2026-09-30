<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

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
            ProductSeeder::class,
        ]);

        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com']
        );

        if (!$admin->wasRecentlyCreated) {
            return;
        }

        $password = Str::random(16);

        $admin->update([
            'name' => 'Admin',
            'password' => $password,
            'role_id' => \App\Models\Role::where('slug', 'admin')->value('id'),
        ]);

        if (app()->runningInConsole()) {
            $this->command?->info("Admin account created: admin@example.com / {$password}");
        }
    }
}
