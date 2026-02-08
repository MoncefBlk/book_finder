<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BookController;
use App\Http\Controllers\Api\V1\FavoriteController;

Route::prefix('v1')->name('v1.')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

        // Book Management
        Route::get('/books', [BookController::class, 'index'])->name('books.index');
        Route::get('/search', [BookController::class, 'search'])
            ->name('books.search')
            ->middleware('throttle:30,1');
        Route::post('/books/import', [BookController::class, 'import'])->name('books.import');

        // Favorites Management
        Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
        Route::post('/favorites/{book}', [FavoriteController::class, 'store'])->name('favorites.store');
        Route::delete('/favorites/{book}', [FavoriteController::class, 'destroy'])->name('favorites.destroy');
    });
});
