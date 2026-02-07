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
        Route::get('/user', function (Request $request) {
            return $request->user();
        })->name('user');

        // Book Management
        Route::get('/books', [BookController::class, 'index'])->name('books.index');
        Route::post('/books/import', [BookController::class, 'import'])->name('books.import');

        // Favorites Management
        Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
        Route::post('/favorites/{book_id}', [FavoriteController::class, 'store'])->name('favorites.store');
        Route::delete('/favorites/{book_id}', [FavoriteController::class, 'destroy'])->name('favorites.destroy');
    });
});
