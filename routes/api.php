<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\UploadController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/auth', [AuthController::class, 'isLoggedIn']);

Route::middleware('auth:api')->group(function ($router) {
    Route::resources([
        'authors' => AuthorController::class,
        'books' => BookController::class,
    ]);
    
    Route::get('/search', [SearchController::class, 'search']);
    Route::post('/upload', [UploadController::class, 'updateFile']);
    Route::get('/signout', [AuthController::class, 'logout']);

});