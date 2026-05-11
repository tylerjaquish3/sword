<?php

namespace App\Http\Controllers;

use App\Models\BookStudy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookStudyController extends Controller
{
    public function store(Request $request)
    {
        $request->validate(['book_id' => 'required|exists:books,id']);

        BookStudy::create([
            'user_id' => Auth::id(),
            'book_id' => $request->book_id,
        ]);

        return redirect()->to(route('topics.index') . '#books');
    }

    public function complete(BookStudy $bookStudy)
    {
        abort_if($bookStudy->user_id !== Auth::id(), 403);

        $bookStudy->update(['completed_at' => now()]);

        return redirect()->to(route('topics.index') . '#books');
    }

    public function destroy(BookStudy $bookStudy)
    {
        abort_if($bookStudy->user_id !== Auth::id(), 403);

        $bookStudy->delete();

        return redirect()->back();
    }
}
