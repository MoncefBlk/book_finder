<?php

namespace Tests\Feature\Api\V1;

use App\Actions\Book\ImportBookAction;
use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BookTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_import_book_from_google_api_structure(): void
    {
        $user = User::factory()->create(['is_admin' => true]);

        $payload = [
            'title' => 'Harry Potter and the Philosopher\'s Stone',
            'author' => 'J. K. Rowling',
            'isbn' => '9780747532743',
            'cover_url' => 'http://example.com/cover.jpg'
        ];

        $response = $this->actingAs($user)->postJson(route('v1.books.import'), $payload);

        $response->assertStatus(201)
            ->assertJsonPath('title', 'Harry Potter and the Philosopher\'s Stone');

        $this->assertDatabaseHas('books', [
            'title' => 'Harry Potter and the Philosopher\'s Stone',
            'author' => 'J. K. Rowling',
            'isbn' => '9780747532743',
            'cover_url' => 'http://example.com/cover.jpg',
        ]);
    }

    public function test_can_list_books(): void
    {
        $user = User::factory()->create();

        $importBookAction = app(ImportBookAction::class);
        
        $book1Data = [
            'title' => 'Book 1',
            'author' => 'Author 1',
            'isbn' => '111',
            'cover_url' => 'url1'
        ];
        
        $book2Data = [
            'title' => 'Book 2',
            'author' => 'Author 2',
            'isbn' => '222',
            'cover_url' => 'url2'
        ];

        $importBookAction->execute($book1Data);
        $importBookAction->execute($book2Data);

        $response = $this->actingAs($user)->getJson(route('v1.books.index'));

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.title', 'Book 1')
            ->assertJsonPath('data.1.title', 'Book 2');
    }

    public function test_import_fails_without_title_and_isbn(): void
    {
        $user = User::factory()->create(['is_admin' => true]);

        $payload = [
            'author' => 'J. K. Rowling',
        ];

        $response = $this->actingAs($user)->postJson(route('v1.books.import'), $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'isbn']);
    }

    public function test_import_fails_with_duplicate_identifier(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        
        $payload1 = [
            'title' => 'Book 1',
            'isbn' => '1234567890'
        ];
        $this->actingAs($user)->postJson(route('v1.books.import'), $payload1)->assertStatus(201);

        $payload2 = [
            'title' => 'Book 2',
            'isbn' => '1234567890'
        ];
        
        $response = $this->actingAs($user)->postJson(route('v1.books.import'), $payload2);
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['isbn']);
    }

    public function test_can_import_with_isbn_only(): void
    {
        $user = User::factory()->create(['is_admin' => true]);

        $payload = [
            'isbn' => '9780747532743',
        ];

        $response = $this->actingAs($user)->postJson(route('v1.books.import'), $payload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('books', [
            'title' => null,
            'isbn' => '9780747532743'
        ]);
    }
}
