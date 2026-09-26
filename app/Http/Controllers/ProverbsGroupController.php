<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Chapter;
use App\Models\ProverbsGroup;
use App\Models\ProverbsVerseGroup;
use App\Models\Translation;
use App\Models\Verse;
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

    public function read(ProverbsGroup $proverbsGroup, Request $request)
    {
        abort_unless($proverbsGroup->user_id === Auth::id(), 403);

        $translationId = (int) ($request->query('translation_id') ?: (Auth::user()->default_translation_id ?? Translation::first()?->id));

        $rows = ProverbsVerseGroup::where('user_id', Auth::id())
            ->where('proverbs_group_id', $proverbsGroup->id)
            ->get();

        [$prevUrl, $nextUrl] = $this->buildNav($proverbsGroup->id);

        return view('proverbs-groups.read', [
            'title' => $proverbsGroup->name,
            'verses' => $this->resolveOrderedVerses($rows, $translationId),
            'translationId' => $translationId,
            'translations' => Translation::orderBy('name')->get(),
            'prevUrl' => $prevUrl,
            'nextUrl' => $nextUrl,
        ]);
    }

    public function readUnassigned(Request $request)
    {
        $translationId = (int) ($request->query('translation_id') ?: (Auth::user()->default_translation_id ?? Translation::first()?->id));

        $book = Book::where('name', 'Proverbs')->first();
        $chapterIds = $book ? Chapter::where('book_id', $book->id)->pluck('id') : collect();

        $assignedKeys = ProverbsVerseGroup::where('user_id', Auth::id())
            ->get()
            ->map(fn ($row) => $row->chapter_id.'-'.$row->verse_number)
            ->all();

        $verses = Verse::where('translation_id', $translationId)
            ->whereIn('chapter_id', $chapterIds)
            ->with('chapter')
            ->get()
            ->reject(fn ($verse) => in_array($verse->chapter_id.'-'.$verse->number, $assignedKeys, true))
            ->sortBy([
                fn ($a, $b) => $a->chapter->number <=> $b->chapter->number,
                fn ($a, $b) => $a->number <=> $b->number,
            ])
            ->values();

        [$prevUrl, $nextUrl] = $this->buildNav(null);

        return view('proverbs-groups.read', [
            'title' => 'Unassigned',
            'verses' => $verses,
            'translationId' => $translationId,
            'translations' => Translation::orderBy('name')->get(),
            'prevUrl' => $prevUrl,
            'nextUrl' => $nextUrl,
        ]);
    }

    private function resolveOrderedVerses($rows, int $translationId)
    {
        $chapterNumbers = Chapter::whereIn('id', $rows->pluck('chapter_id')->unique())->pluck('number', 'id');

        return $rows
            ->sortBy([
                fn ($a, $b) => $chapterNumbers[$a->chapter_id] <=> $chapterNumbers[$b->chapter_id],
                fn ($a, $b) => $a->verse_number <=> $b->verse_number,
            ])
            ->map(fn ($row) => Verse::where('chapter_id', $row->chapter_id)
                ->where('translation_id', $translationId)
                ->where('number', $row->verse_number)
                ->with('chapter')
                ->first())
            ->filter()
            ->values();
    }

    private function buildNav(?int $currentGroupId): array
    {
        $groups = ProverbsGroup::where('user_id', Auth::id())->orderBy('created_at')->orderBy('id')->get();

        $stops = $groups
            ->map(fn ($g) => ['id' => $g->id, 'url' => route('proverbs-groups.read', $g)])
            ->push(['id' => null, 'url' => route('proverbs-groups.read-unassigned')])
            ->values();

        $currentIndex = $stops->search(fn ($stop) => $stop['id'] === $currentGroupId);

        $prevUrl = $currentIndex > 0 ? $stops[$currentIndex - 1]['url'] : null;
        $nextUrl = $currentIndex < $stops->count() - 1 ? $stops[$currentIndex + 1]['url'] : null;

        return [$prevUrl, $nextUrl];
    }
}
