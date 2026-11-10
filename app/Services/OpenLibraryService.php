<?php

namespace App\Services;

use Illuminate\Http\Client\Pool;

class OpenLibraryService
{
    private const BASE_URL = 'https://openlibrary.org';

    public static function request(Pool $pool, string $query): void
    {
        $pool->as('open_library')->timeout(10)->get(self::BASE_URL.'/search.json', [
            'q' => $query,
            'limit' => 10,
            'fields' => 'key,title,author_name,cover_i,first_publish_year,isbn,subject',
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

            foreach (array_slice($data['docs'] ?? [], 0, 10) as $doc) {
                $coverUrl = null;

                if (! empty($doc['cover_i'])) {
                    $coverUrl = "https://covers.openlibrary.org/b/id/{$doc['cover_i']}-M.jpg";
                }

                $tags = [];
                foreach ($doc['subject'] ?? [] as $subject) {
                    if (is_string($subject) && str_contains(mb_strtolower($subject), 'public domain')) {
                        $tags[] = 'Public Domain';
                        break;
                    }
                }

                $results[] = [
                    'id' => 'ol-'.($doc['key'] ?? ''),
                    'title' => $doc['title'] ?? 'Unknown',
                    'author' => implode(', ', $doc['author_name'] ?? ['Unknown']),
                    'cover' => $coverUrl,
                    'year' => $doc['first_publish_year'] ?? null,
                    'source' => 'Open Library',
                    'link' => self::BASE_URL.($doc['key'] ?? ''),
                    'tags' => $tags,
                    'readable' => true,
                ];
            }

            return $results;
        } catch (\Throwable) {
            return [];
        }
    }
}