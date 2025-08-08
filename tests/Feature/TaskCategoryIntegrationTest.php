<?php

use App\Models\Category;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
    
    $this->categories = Category::factory(3)->create(['user_id' => $this->user->id]);
});

describe('Task Creation with Categories', function () {
    it('can create a task with categories', function () {
        $taskData = [
            'title' => 'Test Task with Categories',
            'description' => 'Task description',
            'priority' => 2, // Priority::MEDIUM->value
            'due_date' => '2024-12-31',
            'categories' => [$this->categories[0]->id, $this->categories[1]->id],
        ];

        $response = $this->post('/tasks', $taskData);

        $response->assertRedirect('/');
        
        $task = Task::where('title', 'Test Task with Categories')->first();
        expect($task)->not->toBeNull();
        expect($task->categories)->toHaveCount(2);
        expect($task->categories->pluck('id')->toArray())
            ->toContain($this->categories[0]->id, $this->categories[1]->id);
    });

    it('can create a task without categories', function () {
        $taskData = [
            'title' => 'Task without Categories',
            'description' => 'Task description',
            'priority' => 1, // Priority::LOW->value
            'due_date' => '2024-12-31',
        ];

        $response = $this->post('/tasks', $taskData);

        $response->assertRedirect('/');
        
        $task = Task::where('title', 'Task without Categories')->first();
        expect($task)->not->toBeNull();
        expect($task->categories)->toHaveCount(0);
    });

    it('filters out categories that do not belong to the authenticated user', function () {
        $otherUser = User::factory()->create();
        $otherUserCategory = Category::factory()->create(['user_id' => $otherUser->id]);

        $taskData = [
            'title' => 'Test Task',
            'priority' => 2, // Priority::MEDIUM->value
            'categories' => [$otherUserCategory->id, $this->categories[0]->id],
        ];

        $response = $this->post('/tasks', $taskData);

        $response->assertRedirect('/');
        
        $task = Task::where('title', 'Test Task')->first();
        expect($task)->not->toBeNull();
        expect($task->categories)->toHaveCount(1); // Only user's category should be attached
        expect($task->categories->first()->id)->toBe($this->categories[0]->id);
    });

    it('ignores non-existent category IDs', function () {
        $taskData = [
            'title' => 'Test Task',
            'priority' => 2, // Priority::MEDIUM->value
            'categories' => [999, $this->categories[0]->id], // 999 doesn't exist
        ];

        $response = $this->post('/tasks', $taskData);

        $response->assertSessionHasErrors('categories.0'); // Non-existent category should fail validation
    });

    it('handles empty categories array', function () {
        $taskData = [
            'title' => 'Test Task',
            'priority' => 2, // Priority::MEDIUM->value
            'categories' => [],
        ];

        $response = $this->post('/tasks', $taskData);

        $response->assertRedirect('/');
        
        $task = Task::where('title', 'Test Task')->first();
        expect($task->categories)->toHaveCount(0);
    });
});

describe('Task Updates with Categories', function () {
    it('can update task categories', function () {
        $task = Task::factory()->create(['user_id' => $this->user->id]);
        $task->categories()->attach([$this->categories[0]->id]);

        $updateData = [
            'title' => $task->title,
            'priority' => $task->priority->value,
            'categories' => [$this->categories[1]->id, $this->categories[2]->id],
        ];

        $response = $this->patch("/tasks/{$task->id}", $updateData);

        $response->assertRedirect('/');
        
        $task->refresh();
        expect($task->categories)->toHaveCount(2);
        expect($task->categories->pluck('id')->toArray())
            ->toContain($this->categories[1]->id, $this->categories[2]->id)
            ->not->toContain($this->categories[0]->id);
    });

    it('can add categories to a task that had none', function () {
        $task = Task::factory()->create(['user_id' => $this->user->id]);
        expect($task->categories)->toHaveCount(0);

        $updateData = [
            'title' => $task->title,
            'priority' => $task->priority->value,
            'categories' => [$this->categories[0]->id],
        ];

        $response = $this->patch("/tasks/{$task->id}", $updateData);

        $response->assertRedirect('/');
        
        $task->refresh();
        expect($task->categories)->toHaveCount(1);
        expect($task->categories->first()->id)->toBe($this->categories[0]->id);
    });

    it('can remove all categories from a task', function () {
        $task = Task::factory()->create(['user_id' => $this->user->id]);
        $task->categories()->attach($this->categories->pluck('id'));
        expect($task->categories)->toHaveCount(3);

        $updateData = [
            'title' => $task->title,
            'priority' => $task->priority->value,
            'categories' => [],
        ];

        $response = $this->patch("/tasks/{$task->id}", $updateData);

        $response->assertRedirect('/');
        
        $task->refresh();
        expect($task->categories)->toHaveCount(0);
    });

    it('does not update categories if not provided in request', function () {
        $task = Task::factory()->create(['user_id' => $this->user->id]);
        $task->categories()->attach([$this->categories[0]->id]);

        $updateData = [
            'title' => 'Updated Title',
            'priority' => $task->priority->value,
            // No categories field
        ];

        $response = $this->patch("/tasks/{$task->id}", $updateData);

        $response->assertRedirect('/');
        
        $task->refresh();
        expect($task->title)->toBe('Updated Title');
        expect($task->categories)->toHaveCount(1); // Categories unchanged
        expect($task->categories->first()->id)->toBe($this->categories[0]->id);
    });

    it('filters out categories that do not belong to the authenticated user during update', function () {
        $task = Task::factory()->create(['user_id' => $this->user->id]);
        $otherUser = User::factory()->create();
        $otherUserCategory = Category::factory()->create(['user_id' => $otherUser->id]);

        $updateData = [
            'title' => $task->title,
            'priority' => $task->priority->value,
            'categories' => [$otherUserCategory->id, $this->categories[0]->id],
        ];

        $response = $this->patch("/tasks/{$task->id}", $updateData);

        $response->assertRedirect('/');
        
        $task->refresh();
        expect($task->categories)->toHaveCount(1); // Only user's category should be attached
        expect($task->categories->first()->id)->toBe($this->categories[0]->id);
    });
});

describe('Dashboard with Task Categories', function () {
    it('loads tasks with their categories on dashboard', function () {
        $task = Task::factory()->create(['user_id' => $this->user->id]);
        $task->categories()->attach([$this->categories[0]->id, $this->categories[1]->id]);

        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Dashboard')
                ->has('tasks', 1)
                ->where('tasks.0.id', $task->id)
                ->has('tasks.0.categories', 2)
                ->where('tasks.0.categories.0.id', $this->categories[0]->id)
                ->where('tasks.0.categories.1.id', $this->categories[1]->id)
        );
    });

    it('passes categories to dashboard for task forms', function () {
        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Dashboard')
                ->has('categories', 3)
        );
        

    });
});

describe('Category-Task Relationship Edge Cases', function () {
    it('handles duplicate category IDs in request', function () {
        $taskData = [
            'title' => 'Test Task',
            'priority' => 2, // Priority::MEDIUM->value
            'categories' => [$this->categories[0]->id, $this->categories[0]->id, $this->categories[1]->id],
        ];

        $response = $this->post('/tasks', $taskData);

        $response->assertRedirect('/');
        
        $task = Task::where('title', 'Test Task')->first();
        expect($task->categories)->toHaveCount(2); // Duplicates should be ignored
        expect($task->categories->pluck('id')->toArray())
            ->toContain($this->categories[0]->id, $this->categories[1]->id);
    });

    it('maintains category relationships when task is completed', function () {
        $task = Task::factory()->incomplete()->create(['user_id' => $this->user->id]);
        $task->categories()->attach([$this->categories[0]->id]);

        $response = $this->patch("/tasks/{$task->id}/toggle");

        $response->assertRedirect('/');
        
        $task->refresh();
        expect($task->completed)->toBeTrue();
        expect($task->categories)->toHaveCount(1); // Categories preserved
        expect($task->categories->first()->id)->toBe($this->categories[0]->id);
    });

    it('removes category relationships when task is deleted', function () {
        $task = Task::factory()->create(['user_id' => $this->user->id]);
        $task->categories()->attach($this->categories->pluck('id'));

        // Verify relationships exist
        $this->assertDatabaseHas('task_categories', ['task_id' => $task->id]);

        $response = $this->delete("/tasks/{$task->id}");

        $response->assertRedirect('/');
        
        // Task should be deleted
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
        
        // Pivot relationships should be cleaned up
        $this->assertDatabaseMissing('task_categories', ['task_id' => $task->id]);
        
        // Categories should still exist
        expect(Category::count())->toBe(3);
    });

    it('handles category validation with mixed valid and invalid IDs', function () {
        $otherUser = User::factory()->create();
        $otherUserCategory = Category::factory()->create(['user_id' => $otherUser->id]);

        $taskData = [
            'title' => 'Test Task',
            'priority' => 2, // Priority::MEDIUM->value
            'categories' => [
                $this->categories[0]->id, // Valid
                $otherUserCategory->id,   // Invalid (other user's)
                999,                      // Invalid (doesn't exist)
                $this->categories[1]->id, // Valid
            ],
        ];

        $response = $this->post('/tasks', $taskData);

        $response->assertSessionHasErrors('categories.2'); // Only non-existent ID should fail validation
    });
});

describe('Categories Page with Task Counts', function () {
    it('shows correct task counts for categories', function () {
        // Create tasks and associate with categories
        $task1 = Task::factory()->create(['user_id' => $this->user->id]);
        $task2 = Task::factory()->create(['user_id' => $this->user->id]);
        $task3 = Task::factory()->create(['user_id' => $this->user->id]);

        $this->categories[0]->tasks()->attach([$task1->id, $task2->id]); // 2 tasks
        $this->categories[1]->tasks()->attach([$task1->id]);             // 1 task
        // categories[2] has no tasks

        $response = $this->get('/categories');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Categories')
                ->has('categories', 3)
        );
        
        $categories = $response->viewData('page')['props']['categories'];
        $category0 = collect($categories)->firstWhere('id', $this->categories[0]->id);
        $category1 = collect($categories)->firstWhere('id', $this->categories[1]->id);
        $category2 = collect($categories)->firstWhere('id', $this->categories[2]->id);
        
        expect($category0['tasks_count'])->toBe(2);
        expect($category1['tasks_count'])->toBe(1);
        expect($category2['tasks_count'])->toBe(0);
    });
});
