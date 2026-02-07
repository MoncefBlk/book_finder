<?php

namespace App\Actions\Book;

use App\Services\BookService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetAllBooksAction
{
    public function __construct(protected BookService $bookService)
    {
    }

    public function execute(int $perPage = 15): LengthAwarePaginator
    {
        return $this->bookService->getAllBooks($perPage);
    }
}
