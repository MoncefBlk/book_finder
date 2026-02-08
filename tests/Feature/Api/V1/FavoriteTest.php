<?php

namespace Tests\Feature\Api\V1;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_add_book_to_favorites(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson(route('v1.favorites.store', $book));

        $response->assertStatus(200)
            ->assertJson(['message' => 'Book added to favorites']);

        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    public function test_adding_non_existent_book_returns_404(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson(route('v1.favorites.store', 999));

        $response->assertStatus(404);
    }

    public function test_user_can_remove_book_from_favorites(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $user->books()->attach($book);

        Sanctum::actingAs($user);

        $response = $this->deleteJson(route('v1.favorites.destroy', $book));

        $response->assertStatus(200)
            ->assertJson(['message' => 'Book removed from favorites']);

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    public function test_user_can_list_favorites(): void
    {
        $user = User::factory()->create();
        $books = Book::factory()->count(3)->create();
        $user->books()->attach($books);

        Sanctum::actingAs($user);

        $response = $this->getJson(route('v1.favorites.index'));

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }
}
