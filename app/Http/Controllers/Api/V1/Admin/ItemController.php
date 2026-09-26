<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminItemIndexRequest;
use App\Http\Requests\StoreItemRequest;
use App\Http\Requests\UpdateItemRequest;
use App\Http\Resources\AdminItemResource;
use App\Models\Media;
use App\Models\File;
use Illuminate\Support\Facades\Storage;
use App\Models\Item;
use App\Services\TagResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use App\Support\SlugGenerator;
use Symfony\Component\HttpFoundation\Response;

class ItemController extends Controller
{
    private const RELATIONS = [
        'categories',
        'tags',
        'media.file',
    ];

    /**
     * Display a listing of all items for admin.
     */
    public function index(
        AdminItemIndexRequest $request
    ): AnonymousResourceCollection {
        $query = Item::with(self::RELATIONS);

        if ($request->filled('search')) {
            $search = $request->string('search')->value();

            $query->where(function ($query) use ($search) {
                $query
                    ->where('title', 'like', '%' . $search . '%')
                    ->orWhere('slug', 'like', '%' . $search . '%');
            });
        }

        if ($request->has('is_visible')) {
            $query->where(
                'is_visible',
                $request->boolean('is_visible')
            );
        }

        if ($request->filled('access_type')) {
            $query->where(
                'access_type',
                $request->string('access_type')->value()
            );
        }

        if ($request->filled('category_id')) {
            $query->whereHas('categories', function ($query) use ($request) {
                $query->whereKey($request->integer('category_id'));
            });
        }

        match ($request->string('sort')->value()) {
            'oldest' => $query
                ->orderBy('created_at')
                ->orderBy('id'),

            'title' => $query
                ->orderBy('title')
                ->orderBy('id'),

            default => $query
                ->orderByDesc('created_at')
                ->orderByDesc('id'),
        };

        $items = $query->paginate(15);

        return AdminItemResource::collection($items);
    }

    /**
     * Store a newly created resource.
     */
    public function store(
        StoreItemRequest $request,
        TagResolver $tagResolver
    ): JsonResponse {
        $item = DB::transaction(function () use ($request, $tagResolver) {

            $data = $request->validated();

            $data['slug'] = $data['slug'] ?? SlugGenerator::generate($data['title']);

            $data['is_visible'] = $data['is_visible'] ?? false;
            $data['access_type'] = $data['access_type'] ?? 'free';

            $categories = $data['categories'] ?? [];
            $tagNames = $data['tags'] ?? [];

            unset($data['categories'], $data['tags']);

            $item = Item::create($data);

            $item->categories()->sync($categories);
            $item->tags()->sync(
                $tagResolver->resolve($tagNames)
            );

            return $item;
        });

        $item->load(self::RELATIONS);

        return (new AdminItemResource($item))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(Item $item): AdminItemResource
    {
        $item->load(self::RELATIONS);

        return new AdminItemResource($item);
    }

    /**
     * Update the specified resource.
     */
    public function update(
        UpdateItemRequest $request,
        Item $item,
        TagResolver $tagResolver
    ): JsonResponse {
        DB::transaction(function () use (
            $request,
            $item,
            $tagResolver
        ) {

            $data = $request->validated();

            $categories = $data['categories'] ?? null;
            $tagNames = $data['tags'] ?? null;

            unset($data['categories'], $data['tags']);

            $item->update($data);

            if ($categories !== null) {
                $item->categories()->sync($categories);
            }

            if ($tagNames !== null) {
                $item->tags()->sync(
                    $tagResolver->resolve($tagNames)
                );
            }
        });

        $item->load(self::RELATIONS);

        return (new AdminItemResource($item))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Remove the specified resource.
     */
    public function destroy(Item $item): Response
    {
        abort_if(
            $item->orderItems()->exists(),
            409,
            'Item cannot be deleted because it has order history.'
        );

        $mediaFiles = $item->media()
            ->with('file')
            ->get()
            ->map(fn(Media $media) => [
                'file_id' => $media->file_id,
                'path' => $media->file->path,
            ]);

        DB::transaction(function () use ($item) {
            $item->categories()->detach();
            $item->tags()->detach();

            $item->delete();
        });

        foreach ($mediaFiles as $mediaFile) {
            $fileStillInUse = Media::query()
                ->where('file_id', $mediaFile['file_id'])
                ->exists();

            if ($fileStillInUse) {
                continue;
            }

            Storage::disk('public')->delete($mediaFile['path']);

            File::query()
                ->whereKey($mediaFile['file_id'])
                ->delete();
        }

        return response()->noContent();
    }
}
