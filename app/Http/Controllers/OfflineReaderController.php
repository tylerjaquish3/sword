<?php

namespace App\Http\Controllers;

class OfflineReaderController extends Controller
{
    public function index()
    {
        return view('offline-reader.index');
    }
}
