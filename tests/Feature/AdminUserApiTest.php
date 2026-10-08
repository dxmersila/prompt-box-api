<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_list_users(): void
    {
        $response = $this->getJson('/api/v1/admin/users');

        $response->assertStatus(401);
    }

    public function test_regular_user_cannot_list_users(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $response = $this
            ->actingAs($user, 'sanctum')
            ->getJson('/api/v1/admin/users');

        $response->assertStatus(403);
    }

    public function test_admin_can_list_users(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        User::factory()->count(3)->create();

        $response = $this
            ->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/admin/users');

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'email',
                        'role',
                        'created_at',
                    ],
                ],
                'links',
                'meta' => [
                    'current_page',
                    'from',
                    'last_page',
                    'per_page',
                    'to',
                    'total',
                ],
            ])
            ->assertJsonPath('meta.per_page', 15)
            ->assertJsonPath('meta.total', 4);
    }

    public function test_admin_can_search_users_by_name_or_email(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'name' => 'System Admin',
            'email' => 'admin@example.com',
        ]);

        $nameMatch = User::factory()->create([
            'name' => 'Ali Ahmadi',
            'email' => 'ali@example.com',
        ]);

        $emailMatch = User::factory()->create([
            'name' => 'Reza Karimi',
            'email' => 'ali.karimi@example.com',
        ]);

        User::factory()->create([
            'name' => 'Sara Hosseini',
            'email' => 'sara@example.com',
        ]);

        $response = $this
            ->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/admin/users?search=ali');

        $response
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment([
                'id' => $nameMatch->id,
                'name' => 'Ali Ahmadi',
            ])
            ->assertJsonFragment([
                'id' => $emailMatch->id,
                'name' => 'Reza Karimi',
            ]);
    }

    public function test_admin_can_filter_users_by_role(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $targetAdmin = User::factory()->create([
            'role' => 'admin',
        ]);

        User::factory()->count(3)->create([
            'role' => 'user',
        ]);

        $response = $this
            ->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/admin/users?role=admin');

        $response
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment([
                'id' => $admin->id,
                'role' => 'admin',
            ])
            ->assertJsonFragment([
                'id' => $targetAdmin->id,
                'role' => 'admin',
            ]);
    }

    public function test_admin_can_sort_users_by_latest(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'created_at' => now()->subDays(3),
        ]);

        $oldest = User::factory()->create([
            'created_at' => now()->subDays(2),
        ]);

        $newest = User::factory()->create([
            'created_at' => now(),
        ]);

        $response = $this
            ->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/admin/users?sort=latest');

        $response
            ->assertOk()
            ->assertJsonPath('data.0.id', $newest->id)
            ->assertJsonPath('data.1.id', $oldest->id)
            ->assertJsonPath('data.2.id', $admin->id);
    }

    public function test_admin_can_sort_users_by_oldest(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'created_at' => now()->subDays(3),
        ]);

        $oldest = User::factory()->create([
            'created_at' => now()->subDays(2),
        ]);

        $newest = User::factory()->create([
            'created_at' => now(),
        ]);

        $response = $this
            ->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/admin/users?sort=oldest');

        $response
            ->assertOk()
            ->assertJsonPath('data.0.id', $admin->id)
            ->assertJsonPath('data.1.id', $oldest->id)
            ->assertJsonPath('data.2.id', $newest->id);
    }

    public function test_admin_can_sort_users_by_name(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'name' => 'Zulu Admin',
        ]);

        $alpha = User::factory()->create([
            'name' => 'Alpha User',
        ]);

        $beta = User::factory()->create([
            'name' => 'Beta User',
        ]);

        $response = $this
            ->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/admin/users?sort=name');

        $response
            ->assertOk()
            ->assertJsonPath('data.0.id', $alpha->id)
            ->assertJsonPath('data.1.id', $beta->id)
            ->assertJsonPath('data.2.id', $admin->id);
    }

    public function test_admin_user_index_rejects_invalid_filter_values(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this
            ->actingAs($admin, 'sanctum')
            ->getJson(
                '/api/v1/admin/users'
                . '?search=' . str_repeat('a', 101)
                . '&role=invalid'
                . '&sort=invalid'
            );

        $response->assertStatus(422);
    }

    public function test_admin_users_are_paginated_by_fifteen(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        User::factory()->count(15)->create();

        $response = $this
            ->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/admin/users');

        $response
            ->assertStatus(200)
            ->assertJsonCount(15, 'data')
            ->assertJsonPath('meta.per_page', 15)
            ->assertJsonPath('meta.total', 16)
            ->assertJsonPath('meta.last_page', 2);
    }

    public function test_admin_users_are_ordered_by_latest_then_id(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'created_at' => now()->subDays(3),
        ]);

        $firstSameTimestamp = User::factory()->create([
            'created_at' => now()->subDays(2),
        ]);

        $secondSameTimestamp = User::factory()->create([
            'created_at' => $firstSameTimestamp->created_at,
        ]);

        $newest = User::factory()->create([
            'created_at' => now(),
        ]);

        $response = $this
            ->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/admin/users?sort=latest');

        $response
            ->assertOk()
            ->assertJsonPath('data.0.id', $newest->id)
            ->assertJsonPath('data.1.id', $secondSameTimestamp->id)
            ->assertJsonPath('data.2.id', $firstSameTimestamp->id)
            ->assertJsonPath('data.3.id', $admin->id);
    }

    public function test_guest_cannot_show_user(): void
    {
        $user = User::factory()->create();

        $response = $this->getJson(
            "/api/v1/admin/users/{$user->id}"
        );

        $response->assertStatus(401);
    }

    public function test_regular_user_cannot_show_user(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $target = User::factory()->create();

        $response = $this
            ->actingAs($user, 'sanctum')
            ->getJson(
                "/api/v1/admin/users/{$target->id}"
            );

        $response->assertStatus(403);
    }

    public function test_admin_can_show_user(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $user = User::factory()->create([
            'name' => 'Ali Ahmadi',
            'email' => 'ali@example.com',
            'role' => 'user',
        ]);

        $response = $this
            ->actingAs($admin, 'sanctum')
            ->getJson(
                "/api/v1/admin/users/{$user->id}"
            );

        $response
            ->assertOk()
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.name', 'Ali Ahmadi')
            ->assertJsonPath('data.email', 'ali@example.com')
            ->assertJsonPath('data.role', 'user')
            ->assertJsonPath('data.created_at', $user->created_at->toJSON());
    }

    public function test_admin_can_update_user_name(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $user = User::factory()->create([
            'name' => 'Old Name',
        ]);

        $response = $this
            ->actingAs($admin, 'sanctum')
            ->patchJson(
                "/api/v1/admin/users/{$user->id}",
                [
                    'name' => 'New Name',
                ]
            );

        $response
            ->assertOk()
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.name', 'New Name');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'New Name',
        ]);
    }

    public function test_admin_can_update_user_role(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $response = $this
            ->actingAs($admin, 'sanctum')
            ->patchJson(
                "/api/v1/admin/users/{$user->id}",
                [
                    'role' => 'admin',
                ]
            );

        $response
            ->assertOk()
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.role', 'admin');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'role' => 'admin',
        ]);
    }

    public function test_admin_user_update_rejects_invalid_values(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $user = User::factory()->create();

        $response = $this
            ->actingAs($admin, 'sanctum')
            ->patchJson(
                "/api/v1/admin/users/{$user->id}",
                [
                    'name' => str_repeat('a', 256),
                    'role' => 'invalid',
                ]
            );

        $response->assertStatus(422);
    }
}
