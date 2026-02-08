<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SearchBookTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_search_books(): void
    {
        $user = User::factory()->create(['is_admin' => true]);

        Http::fake([
            'googleapis.com/*' => Http::response([
                'totalItems' => 1,
                'items' => [
                    [
                        'volumeInfo' => [
                            'title' => 'Test Book',
                            'authors' => ['Test Author'],
                            'industryIdentifiers' => [
                                ['type' => 'ISBN_13', 'identifier' => '1234567890123']
                            ],
                            'imageLinks' => [
                                'thumbnail' => 'http://example.com/cover.jpg'
                            ]
                        ]
                    ]
                ]
            ], 200),
        ]);

        $response = $this->actingAs($user)->getJson(route('v1.books.search', ['query' => 'test']));

        $response->assertStatus(200)
            ->assertJsonPath('data.0.title', 'Test Book')
            ->assertJsonPath('data.0.isbn', '1234567890123');
    }

    public function test_non_admin_cannot_search_books(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($user)->getJson(route('v1.books.search', ['query' => 'test']));

        $response->assertStatus(403);
    }

    public function test_search_requires_query_parameter(): void
    {
        $user = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($user)->getJson(route('v1.books.search'));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['query']);
    }

    public function test_search_handles_quota_exceeded(): void
    {
        $user = User::factory()->create(['is_admin' => true]);

        // Clear cache to ensure we hit the API
        Cache::forget('google_books_search_' . md5('test' . '0' . '10'));

        Http::fake([
            'googleapis.com/*' => Http::response([
                'error' => [
                    'code' => 429,
                    'message' => 'Quota exceeded',
                ]
            ], 429),
        ]);

        $response = $this->actingAs($user)->getJson(route('v1.books.search', ['query' => 'test']));

        $response->assertStatus(429)
            ->assertJson(['message' => 'Google Books API quota exceeded. Please try again later.']);
    }

    public function test_search_handles_other_api_errors(): void
    {
        $user = User::factory()->create(['is_admin' => true]);

        // Clear cache
        Cache::forget('google_books_search_' . md5('test' . '0' . '10'));

        Http::fake([
            'googleapis.com/*' => Http::response([
                'error' => [
                    'message' => 'Internal Server Error',
                ]
            ], 500),
        ]);

        $response = $this->actingAs($user)->getJson(route('v1.books.search', ['query' => 'test']));

        $response->assertStatus(500)
            ->assertJson(['message' => 'Internal Server Error']);
    }

    public function test_search_results_are_cached(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $query = 'cached';

        // Clear cache first
        Cache::forget('google_books_search_' . md5($query . '0' . '10'));

        Http::fake([
            'googleapis.com/*' => Http::sequence()
                ->push(['totalItems' => 1, 'items' => [['volumeInfo' => ['title' => 'Cached Book']]]], 200)
                ->push(['totalItems' => 1, 'items' => [['volumeInfo' => ['title' => 'Different Book']]]], 200),
        ]);

        // First call
        $this->actingAs($user)->getJson(route('v1.books.search', ['query' => $query]));
        
        // Second call (should use cache)
        $response = $this->actingAs($user)->getJson(route('v1.books.search', ['query' => $query]));

        $response->assertStatus(200)
            ->assertJsonPath('data.0.title', 'Cached Book');
            
        // Ensure Http::get was called only once for this URL
        Http::assertSentCount(1);
    }
}
