<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\UserCategoryAccess;
use Illuminate\Database\Seeder;

class UserAccessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Находим первого пользователя и делаем его админом
        $admin = User::first();
        if ($admin) {
            $admin->update(['is_admin' => true]);
            $this->command->info("Пользователь {$admin->email} назначен администратором");
        }

        // Создаём тестовых пользователей с доступом к разным категориям
        $categories = Category::all();
        
        // Пользователь с доступом к GD
        $userGD = User::firstOrCreate(
            ['email' => 'gd.user@test.com'],
            [
                'name' => 'GD User',
                'password' => bcrypt('password'),
                'is_admin' => false,
            ]
        );
        $this->assignCategories($userGD, ['GD']);
        
        // Пользователь с доступом к GD и TR
        $userGDTR = User::firstOrCreate(
            ['email' => 'gd.tr.user@test.com'],
            [
                'name' => 'GD TR User',
                'password' => bcrypt('password'),
                'is_admin' => false,
            ]
        );
        $this->assignCategories($userGDTR, ['GD', 'TR']);
        
        $this->command->info('Пользователи созданы. Пароль: password');
    }
    
    private function assignCategories(User $user, array $categoryCodes): void
    {
        $categories = Category::whereIn('code', $categoryCodes)->get();
        
        foreach ($categories as $category) {
            UserCategoryAccess::firstOrCreate([
                'user_id' => $user->id,
                'category_id' => $category->id,
            ]);
        }
        
        $this->command->info("Пользователю {$user->email} назначены категории: " . implode(', ', $categoryCodes));
    }
}
