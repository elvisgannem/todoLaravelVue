<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

class CategoryController extends Controller
{
    use AuthorizesRequests;

    #[OA\Get(
        path: '/categories',
        summary: 'Get all categories for the authenticated user',
        description: 'Retrieves all categories belonging to the authenticated user',
        security: [['session' => []]],
        tags: ['Categories']
    )]
    #[OA\Response(
        response: 200,
        description: 'Categories retrieved successfully',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/Category')
        )
    )]
    public function index()
    {
        $categories = auth()->user()->categories()->withCount('tasks')->get();
        return response()->json($categories);
    }

    #[OA\Post(
        path: '/categories',
        summary: 'Create a new category',
        description: 'Creates a new category for the authenticated user',
        security: [['session' => []]],
        tags: ['Categories']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['name'],
            properties: [
                new OA\Property(property: 'name', type: 'string', maxLength: 255, example: 'Work'),
                new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Work related tasks'),
                new OA\Property(property: 'color', type: 'string', pattern: '^#[0-9A-Fa-f]{6}$', example: '#3B82F6', description: 'Hex color code')
            ]
        )
    )]
    #[OA\Response(
        response: 302,
        description: 'Category created successfully, redirects back'
    )]
    #[OA\Response(
        response: 422,
        description: 'Validation error'
    )]
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/|size:7',
        ]);

        // Generate unique slug for this user
        $baseSlug = Str::slug($validated['name']);
        $slug = $baseSlug;
        $counter = 1;
        
        while (Category::where('user_id', auth()->id())
                      ->where('slug', $slug)
                      ->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        $validated['slug'] = $slug;
        $validated['user_id'] = auth()->id();
        $validated['color'] = $validated['color'] ?? '#6B7280';

        Category::create($validated);

        return back()->with('success', 'Category created successfully!');
    }

    #[OA\Put(
        path: '/categories/{category}',
        summary: 'Update an existing category',
        description: 'Updates a category owned by the authenticated user',
        security: [['session' => []]],
        tags: ['Categories']
    )]
    #[OA\Parameter(
        name: 'category',
        description: 'Category ID',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer', example: 1)
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['name'],
            properties: [
                new OA\Property(property: 'name', type: 'string', maxLength: 255, example: 'Personal'),
                new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Personal tasks and reminders'),
                new OA\Property(property: 'color', type: 'string', pattern: '^#[0-9A-Fa-f]{6}$', example: '#10B981')
            ]
        )
    )]
    #[OA\Response(
        response: 302,
        description: 'Category updated successfully, redirects back'
    )]
    #[OA\Response(
        response: 403,
        description: 'Forbidden - Category does not belong to user'
    )]
    #[OA\Response(
        response: 404,
        description: 'Category not found'
    )]
    public function update(Request $request, Category $category): RedirectResponse
    {
        // Ensure the category belongs to the authenticated user
        if ($category->user_id !== auth()->id()) {
            abort(403, 'This category does not belong to you.');
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'sometimes|required|string|regex:/^#[0-9A-Fa-f]{6}$/|size:7',
        ]);

        // Generate unique slug if name changed
        if (isset($validated['name']) && $validated['name'] !== $category->name) {
            $baseSlug = Str::slug($validated['name']);
            $slug = $baseSlug;
            $counter = 1;
            
            while (Category::where('user_id', auth()->id())
                          ->where('slug', $slug)
                          ->where('id', '!=', $category->id)
                          ->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }
            $validated['slug'] = $slug;
        }

        $category->update($validated);

        return back()->with('success', 'Category updated successfully!');
    }

    #[OA\Delete(
        path: '/categories/{category}',
        summary: 'Delete a category',
        description: 'Deletes a category owned by the authenticated user. Tasks in this category will not be deleted.',
        security: [['session' => []]],
        tags: ['Categories']
    )]
    #[OA\Parameter(
        name: 'category',
        description: 'Category ID',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer', example: 1)
    )]
    #[OA\Response(
        response: 302,
        description: 'Category deleted successfully, redirects back'
    )]
    #[OA\Response(
        response: 403,
        description: 'Forbidden - Category does not belong to user'
    )]
    #[OA\Response(
        response: 404,
        description: 'Category not found'
    )]
    public function destroy(Category $category): RedirectResponse
    {
        // Ensure the category belongs to the authenticated user
        if ($category->user_id !== auth()->id()) {
            abort(403, 'This category does not belong to you.');
        }

        $category->delete();

        return back()->with('success', 'Category deleted successfully!');
    }
}