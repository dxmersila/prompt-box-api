<?php

namespace Tests\Feature;

use App\Models\AnalyticsEvent;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsEventApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_record_a_visit(): void
    {
        $response = $this->postJson('/api/v1/analytics/events', [
            'event' => AnalyticsEvent::EVENT_VISIT,
            'visitor_id' => '550e8400-e29b-41d4-a716-446655440000',
            'session_id' => '550e8400-e29b-41d4-a716-446655440001',
        ]);

        $response->assertNoContent();

        $this->assertDatabaseHas('analytics_events', [
            'event' => AnalyticsEvent::EVENT_VISIT,
            'visitor_id' => '550e8400-e29b-41d4-a716-446655440000',
            'session_id' => '550e8400-e29b-41d4-a716-446655440001',
            'user_id' => null,
            'item_id' => null,
        ]);
    }

    public function test_duplicate_visit_for_same_session_is_ignored(): void
    {
        $payload = [
            'event' => AnalyticsEvent::EVENT_VISIT,
            'visitor_id' => '550e8400-e29b-41d4-a716-446655440000',
            'session_id' => '550e8400-e29b-41d4-a716-446655440001',
        ];

        $this->postJson('/api/v1/analytics/events', $payload)
            ->assertNoContent();

        $this->postJson('/api/v1/analytics/events', $payload)
            ->assertNoContent();

        $this->assertSame(
            1,
            AnalyticsEvent::query()
                ->where('event', AnalyticsEvent::EVENT_VISIT)
                ->where('session_id', $payload['session_id'])
                ->count()
        );
    }

    public function test_guest_can_record_prompt_copy(): void
    {
        $item = Item::factory()->create();

        $response = $this->postJson('/api/v1/analytics/events', [
            'event' => AnalyticsEvent::EVENT_PROMPT_COPY,
            'visitor_id' => '550e8400-e29b-41d4-a716-446655440000',
            'session_id' => '550e8400-e29b-41d4-a716-446655440001',
            'item_id' => $item->id,
        ]);

        $response->assertNoContent();

        $this->assertDatabaseHas('analytics_events', [
            'event' => AnalyticsEvent::EVENT_PROMPT_COPY,
            'visitor_id' => '550e8400-e29b-41d4-a716-446655440000',
            'session_id' => '550e8400-e29b-41d4-a716-446655440001',
            'user_id' => null,
            'item_id' => $item->id,
        ]);
    }

    public function test_authenticated_user_is_attached_to_prompt_copy(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/analytics/events', [
                'event' => AnalyticsEvent::EVENT_PROMPT_COPY,
                'visitor_id' => '550e8400-e29b-41d4-a716-446655440000',
                'session_id' => '550e8400-e29b-41d4-a716-446655440001',
                'item_id' => $item->id,
            ]);

        $response->assertNoContent();

        $this->assertDatabaseHas('analytics_events', [
            'event' => AnalyticsEvent::EVENT_PROMPT_COPY,
            'visitor_id' => '550e8400-e29b-41d4-a716-446655440000',
            'session_id' => '550e8400-e29b-41d4-a716-446655440001',
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);
    }

    public function test_analytics_events_are_rate_limited_per_ip(): void
    {
        $payload = [
            'event' => AnalyticsEvent::EVENT_VISIT,
            'visitor_id' => '550e8400-e29b-41d4-a716-446655440000',
            'session_id' => '550e8400-e29b-41d4-a716-446655440001',
        ];

        for ($i = 0; $i < 60; $i++) {
            $payload['session_id'] = sprintf(
                '550e8400-e29b-41d4-a716-%012d',
                $i + 1
            );

            $this->postJson('/api/v1/analytics/events', $payload)
                ->assertNoContent();
        }

        $payload['session_id'] = '550e8400-e29b-41d4-a716-446655440099';

        $this->postJson('/api/v1/analytics/events', $payload)
            ->assertTooManyRequests();

        $this->assertDatabaseCount('analytics_events', 60);
    }

    public function test_prompt_access_event_is_rejected(): void
    {
        $response = $this->postJson('/api/v1/analytics/events', [
            'event' => AnalyticsEvent::EVENT_PROMPT_ACCESS,
            'visitor_id' => '550e8400-e29b-41d4-a716-446655440000',
            'session_id' => '550e8400-e29b-41d4-a716-446655440001',
        ]);

        $response->assertUnprocessable();

        $this->assertDatabaseCount('analytics_events', 0);
    }

    public function test_visit_cannot_include_item_id(): void
    {
        $item = Item::factory()->create();

        $response = $this->postJson('/api/v1/analytics/events', [
            'event' => AnalyticsEvent::EVENT_VISIT,
            'visitor_id' => '550e8400-e29b-41d4-a716-446655440000',
            'session_id' => '550e8400-e29b-41d4-a716-446655440001',
            'item_id' => $item->id,
        ]);

        $response->assertUnprocessable();

        $this->assertDatabaseCount('analytics_events', 0);
    }

    public function test_prompt_copy_requires_item_id(): void
    {
        $response = $this->postJson('/api/v1/analytics/events', [
            'event' => AnalyticsEvent::EVENT_PROMPT_COPY,
            'visitor_id' => '550e8400-e29b-41d4-a716-446655440000',
            'session_id' => '550e8400-e29b-41d4-a716-446655440001',
        ]);

        $response->assertUnprocessable();

        $this->assertDatabaseCount('analytics_events', 0);
    }

    public function test_invalid_visitor_id_is_rejected(): void
    {
        $response = $this->postJson('/api/v1/analytics/events', [
            'event' => AnalyticsEvent::EVENT_VISIT,
            'visitor_id' => 'invalid-visitor-id',
            'session_id' => '550e8400-e29b-41d4-a716-446655440001',
        ]);

        $response->assertUnprocessable();

        $this->assertDatabaseCount('analytics_events', 0);
    }

    public function test_invalid_session_id_is_rejected(): void
    {
        $response = $this->postJson('/api/v1/analytics/events', [
            'event' => AnalyticsEvent::EVENT_VISIT,
            'visitor_id' => '550e8400-e29b-41d4-a716-446655440000',
            'session_id' => 'invalid-session-id',
        ]);

        $response->assertUnprocessable();

        $this->assertDatabaseCount('analytics_events', 0);
    }

    public function test_nonexistent_item_id_is_rejected(): void
    {
        $response = $this->postJson('/api/v1/analytics/events', [
            'event' => AnalyticsEvent::EVENT_PROMPT_COPY,
            'visitor_id' => '550e8400-e29b-41d4-a716-446655440000',
            'session_id' => '550e8400-e29b-41d4-a716-446655440001',
            'item_id' => 999999,
        ]);

        $response->assertUnprocessable();

        $this->assertDatabaseCount('analytics_events', 0);
    }
}
