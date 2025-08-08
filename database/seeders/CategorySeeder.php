<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();
        
        if (!$user) {
            return;
        }

        $categories = [
            [
                'name' => 'Work',
                'description' => 'Professional tasks and projects',
                'color' => '#3B82F6', // Blue
            ],
            [
                'name' => 'Personal',
                'description' => 'Personal tasks and activities',
                'color' => '#22C55E', // Green
            ],
            [
                'name' => 'Shopping',
                'description' => 'Shopping lists and purchases',
                'color' => '#F97316', // Orange
            ],
            [
                'name' => 'Health',
                'description' => 'Health and fitness related tasks',
                'color' => '#EF4444', // Red
            ],
            [
                'name' => 'Learning',
                'description' => 'Educational and skill development tasks',
                'color' => '#8B5CF6', // Purple
            ],
            [
                'name' => 'Home',
                'description' => 'Household chores and maintenance',
                'color' => '#06B6D4', // Cyan
            ],
        ];

        foreach ($categories as $categoryData) {
            // Only create if category doesn't already exist for this user
            $existingCategory = Category::where('user_id', $user->id)
                ->where('name', $categoryData['name'])
                ->first();

            if (!$existingCategory) {
                Category::create([
                    'user_id' => $user->id,
                    'name' => $categoryData['name'],
                    'description' => $categoryData['description'],
                    'color' => $categoryData['color'],
                ]);
            }
        }
    }
}