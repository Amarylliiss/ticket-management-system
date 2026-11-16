<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Infrastructure\Persistence\Eloquent\Models\CategoryModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', CategoryModel::class);

        $query = CategoryModel::query()->withCount('tickets')->orderBy('name');

        if (! $request->user()->isAdmin()) {
            $query->where('is_active', true);
        }

        return CategoryResource::collection($query->get());
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = CategoryModel::query()->create([
            'name' => $request->string('name')->toString(),
            'slug' => $this->uniqueSlug($request->string('name')->toString()),
            'description' => $request->input('description'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return (new CategoryResource($category))->response()->setStatusCode(201);
    }

    public function update(UpdateCategoryRequest $request, CategoryModel $category): CategoryResource
    {
        $name = $request->string('name')->toString();

        $category->fill([
            'name' => $name,
            'slug' => $category->name === $name ? $category->slug : $this->uniqueSlug($name, $category->id),
            'description' => $request->input('description'),
            'is_active' => $request->boolean('is_active', $category->is_active),
        ])->save();

        return new CategoryResource($category->loadCount('tickets'));
    }

    public function destroy(CategoryModel $category): JsonResponse
    {
        $this->authorize('delete', $category);

        if ($category->tickets()->exists()) {
            return response()->json([
                'message' => 'Kategoria ma zgłoszenia. Wyłącz ją zamiast usuwać.',
            ], 409);
        }

        $category->delete();

        return response()->json(null, 204);
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $base = $base !== '' ? $base : 'kategoria';
        $slug = $base;
        $suffix = 2;

        while (
            CategoryModel::query()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
