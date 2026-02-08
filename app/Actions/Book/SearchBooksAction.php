<?php

namespace App\Actions\Book;

use App\Services\GoogleBooksService;

class SearchBooksAction
{
    public function __construct(
        protected GoogleBooksService $googleBooksService
    ) {}

    public function execute(string $query, int $startIndex = 0, int $maxResults = 10): array
    {
        return $this->googleBooksService->search($query, $startIndex, $maxResults);
    }
}
