<?php

namespace App\Http\Controllers;

use App\Models\Topic;
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
        $topics = Topic::all();

        return view('topics.index', compact('topics'));
    }

    public function create()
    {
        echo password_hash("StepStone1", PASSWORD_DEFAULT);
        return view('topics.create');
    }

    /**
     * Show the form for editing the specified topic.
     */
    public function edit(Topic $topic)
    {
        return view('topics.edit', compact('topic'));
    }
}