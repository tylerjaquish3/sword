<?php

namespace App\Http\Controllers;

use App\Models\Prayer;
use App\Models\PrayerPrompt;
use App\Models\PrayerType;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PrayerController extends Controller
{
    public function index()
    {
        $prayers = Prayer::with('type')->orderByDesc('date')->get()->groupBy('date');
        $prayerTypes = PrayerType::all();
        $today = Carbon::now()->format('m/d/Y');
        $lastPrayer = Prayer::orderByDesc('created_at')->first();
        $todayPrompt = PrayerPrompt::forToday();
        $allPrompts = PrayerPrompt::get()->keyBy(fn($p) => $p->day_of_week ?? 'default');

        return view('prayers.index', compact('prayers', 'prayerTypes', 'today', 'lastPrayer', 'todayPrompt', 'allPrompts'));
    }

    public function create()
    {
        $today = Carbon::now()->format('m/d/Y');

        return view('prayers.create', compact('today'));
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $clientUuid = $data['client_uuid'] ?? null;
        unset($data['client_uuid']);

        $typeFields = array_filter(
            $data,
            fn ($value, $key) => $key !== '_token' && $key !== 'date' && $value !== null,
            ARRAY_FILTER_USE_BOTH
        );

        // Dedupe only makes sense for a single-prayer request (the Offline Reader always
        // sends exactly one type field at a time). A multi-type request falls back to
        // plain creates so a stray client_uuid can never cause a type to be silently dropped.
        $useDedupe = $clientUuid && count($typeFields) === 1;

        foreach ($typeFields as $key => $value) {
            $prayerTypeId = str_replace('type', '', $key);

            if ($useDedupe) {
                Prayer::firstOrCreate(
                    ['client_uuid' => $clientUuid],
                    ['date' => $data['date'], 'content' => $value, 'prayer_type_id' => $prayerTypeId]
                );
            } else {
                Prayer::create([
                    'date'           => $data['date'],
                    'content'        => $value,
                    'prayer_type_id' => $prayerTypeId,
                ]);
            }
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