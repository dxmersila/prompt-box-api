<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminCategoryIndexRequest;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\AdminCategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Support\SlugGenerator;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends Controller
{
    public function index(
        AdminCategoryIndexRequest $request
    ): AnonymousResourceCollection {
        $query = Category::query();

        if ($request->filled('search')) {
            $search = $request->string('search')->value();

            $query->where(function ($query) use ($search) {
                $query
                    ->where('name', 'like', '%' . $search . '%')
                    ->orWhere('slug', 'like', '%' . $search . '%');
            });
        }

        match ($request->string('sort')->value()) {
            'latest' => $query
                ->orderByDesc('created_at')
                ->orderByDesc('id'),

            'oldest' => $query
                ->orderBy('created_at')
                ->orderBy('id'),

            default => $query
                ->orderBy('name')
                ->orderBy('id'),
        };

        $categories = $query->paginate(15);

        return AdminCategoryResource::collection($categories);
    }

    public function store(
        StoreCategoryRequest $request
    ): JsonResponse {
        $data = $request->validated();

        $data['slug'] = $data['slug'] ?? SlugGenerator::generate($data['name']);

        $category = Category::create($data);

        return (new AdminCategoryResource($category))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Category $category): AdminCategoryResource
    {
        return new AdminCategoryResource($category);
    }

    public function update(
        UpdateCategoryRequest $request,
        Category $category
    ): JsonResponse {
        $data = $request->validated();

        $category->update($data);

        return (new AdminCategoryResource($category->fresh()))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function destroy(Category $category): Response
    {
        $category->delete();

        return response()->noContent();
    }
}
