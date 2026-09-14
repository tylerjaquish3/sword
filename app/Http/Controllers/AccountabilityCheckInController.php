<?php

namespace App\Http\Controllers;

use App\Models\AccountabilityCheckIn;
use App\Models\AccountabilityGuestComment;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AccountabilityCheckInController extends Controller
{
    private const FELLOWSHIP_LEVELS = ['No Fellowship', 'Minimal', 'Decent', 'Good', 'Abundant'];

    public function create()
    {
        return view('accountability.create', [
            'formAction' => route('accountability.store'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $isSharing = Auth::check() ? $request->input('submit_action') === 'share' : true;

        $checkIn = AccountabilityCheckIn::create(array_merge($data, [
            'uuid' => Str::uuid()->toString(),
            'user_id' => Auth::id(),
            'sharer_name' => Auth::check() ? Auth::user()->name : $request->input('sharer_name'),
            'is_shared' => $isSharing,
        ]));

        if ($isSharing) {
            return redirect()->route('accountability.share.link', $checkIn->uuid);
        }

        return redirect()
            ->to(route('digest.history') . '#accountability')
            ->with('success', 'Accountability check-in saved.');
    }

    public function edit(AccountabilityCheckIn $checkIn)
    {
        abort_if($checkIn->user_id !== Auth::id(), 403);
        abort_if($checkIn->is_shared, 403, 'Shared check-ins cannot be edited.');

        return view('accountability.create', [
            'formAction' => route('accountability.update', $checkIn),
            'checkIn' => $checkIn,
        ]);
    }

    public function update(Request $request, AccountabilityCheckIn $checkIn)
    {
        abort_if($checkIn->user_id !== Auth::id(), 403);
        abort_if($checkIn->is_shared, 403, 'Shared check-ins cannot be edited.');

        $data = $this->validated($request);

        $isSharing = $request->input('submit_action') === 'share';

        $checkIn->update(array_merge($data, ['is_shared' => $isSharing]));

        if ($isSharing) {
            return redirect()->route('accountability.share.link', $checkIn->uuid);
        }

        return redirect()
            ->to(route('digest.history') . '#accountability')
            ->with('success', 'Accountability check-in updated.');
    }

    public function link(string $uuid)
    {
        $checkIn = AccountabilityCheckIn::where('uuid', $uuid)->firstOrFail();

        return view('accountability.share-link', ['checkIn' => $checkIn]);
    }

    public function show(string $uuid)
    {
        $checkIn = AccountabilityCheckIn::where('uuid', $uuid)->firstOrFail();
        $comments = $checkIn->guestComments()->orderBy('created_at')->get();

        return view('accountability.shared', ['checkIn' => $checkIn, 'comments' => $comments]);
    }

    public function storeComment(Request $request, string $uuid)
    {
        $checkIn = AccountabilityCheckIn::where('uuid', $uuid)->firstOrFail();

        $request->validate([
            'name' => 'nullable|string|max:100',
            'comment' => 'required|string|max:2000',
        ]);

        $comment = AccountabilityGuestComment::create([
            'accountability_check_in_id' => $checkIn->id,
            'name' => filled($request->name) ? trim($request->name) : null,
            'comment' => trim($request->comment),
        ]);

        if ($checkIn->user_id) {
            UserNotification::withoutGlobalScopes()->create([
                'user_id' => $checkIn->user_id,
                'type' => 'accountability_comment',
                'title' => $comment->displayName() . ' commented on your check-in',
                'message' => Str::limit($comment->comment, 120),
                'icon' => 'mdi-comment-text-outline',
                'icon_color' => 'bg-warning',
                'url' => route('accountability.show', $checkIn->id),
                'unique_key' => null,
            ]);
        }

        return redirect()
            ->to(route('accountability.shared.show', $uuid) . '#comments')
            ->with('comment_success', true);
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'word_days' => 'required|integer|min:0|max:7',
            'word_quality' => 'required|integer|min:1|max:5',
            'prayer_days' => 'required|integer|min:0|max:7',
            'prayer_quality' => 'required|integer|min:1|max:5',
            'fellowship_level' => 'required|string|in:' . implode(',', self::FELLOWSHIP_LEVELS),
            'corner_man_prayer_request' => 'nullable|boolean',
            'corner_man_asked_how_to_pray' => 'nullable|boolean',
            'corner_man_encouragement' => 'nullable|boolean',
            'corner_man_multiple_touchpoints' => 'nullable|boolean',
            'overall_number' => 'required|integer|min:1|max:10',
            'overall_why' => 'nullable|string|max:2000',
            'one_praise' => 'nullable|string|max:2000',
            'one_prayer' => 'nullable|string|max:2000',
        ]);

        foreach (['corner_man_prayer_request', 'corner_man_asked_how_to_pray', 'corner_man_encouragement', 'corner_man_multiple_touchpoints'] as $field) {
            $data[$field] = $request->boolean($field);
        }

        return $data;
    }
}
