<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Chapter;
use App\Models\ProverbsGroup;
use App\Models\ProverbsVerseGroup;
use App\Models\Translation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProverbsGroupController extends Controller
{
    public function index()
    {
        $book = Book::where('name', 'Proverbs')->first();
        $translationId = Auth::user()->default_translation_id ?? Translation::first()?->id;

        $chapters = collect();
        if ($book) {
            $chapters = Chapter::where('book_id', $book->id)
                ->orderBy('number')
                ->with(['verses' => fn ($q) => $q->where('translation_id', $translationId)->orderBy('number')])
                ->get();
        }

        $groups = ProverbsGroup::where('user_id', Auth::id())
            ->withCount('verseAssignments')
            ->orderBy('created_at')
            ->get();

        $assignments = ProverbsVerseGroup::where('user_id', Auth::id())
            ->get()
            ->mapWithKeys(fn ($row) => [$row->chapter_id.'-'.$row->verse_number => $row->proverbs_group_id]);

        return view('proverbs-groups.index', compact('chapters', 'groups', 'assignments'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);

        ProverbsGroup::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
        ]);

        return redirect()->route('proverbs-groups.index')->with('status', 'Group created.');
    }

    public function update(Request $request, ProverbsGroup $proverbsGroup)
    {
        abort_unless($proverbsGroup->user_id === Auth::id(), 403);

        $request->validate(['name' => 'required|string|max:255']);

        $proverbsGroup->update(['name' => $request->name]);

        return redirect()->route('proverbs-groups.index')->with('status', 'Group renamed.');
    }

    public function destroy(ProverbsGroup $proverbsGroup)
    {
        abort_unless($proverbsGroup->user_id === Auth::id(), 403);

        $proverbsGroup->delete();

        return redirect()->route('proverbs-groups.index')->with('status', 'Group deleted.');
    }

    public function assign(Request $request)
    {
        $data = $request->validate([
            'assignments' => 'required|array',
            'assignments.*' => 'array',
            'assignments.*.*' => 'nullable|string',
        ]);

        $ownedGroupIds = ProverbsGroup::where('user_id', Auth::id())->pluck('id')->all();

        foreach ($data['assignments'] as $verses) {
            foreach ($verses as $value) {
                if ($value !== 'unassigned' && $value !== null && $value !== '' && !in_array((int) $value, $ownedGroupIds, true)) {
                    abort(422, 'Unknown group.');
                }
            }
        }

        DB::transaction(function () use ($data) {
            foreach ($data['assignments'] as $chapterId => $verses) {
                foreach ($verses as $verseNumber => $value) {
                    if ($value === 'unassigned' || $value === null || $value === '') {
                        ProverbsVerseGroup::where('user_id', Auth::id())
                            ->where('chapter_id', $chapterId)
                            ->where('verse_number', $verseNumber)
                            ->delete();

                        continue;
                    }

                    ProverbsVerseGroup::updateOrCreate(
                        [
                            'user_id' => Auth::id(),
                            'chapter_id' => $chapterId,
                            'verse_number' => $verseNumber,
                        ],
                        ['proverbs_group_id' => (int) $value]
                    );
                }
            }
        });

        return redirect()->route('proverbs-groups.index')->with('status', 'Assignments saved.');
    }
}
