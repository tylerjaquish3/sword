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

        return view('prayers.index', compact('prayers', 'prayerTypes'));
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

            // remove the word type from the key
            $key = str_replace('type', '', $key);

            $values = [
                'date' => $data['date'],
                'content' => $value,
                'prayer_type_id' => $key,
            ];

            Prayer::create($values);
        }

        return redirect()->route('prayers.index');
    }
}