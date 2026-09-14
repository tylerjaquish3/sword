<?php

namespace App\Http\Controllers;

use App\Models\AccountabilityCheckIn;
use Illuminate\Support\Facades\Auth;

class AccountabilityController extends Controller
{
    public function show(AccountabilityCheckIn $checkIn)
    {
        abort_if($checkIn->user_id !== Auth::id(), 403);

        $comments = $checkIn->guestComments()->orderBy('created_at')->get();

        return view('accountability.show', compact('checkIn', 'comments'));
    }

    public function destroy(AccountabilityCheckIn $checkIn)
    {
        abort_if($checkIn->user_id !== Auth::id(), 403);

        $checkIn->delete();

        return response()->json(['success' => true]);
    }

    public function markShared(AccountabilityCheckIn $checkIn)
    {
        abort_if($checkIn->user_id !== Auth::id(), 403);

        $checkIn->update(['is_shared' => true]);

        return redirect()->route('accountability.share.link', $checkIn->uuid);
    }
}
