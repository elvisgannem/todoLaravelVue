<?php

namespace App\Http\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Category',
    title: 'Category',
    description: 'Task category model',
    required: ['id', 'name', 'slug', 'color', 'user_id', 'created_at', 'updated_at'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Work'),
        new OA\Property(property: 'slug', type: 'string', example: 'work'),
        new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Work related tasks and projects'),
        new OA\Property(property: 'color', type: 'string', example: '#3B82F6', description: 'Hex color code for UI display'),
        new OA\Property(property: 'user_id', type: 'integer', example: 1),
        new OA\Property(property: 'tasks_count', type: 'integer', example: 5, description: 'Number of tasks in this category'),
        new OA\Property(property: 'created_at', type: 'string', format: 'datetime', example: '2024-08-07T10:00:00Z'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'datetime', example: '2024-08-07T14:30:00Z')
    ]
)]
class CategorySchema
{
    // This class exists only to hold the schema definition
}
