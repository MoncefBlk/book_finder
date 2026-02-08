<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Book\GetAllBooksAction;
use App\Actions\Book\ImportBookAction;
use App\Actions\Book\SearchBooksAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ImportBookRequest;
use App\Http\Requests\Api\V1\SearchBooksRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookController extends Controller
{
    /**
     * List all books stored in local database
     */
    public function index(Request $request, GetAllBooksAction $getAllBooksAction): JsonResponse
    {
        $perPage = $request->query('per_page', 15);
        $books = $getAllBooksAction->execute($perPage);

        return response()->json($books);
    }

    /**
     * Search for books via Google Books API
     */
    public function search(SearchBooksRequest $request, SearchBooksAction $searchBooksAction): JsonResponse
    {
        $query = $request->input('query');
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);
        $startIndex = ($page - 1) * $perPage;

        $data = $searchBooksAction->execute($query, $startIndex, $perPage);
        
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $data['items'],
            $data['totalItems'],
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return response()->json($paginator);
    }

    /**
     * Import a book from Google Books API results into local DB
     */
    public function import(ImportBookRequest $request, ImportBookAction $importBookAction): JsonResponse
    {
        $data = $request->validated();
        
        $book = $importBookAction->execute($data);

        return response()->json($book, 201);
    }
}
