<?php

namespace App\Http\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ValidationError',
    title: 'Validation Error',
    description: 'Validation error response',
    required: ['message', 'errors'],
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'The given data was invalid.'),
        new OA\Property(
            property: 'errors',
            type: 'object',
            example: [
                'title' => ['The title field is required.'],
                'priority' => ['The priority field must be one of: 1, 2, 3.']
            ]
        )
    ]
)]
#[OA\Schema(
    schema: 'Error',
    title: 'Error',
    description: 'Generic error response',
    required: ['message'],
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'An error occurred')
    ]
)]
class ErrorSchema
{
    // This class exists only to hold the schema definitions
}
