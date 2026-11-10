<?php

namespace App\Services;

use Illuminate\Http\Client\Pool;

class GutenbergService
{
    private const BASE_URL = 'https://gutendex.com';

    public static function request(Pool $pool, string $query): void
    {
        $pool->as('gutenberg')->timeout(10)->get(self::BASE_URL.'/books', [
            'search' => $query,
        ]);
    }

    public static function parse(?string $body): array
    {
        if ($body === null || trim($body) === '') {
            return [];
        }

        try {
            $data = json_decode($body, true);
            $results = [];

            foreach (array_slice($data['results'] ?? [], 0, 10) as $book) {
                $formats = $book['formats'] ?? [];
                $authors = array_column($book['authors'] ?? [], 'name');

                $year = $book['copyright_year']
                    ?? $book['authors'][0]['birth_year']
                    ?? null;

                $results[] = [
                    'id' => 'gb-'.($book['id'] ?? ''),
                    'title' => $book['title'] ?? 'Unknown',
                    'author' => $authors ? implode(', ', $authors) : 'Unknown',
                    'cover' => $formats['image/jpeg'] ?? null,
                    'year' => $year,
                    'source' => 'Project Gutenberg',
                    'link' => $formats['text/html'] ?? $formats['text/plain'] ?? null,
                    'tags' => ['Public Domain', 'Free Download'],
                    'readable' => true,
                ];
            }

            return $results;
        } catch (\Throwable) {
            return [];
        }
    }
}