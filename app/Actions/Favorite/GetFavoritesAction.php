<?php

namespace App\Actions\Favorite;

use App\Models\User;
use App\Services\FavoriteService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetFavoritesAction
{
    public function __construct(protected FavoriteService $favoriteService)
    {
    }

    public function execute(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $this->favoriteService->getFavorites($user, $perPage);
    }
}
