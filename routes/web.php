<?php

use App\Http\Controllers\ChapterController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TranslationController;
use App\Http\Controllers\VerseCommentaryController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


Route::get('/home', [HomeController::class, 'index'])->name('home.index');


Route::group(['prefix' => 'chapters'], function () {
    Route::get('/lookup', [ChapterController::class, 'lookup']);
});


Route::group(['prefix' => 'translations'], function () {
    Route::get('/verses', [TranslationController::class, 'verses']);
});
Route::resource('translations', TranslationController::class);
    

Route::resource('verses', VerseCommentaryController::class);