<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/books', [BookController::class,'index'])->name('getBooks');
Route::post('/books', [BookController::class, 'store'])->name('postBook');
Route::put('/books/{id}', [BookController::class, 'update'])->name('updateBook');
Route::delete('/books/{id}', [BookController::class, 'destroy'])->name('deleteBook');
