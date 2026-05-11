<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookStudy;
use App\Models\Topic;
use App\Models\TopicNote;
use App\Models\UserRead;
use App\Models\Verse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TopicController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $topics = Topic::where('user_id', Auth::id())->get();

        $activeStudies = BookStudy::where('user_id', Auth::id())
            ->active()->with(['book' => fn($q) => $q->withCount('chapters')])->latest()->get();
        $completedStudies = BookStudy::where('user_id', Auth::id())
            ->completed()->with(['book' => fn($q) => $q->withCount('chapters')])->latest('completed_at')->get();
        $allBooks = Book::withCount('chapters')->orderBy('sort_order')->get();

        $studyBookIds = $activeStudies->pluck('book_id')
            ->concat($completedStudies->pluck('book_id'))->unique();
        $chaptersReadByBook = UserRead::where('user_id', Auth::id())
            ->whereIn('book_id', $studyBookIds)
            ->selectRaw('book_id, COUNT(DISTINCT chapter_number) as read_count')
            ->groupBy('book_id')
            ->pluck('read_count', 'book_id');

        return view('topics.index', compact('topics', 'activeStudies', 'completedStudies', 'allBooks', 'chaptersReadByBook'));
    }

    public function create()
    {
        return redirect()->route('topics.index');
    }

    /**
     * Store a newly created topic.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'keywords' => 'nullable|string',
        ]);

        Topic::create([
            'user_id'     => Auth::id(),
            'name'        => $request->name,
            'description' => $request->description,
            'keywords'    => $request->keywords,
        ]);

        if ($request->ajax()) {
            return response()->json(['redirect' => route('topics.index')]);
        }

        return redirect()->route('topics.index')->with('success', 'Topic created successfully.');
    }

    /**
     * Show the form for editing the specified topic.
     */
    public function edit(Topic $topic)
    {
        $matchingVerses = collect();
        
        if ($topic->keywords) {
            $keywords = array_map('trim', explode(',', $topic->keywords));
            
            foreach ($keywords as $keyword) {
                if (empty($keyword)) continue;

                $verses = Verse::with(['chapter.book', 'translation'])
                    ->where('text', 'LIKE', '%' . $keyword . '%')
                    ->limit(500)
                    ->get()
                    ->map(function ($verse) use ($keyword) {
                        $verse->matched_keyword = $keyword;
                        return $verse;
                    });

                $matchingVerses = $matchingVerses->concat($verses);
            }

            // Remove duplicates based on verse id, keeping first match
            $matchingVerses = $matchingVerses->unique('id');
        }
        
        $notes = $topic->notes()->with('verses.chapter.book')->get();

        return view('topics.edit', compact('topic', 'matchingVerses', 'notes'));
    }

    /**
     * Update the specified topic.
     */
    public function update(Request $request, Topic $topic)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'keywords' => 'nullable|string',
        ]);

        $topic->update([
            'name' => $request->name,
            'description' => $request->description,
            'keywords' => $request->keywords,
        ]);

        return redirect()->route('topics.edit', $topic)->with('success', 'Topic updated successfully.');
    }

    public function storeNote(Request $request, Topic $topic)
    {
        $request->validate(['note' => 'required|string']);

        $note = $topic->notes()->create(['user_id' => Auth::id(), 'note' => $request->note]);

        if ($request->filled('verse_ids')) {
            $note->verses()->attach(
                array_filter(array_map('intval', explode(',', $request->verse_ids)))
            );
        }

        $note->load('verses.chapter.book');

        return response()->json([
            'id'         => $note->id,
            'note'       => $note->note,
            'created_at' => $note->created_at->format('M j, Y g:i A'),
            'verses'     => $note->verses->map(fn($v) => [
                'id'        => $v->id,
                'reference' => $v->reference,
                'url'       => route('translations.index') . '?translation=' . $v->translation_id
                             . '&book=' . $v->chapter->book->id
                             . '&chapter=' . $v->chapter->number,
            ]),
        ]);
    }

    public function destroyNote(TopicNote $note)
    {
        $note->delete();
        return response()->json(['ok' => true]);
    }

    public function verseSearch(Request $request)
    {
        $q = trim($request->get('q', ''));
        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $verses = Verse::with(['chapter.book', 'translation'])
            ->where(function ($query) use ($q) {
                $query->where('reference', 'LIKE', '%' . $q . '%')
                      ->orWhere('text', 'LIKE', '%' . $q . '%');
            })
            ->limit(20)
            ->get()
            ->map(fn($v) => [
                'id'          => $v->id,
                'reference'   => $v->reference,
                'translation' => $v->translation->name ?? '',
                'text'        => mb_strimwidth($v->text, 0, 120, '…'),
                'url'         => route('translations.index') . '?translation=' . $v->translation_id
                               . '&book=' . $v->chapter->book->id
                               . '&chapter=' . $v->chapter->number,
            ]);

        return response()->json($verses);
    }

    /**
     * Remove the specified topic.
     */
    public function destroy(Topic $topic)
    {
        $topic->delete();

        return redirect()->route('topics.index')->with('success', 'Topic deleted successfully.');
    }
}