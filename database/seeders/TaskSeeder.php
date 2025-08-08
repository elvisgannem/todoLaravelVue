<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use App\Enums\Priority;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users to assign tasks to them
        $users = User::all();
        
        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please run UserSeeder first.');
            return;
        }

        // Create tasks for each user
        $users->each(function ($user) {
            // Create a mix of completed and incomplete tasks
            Task::factory()->count(3)->create([
                'user_id' => $user->id,
                'completed' => false,
            ]);

            Task::factory()->count(2)->completed()->create([
                'user_id' => $user->id,
            ]);

            // Create some high priority tasks
            Task::factory()->count(2)->highPriority()->create([
                'user_id' => $user->id,
            ]);

            // Create some overdue tasks
            Task::factory()->count(1)->overdue()->create([
                'user_id' => $user->id,
            ]);

            // Create tasks due today
            Task::factory()->count(1)->dueToday()->create([
                'user_id' => $user->id,
            ]);
        });

        $this->command->info('Tasks seeded successfully!');
    }
}