<?php

namespace App\Http\Controllers;

use App\Models\Verse;
use App\Models\VerseLink;
use Illuminate\Http\Request;

class VerseLinkController extends Controller
{
    /**
     * Select2 remote search — find a verse anywhere in the Bible by reference or text,
     * independent of translation (grouped by chapter_id + verse number).
     */
    public function search(Request $request)
    {
        $q = trim($request->get('q', ''));
        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $verses = Verse::selectRaw('verses.chapter_id, verses.number, verses.reference, MIN(verses.text) as text')
            ->join('chapters', 'chapters.id', '=', 'verses.chapter_id')
            ->join('books', 'books.id', '=', 'chapters.book_id')
            ->where(function ($query) use ($q) {
                $query->where('verses.reference', 'LIKE', '%' . $q . '%')
                      ->orWhere('verses.text', 'LIKE', '%' . $q . '%');
            })
            ->groupBy('verses.chapter_id', 'verses.number', 'verses.reference')
            // Order canonically (book order, then chapter/verse as numbers) rather than
            // alphabetically by reference string, which would put "3:10" before "3:2".
            ->orderBy('books.sort_order')
            ->orderBy('chapters.number')
            ->orderBy('verses.number')
            ->limit(20)
            ->get()
            ->map(fn ($v) => [
                'id'      => $v->chapter_id . ':' . $v->number,
                'text'    => $v->reference,
                'preview' => mb_strimwidth($v->text, 0, 140, '…'),
            ]);

        return response()->json($verses);
    }

    /**
     * All cross references touching a chapter (as either side of the link), resolved
     * to the requested translation for preview text. Used to render the footnote
     * markers/list and to power the panel's "existing references" section.
     */
    public function index(Request $request)
    {
        $chapterId = $request->chapter_id;

        $links = VerseLink::where('chapter_id', $chapterId)
            ->orWhere('linked_chapter_id', $chapterId)
            ->get();

        $result = collect();

        foreach ($links as $link) {
            if ($link->chapter_id == $chapterId) {
                $result->push($this->buildFootnote($link->id, $link->verse_number, $link->linked_chapter_id, $link->linked_verse_number, $request->translation_id));
            }
            if ($link->linked_chapter_id == $chapterId) {
                $result->push($this->buildFootnote($link->id, $link->linked_verse_number, $link->chapter_id, $link->verse_number, $request->translation_id));
            }
        }

        return response()->json($result->sortBy('verse_number')->values());
    }

    private function buildFootnote(int $linkId, int $verseNumber, int $otherChapterId, int $otherVerseNumber, $translationId): array
    {
        $otherVerse = Verse::where('chapter_id', $otherChapterId)
            ->where('number', $otherVerseNumber)
            ->when($translationId, fn ($q) => $q->where('translation_id', $translationId))
            ->first();

        if (!$otherVerse) {
            $otherVerse = Verse::where('chapter_id', $otherChapterId)
                ->where('number', $otherVerseNumber)
                ->first();
        }

        $otherVerse?->loadMissing('chapter.book');

        return [
            'id'                    => $linkId,
            'verse_number'          => $verseNumber,
            'linked_chapter_id'     => $otherChapterId,
            'linked_verse_number'   => $otherVerseNumber,
            'linked_reference'      => $otherVerse->reference ?? null,
            'linked_preview'        => $otherVerse ? mb_strimwidth($otherVerse->text, 0, 160, '…') : null,
            'linked_book_id'        => $otherVerse?->chapter?->book_id,
            'linked_chapter_number' => $otherVerse?->chapter?->number,
        ];
    }

    /**
     * Create a cross reference. Shared across all users (no user scoping), and stored
     * once per pair regardless of which side was picked first.
     */
    public function store(Request $request)
    {
        $request->validate([
            'chapter_id'           => 'required|exists:chapters,id',
            'verse_number'         => 'required|integer',
            'linked_chapter_id'    => 'required|exists:chapters,id',
            'linked_verse_number'  => 'required|integer',
        ]);

        if ($request->chapter_id == $request->linked_chapter_id && $request->verse_number == $request->linked_verse_number) {
            return response()->json(['error' => 'A verse cannot be linked to itself.'], 422);
        }

        $existing = VerseLink::where(function ($q) use ($request) {
                $q->where('chapter_id', $request->chapter_id)
                  ->where('verse_number', $request->verse_number)
                  ->where('linked_chapter_id', $request->linked_chapter_id)
                  ->where('linked_verse_number', $request->linked_verse_number);
            })
            ->orWhere(function ($q) use ($request) {
                $q->where('chapter_id', $request->linked_chapter_id)
                  ->where('verse_number', $request->linked_verse_number)
                  ->where('linked_chapter_id', $request->chapter_id)
                  ->where('linked_verse_number', $request->verse_number);
            })
            ->first();

        $link = $existing ?? VerseLink::create($request->only([
            'chapter_id', 'verse_number', 'linked_chapter_id', 'linked_verse_number',
        ]));

        return response()->json(['success' => true, 'id' => $link->id]);
    }

    /**
     * Remove a cross reference.
     */
    public function destroy(VerseLink $verseLink)
    {
        $verseLink->delete();

        return response()->json(['success' => true]);
    }
}
