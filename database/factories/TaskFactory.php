<?php

namespace Database\Factories;

use App\Enums\Priority;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $completed = fake()->boolean(30); // 30% chance of being completed
        
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(rand(3, 8)),
            'description' => fake()->boolean(70) ? fake()->paragraph() : null,
            'completed' => $completed,
            'priority' => fake()->randomElement(Priority::cases()),
            'due_date' => fake()->boolean(60) ? fake()->dateTimeBetween('-1 week', '+2 weeks')->format('Y-m-d') : null,
            'completed_at' => $completed ? fake()->dateTimeBetween('-2 weeks', 'now') : null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'completed' => true,
            'completed_at' => fake()->dateTimeBetween('-2 weeks', 'now'),
        ]);
    }

    public function incomplete(): static
    {
        return $this->state(fn (array $attributes) => [
            'completed' => false,
            'completed_at' => null,
        ]);
    }

    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => Priority::HIGH,
        ]);
    }

    public function lowPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => Priority::LOW,
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'due_date' => fake()->dateTimeBetween('-1 week', '-1 day')->format('Y-m-d'),
            'completed' => false,
            'completed_at' => null,
        ]);
    }

    public function dueToday(): static
    {
        return $this->state(fn (array $attributes) => [
            'due_date' => today()->format('Y-m-d'),
        ]);
    }
}
