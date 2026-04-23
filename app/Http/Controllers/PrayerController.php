<?php

namespace App\Http\Controllers;

use App\Models\Prayer;
use App\Models\PrayerType;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PrayerController extends Controller
{
    public function index()
    {
        $prayers = Prayer::with('type')->get()->groupBy('date');
        $prayerTypes = PrayerType::all();
        $today = Carbon::now()->format('m/d/Y');
        $lastPrayer = Prayer::orderByDesc('created_at')->first();

        return view('prayers.index', compact('prayers', 'prayerTypes', 'today', 'lastPrayer'));
    }

    public function create()
    {
        $today = Carbon::now()->format('m/d/Y');

        return view('prayers.create', compact('today'));
    }

    public function store(Request $request)
    {
        $data = $request->all();

        foreach ($data as $key => $value) {
            if ($key === '_token' || $key == 'date' || $value === null) {
                continue;
            }

            $key = str_replace('type', '', $key);

            Prayer::create([
                'date'           => $data['date'],
                'content'        => $value,
                'prayer_type_id' => $key,
            ]);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('prayers.index');
    }

    public function destroyByDate(Request $request)
    {
        Prayer::where('date', $request->date)->delete();

        return response()->json(['success' => true]);
    }
}