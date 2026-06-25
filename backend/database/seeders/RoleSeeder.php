<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\RolePermission;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    private array $roles = [
        [
            'name' => 'Admin',
            'slug' => 'admin',
            'description' => 'Full system access',
            'is_default' => false,
            'permissions' => ['*'],
        ],
        [
            'name' => 'Manager',
            'slug' => 'manager',
            'description' => 'Oversee operations, view reports',
            'is_default' => false,
            'permissions' => [
                'orders.view', 'orders.create', 'orders.edit', 'orders.void',
                'menu.view', 'menu.create', 'menu.edit',
                'inventory.view', 'inventory.create', 'inventory.edit',
                'reports.view',
                'users.view',
                'reservations.view', 'reservations.create', 'reservations.edit',
            ],
        ],
        [
            'name' => 'Waiter',
            'slug' => 'waiter',
            'description' => 'Take orders and serve customers',
            'is_default' => true,
            'permissions' => [
                'orders.view', 'orders.create', 'orders.edit',
                'menu.view',
                'tables.view',
                'reservations.view',
            ],
        ],
        [
            'name' => 'Cashier',
            'slug' => 'cashier',
            'description' => 'Process payments and manage orders',
            'is_default' => false,
            'permissions' => [
                'orders.view', 'orders.create', 'orders.edit',
                'orders.process-payment', 'orders.refund',
                'menu.view',
                'reports.view',
            ],
        ],
        [
            'name' => 'Kitchen',
            'slug' => 'kitchen',
            'description' => 'View and prepare orders',
            'is_default' => false,
            'permissions' => [
                'orders.view',
                'kitchen.view', 'kitchen.update-status',
            ],
        ],
    ];

    public function run(): void
    {
        foreach ($this->roles as $roleData) {
            $permissions = $roleData['permissions'];
            unset($roleData['permissions']);

            $role = Role::firstOrCreate(
                ['slug' => $roleData['slug']],
                $roleData
            );

            foreach ($permissions as $permission) {
                RolePermission::firstOrCreate([
                    'role_id' => $role->id,
                    'permission' => $permission,
                ]);
            }
        }
    }
}
