<?php

use App\Models\Category;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

describe('Category Creation', function () {
    it('can create a category', function () {
        $categoryData = [
            'name' => 'Work Projects',
            'description' => 'Work-related tasks and projects',
            'color' => '#3B82F6',
        ];

        $response = $this->post('/categories', $categoryData);

        $response->assertRedirect('/');
        
        $this->assertDatabaseHas('categories', [
            'user_id' => $this->user->id,
            'name' => 'Work Projects',
            'slug' => 'work-projects',
            'description' => 'Work-related tasks and projects',
            'color' => '#3B82F6',
        ]);
    });

    it('automatically generates slug from name', function () {
        $response = $this->post('/categories', [
            'name' => 'Personal Health & Fitness',
            'color' => '#22C55E',
        ]);

        $response->assertRedirect('/');
        
        $this->assertDatabaseHas('categories', [
            'user_id' => $this->user->id,
            'name' => 'Personal Health & Fitness',
            'slug' => 'personal-health-fitness',
        ]);
    });

    it('requires name field', function () {
        $response = $this->post('/categories', [
            'color' => '#3B82F6',
        ]);

        $response->assertSessionHasErrors('name');
    });

    it('requires color field', function () {
        $response = $this->post('/categories', [
            'name' => 'Test Category',
        ]);

        $response->assertSessionHasErrors('color');
    });

    it('validates name length', function () {
        $response = $this->post('/categories', [
            'name' => str_repeat('a', 256), // Exceeds 255 character limit
            'color' => '#3B82F6',
        ]);

        $response->assertSessionHasErrors('name');
    });

    it('allows optional description', function () {
        $response = $this->post('/categories', [
            'name' => 'Simple Category',
            'color' => '#3B82F6',
        ]);

        $response->assertRedirect('/');
        
        $this->assertDatabaseHas('categories', [
            'user_id' => $this->user->id,
            'name' => 'Simple Category',
            'description' => null,
        ]);
    });
});

describe('Category Updates', function () {
    it('can update a category', function () {
        $category = Category::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Original Name',
            'description' => 'Original description',
            'color' => '#000000',
        ]);

        $updateData = [
            'name' => 'Updated Name',
            'description' => 'Updated description',
            'color' => '#FF0000',
        ];

        $response = $this->patch("/categories/{$category->id}", $updateData);

        $response->assertRedirect('/');
        
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'user_id' => $this->user->id,
            'name' => 'Updated Name',
            'slug' => 'updated-name',
            'description' => 'Updated description',
            'color' => '#FF0000',
        ]);
    });

    it('updates slug when name changes', function () {
        $category = Category::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Old Category Name',
        ]);

        $response = $this->patch("/categories/{$category->id}", [
            'name' => 'New Category Name',
            'color' => $category->color,
        ]);

        $response->assertRedirect('/');
        
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'slug' => 'new-category-name',
        ]);
    });

    it('can update only specific fields', function () {
        $category = Category::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Original Name',
            'description' => 'Original description',
            'color' => '#000000',
        ]);

        $response = $this->patch("/categories/{$category->id}", [
            'color' => '#FF0000',
        ]);

        $response->assertRedirect('/');
        
        // Name and description should remain unchanged
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Original Name',
            'description' => 'Original description',
            'color' => '#FF0000',
        ]);
    });

    it('prevents updating categories owned by other users', function () {
        $otherUser = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->patch("/categories/{$category->id}", [
            'name' => 'Hacked Name',
            'color' => '#FF0000',
        ]);

        $response->assertStatus(403);
        
        // Ensure the category wasn't updated
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => $category->name,
            'color' => $category->color,
        ]);
    });
});

describe('Category Deletion', function () {
    it('can delete a category without tasks', function () {
        $category = Category::factory()->create(['user_id' => $this->user->id]);

        $response = $this->delete("/categories/{$category->id}");

        $response->assertRedirect('/');
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    });

    it('can delete a category and detach its tasks', function () {
        $category = Category::factory()->create(['user_id' => $this->user->id]);
        $task = Task::factory()->create(['user_id' => $this->user->id]);
        
        // Attach category to task
        $task->categories()->attach($category);
        
        // Verify the relationship exists
        expect($task->categories)->toHaveCount(1);

        $response = $this->delete("/categories/{$category->id}");

        $response->assertRedirect('/');
        
        // Category should be deleted
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
        
        // Task should still exist but category relationship should be removed
        $this->assertDatabaseHas('tasks', ['id' => $task->id]);
        $this->assertDatabaseMissing('task_categories', [
            'task_id' => $task->id,
            'category_id' => $category->id,
        ]);
    });

    it('prevents deleting categories owned by other users', function () {
        $otherUser = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->delete("/categories/{$category->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    });
});

describe('Category Authorization', function () {
    it('requires authentication to create categories', function () {
        auth()->logout();

        $response = $this->post('/categories', [
            'name' => 'Test Category',
            'color' => '#3B82F6',
        ]);

        $response->assertRedirect('/login');
    });

    it('requires authentication to update categories', function () {
        $category = Category::factory()->create(['user_id' => $this->user->id]);
        auth()->logout();

        $response = $this->patch("/categories/{$category->id}", [
            'name' => 'Updated Name',
            'color' => '#FF0000',
        ]);

        $response->assertRedirect('/login');
    });

    it('requires authentication to delete categories', function () {
        $category = Category::factory()->create(['user_id' => $this->user->id]);
        auth()->logout();

        $response = $this->delete("/categories/{$category->id}");

        $response->assertRedirect('/login');
    });

    it('only shows user own categories on categories page', function () {
        $userCategory = Category::factory()->create(['user_id' => $this->user->id]);
        $otherUserCategory = Category::factory()->create(['user_id' => User::factory()->create()->id]);

        $response = $this->get('/categories');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Categories')
                ->has('categories', 1)
                ->where('categories.0.id', $userCategory->id)
        );
    });
});

describe('Category Model Behavior', function () {
    it('automatically generates slug when creating category', function () {
        $category = Category::create([
            'user_id' => $this->user->id,
            'name' => 'Test Category Name',
            'color' => '#3B82F6',
        ]);

        expect($category->slug)->toBe('test-category-name');
    });

    it('updates slug when name is updated', function () {
        $category = Category::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Original Name',
        ]);

        $category->update(['name' => 'Updated Name']);

        expect($category->fresh()->slug)->toBe('updated-name');
    });

    it('has tasks relationship', function () {
        $category = Category::factory()->create(['user_id' => $this->user->id]);
        $task = Task::factory()->create(['user_id' => $this->user->id]);
        
        $category->tasks()->attach($task);

        expect($category->tasks)->toHaveCount(1);
        expect($category->tasks->first()->id)->toBe($task->id);
    });

    it('has user relationship', function () {
        $category = Category::factory()->create(['user_id' => $this->user->id]);

        expect($category->user->id)->toBe($this->user->id);
    });

    it('scopes categories for specific user', function () {
        $userCategory = Category::factory()->create(['user_id' => $this->user->id]);
        $otherUserCategory = Category::factory()->create(['user_id' => User::factory()->create()->id]);

        $userCategories = Category::forUser($this->user->id)->get();

        expect($userCategories)->toHaveCount(1);
        expect($userCategories->first()->id)->toBe($userCategory->id);
    });

    it('counts tasks correctly', function () {
        $category = Category::factory()->create(['user_id' => $this->user->id]);
        $tasks = Task::factory(3)->create(['user_id' => $this->user->id]);
        
        $category->tasks()->attach($tasks);

        expect($category->getTasksCountAttribute())->toBe(3);
    });
});
