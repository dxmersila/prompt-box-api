<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\AnalyticsEventRequest;
use App\Models\AnalyticsEvent;
use App\Models\Item;
use App\Services\AnalyticsEventService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class AnalyticsEventController extends Controller
{
    public function store(
        AnalyticsEventRequest $request,
        AnalyticsEventService $analyticsEventService
    ): Response {
        $user = Auth::guard('sanctum')->user();

        if ($request->input('event') === AnalyticsEvent::EVENT_VISIT) {
            $analyticsEventService->recordVisit(
                $request->string('visitor_id')->toString(),
                $request->string('session_id')->toString(),
                $user
            );
        } else {
            $item = Item::query()->findOrFail($request->integer('item_id'));

            $analyticsEventService->recordPromptCopy(
                $request->string('visitor_id')->toString(),
                $request->string('session_id')->toString(),
                $item,
                $user
            );
        }

        return response()->noContent();
    }
}
