<?php

namespace App\Http\Controllers;

use App\Models\Memory;
use App\Models\Book;
use App\Models\Verse;
use Illuminate\Http\Request;

class MemoryController extends Controller
{
    public function index()
    {
        // For now, using user_id = 1 since we don't have auth
        $userId = 1;
        
        $activeMemories = Memory::with(['verses.chapter.book', 'verses.translation'])
            ->where('user_id', $userId)
            ->active()
            ->orderBy('start_date', 'desc')
            ->get();
            
        $completedMemories = Memory::with(['verses.chapter.book', 'verses.translation'])
            ->where('user_id', $userId)
            ->completed()
            ->orderBy('completed_at', 'desc')
            ->get();

        $books = Book::orderBy('id')->get();

        return view('memory.index', compact('activeMemories', 'completedMemories', 'books'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'verse_ids' => 'required|array|min:1',
            'verse_ids.*' => 'exists:verses,id',
            'notes' => 'nullable|string',
        ]);

        $memory = Memory::create([
            'user_id' => 1, // Hardcoded for now
            'title' => $request->title,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'notes' => $request->notes,
        ]);

        $memory->verses()->attach($request->verse_ids);

        return redirect()->route('memory.index')->with('success', 'Memory goal created successfully!');
    }

    public function update(Request $request, Memory $memory)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'verse_ids' => 'required|array|min:1',
            'verse_ids.*' => 'exists:verses,id',
            'notes' => 'nullable|string',
        ]);

        $memory->update([
            'title' => $request->title,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'notes' => $request->notes,
        ]);

        $memory->verses()->sync($request->verse_ids);

        return redirect()->route('memory.index')->with('success', 'Memory goal updated successfully!');
    }

    public function complete(Memory $memory)
    {
        $memory->update([
            'completed_at' => now(),
        ]);

        return redirect()->route('memory.index')->with('success', 'Memory goal marked as complete!');
    }

    public function uncomplete(Memory $memory)
    {
        $memory->update([
            'completed_at' => null,
        ]);

        return redirect()->route('memory.index')->with('success', 'Memory goal reopened!');
    }

    public function destroy(Memory $memory)
    {
        $memory->verses()->detach();
        $memory->delete();

        return redirect()->route('memory.index')->with('success', 'Memory goal deleted successfully!');
    }

    public function getVerses(Request $request)
    {
        $bookId = $request->input('book_id');
        $chapterNumber = $request->input('chapter_number');
        $translationId = $request->input('translation_id', 1);

        $verses = Verse::whereHas('chapter', function ($query) use ($bookId, $chapterNumber) {
            $query->where('book_id', $bookId)
                  ->where('number', $chapterNumber);
        })
        ->where('translation_id', $translationId)
        ->orderBy('number')
        ->get();

        return response()->json($verses);
    }
}
