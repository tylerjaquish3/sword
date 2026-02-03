<?php

namespace App\Jobs;

use App\Models\Book;
use App\Models\Chapter;
use App\Models\Translation;
use App\Models\Verse;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use Illuminate\Support\Facades\Log;

class KeplinVerses implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Auth token for Keplin API
     */
    private ?string $authToken = null;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Get auth token before making requests
        // $this->authToken = $this->getAuthToken();
        $success        = true;
        $message        = "";

        // Keplin API has no limit for requests
        $booksToUpdate = [
            'Genesis',
            'Exodus', 'Leviticus', 'Numbers', 'Deuteronomy', 'Joshua', 'Judges', 'Ruth',
            '1 Samuel', '2 Samuel','1 Kings', '2 Kings', '1 Chronicles', '2 Chronicles', 'Ezra', 'Nehemiah', 'Esther', 'Job',
            'Psalms', 'Proverbs', 'Ecclesiastes', 'Song of Solomon', 
            'Isaiah', 'Jeremiah', 'Lamentations', 'Ezekiel', 'Daniel',
            'Hosea', 'Joel', 'Amos', 'Obadiah', 'Jonah', 'Micah', 'Nahum', 
            'Habakkuk', 'Zephaniah', 'Haggai', 'Zechariah', 'Malachi',
            'Matthew', 'Mark', 'Luke', 'John',
            'Acts', 'Romans', '1 Corinthians', '2 Corinthians', 'Galatians', 'Ephesians', 'Philippians', 'Colossians',
            '1 Thessalonians', '2 Thessalonians', '1 Timothy', '2 Timothy', 'Titus', 'Philemon', 'Hebrews', 'James', '1 Peter', '2 Peter', '1 John', '2 John', '3 John', 'Jude', 'Revelation'
        ];

        try {

            // Available versions
            // $translationId = 'NLT';
            // $translationId = 'NIV';
            $translationId = 'KJV';

            // Insert translation if not already in database
            $translationModel = Translation::firstOrCreate([
                'name' => $translationId,
            ]);
            Log::info('Using translation: ' . $translationId);

            // Get all books
            Log::info('Fetching books from API...');
            $books = $this->makeApiRequest('/books', 'GET', $translationId);
            Log::info('Retrieved ' . count($books) . ' books from API');

            foreach ($books as $book) {

                // Only update specific books
                if (!in_array($book->name, $booksToUpdate)) {
                    continue;
                }

                // Determine if New Testament (books 40-66 are NT)
                $isNewTestament = isset($book->id) && $book->id >= 40 ? 1 : 0;

                $bookModel = Book::firstOrCreate(
                    ['name' => $book->name],
                    [
                        'abbr' => $book->abbr ?? $book->abbreviation ?? substr($book->name, 0, 3),
                        'new_testament' => $isNewTestament,
                    ]
                );

                Log::info('Processing book: ' . $bookModel->name);

                // Fetch chapters from API and create if needed
                $chaptersUrl = '/books/' . $book->id . '/chapters';
                $apiChapters = $this->makeApiRequest($chaptersUrl, 'GET', $translationId);
                Log::info('Found ' . count($apiChapters) . ' chapters for ' . $bookModel->name . ' from API');

                foreach ($apiChapters as $apiChapter) {

                    $chapter = Chapter::firstOrCreate(
                        [
                            'book_id' => $bookModel->id,
                            'number' => $apiChapter->id ?? $apiChapter->chapterId ?? $apiChapter->number,
                        ]
                    );

                    Log::debug('Fetching verses for ' . $bookModel->name . ' ' . $chapter->number);
                    // Get all verses
                    $verseUrl = '/books/'.$book->id.'/chapters/'.$chapter->number;
                    $allVerses = $this->makeApiRequest($verseUrl, 'GET', $translationId);

                    $versesCreated = 0;
                    foreach ($allVerses as $allVerse) {

                        // Save verse
                        $verse = Verse::firstOrCreate([
                            'chapter_id' => $chapter->id,
                            'translation_id' => $translationModel->id,
                            'number' => $allVerse->verseId,
                            'reference' => $bookModel->name.' '.$allVerse->chapterId.':'.$allVerse->verseId,
                            'text' => $allVerse->verse
                        ]);

                        if ($verse->wasRecentlyCreated) {
                            $versesCreated++;
                        }
                    }

                    Log::debug('Chapter ' . $chapter->number . ': ' . count($allVerses) . ' verses fetched, ' . $versesCreated . ' new verses created');
                }

                Log::info('Completed book: ' . $bookModel->name);
            }

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();
            $message = $response->getBody()->getContents();
            $success = false;

            if ($statusCode === 429) {
                Log::error('API RATE LIMIT HIT! Status: 429 Too Many Requests', [
                    'response' => $message,
                    'headers' => $response->getHeaders(),
                ]);
            } else {
                Log::error('API Client Error', [
                    'status' => $statusCode,
                    'message' => $message,
                ]);
            }
        } catch (\Exception $e) {
            $success = false;
            $message = $e->getMessage();
            Log::error('KeplinVerses job failed with exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        if ($success) {
            Log::info('KeplinVerses job finished successfully!');
        } else {
            Log::error('KeplinVerses job finished with errors: ' . $message);
        }
    }

    /**
     * Make a request to API
     */
    private function makeApiRequest(string $endpoint, string $method, string $translationId): array
    {
        $baseUrl = env('KEPLIN_API_HOST');

        $client     = new GuzzleClient();
        $endpoint   = $baseUrl.$endpoint.'?translation='.$translationId; 

        // $headers = [
        //     'X-Authorization' => $this->authToken,
        // ];

        $request        = new GuzzleRequest($method, $endpoint);
        $response       = $client->send($request);

        // Check for rate limit headers
        $remainingRequests = $response->getHeader('X-RateLimit-Remaining');
        $rateLimit = $response->getHeader('X-RateLimit-Limit');
        if (!empty($remainingRequests)) {
            $remaining = $remainingRequests[0] ?? null;
            $limit = $rateLimit[0] ?? null;
            if ($remaining !== null && (int)$remaining < 10) {
                Log::warning('API rate limit warning: ' . $remaining . ' requests remaining out of ' . $limit);
            }
        }

        $contents       = $response->getBody()->getContents();
        $contentArray   = json_decode($contents);

        return $contentArray;
    }

    /**
     * Get auth token from Keplin API
     */
    private function getAuthToken(): string
    {
        $baseUrl = env('KEPLIN_API_HOST');
        $email = env('KEPLIN_API_EMAIL');
        $domain = env('APP_URL');

        Log::debug('Authenticating with Keplin API', ['email' => $email, 'domain' => $domain]);

        $client = new GuzzleClient();
        $endpoint = $baseUrl.'/auth';

        $response = $client->post($endpoint, [
            'json' => [
                'email' => $email,
                'domain' => $domain,
            ],
        ]);

        $contents = json_decode($response->getBody()->getContents());

        Log::debug('Auth token received');

        return $contents->token;
    }

}