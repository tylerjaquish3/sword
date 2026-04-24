<?php

namespace App\Jobs;

use App\Models\Book;
use App\Models\Translation;
use App\Models\Verse;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ImportEsvVerses implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private int $limit = 75) {}

    public function handle(): void
    {
        $client = new GuzzleClient([
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
                'Accept'     => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            ],
            'allow_redirects' => true,
        ]);

        $translation = Translation::firstOrCreate(
            ['name' => 'ESV'],
            ['description' => 'English Standard Version']
        );

        $books     = Book::with('chapters')->get();
        $processed = 0;

        foreach ($books as $book) {
            foreach ($book->chapters as $chapter) {
                // Skip chapters already imported
                $alreadyDone = Verse::where('chapter_id', $chapter->id)
                    ->where('translation_id', $translation->id)
                    ->exists();

                if ($alreadyDone) {
                    continue;
                }

                if ($processed >= $this->limit) {
                    Log::info("ImportEsvVerses: reached limit of {$this->limit}, stopping. Run again to continue.");
                    return;
                }

                $query = $book->name . ' ' . $chapter->number;

                try {
                    $response = $client->get('https://www.biblegateway.com/passage/', [
                        'query' => [
                            'search'  => $query,
                            'version' => 'ESV',
                        ],
                    ]);

                    $html   = $response->getBody()->getContents();
                    $verses = $this->parseVerses($html);

                    if (empty($verses)) {
                        Log::warning('ImportEsvVerses: no verses parsed for ' . $query);
                        $processed++;
                        continue;
                    }

                    $versesCreated = 0;
                    foreach ($verses as $verseNumber => $verseText) {
                        $verse = Verse::firstOrCreate(
                            [
                                'chapter_id'     => $chapter->id,
                                'translation_id' => $translation->id,
                                'number'         => $verseNumber,
                            ],
                            [
                                'reference' => $book->name . ' ' . $chapter->number . ':' . $verseNumber,
                                'text'      => $verseText,
                            ]
                        );

                        if ($verse->wasRecentlyCreated) {
                            $versesCreated++;
                        }
                    }

                    Log::info("ImportEsvVerses: {$query} — {$versesCreated} verses created ({$processed}/{$this->limit} this run)");
                    $processed++;

                    // Be polite to BibleGateway — 1 request per 500ms
                    usleep(500000);

                } catch (\Exception $e) {
                    Log::error('ImportEsvVerses: failed on ' . $query, [
                        'message' => $e->getMessage(),
                    ]);
                    $processed++;
                }
            }
        }

        Log::info('ImportEsvVerses: all chapters imported.');
    }

    private function parseVerses(string $html): array
    {
        $dom = new \DOMDocument();
        @$dom->loadHTML($html, LIBXML_NOERROR | LIBXML_NOWARNING);
        $xpath = new \DOMXPath($dom);

        // Verse spans have class "text BookAbbr-Chapter-Verse" e.g. "text Gen-1-1".
        // We intentionally do NOT filter by the en-ESV- id: verse 1 and poetry continuation
        // spans often have no id (only the first inline occurrence of a verse gets it).
        // Exclude h3 descendants — those are section headings that share the class but hold heading text.
        $spanNodes = $xpath->query(
            '//span[starts-with(@class, "text ") and not(ancestor::h3)]'
        );

        $verses = [];

        foreach ($spanNodes as $node) {
            if (!$node instanceof \DOMElement) {
                continue;
            }

            // Validate class shape: "text BookAbbr-ChapterNum-VerseNum"
            $class = $node->getAttribute('class');
            if (!preg_match('/^text \w+-\d+-(\d+)/', $class, $m)) {
                continue;
            }
            $verseNumber = (int) $m[1];

            if ($verseNumber === 0) {
                continue;
            }

            // Strip all <sup> elements (verse numbers, footnote markers, cross-reference letters)
            // and <span class="chapternum"> (the large "1" at chapter start)
            $toRemove = [];
            $noise    = $xpath->query('.//sup | .//span[@class="chapternum"]', $node);
            foreach ($noise as $noiseNode) {
                $toRemove[] = $noiseNode;
            }
            foreach ($toRemove as $noiseNode) {
                $noiseNode->parentNode->removeChild($noiseNode);
            }

            $text = trim(preg_replace('/\s+/', ' ', $node->textContent));

            if (empty($text)) {
                continue;
            }

            // A single verse can appear across multiple spans (e.g. poetry line breaks) — concatenate
            if (isset($verses[$verseNumber])) {
                $verses[$verseNumber] .= ' ' . $text;
            } else {
                $verses[$verseNumber] = $text;
            }
        }

        return $verses;
    }
}
