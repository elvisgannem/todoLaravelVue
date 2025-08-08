<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'user_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship with User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Many-to-many relationship with Tasks
     */
    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_categories')
            ->withTimestamps();
    }
    public function userTasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_categories')
            ->where('tasks.user_id', $this->user_id)
            ->withTimestamps();
    }

    /**
     * Generate slug from name
     */
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = Str::slug($value);
    }

    /**
     * Scope to get categories for a specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Get the number of tasks in this category
     */
    public function getTasksCountAttribute()
    {
        return $this->tasks()->count();
    }
}
