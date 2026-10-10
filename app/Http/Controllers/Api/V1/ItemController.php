<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ItemResource;
use App\Models\Item;
use App\Models\User;
use App\Http\Requests\ItemIndexRequest;
use App\Services\AccessGrantService;
use App\Services\AccessResolver;
use App\Services\AnalyticsEventService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ItemController extends Controller
{
    public function index(ItemIndexRequest $request): AnonymousResourceCollection
    {

        $query = Item::query()
            ->where('is_visible', true)
            ->with([
                'categories',
                'tags',
                'media.file',
            ]);

        if ($request->filled('category')) {
            $query->whereHas('categories', function ($categoryQuery) use ($request) {
                $categoryQuery->where('slug', $request->category);
            });
        }

        if ($request->filled('tag')) {
            $query->whereHas('tags', function ($tagQuery) use ($request) {
                $tagQuery->where('slug', $request->tag);
            });
        }

        if ($request->filled('access_type')) {
            $query->where(
                'access_type',
                $request->string('access_type')->value()
            );
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('sort')) {
            match ($request->sort) {
                'latest' => $query
                    ->orderByDesc('created_at')
                    ->orderByDesc('id'),

                'oldest' => $query
                    ->orderBy('created_at')
                    ->orderBy('id'),

                'title' => $query
                    ->orderBy('title')
                    ->orderBy('id'),

                default => null,
            };
        }

        if (!$request->filled('sort')) {
            $query
                ->orderByDesc('created_at')
                ->orderByDesc('id');
        }

        $items = $query->paginate(12);

        return ItemResource::collection($items);
    }

    public function show(Item $item): ItemResource
    {
        abort_unless($item->is_visible, 404);

        $item->load([
            'categories',
            'tags',
            'media.file',
        ]);

        return new ItemResource($item);
    }

    public function claimFree(
        Request $request,
        Item $item,
        AccessGrantService $accessGrantService
    ): JsonResponse {
        abort_unless($item->is_visible, 404);

        abort_unless($item->access_type === 'free', 403);

        /** @var User $user */
        $user = $request->user();

        $accessGrantService->grant($user, $item);

        return response()->json([
            'message' => 'Item added to library.',
        ]);
    }

    public function access(Request $request, Item $item, AccessResolver $accessResolver): JsonResponse
    {
        abort_unless($item->is_visible, 404);

        /** @var User $user */
        $user = $request->user();

        abort_unless(
            $accessResolver->canAccess($user, $item),
            403
        );

        return response()->json([
            'message' => 'Item access granted.',
        ]);
    }

    public function content(
        Request $request,
        Item $item,
        AccessResolver $accessResolver,
        AnalyticsEventService $analyticsEventService
    ): JsonResponse {
        abort_unless($item->is_visible, 404);

        /** @var User $user */
        $user = $request->user();

        abort_unless(
            $accessResolver->canAccess($user, $item),
            403
        );

        $visitorId = $request->header('X-Analytics-Visitor-Id');
        $sessionId = $request->header('X-Analytics-Session-Id');

        if (
            is_string($visitorId)
            && is_string($sessionId)
            && Str::isUuid($visitorId)
            && Str::isUuid($sessionId)
        ) {
            $analyticsEventService->recordItemAccess(
                $visitorId,
                $sessionId,
                $item,
                $user
            );
        }

        return response()->json([
            'content' => $item->content,
            'item_id' => $item->id,
        ]);
    }
}
