<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsEvent;
use App\Models\User;
use App\Models\Item;
use App\Models\Category;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $now = Carbon::now();

        return response()->json([
            'users_count' => User::count(),
            'items_count' => Item::count(),
            'categories_count' => Category::count(),
            'analytics' => [
                'today' => $this->getAnalyticsForPeriod($now->copy()->startOfDay()),
                'week' => $this->getAnalyticsForPeriod($now->copy()->startOfWeek()),
                'month' => $this->getAnalyticsForPeriod($now->copy()->startOfMonth()),
                'year' => $this->getAnalyticsForPeriod($now->copy()->startOfYear()),
            ],
        ]);
    }

    private function getAnalyticsForPeriod(Carbon $start): array
    {
        $events = AnalyticsEvent::query()
            ->where('created_at', '>=', $start)
            ->selectRaw(
                'COUNT(CASE WHEN event = ? THEN 1 END) as visits',
                [AnalyticsEvent::EVENT_VISIT]
            )
            ->selectRaw(
                'COUNT(DISTINCT CASE WHEN event = ? THEN visitor_id END) as unique_visitors',
                [AnalyticsEvent::EVENT_VISIT]
            )
            ->selectRaw(
                'COUNT(CASE WHEN event = ? THEN 1 END) as item_access',
                [AnalyticsEvent::EVENT_ITEM_ACCESS]
            )
            ->selectRaw(
                'COUNT(CASE WHEN event = ? THEN 1 END) as item_copies',
                [AnalyticsEvent::EVENT_ITEM_COPY]
            )
            ->first();

        return [
            'visits' => (int) $events->visits,
            'unique_visitors' => (int) $events->unique_visitors,
            'item_access' => (int) $events->item_access,
            'item_copies' => (int) $events->item_copies,
        ];
    }
}
