<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
})->name('home');

Route::get('dashboard', function () {
    $tasks = auth()->user()->tasks()->with('categories')->latest()->get();
    $categories = auth()->user()->categories()->get();
    
    return Inertia::render('Dashboard', [
        'tasks' => $tasks,
        'categories' => $categories,
        'priorityOptions' => \App\Enums\Priority::options(),
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('categories', function () {
    $user = auth()->user();
    $categories = $user->categories()->withCount('userTasks as tasks_count')->orderBy('name')->get();
    
    return Inertia::render('Categories', [
        'categories' => $categories,
    ]);
})->middleware(['auth', 'verified'])->name('categories');

Route::middleware(['auth'])->group(function () {
    Route::post('tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::patch('tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::patch('tasks/{task}/toggle', [TaskController::class, 'toggleComplete'])->name('tasks.toggle');
    Route::delete('tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
    
    Route::post('categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::patch('categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
