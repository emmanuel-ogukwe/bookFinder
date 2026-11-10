<?php

use App\Http\Controllers\BookSearchController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/search', [BookSearchController::class, 'search']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
