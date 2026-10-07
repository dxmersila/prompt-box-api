<?php

namespace App\Services;

use App\Models\AnalyticsEvent;
use App\Models\Item;
use App\Models\User;

class AnalyticsEventService
{
    public function recordVisit(
        string $visitorId,
        string $sessionId,
        ?User $user = null
    ): void {
        $exists = AnalyticsEvent::query()
            ->where('event', AnalyticsEvent::EVENT_VISIT)
            ->where('session_id', $sessionId)
            ->exists();

        if ($exists) {
            return;
        }

        AnalyticsEvent::create([
            'event' => AnalyticsEvent::EVENT_VISIT,
            'visitor_id' => $visitorId,
            'session_id' => $sessionId,
            'user_id' => $user?->id,
        ]);
    }

    public function recordPromptAccess(
        string $visitorId,
        string $sessionId,
        Item $item,
        ?User $user = null
    ): void {
        $this->record(
            AnalyticsEvent::EVENT_PROMPT_ACCESS,
            $visitorId,
            $sessionId,
            $user,
            $item
        );
    }

    public function recordPromptCopy(
        string $visitorId,
        string $sessionId,
        Item $item,
        ?User $user = null
    ): void {
        $this->record(
            AnalyticsEvent::EVENT_PROMPT_COPY,
            $visitorId,
            $sessionId,
            $user,
            $item
        );
    }

    private function record(
        string $event,
        string $visitorId,
        string $sessionId,
        ?User $user,
        Item $item
    ): void {
        AnalyticsEvent::create([
            'event' => $event,
            'visitor_id' => $visitorId,
            'session_id' => $sessionId,
            'user_id' => $user?->id,
            'item_id' => $item->id,
        ]);
    }
}
