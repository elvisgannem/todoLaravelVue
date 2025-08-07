<?php

use App\Enums\Priority;
use App\Models\Task;
use App\Models\User;

describe('Task Management', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
    });

    describe('Task Creation', function () {
        test('authenticated users can create tasks', function () {
            $taskData = [
                'title' => 'Test Task',
                'description' => 'Test Description',
                'priority' => Priority::HIGH->value,
                'due_date' => today()->addDays(3)->format('Y-m-d'),
            ];

            $response = $this->actingAs($this->user)
                ->post('/tasks', $taskData);

            $response->assertRedirect('/');
            $response->assertSessionHas('success', 'Task created successfully!');

            $this->assertDatabaseHas('tasks', [
                'user_id' => $this->user->id,
                'title' => 'Test Task',
                'description' => 'Test Description',
                'priority' => Priority::HIGH->value,
            ]);

            // Check date separately to handle different database formats
            $task = \App\Models\Task::where('user_id', $this->user->id)
                ->where('title', 'Test Task')
                ->first();
            
            expect($task->due_date->format('Y-m-d'))->toBe(today()->addDays(3)->format('Y-m-d'));
            expect($task->completed)->toBeFalse();
        });

        test('guests cannot create tasks', function () {
            $taskData = [
                'title' => 'Test Task',
                'priority' => Priority::LOW->value,
            ];

            $response = $this->post('/tasks', $taskData);

            $response->assertRedirect('/login');
            $this->assertDatabaseCount('tasks', 0);
        });

        test('task creation requires title', function () {
            $response = $this->actingAs($this->user)
                ->post('/tasks', [
                    'description' => 'Test Description',
                    'priority' => Priority::LOW->value,
                ]);

            $response->assertSessionHasErrors('title');
            $this->assertDatabaseCount('tasks', 0);
        });

        test('task creation requires valid priority', function () {
            $response = $this->actingAs($this->user)
                ->post('/tasks', [
                    'title' => 'Test Task',
                    'priority' => 999, // Invalid priority
                ]);

            $response->assertSessionHasErrors('priority');
            $this->assertDatabaseCount('tasks', 0);
        });

        test('task creation accepts past due dates', function () {
            $pastDate = today()->subDays(5)->format('Y-m-d');
            
            $response = $this->actingAs($this->user)
                ->post('/tasks', [
                    'title' => 'Overdue Task',
                    'priority' => Priority::HIGH->value,
                    'due_date' => $pastDate,
                ]);

            $response->assertRedirect('/');
            $this->assertDatabaseHas('tasks', [
                'user_id' => $this->user->id,
                'title' => 'Overdue Task',
            ]);

            // Check date separately to handle different database formats
            $task = \App\Models\Task::where('user_id', $this->user->id)
                ->where('title', 'Overdue Task')
                ->first();
            
            expect($task->due_date->format('Y-m-d'))->toBe($pastDate);
        });

        test('task creation works without optional fields', function () {
            $response = $this->actingAs($this->user)
                ->post('/tasks', [
                    'title' => 'Minimal Task',
                    'priority' => Priority::LOW->value,
                ]);

            $response->assertRedirect('/');
            $this->assertDatabaseHas('tasks', [
                'user_id' => $this->user->id,
                'title' => 'Minimal Task',
                'description' => null,
                'due_date' => null,
            ]);
        });
    });

    describe('Task Updates', function () {
        test('users can update their own tasks', function () {
            $task = Task::factory()->create([
                'user_id' => $this->user->id,
                'title' => 'Original Title',
                'priority' => Priority::LOW->value,
            ]);

            $updateData = [
                'title' => 'Updated Title',
                'description' => 'Updated Description',
                'priority' => Priority::HIGH->value,
                'due_date' => today()->addWeek()->format('Y-m-d'),
            ];

            $response = $this->actingAs($this->user)
                ->patch("/tasks/{$task->id}", $updateData);

            $response->assertRedirect('/');
            $response->assertSessionHas('success', 'Task updated successfully!');

            $this->assertDatabaseHas('tasks', [
                'id' => $task->id,
                'title' => 'Updated Title',
                'description' => 'Updated Description',
                'priority' => Priority::HIGH->value,
            ]);

            // Check date separately to handle different database formats
            $updatedTask = $task->fresh();
            expect($updatedTask->due_date->format('Y-m-d'))->toBe(today()->addWeek()->format('Y-m-d'));
        });

        test('users cannot update other users tasks', function () {
            $task = Task::factory()->create([
                'user_id' => $this->otherUser->id,
                'title' => 'Other User Task',
            ]);

            $response = $this->actingAs($this->user)
                ->patch("/tasks/{$task->id}", [
                    'title' => 'Hacked Title',
                    'priority' => Priority::HIGH->value,
                ]);

            $response->assertForbidden();
            
            $this->assertDatabaseHas('tasks', [
                'id' => $task->id,
                'title' => 'Other User Task', // Should remain unchanged
            ]);
        });

        test('guests cannot update tasks', function () {
            $task = Task::factory()->create([
                'user_id' => $this->user->id,
            ]);

            $response = $this->patch("/tasks/{$task->id}", [
                'title' => 'Updated Title',
                'priority' => Priority::HIGH->value,
            ]);

            $response->assertRedirect('/login');
        });
    });

    describe('Task Completion Toggle', function () {
        test('users can toggle completion status of their tasks', function () {
            $task = Task::factory()->create([
                'user_id' => $this->user->id,
                'completed' => false,
                'completed_at' => null,
            ]);

            $response = $this->actingAs($this->user)
                ->patch("/tasks/{$task->id}/toggle");

            $response->assertRedirect('/');
            $response->assertSessionHas('success', 'Task completed!');

            $task->refresh();
            $this->assertTrue($task->completed);
            $this->assertNotNull($task->completed_at);
        });

        test('users can toggle completed tasks back to incomplete', function () {
            $task = Task::factory()->completed()->create([
                'user_id' => $this->user->id,
            ]);

            $response = $this->actingAs($this->user)
                ->patch("/tasks/{$task->id}/toggle");

            $response->assertRedirect('/');
            $response->assertSessionHas('success', 'Task marked as incomplete!');

            $task->refresh();
            $this->assertFalse($task->completed);
            $this->assertNull($task->completed_at);
        });

        test('users cannot toggle other users tasks', function () {
            $task = Task::factory()->create([
                'user_id' => $this->otherUser->id,
                'completed' => false,
            ]);

            $response = $this->actingAs($this->user)
                ->patch("/tasks/{$task->id}/toggle");

            $response->assertForbidden();

            $task->refresh();
            $this->assertFalse($task->completed);
        });
    });

    describe('Task Deletion', function () {
        test('users can delete their own tasks', function () {
            $task = Task::factory()->create([
                'user_id' => $this->user->id,
            ]);

            $response = $this->actingAs($this->user)
                ->delete("/tasks/{$task->id}");

            $response->assertRedirect('/');
            $response->assertSessionHas('success', 'Task deleted successfully!');

            $this->assertDatabaseMissing('tasks', [
                'id' => $task->id,
            ]);
        });

        test('users cannot delete other users tasks', function () {
            $task = Task::factory()->create([
                'user_id' => $this->otherUser->id,
            ]);

            $response = $this->actingAs($this->user)
                ->delete("/tasks/{$task->id}");

            $response->assertForbidden();

            $this->assertDatabaseHas('tasks', [
                'id' => $task->id,
            ]);
        });

        test('guests cannot delete tasks', function () {
            $task = Task::factory()->create([
                'user_id' => $this->user->id,
            ]);

            $response = $this->delete("/tasks/{$task->id}");

            $response->assertRedirect('/login');

            $this->assertDatabaseHas('tasks', [
                'id' => $task->id,
            ]);
        });
    });

    describe('Dashboard Task Display', function () {
        test('dashboard shows only authenticated users tasks', function () {
            // Create tasks for both users
            $userTasks = Task::factory(3)->create(['user_id' => $this->user->id]);
            $otherUserTasks = Task::factory(2)->create(['user_id' => $this->otherUser->id]);

            $response = $this->actingAs($this->user)->get('/dashboard');

            $response->assertStatus(200);
            
            // Check that only user's tasks are passed to Inertia
            $response->assertInertia(fn ($page) => 
                $page->component('Dashboard')
                    ->has('tasks', 3)
                    ->where('tasks.0.user_id', $this->user->id)
                    ->where('tasks.1.user_id', $this->user->id)
                    ->where('tasks.2.user_id', $this->user->id)
            );
        });

        test('dashboard shows tasks ordered by latest first', function () {
            // Create tasks with different creation times
            $oldTask = Task::factory()->create([
                'user_id' => $this->user->id,
                'created_at' => now()->subDays(2),
            ]);
            
            $newTask = Task::factory()->create([
                'user_id' => $this->user->id,
                'created_at' => now(),
            ]);

            $response = $this->actingAs($this->user)->get('/dashboard');

            // Should be ordered by latest first
            $response->assertInertia(fn ($page) => 
                $page->component('Dashboard')
                    ->where('tasks.0.id', $newTask->id)
                    ->where('tasks.1.id', $oldTask->id)
            );
        });

        test('dashboard includes priority options', function () {
            $response = $this->actingAs($this->user)->get('/dashboard');

            $response->assertInertia(fn ($page) => 
                $page->component('Dashboard')
                    ->has('priorityOptions', 3)
                    ->where('priorityOptions.0', ['value' => 1, 'label' => 'Low'])
                    ->where('priorityOptions.1', ['value' => 2, 'label' => 'Medium'])
                    ->where('priorityOptions.2', ['value' => 3, 'label' => 'High'])
            );
        });
    });

    describe('Task Model Scopes', function () {
        test('completed scope returns only completed tasks', function () {
            $completedTask = Task::factory()->completed()->create(['user_id' => $this->user->id]);
            $incompleteTask = Task::factory()->incomplete()->create(['user_id' => $this->user->id]);

            $completedTasks = Task::completed()->get();

            $this->assertTrue($completedTasks->contains($completedTask));
            $this->assertFalse($completedTasks->contains($incompleteTask));
        });

        test('incomplete scope returns only incomplete tasks', function () {
            $completedTask = Task::factory()->completed()->create(['user_id' => $this->user->id]);
            $incompleteTask = Task::factory()->incomplete()->create(['user_id' => $this->user->id]);

            $incompleteTasks = Task::incomplete()->get();

            $this->assertFalse($incompleteTasks->contains($completedTask));
            $this->assertTrue($incompleteTasks->contains($incompleteTask));
        });

        test('overdue scope returns only overdue incomplete tasks', function () {
            $overdueTask = Task::factory()->overdue()->create(['user_id' => $this->user->id]);
            $futureTask = Task::factory()->create([
                'user_id' => $this->user->id,
                'due_date' => today()->addWeek(),
                'completed' => false,
            ]);
            $completedOverdueTask = Task::factory()->create([
                'user_id' => $this->user->id,
                'due_date' => today()->subWeek(),
                'completed' => true,
            ]);

            $overdueTasks = Task::overdue()->get();

            $this->assertTrue($overdueTasks->contains($overdueTask));
            $this->assertFalse($overdueTasks->contains($futureTask));
            $this->assertFalse($overdueTasks->contains($completedOverdueTask));
        });

        test('dueToday scope returns tasks due today', function () {
            $todayTask = Task::factory()->dueToday()->create(['user_id' => $this->user->id]);
            $tomorrowTask = Task::factory()->create([
                'user_id' => $this->user->id,
                'due_date' => today()->addDay(),
            ]);

            $todayTasks = Task::dueToday()->get();

            $this->assertTrue($todayTasks->contains($todayTask));
            $this->assertFalse($todayTasks->contains($tomorrowTask));
        });
    });
});
