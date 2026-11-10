<?php

namespace App\Services;

use Illuminate\Http\Client\Pool;

class GoogleBooksService
{
    private const BASE_URL = 'https://www.googleapis.com/books/v1';

    public static function request(Pool $pool, string $query): void
    {
        $pool->as('google_books')->timeout(10)->get(self::BASE_URL.'/volumes', [
            'q' => $query,
            'maxResults' => 10,
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

            foreach (array_slice($data['items'] ?? [], 0, 10) as $item) {
                $volumeInfo = $item['volumeInfo'] ?? [];
                $access = $item['accessInfo'] ?? [];
                $imageLinks = $volumeInfo['imageLinks'] ?? [];

                $cover = $imageLinks['thumbnail'] ?? $imageLinks['smallThumbnail'] ?? null;

                $tags = match ($access['viewability'] ?? null) {
                    'FULL_PUBLIC_DOMAIN' => ['Public Domain'],
                    'ALL_PAGES' => ['Full Preview'],
                    'PARTIAL' => ['Preview'],
                    default => [],
                };

                if (! empty($access['pdf']['isAvailable'])) {
                    $tags[] = 'PDF Available';
                }

                if (! empty($access['epub']['isAvailable'])) {
                    $tags[] = 'EPUB Available';
                }

                $results[] = [
                    'id' => 'gbk-'.($item['id'] ?? ''),
                    'title' => $volumeInfo['title'] ?? 'Unknown',
                    'author' => implode(', ', $volumeInfo['authors'] ?? ['Unknown']),
                    'cover' => $cover,
                    'year' => isset($volumeInfo['publishedDate']) ? mb_substr((string) $volumeInfo['publishedDate'], 0, 4) : null,
                    'source' => 'Google Books',
                    'link' => $volumeInfo['infoLink'] ?? $volumeInfo['canonicalVolumeLink'] ?? '',
                    'tags' => $tags,
                    'readable' => (bool) $tags,
                ];
            }

            return $results;
        } catch (\Throwable) {
            return [];
        }
    }
}