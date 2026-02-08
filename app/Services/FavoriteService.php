<?php

namespace App\Services;

use App\Models\Book;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class FavoriteService
{
    public function getFavorites(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $user->books()->paginate($perPage);
    }

    public function addFavorite(User $user, Book $book): void
    {
        $user->books()->syncWithoutDetaching([$book->id]);
    }

    public function removeFavorite(User $user, Book $book): void
    {
        $user->books()->detach($book->id);
    }
}
