<?php

namespace App\Http\Controllers;

use App\Enums\Priority;
use App\Models\Task;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    use AuthorizesRequests;
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => ['required', Rule::enum(Priority::class)],
            'due_date' => 'nullable|date|after_or_equal:today',
        ]);

        $request->user()->tasks()->create($validated);

        return back()->with('success', 'Task created successfully!');
    }

    public function update(Request $request, Task $task): RedirectResponse
    {
        // Ensure the task belongs to the authenticated user
        $this->authorize('update', $task);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => ['required', Rule::enum(Priority::class)],
            'due_date' => 'nullable|date|after_or_equal:today',
        ]);

        $task->update($validated);

        return back()->with('success', 'Task updated successfully!');
    }

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

    public function destroy(Task $task): RedirectResponse
    {
        $this->authorize('delete', $task);

        $task->delete();

        return back()->with('success', 'Task deleted successfully!');
    }
}
