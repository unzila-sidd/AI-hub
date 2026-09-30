<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'products' => [
                ['name' => 'View Products', 'slug' => 'products.view'],
                ['name' => 'Add Products', 'slug' => 'products.add'],
                ['name' => 'Edit Products', 'slug' => 'products.edit'],
                ['name' => 'Delete Products', 'slug' => 'products.delete'],
            ],
            'sales' => [
                ['name' => 'Create Sales (POS)', 'slug' => 'sales.create'],
                ['name' => 'View Sales', 'slug' => 'sales.view'],
            ],
            'payments' => [
                ['name' => 'Add Payment', 'slug' => 'payments.add'],
                ['name' => 'Edit Payment', 'slug' => 'payments.edit'],
                ['name' => 'Delete Payment', 'slug' => 'payments.delete'],
                ['name' => 'View Payments', 'slug' => 'payments.view'],
            ],
            'reports' => [
                ['name' => 'View Reports', 'slug' => 'reports.view'],
            ],
            'roles' => [
                ['name' => 'Manage Roles & Permissions', 'slug' => 'roles.manage'],
            ],
            'users' => [
                ['name' => 'Manage Users', 'slug' => 'users.manage'],
            ],
        ];

        $created = collect();

        foreach ($permissions as $group => $items) {
            foreach ($items as $item) {
                $created->push(Permission::firstOrCreate(['slug' => $item['slug']], ['name' => $item['name'], 'group' => $group]));
            }
        }

        $all = $created->pluck('id')->all();

        $roles = [
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'Full access to everything.',
                'permissions' => $all,
            ],
            [
                'name' => 'Manager',
                'slug' => 'manager',
                'description' => 'Manages products, sales, payments and reports.',
                'permissions' => Permission::whereIn('slug', [
                    'products.view', 'products.add', 'products.edit', 'products.delete',
                    'sales.create', 'sales.view',
                    'payments.view', 'payments.add', 'payments.edit',
                    'reports.view',
                ])->pluck('id')->all(),
            ],
            [
                'name' => 'Cashier',
                'slug' => 'cashier',
                'description' => 'Runs the POS terminal and records payments.',
                'permissions' => Permission::whereIn('slug', [
                    'products.view',
                    'sales.create',
                    'payments.view', 'payments.add',
                ])->pluck('id')->all(),
            ],
        ];

        foreach ($roles as $roleData) {
            $role = Role::firstOrCreate(
                ['slug' => $roleData['slug']],
                $roleData
            );
            $role->permissions()->sync($roleData['permissions']);
        }

        User::whereNull('role_id')->first()?->update(['role_id' => Role::where('slug', 'admin')->value('id')]);
    }
}