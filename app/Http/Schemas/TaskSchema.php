<?php

namespace App\Http\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Task',
    title: 'Task',
    description: 'Task model',
    required: ['id', 'title', 'completed', 'priority', 'created_at', 'updated_at'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'title', type: 'string', example: 'Complete project documentation'),
        new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Write comprehensive documentation for the todo application'),
        new OA\Property(property: 'completed', type: 'boolean', example: false),
        new OA\Property(property: 'priority', type: 'integer', enum: [1, 2, 3], example: 2, description: '1=Low, 2=Medium, 3=High'),
        new OA\Property(property: 'due_date', type: 'string', format: 'date', nullable: true, example: '2024-12-31'),
        new OA\Property(property: 'completed_at', type: 'string', format: 'datetime', nullable: true, example: '2024-08-07T14:30:00Z'),
        new OA\Property(property: 'created_at', type: 'string', format: 'datetime', example: '2024-08-07T10:00:00Z'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'datetime', example: '2024-08-07T14:30:00Z'),
        new OA\Property(property: 'user_id', type: 'integer', example: 1)
    ]
)]
class TaskSchema
{
    // This class exists only to hold the schema definition
}
