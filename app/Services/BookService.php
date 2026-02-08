<?php

namespace App\Services;

use App\Models\Book;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class BookService
{
    public function getAllBooks(int $perPage = 15): LengthAwarePaginator
    {
        return Book::paginate($perPage);
    }

    public function importBook(array $data): Book
    {
        return Book::create([
            'title' => $data['title'] ?? null,
            'author' => $data['author'] ?? null,
            'isbn' => $data['isbn'] ?? null,
            'cover_url' => $data['cover_url'] ?? null,
        ]);
    }
}
