<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpKernel\Exception\HttpException;

class GoogleBooksService
{
    protected string $baseUrl = 'https://www.googleapis.com/books/v1/volumes';

    public function search(string $query, int $startIndex = 0, int $maxResults = 10): array
    {
        return Cache::remember('google_books_search_' . md5($query . $startIndex . $maxResults), 3600, function () use ($query, $startIndex, $maxResults) {
            $queryParams = [
                'q' => $query,
                'startIndex' => $startIndex,
                'maxResults' => $maxResults,
                'fields' => 'totalItems,items(volumeInfo(title,authors,industryIdentifiers,imageLinks/thumbnail))',
            ];

            // Add API Key if available to increase quota
            if ($key = config('services.google_books.key')) {
                $queryParams['key'] = $key;
            }

            $response = Http::get($this->baseUrl, $queryParams);

            if ($response->failed()) {
                Log::error('Google Books API Error', ['body' => $response->body()]);

                $statusCode = $response->status();
                $errorMessage = $response->json('error.message') ?? 'Google Books API request failed.';

                if ($statusCode === 429) {
                    $errorMessage = 'Google Books API quota exceeded. Please try again later.';
                }

                throw new HttpException($statusCode, $errorMessage);
            }

            $data = $response->json();
            $items = $data['items'] ?? [];
            $totalItems = $data['totalItems'] ?? 0;

            $formattedItems = array_map(function ($item) {
                $volumeInfo = $item['volumeInfo'] ?? [];
                
                $authors = $volumeInfo['authors'] ?? ['Unknown Author'];
                $author = is_array($authors) ? implode(', ', $authors) : $authors;

                $identifiers = $volumeInfo['industryIdentifiers'] ?? [];
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

                $coverUrl = $volumeInfo['imageLinks']['thumbnail'] ?? null;

                return [
                    'title' => $volumeInfo['title'] ?? 'Unknown Title',
                    'author' => $author,
                    'isbn' => $isbn,
                    'cover_url' => $coverUrl,
                ];
            }, $items);

            return [
                'totalItems' => $totalItems,
                'items' => $formattedItems
            ];
        });
    }
}
