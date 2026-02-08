<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Favorite\AddFavoriteAction;
use App\Actions\Favorite\GetFavoritesAction;
use App\Actions\Favorite\RemoveFavoriteAction;
use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    /**
     * List all favorite books for authenticated user
     */
    public function index(Request $request, GetFavoritesAction $getFavoritesAction): JsonResponse
    {
        $perPage = $request->query('per_page', 15);
        $favorites = $getFavoritesAction->execute($request->user(), $perPage);

        return response()->json($favorites);
    }

    /**
     * Add a book to user’s favorites
     */
    public function store(Request $request, Book $book, AddFavoriteAction $addFavoriteAction): JsonResponse
    {
        
        $addFavoriteAction->execute($request->user(), $book);

        return response()->json(['message' => 'Book added to favorites']);
    }

    /**
     * Remove a book from favorites
     */
    public function destroy(Request $request, Book $book, RemoveFavoriteAction $removeFavoriteAction): JsonResponse
    {
        $removeFavoriteAction->execute($request->user(), $book);

        return response()->json(['message' => 'Book removed from favorites']);
    }
}
