<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->randomElement([
            'Work', 'Personal', 'Shopping', 'Health', 'Finance', 
            'Travel', 'Education', 'Hobbies', 'Home', 'Family'
        ]);

        return [
            'name' => $name,
            'slug' => Str::slug($name) . '-' . $this->faker->unique()->randomNumber(3),
            'description' => $this->faker->optional()->sentence(),
            'color' => $this->faker->randomElement([
                '#EF4444', // Red
                '#F97316', // Orange  
                '#F59E0B', // Yellow
                '#10B981', // Green
                '#06B6D4', // Cyan
                '#3B82F6', // Blue
                '#8B5CF6', // Purple
                '#EC4899', // Pink
                '#6B7280', // Gray
            ]),
            'user_id' => User::factory(),
        ];
    }
}