<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Book\GetAllBooksAction;
use App\Actions\Book\ImportBookAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ImportBookRequest;
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
     * Import a book from Google Books API results into local DB
     */
    public function import(ImportBookRequest $request, ImportBookAction $importBookAction): JsonResponse
    {
        $volumeInfo = $request->input('volumeInfo') ?? [];
        
        $book = $importBookAction->execute($volumeInfo);

        return response()->json($book, 201);
    }
}
