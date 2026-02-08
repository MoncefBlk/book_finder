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
            'volumeInfo' => [
                'title' => 'Harry Potter and the Philosopher\'s Stone',
                'authors' => ['J. K. Rowling'],
                'industryIdentifiers' => [
                    ['type' => 'ISBN_13', 'identifier' => '9780747532743']
                ],
                'imageLinks' => [
                    'thumbnail' => 'http://example.com/cover.jpg'
                ]
            ]
        ];

        $response = $this->actingAs($user)->postJson(route('v1.books.import'), $payload);

        $response->assertStatus(201)
            ->assertJsonPath('title', 'Harry Potter and the Philosopher\'s Stone');

        $this->assertDatabaseHas('books', [
            'title' => 'Harry Potter and the Philosopher\'s Stone',
        ]);
        
        $book = Book::where('title', 'Harry Potter and the Philosopher\'s Stone')->first();
        $this->assertEquals('J. K. Rowling', $book->author);
        $this->assertEquals('http://example.com/cover.jpg', $book->cover_url);
    }

    public function test_can_list_books(): void
    {
        $user = User::factory()->create();

        $importBookAction = app(ImportBookAction::class);
        
        $book1Data = [
            'title' => 'Book 1',
            'authors' => ['Author 1'],
            'industryIdentifiers' => [],
            'imageLinks' => ['thumbnail' => 'url1']
        ];
        
        $book2Data = [
            'title' => 'Book 2',
            'authors' => ['Author 2'],
            'industryIdentifiers' => [],
            'imageLinks' => ['thumbnail' => 'url2']
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
            'volumeInfo' => [
                'authors' => ['J. K. Rowling'],
            ]
        ];

        $response = $this->actingAs($user)->postJson(route('v1.books.import'), $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['volumeInfo']);
    }

    public function test_import_fails_with_duplicate_identifier(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        
        $payload1 = [
            'volumeInfo' => [
                'title' => 'Book 1',
                'industryIdentifiers' => [
                    ['type' => 'ISBN_13', 'identifier' => '1234567890']
                ]
            ]
        ];
        $this->actingAs($user)->postJson(route('v1.books.import'), $payload1)->assertStatus(201);

        $payload2 = [
            'volumeInfo' => [
                'title' => 'Book 2',
                'industryIdentifiers' => [
                    ['type' => 'ISBN_13', 'identifier' => '1234567890']
                ]
            ]
        ];
        
        $response = $this->actingAs($user)->postJson(route('v1.books.import'), $payload2);
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['volumeInfo.industryIdentifiers']);
    }

    public function test_can_import_with_isbn_only(): void
    {
        $user = User::factory()->create(['is_admin' => true]);

        $payload = [
            'volumeInfo' => [
                'industryIdentifiers' => [
                    ['type' => 'ISBN_13', 'identifier' => '9780747532743']
                ],
            ]
        ];

        $response = $this->actingAs($user)->postJson(route('v1.books.import'), $payload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('books', [
            'title' => null,
        ]);
    }
}
