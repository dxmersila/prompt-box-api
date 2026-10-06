<?php

namespace Tests\Feature;

use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_items_endpoint_returns_only_visible_items(): void
    {
        Item::factory()->create([
            'title' => 'Visible Item',
            'is_visible' => true,
        ]);

        Item::factory()->create([
            'title' => 'Hidden Item',
            'is_visible' => false,
        ]);

        $response = $this->getJson('/api/v1/items');

        $response
            ->assertStatus(200)
            ->assertJsonFragment([
                'title' => 'Visible Item',
            ])
            ->assertJsonMissing([
                'title' => 'Hidden Item',
            ]);
    }

    public function test_items_endpoint_can_search_by_title(): void
    {
        Item::factory()->create([
            'title' => 'Laravel Template Item',
            'description' => 'Test description',
            'is_visible' => true,
        ]);

        Item::factory()->create([
            'title' => 'Another Item',
            'description' => 'Something else',
            'is_visible' => true,
        ]);

        $response = $this->getJson('/api/v1/items?search=Laravel');

        $response
            ->assertStatus(200)
            ->assertJsonFragment([
                'title' => 'Laravel Template Item',
            ])
            ->assertJsonMissing([
                'title' => 'Another Item',
            ]);
    }

    public function test_items_endpoint_can_filter_by_free_access_type(): void
    {
        Item::factory()->create([
            'title' => 'Free Item',
            'access_type' => 'free',
            'is_visible' => true,
        ]);

        Item::factory()->create([
            'title' => 'Paid Item',
            'access_type' => 'paid',
            'is_visible' => true,
        ]);

        $response = $this->getJson('/api/v1/items?access_type=free');

        $response
            ->assertOk()
            ->assertJsonFragment([
                'title' => 'Free Item',
            ])
            ->assertJsonMissing([
                'title' => 'Paid Item',
            ]);
    }

    public function test_items_endpoint_can_filter_by_paid_access_type(): void
    {
        Item::factory()->create([
            'title' => 'Free Item',
            'access_type' => 'free',
            'is_visible' => true,
        ]);

        Item::factory()->create([
            'title' => 'Paid Item',
            'access_type' => 'paid',
            'is_visible' => true,
        ]);

        $response = $this->getJson('/api/v1/items?access_type=paid');

        $response
            ->assertOk()
            ->assertJsonFragment([
                'title' => 'Paid Item',
            ])
            ->assertJsonMissing([
                'title' => 'Free Item',
            ]);
    }

    public function test_items_endpoint_can_sort_latest(): void
    {
        Item::factory()->create([
            'title' => 'Old Item',
            'is_visible' => true,
            'created_at' => now()->subDay(),
        ]);

        Item::factory()->create([
            'title' => 'New Item',
            'is_visible' => true,
            'created_at' => now(),
        ]);

        $response = $this->getJson('/api/v1/items?sort=latest');

        $response
            ->assertStatus(200)
            ->assertJsonPath('data.0.title', 'New Item');
    }

    public function test_items_endpoint_defaults_to_latest_order(): void
    {
        $older = Item::factory()->create([
            'title' => 'Older Item',
            'is_visible' => true,
            'created_at' => now()->subDay(),
        ]);

        $newer = Item::factory()->create([
            'title' => 'Newer Item',
            'is_visible' => true,
            'created_at' => now(),
        ]);

        $response = $this->getJson('/api/v1/items');

        $response
            ->assertOk()
            ->assertJsonPath('data.0.id', $newer->id)
            ->assertJsonPath('data.1.id', $older->id);
    }

    public function test_items_endpoint_default_order_is_deterministic_when_created_at_matches(): void
    {
        $createdAt = now();

        $first = Item::factory()->create([
            'title' => 'First Item',
            'is_visible' => true,
            'created_at' => $createdAt,
        ]);

        $second = Item::factory()->create([
            'title' => 'Second Item',
            'is_visible' => true,
            'created_at' => $createdAt,
        ]);

        $response = $this->getJson('/api/v1/items');

        $response
            ->assertOk()
            ->assertJsonPath('data.0.id', $second->id)
            ->assertJsonPath('data.1.id', $first->id);
    }

    public function test_items_endpoint_does_not_expose_item_content(): void
    {
        Item::factory()->create([
            'title' => 'Public Item',
            'is_visible' => true,
            'content' => 'SECRET PROTECTED CONTENT',
        ]);

        $response = $this->getJson('/api/v1/items');

        $response
            ->assertOk()
            ->assertJsonFragment([
                'title' => 'Public Item',
            ])
            ->assertJsonMissing([
                'content' => 'SECRET PROTECTED CONTENT',
            ]);
    }

    public function test_items_latest_sort_is_deterministic_when_created_at_matches(): void
    {
        $createdAt = now();

        $firstItem = Item::factory()->create([
            'created_at' => $createdAt,
            'is_visible' => true,
        ]);

        $secondItem = Item::factory()->create([
            'created_at' => $createdAt,
            'is_visible' => true,
        ]);

        $response = $this->getJson('/api/v1/items?sort=latest');

        $response
            ->assertOk()
            ->assertJsonPath('data.0.id', $secondItem->id)
            ->assertJsonPath('data.1.id', $firstItem->id);
    }

    public function test_items_oldest_sort_is_deterministic_when_created_at_matches(): void
    {
        $createdAt = now();

        $firstItem = Item::factory()->create([
            'created_at' => $createdAt,
            'is_visible' => true,
        ]);

        $secondItem = Item::factory()->create([
            'created_at' => $createdAt,
            'is_visible' => true,
        ]);

        $response = $this->getJson('/api/v1/items?sort=oldest');

        $response
            ->assertOk()
            ->assertJsonPath('data.0.id', $firstItem->id)
            ->assertJsonPath('data.1.id', $secondItem->id);
    }

    public function test_items_title_sort_is_deterministic_when_titles_match(): void
    {
        $firstItem = Item::factory()->create([
            'title' => 'Same Title',
            'is_visible' => true,
        ]);

        $secondItem = Item::factory()->create([
            'title' => 'Same Title',
            'is_visible' => true,
        ]);

        $response = $this->getJson('/api/v1/items?sort=title');

        $response
            ->assertOk()
            ->assertJsonPath('data.0.id', $firstItem->id)
            ->assertJsonPath('data.1.id', $secondItem->id);
    }
}
