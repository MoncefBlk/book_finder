<?php

namespace App\Actions\Favorite;

use App\Models\Book;
use App\Models\User;
use App\Services\FavoriteService;

class RemoveFavoriteAction
{
    public function __construct(protected FavoriteService $favoriteService)
    {
    }

    public function execute(User $user, Book $book): void
    {
        $this->favoriteService->removeFavorite($user, $book);
    }
}
