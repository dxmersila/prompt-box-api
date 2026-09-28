<?php

namespace Tests\Feature;

use Tests\TestCase;

class ItemQueryValidationTest extends TestCase
{
    public function test_invalid_sort_returns_validation_error(): void
    {
        $response = $this->getJson('/api/v1/items?sort=invalid');

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'sort',
            ]);
    }

    public function test_invalid_access_type_returns_validation_error(): void
    {
        $response = $this->getJson('/api/v1/items?access_type=invalid');

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'access_type',
            ]);
    }

    public function test_search_cannot_exceed_max_length(): void
    {
        $search = str_repeat('a', 101);

        $response = $this->getJson("/api/v1/items?search={$search}");

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'search',
            ]);
    }

    public function test_page_must_be_at_least_one(): void
    {
        $response = $this->getJson('/api/v1/items?page=0');

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'page',
            ]);
    }
}
