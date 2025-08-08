<?php

namespace App\Http\Controllers;

use App\Enums\Priority;
use App\Models\Task;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

class TaskController extends Controller
{
    use AuthorizesRequests;

    #[OA\Post(
        path: '/tasks',
        summary: 'Create a new task',
        description: 'Creates a new task for the authenticated user',
        security: [['session' => []]],
        tags: ['Tasks']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['title', 'priority'],
            properties: [
                new OA\Property(property: 'title', type: 'string', maxLength: 255, example: 'Complete project documentation'),
                new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Write comprehensive documentation for the todo application'),
                new OA\Property(property: 'priority', type: 'integer', enum: [1, 2, 3], example: 2, description: '1=Low, 2=Medium, 3=High'),
                new OA\Property(property: 'due_date', type: 'string', format: 'date', nullable: true, example: '2024-12-31'),
                new OA\Property(
                    property: 'categories',
                    type: 'array',
                    items: new OA\Items(type: 'integer'),
                    nullable: true,
                    example: [1, 2],
                    description: 'Array of category IDs to associate with the task'
                )
            ]
        )
    )]
    #[OA\Response(
        response: 302,
        description: 'Task created successfully, redirects back',
        headers: [
            new OA\Header(header: 'Location', schema: new OA\Schema(type: 'string'))
        ]
    )]
    #[OA\Response(
        response: 422,
        description: 'Validation error',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'The given data was invalid.'),
                new OA\Property(
                    property: 'errors',
                    type: 'object',
                    example: ['title' => ['The title field is required.']]
                )
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: 'Unauthenticated'
    )]
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => ['required', Rule::enum(Priority::class)],
            'due_date' => 'nullable|date',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
        ]);

        $categories = $validated['categories'] ?? [];
        unset($validated['categories']);

        $task = $request->user()->tasks()->create($validated);
        
        // Sync categories (only attach categories that belong to the user)
        if (!empty($categories)) {
            $userCategoryIds = $request->user()->categories()->whereIn('id', $categories)->pluck('id');
            $task->categories()->sync($userCategoryIds);
        }

        return back()->with('success', 'Task created successfully!');
    }

    #[OA\Patch(
        path: '/tasks/{task}',
        summary: 'Update an existing task',
        description: 'Updates a task owned by the authenticated user',
        security: [['session' => []]],
        tags: ['Tasks']
    )]
    #[OA\Parameter(
        name: 'task',
        description: 'Task ID',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer', example: 1)
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['title', 'priority'],
            properties: [
                new OA\Property(property: 'title', type: 'string', maxLength: 255, example: 'Updated task title'),
                new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Updated task description'),
                new OA\Property(property: 'priority', type: 'integer', enum: [1, 2, 3], example: 3, description: '1=Low, 2=Medium, 3=High'),
                new OA\Property(property: 'due_date', type: 'string', format: 'date', nullable: true, example: '2025-01-15'),
                new OA\Property(
                    property: 'categories',
                    type: 'array',
                    items: new OA\Items(type: 'integer'),
                    nullable: true,
                    example: [1, 3],
                    description: 'Array of category IDs to associate with the task'
                )
            ]
        )
    )]
    #[OA\Response(
        response: 302,
        description: 'Task updated successfully, redirects back'
    )]
    #[OA\Response(
        response: 403,
        description: 'Forbidden - Task does not belong to user'
    )]
    #[OA\Response(
        response: 404,
        description: 'Task not found'
    )]
    #[OA\Response(
        response: 422,
        description: 'Validation error'
    )]
    public function update(Request $request, Task $task): RedirectResponse
    {
        // Ensure the task belongs to the authenticated user
        $this->authorize('update', $task);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => ['required', Rule::enum(Priority::class)],
            'due_date' => 'nullable|date',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
        ]);

        $categories = $validated['categories'] ?? [];
        unset($validated['categories']);

        $task->update($validated);
        
        // Sync categories (only attach categories that belong to the user)
        if (array_key_exists('categories', $request->all())) {
            $userCategoryIds = $request->user()->categories()->whereIn('id', $categories)->pluck('id');
            $task->categories()->sync($userCategoryIds);
        }

        return back()->with('success', 'Task updated successfully!');
    }

    #[OA\Patch(
        path: '/tasks/{task}/toggle',
        summary: 'Toggle task completion status',
        description: 'Toggles the completion status of a task (completed/incomplete)',
        security: [['session' => []]],
        tags: ['Tasks']
    )]
    #[OA\Parameter(
        name: 'task',
        description: 'Task ID',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer', example: 1)
    )]
    #[OA\Response(
        response: 302,
        description: 'Task completion status toggled successfully, redirects back'
    )]
    #[OA\Response(
        response: 403,
        description: 'Forbidden - Task does not belong to user'
    )]
    #[OA\Response(
        response: 404,
        description: 'Task not found'
    )]
    public function toggleComplete(Task $task): RedirectResponse
    {
        $this->authorize('update', $task);

        if ($task->completed) {
            $task->markAsIncomplete();
            $message = 'Task marked as incomplete!';
        } else {
            $task->markAsCompleted();
            $message = 'Task completed!';
        }

        return back()->with('success', $message);
    }

    #[OA\Delete(
        path: '/tasks/{task}',
        summary: 'Delete a task',
        description: 'Deletes a task owned by the authenticated user',
        security: [['session' => []]],
        tags: ['Tasks']
    )]
    #[OA\Parameter(
        name: 'task',
        description: 'Task ID',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer', example: 1)
    )]
    #[OA\Response(
        response: 302,
        description: 'Task deleted successfully, redirects back'
    )]
    #[OA\Response(
        response: 403,
        description: 'Forbidden - Task does not belong to user'
    )]
    #[OA\Response(
        response: 404,
        description: 'Task not found'
    )]
    public function destroy(Task $task): RedirectResponse
    {
        $this->authorize('delete', $task);

        $task->delete();

        return back()->with('success', 'Task deleted successfully!');
    }
}
