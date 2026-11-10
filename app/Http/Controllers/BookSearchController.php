<?php

namespace App\Http\Controllers;

use App\Services\GutenbergService;
use App\Services\GoogleBooksService;
use App\Services\InternetArchiveService;
use App\Services\OpenLibraryService;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class BookSearchController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $query = trim($request->validate([
            'q' => ['required', 'string', 'min:1'],
        ])['q']);

        $responses = Http::pool(function (Pool $pool) use ($query) {
            OpenLibraryService::request($pool, $query);
            GutenbergService::request($pool, $query);
            InternetArchiveService::request($pool, $query);
            GoogleBooksService::request($pool, $query);
        });

        $books = [];

        foreach ($responses as $name => $response) {
            if (! $response instanceof Response || $response->failed()) {
                continue;
            }

            $books = array_merge($books, match ($name) {
                'open_library' => OpenLibraryService::parse($response->body()),
                'gutenberg' => GutenbergService::parse($response->body()),
                'internet_archive' => InternetArchiveService::parse($response->body()),
                'google_books' => GoogleBooksService::parse($response->body()),
                default => [],
            });
        }

        $seen = [];
        $deduped = [];

        foreach ($books as $book) {
            $key = mb_strtolower(trim($book['title']));

            if (! isset($seen[$key])) {
                $seen[$key] = true;
                $deduped[] = $book;
            }
        }

        return response()->json([
            'total' => count($deduped),
            'books' => $deduped,
        ]);
    }
}
