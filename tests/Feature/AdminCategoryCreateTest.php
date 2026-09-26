<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCategoryCreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_create_category(): void
    {
        $response = $this->postJson('/api/v1/admin/categories', [
            'name' => 'Test Category',
        ]);

        $response->assertStatus(401);
    }

    public function test_regular_user_cannot_create_category(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $response = $this
            ->actingAs($user, 'sanctum')
            ->postJson('/api/v1/admin/categories', [
                'name' => 'Test Category',
            ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_create_category_with_persian_auto_generated_slug(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this
            ->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/admin/categories', [
                'name' => 'دسته‌بندی پرامپت حرفه‌ای',
            ]);

        $response
            ->assertStatus(201)
            ->assertJson([
                'data' => [
                    'name' => 'دسته‌بندی پرامپت حرفه‌ای',
                    'slug' => 'دسته‌بندی-پرامپت-حرفه‌ای',
                ],
            ]);

        $this->assertDatabaseHas('categories', [
            'name' => 'دسته‌بندی پرامپت حرفه‌ای',
            'slug' => 'دسته‌بندی-پرامپت-حرفه‌ای',
        ]);
    }

    public function test_admin_can_create_category(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this
            ->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/admin/categories', [
                'name' => 'Test Category',
            ]);

        $response
            ->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'slug',
                ],
            ])
            ->assertJson([
                'data' => [
                    'name' => 'Test Category',
                    'slug' => 'test-category',
                ],
            ]);

        $this->assertDatabaseHas('categories', [
            'name' => 'Test Category',
            'slug' => 'test-category',
        ]);
    }

    public function test_admin_cannot_create_category_with_duplicate_slug(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        Category::factory()->create([
            'name' => 'Existing Category',
            'slug' => 'existing-category',
        ]);

        $response = $this
            ->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/admin/categories', [
                'name' => 'Another Category',
                'slug' => 'existing-category',
            ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'slug',
            ]);
    }
}
