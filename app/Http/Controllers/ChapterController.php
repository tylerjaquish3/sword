<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\ChapterComment;
use Illuminate\Http\Request;

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
            'user_id' => 1 // TODO: use auth user
        ]);

        return response()->json(['success' => true]);
    }
}