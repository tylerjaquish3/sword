<?php

namespace App\Http\Controllers;

class OfflineReaderController extends Controller
{
    public function index()
    {
        return view('offline-reader.index');
    }

    public function prayers()
    {
        return view('offline-reader.prayers');
    }

    public function digest()
    {
        return view('offline-reader.digest');
    }

    public function unavailable()
    {
        return view('offline-reader.unavailable');
    }
}
