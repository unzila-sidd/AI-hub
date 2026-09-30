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

        $roleId = \App\Models\Role::where('slug', 'admin')->value('id');

        $admin = User::where('email', 'admin@example.com')->first();

        if (!$admin) {
            $password = Str::random(16);

            $admin = User::create([
                'name' => 'Admin',
                'email' => 'admin@example.com',
                'password' => $password,
                'role_id' => $roleId,
            ]);

            if (app()->runningInConsole()) {
                $this->command?->info("Admin account created: admin@example.com / {$password}");
            }
        }

        if ($admin->role_id !== $roleId) {
            $admin->update(['role_id' => $roleId]);
        }
    }
}
