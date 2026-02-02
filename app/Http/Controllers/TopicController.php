<?php

namespace App\Http\Controllers;

use App\Models\Topic;
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
        return view('topics.create');
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
        
        return view('topics.edit', compact('topic', 'matchingVerses'));
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

    /**
     * Remove the specified topic.
     */
    public function destroy(Topic $topic)
    {
        $topic->delete();

        return redirect()->route('topics.index')->with('success', 'Topic deleted successfully.');
    }
}