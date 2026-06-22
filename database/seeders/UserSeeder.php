<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@restaurant.com'],
            ['name' => 'Admin User', 'password' => Hash::make('password123'), 'role' => 'admin', 'is_active' => true]
        );

        User::firstOrCreate(
            ['email' => 'waiter1@restaurant.com'],
            ['name' => 'John Waiter', 'password' => Hash::make('password123'), 'role' => 'waiter', 'is_active' => true]
        );

        User::firstOrCreate(
            ['email' => 'waiter2@restaurant.com'],
            ['name' => 'Sarah Waiter', 'password' => Hash::make('password123'), 'role' => 'waiter', 'is_active' => true]
        );

        User::firstOrCreate(
            ['email' => 'cashier1@restaurant.com'],
            ['name' => 'Mike Cashier', 'password' => Hash::make('password123'), 'role' => 'cashier', 'is_active' => true]
        );

        User::firstOrCreate(
            ['email' => 'cashier2@restaurant.com'],
            ['name' => 'Emma Cashier', 'password' => Hash::make('password123'), 'role' => 'cashier', 'is_active' => true]
        );

        User::firstOrCreate(
            ['email' => 'kitchen1@restaurant.com'],
            ['name' => 'Chef Marco', 'password' => Hash::make('password123'), 'role' => 'kitchen', 'kitchen_id' => 1, 'is_active' => true]
        );

        User::firstOrCreate(
            ['email' => 'kitchen2@restaurant.com'],
            ['name' => 'Bartender Lisa', 'password' => Hash::make('password123'), 'role' => 'kitchen', 'kitchen_id' => 2, 'is_active' => true]
        );

        User::firstOrCreate(
            ['email' => 'kitchen3@restaurant.com'],
            ['name' => 'Pastry Chef Anna', 'password' => Hash::make('password123'), 'role' => 'kitchen', 'kitchen_id' => 3, 'is_active' => true]
        );
    }
}
