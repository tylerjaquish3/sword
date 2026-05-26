<?php

namespace App\Http\Controllers;

use App\Models\ChapterComment;
use App\Models\DigestGuestComment;
use App\Models\Memory;
use App\Models\Prayer;
use App\Models\SharedDigest;
use App\Models\UserNotification;
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
            'fruits_description' => 'nullable|string|max:2000',
            'impactful_scripture' => 'nullable|string|max:2000',
            'idols' => 'nullable|array',
            'idols.*' => 'string',
            'idols_other' => 'nullable|string|max:500',
            'idols_description' => 'nullable|string|max:2000',
            'additional_content' => 'nullable|string|max:5000',
            'sermon_notes' => 'nullable|string|max:5000',
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
            'fruits_description' => $request->input('fruits_description'),
            'impactful_scripture' => $request->input('impactful_scripture'),
            'idols' => $idols,
            'idols_description' => $request->input('idols_description'),
            'additional_content' => $request->input('additional_content'),
            'sermon_notes' => $request->input('sermon_notes'),
        ]);

        if ($isSharing) {
            return redirect()->route('digest.share.link', $shared->uuid);
        }

        return redirect()->route('digest.history')->with('success', 'Digest saved for ' . $weekStart->format('M j') . '–' . $weekEnd->format('M j, Y') . '.');
    }

    public function edit(SharedDigest $shared)
    {
        abort_if($shared->user_id !== Auth::id(), 403);
        abort_if($shared->is_shared, 403, 'Shared digests cannot be edited.');

        [$weekStart, $weekEnd, $data] = $this->fetchWeeklyData(\Carbon\Carbon::parse($shared->week_start));

        return view('digest.share', array_merge($data, [
            'weekStart' => $weekStart,
            'weekEnd'   => $weekEnd,
            'formAction' => route('digest.update', $shared),
            'shared'    => $shared,
        ]));
    }

    public function update(Request $request, SharedDigest $shared)
    {
        abort_if($shared->user_id !== Auth::id(), 403);
        abort_if($shared->is_shared, 403, 'Shared digests cannot be edited.');

        $request->validate([
            'show_chapters' => 'nullable|boolean',
            'show_prayers' => 'nullable|boolean',
            'show_commentary' => 'nullable|boolean',
            'show_memory' => 'nullable|boolean',
            'show_past_note' => 'nullable|boolean',
            'fruits_needing_prayer' => 'nullable|array',
            'fruits_needing_prayer.*' => 'string',
            'fruits_description' => 'nullable|string|max:2000',
            'impactful_scripture' => 'nullable|string|max:2000',
            'idols' => 'nullable|array',
            'idols.*' => 'string',
            'idols_other' => 'nullable|string|max:500',
            'idols_description' => 'nullable|string|max:2000',
            'additional_content' => 'nullable|string|max:5000',
            'sermon_notes' => 'nullable|string|max:5000',
        ]);

        [, , $data] = $this->fetchWeeklyData(\Carbon\Carbon::parse($shared->week_start));
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

        $shared->update([
            'is_shared' => $isSharing,
            'snapshot' => $snapshot,
            'show_chapters' => $request->boolean('show_chapters'),
            'show_prayers' => $request->boolean('show_prayers'),
            'show_commentary' => $request->boolean('show_commentary'),
            'show_memory' => $request->boolean('show_memory'),
            'show_past_note' => $request->boolean('show_past_note'),
            'fruits_needing_prayer' => $request->input('fruits_needing_prayer', []),
            'fruits_description' => $request->input('fruits_description'),
            'impactful_scripture' => $request->input('impactful_scripture'),
            'idols' => $idols,
            'idols_description' => $request->input('idols_description'),
            'additional_content' => $request->input('additional_content'),
            'sermon_notes' => $request->input('sermon_notes'),
        ]);

        if ($isSharing) {
            return redirect()->route('digest.share.link', $shared->uuid);
        }

        return redirect()->route('digest.history')
            ->with('success', 'Digest updated for ' . $shared->week_start->format('M j') . '–' . $shared->week_end->format('M j, Y') . '.');
    }

    public function link(string $uuid)
    {
        $shared = SharedDigest::where('uuid', $uuid)->firstOrFail();

        return view('digest.share-link', ['shared' => $shared]);
    }

    public function show(string $uuid)
    {
        $shared = SharedDigest::where('uuid', $uuid)->firstOrFail();
        $comments = $shared->guestComments()->orderBy('created_at')->get();

        return view('digest.shared', ['shared' => $shared, 'comments' => $comments]);
    }

    public function storeComment(Request $request, string $uuid)
    {
        $shared = SharedDigest::where('uuid', $uuid)->firstOrFail();

        $request->validate([
            'name'    => 'nullable|string|max:100',
            'comment' => 'required|string|max:2000',
        ]);

        $comment = DigestGuestComment::create([
            'shared_digest_id' => $shared->id,
            'name'             => filled($request->name) ? trim($request->name) : null,
            'comment'          => trim($request->comment),
        ]);

        UserNotification::withoutGlobalScopes()->create([
            'user_id'    => $shared->user_id,
            'type'       => 'digest_comment',
            'title'      => ($comment->displayName()) . ' commented on your digest',
            'message'    => Str::limit($comment->comment, 120),
            'icon'       => 'mdi-comment-text-outline',
            'icon_color' => 'bg-warning',
            'url'        => route('digest.show', $shared->id),
            'unique_key' => null,
        ]);

        return redirect()
            ->to(route('digest.shared.show', $uuid) . '#comments')
            ->with('comment_success', true);
    }

    private function fetchWeeklyData(?\Carbon\Carbon $forWeekOf = null): array
    {
        $base = $forWeekOf ?? now();
        $weekStart = $base->copy()->startOfWeek();
        $weekEnd = $base->copy()->endOfWeek();

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
            ->with(['chapter.book', 'verse'])
            ->orderByDesc('created_at')
            ->get();

        $activeMemories = Memory::active()->withCount('verses')->get();

        $completedMemories = Memory::completed()
            ->whereBetween('completed_at', [$weekStart, $weekEnd])
            ->with(['verses.chapter.book'])
            ->get();

        $completedThisWeek = $completedMemories->count();

        $startedThisWeek = Memory::whereBetween('start_date', [$weekStart, $weekEnd])
            ->with(['verses.chapter.book'])
            ->get();

        $daysStudied = UserRead::where('user_id', Auth::id())
            ->whereBetween('read_at', [$weekStart, $weekEnd])
            ->selectRaw('DATE(read_at) as date')
            ->groupBy('date')
            ->get()
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
            'completedMemories',
            'completedThisWeek',
            'startedThisWeek',
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
                'date' => $c->created_at->format('M j'),
                'created_at' => $c->created_at->toIso8601String(),
            ];
        })->concat(collect($data['verseComments'])->map(function ($c) {
            return [
                'type' => 'verse',
                'ref' => ($c->chapter?->book?->name ?? '') . ' ' . ($c->chapter?->number ?? '') . ':' . $c->verse_number,
                'comment' => $c->comment,
                'verse_text' => $c->verse?->text,
                'date' => $c->created_at->format('M j'),
                'created_at' => $c->created_at->toIso8601String(),
            ];
        }))->sortBy('created_at')->values()->all();

        $memories = $data['activeMemories']->map(function ($m) {
            return [
                'title' => $m->title ?? 'Untitled Set',
                'verses' => $m->verses_count,
            ];
        })->all();

        $completedMemoriesSnap = $data['completedMemories']->map(function ($m) {
            return [
                'title' => $m->title ?? 'Untitled Set',
                'verses' => $m->verses->sortBy(fn($v) => [$v->chapter->book->id ?? 0, $v->chapter->number ?? 0, $v->number])->map(function ($v) {
                    return [
                        'reference' => ($v->chapter->book->name ?? '') . ' ' . ($v->chapter->number ?? '') . ':' . $v->number,
                        'text' => $v->text,
                    ];
                })->values()->all(),
            ];
        })->all();

        $startedThisWeekSnap = $data['startedThisWeek']->map(function ($m) {
            return [
                'title' => $m->title ?? 'Untitled Set',
                'verses' => $m->verses->sortBy(fn($v) => [$v->chapter->book->id ?? 0, $v->chapter->number ?? 0, $v->number])->map(function ($v) {
                    return [
                        'reference' => ($v->chapter->book->name ?? '') . ' ' . ($v->chapter->number ?? '') . ':' . $v->number,
                    ];
                })->values()->all(),
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
            'completedMemories' => $completedMemoriesSnap,
            'startedThisWeek' => $startedThisWeekSnap,
            'pastNote' => $pastNote,
        ];
    }
}
