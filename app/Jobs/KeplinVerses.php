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

class KeplinVerses implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $success        = true;
        $message        = "";

        // Keplin API has no limit for requests
        $booksToUpdate = [
            // 'Genesis',
            // 'Exodus', 'Leviticus', 'Numbers', 'Deuteronomy', 'Joshua', 'Judges', 'Ruth',
            // '1 Samuel', '2 Samuel','1 Kings', '2 Kings', '1 Chronicles', '2 Chronicles', 'Ezra', 'Nehemiah', 'Esther', 'Job',
            // 'Psalms', 'Proverbs', 'Ecclesiastes', 'Song of Songs', 
            // 'Isaiah', 'Jeremiah', 'Lamentations', 'Ezekiel', 'Daniel',
            // 'Hosea', 'Joel', 'Amos', 'Obadiah', 'Jonah', 'Micah', 'Nahum', 
            // 'Habakkuk', 'Zephaniah', 'Haggai', 'Zechariah', 'Malachi',
            // 'Matthew', 'Mark', 'Luke', 'John',
            // 'Acts', 'Romans', '1 Corinthians', '2 Corinthians', 'Galatians', 'Ephesians', 'Philippians', 'Colossians',
            // '1 Thessalonians', '2 Thessalonians', '1 Timothy', '2 Timothy', 'Titus', 'Philemon', 'Hebrews', 'James', '1 Peter', '2 Peter', '1 John', '2 John', '3 John', 'Jude', 'Revelation'
        ];

        try {

            // Available versions
            // $translationId = 'NIV';
            // $translationId = 'ESV';
            // $translationId = 'NLT';
            $translationId = 'KJV';

            // Insert translation if not already in database
            $translationModel = Translation::firstOrCreate([
                'name' => $translationId,
            ]);

            // Get all books
            $books = $this->makeApiRequest('/books', 'GET', $translationId);

            foreach ($books as $book) {

                $bookModel = Book::where('name', $book->name)->first();

                // Only update specific books
                if (!in_array($book->name, $booksToUpdate)) {
                    continue;
                }

                // Get all chapters
                $chapters = Chapter::where('book_id', $bookModel->id)->get();

                foreach ($chapters as $chapter) {

                    echo $bookModel->name.' '.$chapter->number.PHP_EOL;
                    // Get all verses
                    $verseUrl = '/books/'.$bookModel->id.'/chapters/'.$chapter->number;
                    $allVerses = $this->makeApiRequest($verseUrl, 'GET', $translationId);
// dd($allVerses);
                    foreach ($allVerses as $allVerse) {
// dd($allVerse);

                        // Save verse
                        Verse::firstOrCreate([
                            'chapter_id' => $chapter->id,
                            'translation_id' => $translationModel->id,
                            'number' => $allVerse->verseId,
                            'reference' => $bookModel->name.' '.$allVerse->chapterId.':'.$allVerse->verseId,
                            'text' => $allVerse->verse
                        ]);
                    }
                }
            }

        } catch (GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $message = json_decode($response->getBody()->getContents());
            $success = false;
            dd($e);
        } catch (\Exception $e) {
            $success = false;
            $message = $e->getMessage();
            dd($e);
        }

        if ($success) {
            echo 'Finished!'.PHP_EOL;
        }

        echo $message;
    }

    /**
     * Make a request to API
     */
    private function makeApiRequest(string $endpoint, string $method, string $translationId): array
    {
        $baseUrl = env('KEPLIN_API_HOST');

        $client     = new GuzzleClient();
        $endpoint   = $baseUrl.$endpoint.'?translation='.$translationId; 

        $request        = new GuzzleRequest($method, $endpoint);
        $response       = $client->send($request);
        $contents       = $response->getBody()->getContents();
        $contentArray   = json_decode($contents);

        return $contentArray;
    }

}