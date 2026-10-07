<?php

namespace Tests\Feature;

use App\Models\AnalyticsEvent;
use App\Models\Item;
use App\Models\User;
use App\Services\AnalyticsEventService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsEventServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_records_a_visit(): void
    {
        $user = User::factory()->create();

        app(AnalyticsEventService::class)->recordVisit(
            'visitor-123',
            'session-123',
            $user
        );

        $this->assertDatabaseHas('analytics_events', [
            'event' => AnalyticsEvent::EVENT_VISIT,
            'visitor_id' => 'visitor-123',
            'session_id' => 'session-123',
            'user_id' => $user->id,
            'item_id' => null,
        ]);
    }

    public function test_it_does_not_create_duplicate_visit_for_same_session(): void
    {
        $user = User::factory()->create();

        $service = app(AnalyticsEventService::class);

        $service->recordVisit(
            'visitor-123',
            'session-123',
            $user
        );

        $service->recordVisit(
            'visitor-123',
            'session-123',
            $user
        );

        $this->assertSame(
            1,
            AnalyticsEvent::query()
                ->where('event', AnalyticsEvent::EVENT_VISIT)
                ->where('session_id', 'session-123')
                ->count()
        );
    }

    public function test_it_records_a_guest_visit_without_user(): void
    {
        app(AnalyticsEventService::class)->recordVisit(
            'visitor-123',
            'session-123'
        );

        $this->assertDatabaseHas('analytics_events', [
            'event' => AnalyticsEvent::EVENT_VISIT,
            'visitor_id' => 'visitor-123',
            'session_id' => 'session-123',
            'user_id' => null,
            'item_id' => null,
        ]);
    }

    public function test_it_records_prompt_access(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();

        app(AnalyticsEventService::class)->recordPromptAccess(
            'visitor-123',
            'session-123',
            $item,
            $user
        );

        $this->assertDatabaseHas('analytics_events', [
            'event' => AnalyticsEvent::EVENT_PROMPT_ACCESS,
            'visitor_id' => 'visitor-123',
            'session_id' => 'session-123',
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);
    }

    public function test_it_records_prompt_copy(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();

        app(AnalyticsEventService::class)->recordPromptCopy(
            'visitor-123',
            'session-123',
            $item,
            $user
        );

        $this->assertDatabaseHas('analytics_events', [
            'event' => AnalyticsEvent::EVENT_PROMPT_COPY,
            'visitor_id' => 'visitor-123',
            'session_id' => 'session-123',
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);
    }
}
