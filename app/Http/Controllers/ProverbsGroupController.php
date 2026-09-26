<?php

namespace App\Http\Controllers;

use App\Models\ProverbsGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProverbsGroupController extends Controller
{
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
}
