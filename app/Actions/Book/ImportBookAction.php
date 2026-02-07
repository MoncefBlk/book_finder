<?php

namespace App\Actions\Book;

use App\Models\Book;
use App\Services\BookService;

class ImportBookAction
{
    public function __construct(protected BookService $bookService)
    {
    }

    public function execute(array $data): Book
    {
        return $this->bookService->importBook($data);
    }
}
