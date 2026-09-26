<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Item;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminItemCreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_create_item(): void
    {
        $response = $this->postJson('/api/v1/admin/items', [
            'title' => 'Test Item',
        ]);

        $response->assertStatus(401);
    }

    public function test_regular_user_cannot_create_item(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $response = $this
            ->actingAs($user, 'sanctum')
            ->postJson('/api/v1/admin/items', [
                'title' => 'Test Item',
            ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_reuse_existing_tag_without_creating_duplicate(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $tag = Tag::factory()->create([
            'name' => 'Laravel',
            'slug' => 'laravel',
        ]);

        $firstResponse = $this
            ->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/admin/items', [
                'title' => 'First Item',
                'tags' => ['Laravel'],
            ]);

        $firstResponse->assertStatus(201);

        $secondResponse = $this
            ->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/admin/items', [
                'title' => 'Second Item',
                'tags' => ['laravel'],
            ]);

        $secondResponse->assertStatus(201);

        $this->assertDatabaseCount('tags', 1);

        $firstItem = Item::query()
            ->where('title', 'First Item')
            ->firstOrFail();

        $secondItem = Item::query()
            ->where('title', 'Second Item')
            ->firstOrFail();

        $this->assertDatabaseHas('item_tag', [
            'item_id' => $firstItem->id,
            'tag_id' => $tag->id,
        ]);

        $this->assertDatabaseHas('item_tag', [
            'item_id' => $secondItem->id,
            'tag_id' => $tag->id,
        ]);
    }

    public function test_admin_can_create_item_with_persian_tag_slug(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this
            ->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/admin/items', [
                'title' => 'Test Item',
                'tags' => ['پرامپت حرفه‌ای'],
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('tags', [
            'name' => 'پرامپت حرفه‌ای',
            'slug' => 'پرامپت-حرفه‌ای',
        ]);
    }

    public function test_admin_can_create_item_with_manual_slug_unchanged(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this
            ->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/admin/items', [
                'title' => 'راهنمای ساخت پرامپت',
                'slug' => 'اسلاگ_یک',
            ]);

        $response
            ->assertStatus(201)
            ->assertJson([
                'data' => [
                    'title' => 'راهنمای ساخت پرامپت',
                    'slug' => 'اسلاگ_یک',
                ],
            ]);

        $this->assertDatabaseHas('items', [
            'title' => 'راهنمای ساخت پرامپت',
            'slug' => 'اسلاگ_یک',
        ]);
    }

    public function test_admin_can_create_item_with_duplicate_persian_tag_slug(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        Tag::factory()->create([
            'name' => 'Prompt',
            'slug' => 'پرامپت',
        ]);

        $response = $this
            ->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/admin/items', [
                'title' => 'Test Item',
                'tags' => ['پرامپت'],
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('tags', [
            'name' => 'پرامپت',
            'slug' => 'پرامپت-2',
        ]);
    }

    public function test_admin_can_create_item_with_persian_auto_generated_slug(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this
            ->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/admin/items', [
                'title' => 'راهنمای ساخت پرامپت حرفه‌ای',
            ]);

        $response
            ->assertStatus(201)
            ->assertJson([
                'data' => [
                    'title' => 'راهنمای ساخت پرامپت حرفه‌ای',
                    'slug' => 'راهنمای-ساخت-پرامپت-حرفه‌ای',
                ],
            ]);

        $this->assertDatabaseHas('items', [
            'title' => 'راهنمای ساخت پرامپت حرفه‌ای',
            'slug' => 'راهنمای-ساخت-پرامپت-حرفه‌ای',
        ]);
    }

    public function test_admin_can_create_item(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $category = Category::factory()->create();
        $tag = Tag::factory()->create([
            'name' => 'Laravel',
            'slug' => 'laravel',
        ]);

        $response = $this
            ->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/admin/items', [
                'title' => 'Test Item',
                'description' => 'Test item description.',
                'content' => 'Test item protected content.',
                'price' => 250000,
                'categories' => [$category->id],
                'tags' => ['laravel'],
            ]);

        $response
            ->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'slug',
                    'description',
                    'is_visible',
                    'price',
                    'categories',
                    'tags',
                    'media',
                ],
            ])
            ->assertJson([
                'data' => [
                    'title' => 'Test Item',
                    'slug' => 'test-item',
                    'description' => 'Test item description.',
                    'content' => 'Test item protected content.',
                    'is_visible' => false,
                ],
            ]);

        $item = Item::where('title', 'Test Item')->firstOrFail();

        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'title' => 'Test Item',
            'slug' => 'test-item',
            'content' => 'Test item protected content.',
            'is_visible' => false,
        ]);

        $this->assertDatabaseHas('category_item', [
            'item_id' => $item->id,
            'category_id' => $category->id,
        ]);

        $this->assertDatabaseHas('item_tag', [
            'item_id' => $item->id,
            'tag_id' => $tag->id,
        ]);

        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'price' => 250000,
        ]);
    }
}
