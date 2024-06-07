<?php

namespace App\Jobs;

use App\Models\Book;
use App\Models\Chapter;
use App\Models\Verse;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Request as GuzzleRequest;

class FetchVerses implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $success        = true;
        $message        = "";

        // Bible API has limit of 5000 requests per day
        $booksToUpdate = [
            // 'Genesis',
            // 'Exodus',
            // 'Leviticus',
            // 'Numbers',
            // 'Deuteronomy',
            // 'Joshua',
            // 'Judges',
            // 'Ruth',
            // '1 Samuel', '2 Samuel',
            // '1 Kings', '2 Kings',
            // '1 Chronicles', '2 Chronicles',
            // 'Ezra', 'Nehemiah', 'Esther', 'Job',
            'Psalms',
            // 'Proverbs',
            // 'Ecclesiastes', 'Song of Solomon',
            // 'Isaiah',
            // 'Jeremiah',
            // 'Lamentations',
            // 'Ezekiel',
            // 'Daniel',
            // 'Hosea', 'Joel', 'Amos', 'Obadiah', 'Jonah', 'Micah', 'Nahum', 
            // 'Habakkuk', 'Zephaniah', 'Haggai', 'Zechariah', 'Malachi',
        ];

        try {

            // Free Bible version
            $bibleId = '65eec8e0b60e656b-01';
            // Get all books
            $books = $this->makeApiRequest('/bibles/'.$bibleId.'/books', 'GET');

            foreach ($books->data as $book) {

                // Save book (shouldn't need this ever again)
                // $bookModel = Book::firstOrCreate([
                //     'name' => $book->name,
                //     'abbr' => $book->id,
                //     'new_testament' => 0 // should be dynamic but isn't
                // ]);
                $bookModel = Book::where('name', $book->name)->first();

                // Only update specific books
                if (!in_array($book->name, $booksToUpdate)) {
                    continue;
                }

                // Get all chapters
                $chapters = $this->makeApiRequest('/bibles/'.$bibleId.'/books/'.$book->id.'/chapters', 'GET');

                foreach ($chapters->data as $chapter) {

                    if ($chapter->number == 'intro') {
                        continue;
                    }

                    // Save chapter (shouldn't need this ever again)
                    // $chapterModel = Chapter::firstOrCreate([
                    //     'book_id' => $bookModel->id,
                    //     'number' => $chapter->number,
                    // ]);
                    $chapterModel = Chapter::where('number', $chapter->number)->where('book_id', $bookModel->id)->first();

                    // Get all verses
                    $verseUrl = '/bibles/'.$bibleId.'/chapters/'.$chapter->id.'/verses';
                    $allVerses = $this->makeApiRequest($verseUrl, 'GET');

                    foreach ($allVerses->data as $allVerse) {
// dd($allVerse);
                        echo $allVerse->reference.PHP_EOL;
                        // Look for $allVerse->reference in verses table
                        $verseModel = Verse::where('reference', $allVerse->reference)->first();
                        if ($verseModel) {
                            continue;
                        }

                        $verseUrl = '/bibles/'.$bibleId.'/verses/'.$allVerse->id;
                        $settings = '?content-type=json&include-notes=false&include-titles=false&include-chapter-numbers=false&include-verse-numbers=false&include-verse-spans=false&use-org-id=false';
                        $verse = $this->makeApiRequest($verseUrl.$settings, 'GET');
// dd($verse);
                        $verse = $verse->data;
                        $text = '';
                        foreach ($verse->content[0]->items as $phrase) {
                            if (property_exists($phrase, 'text')) {
                                $text .= $phrase->text;
                            } elseif (property_exists($phrase, 'items')) {
                                foreach ($phrase->items as $subPhrase) {
                                    if (property_exists($subPhrase, 'text')) {
                                        $text .= $subPhrase->text;
                                    } else {
                                        dd($subPhrase);
                                    }
                                }
                            } else {
                                dd($verse);
                            }
                        }

                        // $number is the third part of orgId split by period
                        $number = explode('.', $verse->orgId)[2];

                        // Save verse
                        Verse::firstOrCreate([
                            'chapter_id' => $chapterModel->id,
                            'translation_id' => 1,
                            'number' => $number,
                            'reference' => $verse->reference,
                            'text' => $text,
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
    private function makeApiRequest(string $endpoint, string $method): object
    {
        $baseUrl = env('BIBLE_API_HOST');

        $client     = new GuzzleClient();
        $endpoint   = $baseUrl.$endpoint; 
        $headers = [
            'api-key' => env('BIBLE_API_KEY'),
        ];

        $request        = new GuzzleRequest($method, $endpoint, $headers);
        $response       = $client->send($request);
        $contents       = $response->getBody()->getContents();
        $contentArray   = json_decode($contents);

        return $contentArray;
    }

}