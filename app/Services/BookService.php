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
        $title = $data['title'] ?? null;
        $isbn = $data['industryIdentifiers'] ?? [];

        return Book::create([
            'title' => $title,
            'authors' => $data['authors'] ?? [],
            'isbn' => $isbn,
            'cover_url' => $data['imageLinks']['thumbnail'] ?? null,
        ]);
    }
}
