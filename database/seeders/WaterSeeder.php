<?php

namespace Database\Seeders;

use App\Models\Water;
use App\Models\User;
use Illuminate\Database\Seeder;

class WaterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->error('No users found. Please create users first.');
            return;
        }

        foreach ($users as $user) {
            Water::factory()->count(5)->create(['user_id' => $user->id]);
        }

        $this->command->info('Water records seeded successfully!');
    }
}
