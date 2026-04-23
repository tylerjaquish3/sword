<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use App\Models\TopicNote;
use App\Models\Verse;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TopicController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $topics = Topic::all();

        return view('topics.index', compact('topics'));
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
            'name' => $request->name,
            'description' => $request->description,
            'keywords' => $request->keywords,
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

        $note = $topic->notes()->create(['note' => $request->note]);

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