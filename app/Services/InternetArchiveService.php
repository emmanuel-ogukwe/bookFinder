<?php

namespace App\Services;

use Illuminate\Http\Client\Pool;

class InternetArchiveService
{
    private const BASE_URL = 'https://archive.org';

    public static function request(Pool $pool, string $query): void
    {
        $pool->as('internet_archive')->timeout(10)->get(self::BASE_URL.'/advancedsearch.php', [
            'q' => "title:({$query}) AND mediatype:(texts OR books)",
            'fl[]' => 'identifier,title,creator,date,description',
            'rows' => 10,
            'output' => 'json',
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

            foreach (array_slice($data['response']['docs'] ?? [], 0, 10) as $doc) {
                $identifier = $doc['identifier'] ?? '';
                $creator = $doc['creator'] ?? [];

                if (is_array($creator)) {
                    $author = implode(', ', $creator);
                } elseif (is_string($creator)) {
                    $author = $creator;
                } else {
                    $author = 'Unknown';
                }

                $results[] = [
                    'id' => 'ia-'.$identifier,
                    'title' => $doc['title'] ?? 'Unknown',
                    'author' => $author,
                    'cover' => "https://archive.org/services/img/{$identifier}",
                    'year' => isset($doc['date']) ? mb_substr((string) $doc['date'], 0, 4) : null,
                    'source' => 'Internet Archive',
                    'link' => "https://archive.org/details/{$identifier}",
                    'tags' => ['Read Online'],
                    'readable' => true,
                ];
            }

            return $results;
        } catch (\Throwable) {
            return [];
        }
    }
}