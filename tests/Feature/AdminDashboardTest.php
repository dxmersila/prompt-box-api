<?php

namespace Tests\Feature;

use App\Models\AnalyticsEvent;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_admin_dashboard(): void
    {
        $response = $this->getJson('/api/v1/admin/dashboard');

        $response->assertStatus(401);
    }

    public function test_regular_user_cannot_access_admin_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $response = $this
            ->actingAs($user, 'sanctum')
            ->getJson('/api/v1/admin/dashboard');

        $response->assertStatus(403);
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this
            ->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/admin/dashboard');

        $response
            ->assertStatus(200)
            ->assertJson([
                'users_count' => 1,
                'items_count' => 0,
                'categories_count' => 0,
            ]);
    }

    public function test_admin_dashboard_includes_analytics_for_current_periods(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $now = Carbon::create(2026, 10, 8, 12, 0, 0);

        Carbon::setTestNow($now);

        $visitorA = '11111111-1111-4111-8111-111111111111';
        $visitorB = '22222222-2222-4222-8222-222222222222';
        $visitorC = '33333333-3333-4333-8333-333333333333';
        $visitorD = '44444444-4444-4444-8444-444444444444';

        AnalyticsEvent::create([
            'event' => AnalyticsEvent::EVENT_VISIT,
            'visitor_id' => $visitorA,
            'session_id' => 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
            'created_at' => $now->copy()->subHour(),
        ]);

        AnalyticsEvent::create([
            'event' => AnalyticsEvent::EVENT_VISIT,
            'visitor_id' => $visitorA,
            'session_id' => 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb',
            'created_at' => $now->copy()->subHours(2),
        ]);

        AnalyticsEvent::create([
            'event' => AnalyticsEvent::EVENT_ITEM_ACCESS,
            'visitor_id' => $visitorB,
            'session_id' => 'cccccccc-cccc-4ccc-8ccc-cccccccccccc',
            'user_id' => $admin->id,
            'created_at' => $now->copy()->subHours(3),
        ]);

        AnalyticsEvent::create([
            'event' => AnalyticsEvent::EVENT_ITEM_COPY,
            'visitor_id' => $visitorB,
            'session_id' => 'cccccccc-cccc-4ccc-8ccc-cccccccccccc',
            'user_id' => $admin->id,
            'created_at' => $now->copy()->subHours(4),
        ]);

        AnalyticsEvent::create([
            'event' => AnalyticsEvent::EVENT_VISIT,
            'visitor_id' => $visitorB,
            'session_id' => 'dddddddd-dddd-4ddd-8ddd-dddddddddddd',
            'created_at' => $now->copy()->subDays(2),
        ]);

        AnalyticsEvent::create([
            'event' => AnalyticsEvent::EVENT_ITEM_ACCESS,
            'visitor_id' => $visitorB,
            'session_id' => 'dddddddd-dddd-4ddd-8ddd-dddddddddddd',
            'user_id' => $admin->id,
            'created_at' => $now->copy()->subDays(2)->addHour(),
        ]);

        AnalyticsEvent::create([
            'event' => AnalyticsEvent::EVENT_VISIT,
            'visitor_id' => $visitorC,
            'session_id' => 'eeeeeeee-eeee-4eee-8eee-eeeeeeeeeeee',
            'created_at' => $now->copy()->startOfMonth()->addDay(),
        ]);

        AnalyticsEvent::create([
            'event' => AnalyticsEvent::EVENT_ITEM_COPY,
            'visitor_id' => $visitorC,
            'session_id' => 'eeeeeeee-eeee-4eee-8eee-eeeeeeeeeeee',
            'user_id' => $admin->id,
            'created_at' => $now->copy()->startOfMonth()->addDay()->addHour(),
        ]);

        AnalyticsEvent::create([
            'event' => AnalyticsEvent::EVENT_VISIT,
            'visitor_id' => $visitorD,
            'session_id' => 'ffffffff-ffff-4fff-8fff-ffffffffffff',
            'created_at' => $now->copy()->subDays(40),
        ]);

        AnalyticsEvent::create([
            'event' => AnalyticsEvent::EVENT_ITEM_ACCESS,
            'visitor_id' => $visitorD,
            'session_id' => 'ffffffff-ffff-4fff-8fff-ffffffffffff',
            'user_id' => $admin->id,
            'created_at' => $now->copy()->subDays(40)->addHour(),
        ]);

        $response = $this
            ->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/admin/dashboard');

        $response
            ->assertStatus(200)
            ->assertJson([
                'users_count' => 1,
                'items_count' => 0,
                'categories_count' => 0,
                'analytics' => [
                    'today' => [
                        'visits' => 2,
                        'unique_visitors' => 1,
                        'item_access' => 1,
                        'item_copies' => 1,
                    ],
                    'week' => [
                        'visits' => 3,
                        'unique_visitors' => 2,
                        'item_access' => 2,
                        'item_copies' => 1,
                    ],
                    'month' => [
                        'visits' => 4,
                        'unique_visitors' => 3,
                        'item_access' => 2,
                        'item_copies' => 2,
                    ],
                    'year' => [
                        'visits' => 5,
                        'unique_visitors' => 4,
                        'item_access' => 3,
                        'item_copies' => 2,
                    ],
                ],
            ]);
    }
}
