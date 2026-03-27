<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Создаём админа
        User::firstOrCreate(
            ['email' => 'admin@greenmetric.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('Admin@2026'),
                'is_admin' => true,
            ]
        );

        // Создаём тестовых пользователей
        $users = [
            ['email' => 'user1@greenmetric.com', 'name' => 'Test User 1', 'is_admin' => false],
            ['email' => 'user2@greenmetric.com', 'name' => 'Test User 2', 'is_admin' => false],
            ['email' => 'manager@greenmetric.com', 'name' => 'Manager', 'is_admin' => false],
        ];

        foreach ($users as $userData) {
            User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make('User@2026'),
                    'is_admin' => $userData['is_admin'],
                ]
            );
        }
    }
}
