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
        
        // Handle authors (array -> string)
        $authors = $data['authors'] ?? [];
        $author = is_array($authors) ? implode(', ', $authors) : $authors;
        
        // Handle ISBN (identifiers array -> string)
        $identifiers = $data['industryIdentifiers'] ?? [];
        $isbn = null;

        foreach ($identifiers as $identifier) {
            if (($identifier['type'] ?? '') === 'ISBN_13') {
                $isbn = $identifier['identifier'] ?? null;
                break;
            }
        }

        if (!$isbn && !empty($identifiers)) {
            $isbn = $identifiers[0]['identifier'] ?? null;
        }

        return Book::create([
            'title' => $title,
            'author' => $author,
            'isbn' => $isbn,
            'cover_url' => $data['imageLinks']['thumbnail'] ?? null,
        ]);
    }
}
