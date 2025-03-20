<?php

namespace App\Http\Controllers;

use App\Models\Verse;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TopicController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $verses = Verse::select('key_words')->where('translation_id', 3)->get();
        
        $topics = [];
        foreach ($verses as $verse) {
            // Split the key_words by space and add to topics array
            $keyWords = explode(' ', $verse->key_words);
            foreach ($keyWords as $keyWord) {

                if ($keyWord == '') {
                    continue;
                }

                if (array_key_exists($keyWord, $topics)) {
                    $topics[$keyWord]['count']++;
                } else {
                    $topics[$keyWord] = [
                        'topic' => $keyWord,
                        'count' => 1,
                    ];
                }
            }
        }
        // dd($topics);

        return view('topics.index', compact('topics'));
    }

    public function create()
    {
        echo password_hash("StepStone1", PASSWORD_DEFAULT);
        return view('topics.create');
    }
}