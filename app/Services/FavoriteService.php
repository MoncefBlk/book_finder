<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class FavoriteService
{
    public function getFavorites(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $user->books()->paginate($perPage);
    }

    public function addFavorite(User $user, int $bookId): void
    {
        $user->books()->syncWithoutDetaching([$bookId]);
    }

    public function removeFavorite(User $user, int $bookId): void
    {
        $user->books()->detach($bookId);
    }
}
