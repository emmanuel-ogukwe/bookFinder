<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SearchApiTest extends TestCase
{
    public function test_search_aggregates_from_multiple_sources(): void
    {
        Http::fake([
            'openlibrary.org/*' => Http::response([
                'docs' => [[
                    'key' => '/works/OL123W',
                    'title' => 'Pride and Prejudice',
                    'author_name' => ['Jane Austen'],
                    'cover_i' => 123,
                    'first_publish_year' => 1813,
                    'subject' => ['classic'],
                ]],
            ]),
            'gutendex.com/*' => Http::response([
                'results' => [[
                    'id' => 1342,
                    'title' => 'Pride and Prejudice',
                    'authors' => [['name' => 'Austen, Jane']],
                    'copyright_year' => null,
                    'formats' => [
                        'text/html' => 'https://www.gutenberg.org/ebooks/1342',
                        'image/jpeg' => 'https://www.gutenberg.org/cache/epub/1342/pg1342.cover.medium.jpg',
                    ],
                ]],
            ]),
            'archive.org/*' => Http::response([
                'response' => [
                    'docs' => [[
                        'identifier' => 'mobydick',
                        'title' => 'Moby Dick',
                        'creator' => ['Herman Melville'],
                        'date' => '1851-10-18',
                    ]],
                ],
            ]),
            'googleapis.com/*' => Http::response([
                'items' => [[
                    'id' => 'GATSBY',
                    'volumeInfo' => [
                        'title' => 'The Great Gatsby',
                        'authors' => ['F. Scott Fitzgerald'],
                        'imageLinks' => ['thumbnail' => 'https://example.com/gatsby.jpg'],
                        'infoLink' => 'https://example.com/gatsby/info',
                        'publishedDate' => '1925',
                    ],
                    'accessInfo' => [
                        'viewability' => 'PARTIAL',
                        'pdf' => ['isAvailable' => true],
                        'epub' => ['isAvailable' => false],
                    ],
                ]],
            ]),
        ]);

        $response = $this->getJson('/api/search?q=test');

        $response->assertOk()
            ->assertJsonCount(3, 'books')
            ->assertJsonPath('total', 3);

        $books = collect($response->json('books'));

        $this->assertSame('Open Library', $books->firstWhere('title', 'Pride and Prejudice')['source']);
        $this->assertSame('Herman Melville', $books->firstWhere('title', 'Moby Dick')['author']);
        $this->assertSame('Internet Archive', $books->firstWhere('title', 'Moby Dick')['source']);
        $this->assertSame('Google Books', $books->firstWhere('title', 'The Great Gatsby')['source']);
        $this->assertTrue($books->firstWhere('title', 'The Great Gatsby')['readable']);

        $gatsby = $books->firstWhere('title', 'The Great Gatsby');
        $this->assertContains('Preview', $gatsby['tags']);
        $this->assertContains('PDF Available', $gatsby['tags']);
    }

    public function test_duplicate_titles_are_deduplicated(): void
    {
        Http::fake([
            'openlibrary.org/*' => Http::response([
                'docs' => [[
                    'key' => '/works/OL123W',
                    'title' => '  Moby Dick  ',
                    'author_name' => ['Herman Melville'],
                ]],
            ]),
            'gutendex.com/*' => Http::response([
                'results' => [[
                    'id' => 2701,
                    'title' => 'Moby Dick; Or, The Whale',
                    'authors' => [['name' => 'Melville, Herman']],
                    'formats' => [],
                ]],
            ]),
            'archive.org/*' => Http::response([
                'response' => ['docs' => []],
            ]),
            'googleapis.com/*' => Http::response([
                'items' => [[
                    'id' => 'MOBY',
                    'volumeInfo' => [
                        'title' => 'Moby Dick',
                        'authors' => ['Herman Melville'],
                        'infoLink' => 'https://example.com/moby',
                    ],
                    'accessInfo' => [
                        'viewability' => 'FULL_PUBLIC_DOMAIN',
                        'pdf' => ['isAvailable' => false],
                        'epub' => ['isAvailable' => false],
                    ],
                ]],
            ]),
        ]);

        $response = $this->getJson('/api/search?q=moby');

        $response->assertOk()
            ->assertJsonPath('total', 2)
            ->assertJsonCount(2, 'books');
    }

    public function test_failed_source_is_skipped(): void
    {
        Http::fake([
            'openlibrary.org/*' => Http::response([
                'docs' => [[
                    'key' => '/works/OL1W',
                    'title' => 'A Book',
                    'author_name' => ['An Author'],
                ]],
            ]),
            'gutendex.com/*' => Http::response([], 500),
            'archive.org/*' => Http::response(['response' => ['docs' => []]]),
            'googleapis.com/*' => Http::response(['items' => []]),
        ]);

        $response = $this->getJson('/api/search?q=book');

        $response->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonCount(1, 'books')
            ->assertJsonPath('books.0.source', 'Open Library');
    }

    public function test_missing_query_returns_validation_error(): void
    {
        $this->getJson('/api/search')->assertUnprocessable();
    }
}