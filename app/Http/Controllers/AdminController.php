<?php

namespace App\Http\Controllers;

use App\Mail\AccountActivated;
use App\Models\Book;
use App\Models\ChapterComment;
use App\Models\Memory;
use App\Models\Prayer;
use App\Models\SharedDigest;
use App\Models\Topic;
use App\Models\User;
use App\Models\UserLogin;
use App\Models\VerseComment;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class AdminController extends Controller
{
    private const MONTHS_PER_PAGE = 12;

    public function index()
    {
        $users = User::orderBy('created_at', 'desc')->get();

        foreach ($users as $user) {
            $user->activity = $this->metricsFor($user);
        }

        return view('admin.users', compact('users'));
    }

    public function formatting()
    {
        // Prefixes are shared across translations, so aggregate per chapter by distinct verse number.
        $stats = DB::table('verses')
            ->select('chapter_id')
            ->selectRaw('COUNT(DISTINCT number) AS verse_count')
            ->selectRaw("COUNT(DISTINCT CASE WHEN prefix LIKE '%</p>%' OR prefix LIKE '%<br>%' THEN number END) AS break_count")
            ->selectRaw("COUNT(DISTINCT CASE WHEN prefix LIKE '%<h5%' THEN number END) AS header_count")
            ->groupBy('chapter_id')
            ->get()
            ->keyBy('chapter_id');

        $books = Book::with(['chapters' => fn ($q) => $q->orderBy('number')])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(function (Book $book) use ($stats) {
                $chapters = $book->chapters->map(function ($chapter) use ($stats) {
                    $s = $stats->get($chapter->id);

                    return [
                        'number' => $chapter->number,
                        'verses' => (int) ($s->verse_count ?? 0),
                        'breaks' => (int) ($s->break_count ?? 0),
                        'headers' => (int) ($s->header_count ?? 0),
                    ];
                });

                $total = $chapters->count();
                $withBreaks = $chapters->where('breaks', '>', 0)->count();
                $withHeaders = $chapters->where('headers', '>', 0)->count();

                return [
                    'name' => $book->name,
                    'new_testament' => (bool) $book->new_testament,
                    'chapters' => $chapters,
                    'total' => $total,
                    'with_breaks' => $withBreaks,
                    'with_headers' => $withHeaders,
                    'state' => match (true) {
                        $total > 0 && $withBreaks === $total => 'complete',
                        $withBreaks > 0 || $withHeaders > 0 => 'partial',
                        default => 'none',
                    },
                ];
            });

        $summary = [
            'books' => $books->count(),
            'complete' => $books->where('state', 'complete')->count(),
            'partial' => $books->where('state', 'partial')->count(),
            'none' => $books->where('state', 'none')->count(),
            'chapters' => $books->sum('total'),
            'chapters_with_breaks' => $books->sum('with_breaks'),
            'chapters_with_headers' => $books->sum('with_headers'),
        ];

        return view('admin.formatting', compact('books', 'summary'));
    }

    public function show(User $user, Request $request)
    {
        $lastLogin = UserLogin::where('user_id', $user->id)->orderByDesc('logged_in_at')->first();

        $totalMonths = abs(now()->startOfMonth()->diffInMonths($user->created_at->copy()->startOfMonth())) + 1;
        $lastPage = (int) ceil($totalMonths / self::MONTHS_PER_PAGE);
        $page = max(1, min($lastPage, (int) $request->query('page', 1)));

        $firstOffset = ($page - 1) * self::MONTHS_PER_PAGE;
        $lastOffset = min($firstOffset + self::MONTHS_PER_PAGE, $totalMonths) - 1;

        $months = [];
        for ($i = $firstOffset; $i <= $lastOffset; $i++) {
            $monthStart = now()->copy()->subMonths($i)->startOfMonth();
            $monthEnd = $monthStart->copy()->endOfMonth();

            $months[] = [
                'label' => $monthStart->format('F Y'),
            ] + $this->metricsFor($user, $monthStart, $monthEnd);
        }

        $months = new LengthAwarePaginator($months, $totalMonths, self::MONTHS_PER_PAGE, $page, [
            'path' => $request->url(),
        ]);

        $allTime = $this->metricsFor($user);

        return view('admin.user-show', [
            'targetUser' => $user,
            'lastLogin' => $lastLogin,
            'months' => $months,
            'allTime' => $allTime,
        ]);
    }

    private function metricsFor(User $user, $start = null, $end = null): array
    {
        return [
            'chapters_read' => $this->distinctChaptersRead($user, $start, $end),
            'prayers' => $this->scoped(Prayer::withoutGlobalScope('user')->where('user_id', $user->id), $start, $end)->count(),
            'commentary' => $this->scoped(ChapterComment::withoutGlobalScope('user')->where('user_id', $user->id), $start, $end)->count()
                + $this->scoped(VerseComment::withoutGlobalScope('user')->where('user_id', $user->id), $start, $end)->count(),
            'topics' => $this->scoped(Topic::where('user_id', $user->id), $start, $end)->count(),
            'memory_completed' => $this->scoped(Memory::withoutGlobalScope('user')->where('user_id', $user->id), $start, $end, 'completed_at')->count(),
            'digests' => $this->scoped(SharedDigest::where('user_id', $user->id), $start, $end)->count(),
        ];
    }

    private function scoped($query, $start, $end, string $column = 'created_at')
    {
        if ($start && $end) {
            $query->whereBetween($column, [$start, $end]);
        }

        return $query;
    }

    private function distinctChaptersRead(User $user, $start = null, $end = null): int
    {
        return \DB::table(function ($q) use ($user, $start, $end) {
            $q->from('user_reads')
                ->where('user_id', $user->id)
                ->select('book_id', 'chapter_number')
                ->distinct();

            if ($start && $end) {
                $q->whereBetween('read_at', [$start, $end]);
            }
        }, 'sub')->count();
    }

    public function activate(User $user)
    {
        $user->update(['is_active' => true]);

        dispatch(function () use ($user) {
            Mail::to($user->email)->send(new AccountActivated($user));
        })->afterResponse();

        return back()->with('status', "Account for {$user->name} has been activated.");
    }

    public function deactivate(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'You cannot deactivate your own account.']);
        }

        $user->update(['is_active' => false]);

        return back()->with('status', "Account for {$user->name} has been deactivated.");
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'You cannot delete your own account.']);
        }

        $name = $user->name;
        $user->delete();

        return back()->with('status', "Account for {$name} has been deleted.");
    }
}
