<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\ChapterComment;
use App\Models\UserRead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChapterController extends Controller
{
    /**
     * Get chapters by book
     */
    public function lookup(Request $request)
    {
        $chapters = Chapter::where('book_id', $request->book_id)
            ->get();

        return response()->json($chapters);
    }

    /**
     * Get chapter with its comments
     */
    public function getComments(Request $request)
    {
        // Allow fetching by chapter_id directly or by book_id + chapter_number
        if ($request->has('chapter_id')) {
            $chapter = Chapter::with('book')->find($request->chapter_id);
        } else {
            $chapter = Chapter::with('book')
                ->where('book_id', $request->book_id)
                ->where('number', $request->chapter_number)
                ->first();
        }

        if (!$chapter) {
            return response()->json(['error' => 'Chapter not found'], 404);
        }

        $comments = ChapterComment::where('chapter_id', $chapter->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'chapter' => $chapter,
            'reference' => $chapter->book->name . ' ' . $chapter->number,
            'comments' => $comments
        ]);
    }

    /**
     * Add a comment to a chapter
     */
    public function storeComment(Request $request, Chapter $chapter)
    {
        ChapterComment::create([
            'chapter_id' => $chapter->id,
            'comment' => $request->comment,
        ]);

        return response()->json(['success' => true]);
    }

    public function markRead(Request $request)
    {
        $request->validate([
            'book_id'        => 'required|exists:books,id',
            'chapter_number' => 'required|integer|min:1',
            'translation_id' => 'required|exists:translations,id',
        ]);

        UserRead::updateOrCreate(
            [
                'user_id'        => Auth::id(),
                'book_id'        => $request->book_id,
                'chapter_number' => $request->chapter_number,
                'translation_id' => $request->translation_id,
            ],
            ['read_at' => now()]
        );

        return response()->json(['success' => true]);
    }

    public function readStatus(Request $request)
    {
        $read = UserRead::where('user_id', Auth::id())
            ->where('book_id', $request->book_id)
            ->where('chapter_number', $request->chapter_number)
            ->where('translation_id', $request->translation_id)
            ->first();

        return response()->json(['read_at' => $read ? $read->read_at : null]);
    }

    public function lastRead()
    {
        $last = UserRead::where('user_id', Auth::id())
            ->orderByDesc('read_at')
            ->first();

        if (!$last) {
            return response()->json(null);
        }

        return response()->json([
            'book_id'        => $last->book_id,
            'chapter_number' => $last->chapter_number,
            'translation_id' => $last->translation_id,
        ]);
    }
}