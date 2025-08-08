<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Todo Laravel Vue API',
    description: 'A comprehensive task management API built with Laravel and Vue.js'
)]
#[OA\Server(
    url: '/',
    description: 'Web Server'
)]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT'
)]
abstract class Controller
{
    //
}
