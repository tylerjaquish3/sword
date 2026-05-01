<?php

namespace App\Http\Controllers;

use App\Models\ChapterComment;
use App\Models\Memory;
use App\Models\Prayer;
use App\Models\SharedDigest;
use App\Models\UserRead;
use App\Models\VerseComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SharedDigestController extends Controller
{
    public function create()
    {
        [$weekStart, $weekEnd, $data] = $this->fetchWeeklyData();

        return view('digest.share', array_merge($data, [
            'weekStart' => $weekStart,
            'weekEnd'   => $weekEnd,
            'formAction' => route('digest.complete.store'),
        ]));
    }

    public function store(Request $request)
    {
        $request->validate([
            'show_chapters' => 'nullable|boolean',
            'show_prayers' => 'nullable|boolean',
            'show_commentary' => 'nullable|boolean',
            'show_memory' => 'nullable|boolean',
            'show_past_note' => 'nullable|boolean',
            'fruits_needing_prayer' => 'nullable|array',
            'fruits_needing_prayer.*' => 'string',
            'impactful_scripture' => 'nullable|string|max:2000',
            'idols' => 'nullable|array',
            'idols.*' => 'string',
            'idols_other' => 'nullable|string|max:500',
            'additional_content' => 'nullable|string|max:5000',
        ]);

        [$weekStart, $weekEnd, $data] = $this->fetchWeeklyData();

        $snapshot = $this->buildSnapshot($data);

        $idols = $request->input('idols', []);
        if ($request->filled('idols_other')) {
            foreach (explode(',', $request->input('idols_other')) as $extra) {
                $trimmed = trim($extra);
                if ($trimmed) {
                    $idols[] = $trimmed;
                }
            }
        }

        $isSharing = $request->input('submit_action') === 'share';

        $shared = SharedDigest::create([
            'uuid' => Str::uuid()->toString(),
            'user_id' => Auth::id(),
            'sharer_name' => Auth::user()->name,
            'week_start' => $weekStart->toDateString(),
            'week_end' => $weekEnd->toDateString(),
            'snapshot' => $snapshot,
            'is_shared' => $isSharing,
            'show_chapters' => $request->boolean('show_chapters'),
            'show_prayers' => $request->boolean('show_prayers'),
            'show_commentary' => $request->boolean('show_commentary'),
            'show_memory' => $request->boolean('show_memory'),
            'show_past_note' => $request->boolean('show_past_note'),
            'fruits_needing_prayer' => $request->input('fruits_needing_prayer', []),
            'impactful_scripture' => $request->input('impactful_scripture'),
            'idols' => $idols,
            'additional_content' => $request->input('additional_content'),
        ]);

        if ($isSharing) {
            return redirect()->route('digest.share.link', $shared->uuid);
        }

        return redirect()->route('digest.history')->with('success', 'Digest saved for ' . $weekStart->format('M j') . '–' . $weekEnd->format('M j, Y') . '.');
    }

    public function link(string $uuid)
    {
        $shared = SharedDigest::where('uuid', $uuid)->firstOrFail();

        return view('digest.share-link', ['shared' => $shared]);
    }

    public function show(string $uuid)
    {
        $shared = SharedDigest::where('uuid', $uuid)->firstOrFail();

        return view('digest.shared', ['shared' => $shared]);
    }

    private function fetchWeeklyData(): array
    {
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();

        $chaptersRead = UserRead::where('user_id', Auth::id())
            ->whereBetween('read_at', [$weekStart, $weekEnd])
            ->with('book')
            ->get()
            ->groupBy('book_id')
            ->sortBy(fn($reads) => $reads->first()->book->sort_order ?? 999);

        $prayers = Prayer::whereBetween('created_at', [$weekStart, $weekEnd])
            ->with('type')
            ->orderByDesc('created_at')
            ->get();

        $chapterComments = ChapterComment::whereBetween('created_at', [$weekStart, $weekEnd])
            ->with('chapter.book')
            ->orderByDesc('created_at')
            ->get();

        $verseComments = VerseComment::whereBetween('created_at', [$weekStart, $weekEnd])
            ->with('chapter.book')
            ->orderByDesc('created_at')
            ->get();

        $activeMemories = Memory::active()->withCount('verses')->get();

        $completedThisWeek = Memory::completed()
            ->whereBetween('completed_at', [$weekStart, $weekEnd])
            ->count();

        $daysStudied = UserRead::where('user_id', Auth::id())
            ->whereBetween('read_at', [$weekStart, $weekEnd])
            ->selectRaw('DATE(read_at) as date')
            ->groupBy('date')
            ->count();

        $yearAgoStart = now()->subYear()->startOfWeek();
        $yearAgoEnd = now()->subYear()->endOfWeek();

        $pastNote = VerseComment::whereBetween('created_at', [$yearAgoStart, $yearAgoEnd])
            ->with('chapter.book')
            ->inRandomOrder()
            ->first();

        $pastNoteType = 'verse';
        if (!$pastNote) {
            $pastNote = ChapterComment::whereBetween('created_at', [$yearAgoStart, $yearAgoEnd])
                ->with('chapter.book')
                ->inRandomOrder()
                ->first();
            $pastNoteType = 'chapter';
        }

        return [$weekStart, $weekEnd, compact(
            'chaptersRead',
            'prayers',
            'chapterComments',
            'verseComments',
            'activeMemories',
            'completedThisWeek',
            'daysStudied',
            'pastNote',
            'pastNoteType'
        )];
    }

    private function buildSnapshot(array $data): array
    {
        $chaptersRead = $data['chaptersRead']->map(function ($reads) {
            return [
                'book' => $reads->first()->book?->name ?? 'Unknown',
                'count' => $reads->count(),
            ];
        })->values()->all();

        $prayers = $data['prayers']->map(function ($prayer) {
            return [
                'type' => $prayer->type?->name,
                'date' => \Carbon\Carbon::parse($prayer->date)->format('M j'),
                'content' => $prayer->content,
            ];
        })->all();

        $commentary = collect($data['chapterComments'])->map(function ($c) {
            return [
                'type' => 'chapter',
                'ref' => ($c->chapter?->book?->name ?? '') . ' ' . ($c->chapter?->number ?? ''),
                'comment' => $c->comment,
            ];
        })->concat(collect($data['verseComments'])->map(function ($c) {
            return [
                'type' => 'verse',
                'ref' => ($c->chapter?->book?->name ?? '') . ' ' . ($c->chapter?->number ?? '') . ':' . $c->verse_number,
                'comment' => $c->comment,
            ];
        }))->sortByDesc(fn($n) => $n['type'])->values()->all();

        $memories = $data['activeMemories']->map(function ($m) {
            return [
                'title' => $m->title ?? 'Untitled Set',
                'verses' => $m->verses_count,
            ];
        })->all();

        $pastNote = null;
        if ($data['pastNote']) {
            $n = $data['pastNote'];
            $book = $n->chapter?->book;
            if ($data['pastNoteType'] === 'verse') {
                $ref = ($book?->name ?? '') . ' ' . ($n->chapter?->number ?? '') . ':' . $n->verse_number;
            } else {
                $ref = ($book?->name ?? '') . ' ' . ($n->chapter?->number ?? '') . ' (chapter)';
            }
            $pastNote = [
                'ref' => $ref,
                'type' => $data['pastNoteType'],
                'comment' => $n->comment,
                'date' => $n->created_at->format('M j, Y'),
            ];
        }

        return [
            'daysStudied' => $data['daysStudied'],
            'totalChapters' => $data['chaptersRead']->sum(fn($r) => $r->count()),
            'totalPrayers' => $data['prayers']->pluck('date')->unique()->count(),
            'totalNotes' => $data['chapterComments']->count() + $data['verseComments']->count(),
            'chaptersRead' => $chaptersRead,
            'prayers' => $prayers,
            'commentary' => $commentary,
            'memories' => $memories,
            'completedThisWeek' => $data['completedThisWeek'],
            'pastNote' => $pastNote,
        ];
    }
}
