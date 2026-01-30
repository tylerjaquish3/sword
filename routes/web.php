<?php

use App\Http\Controllers\ChapterController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PrayerController;
use App\Http\Controllers\TranslationController;
use App\Http\Controllers\CommentaryController;
use App\Http\Controllers\TopicController;
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

// Redirect / to /home
Route::redirect('/', '/home');

Route::get('/home', [HomeController::class, 'index'])->name('home.index');


Route::group(['prefix' => 'chapters'], function () {
    Route::get('/lookup', [ChapterController::class, 'lookup']);
});

// Commentary routes
Route::get('/commentary', [CommentaryController::class, 'index'])->name('commentary.index');
Route::get('/commentary/create', [CommentaryController::class, 'create'])->name('commentary.create');
Route::post('/commentary', [CommentaryController::class, 'store'])->name('commentary.store');

// Chapter comment routes
Route::get('/commentary/chapter/{chapterComment}/edit', [CommentaryController::class, 'editChapter'])->name('commentary.edit-chapter');
Route::put('/commentary/chapter/{chapterComment}', [CommentaryController::class, 'updateChapter'])->name('commentary.update-chapter');
Route::delete('/commentary/chapter/{chapterComment}', [CommentaryController::class, 'destroyChapter'])->name('commentary.destroy-chapter');

// Verse comment routes
Route::get('/commentary/verse/{verseComment}/edit', [CommentaryController::class, 'editVerse'])->name('commentary.edit-verse');
Route::put('/commentary/verse/{verseComment}', [CommentaryController::class, 'updateVerse'])->name('commentary.update-verse');
Route::delete('/commentary/verse/{verseComment}', [CommentaryController::class, 'destroyVerse'])->name('commentary.destroy-verse');

Route::resource('prayers', PrayerController::class);
Route::resource('topics', TopicController::class);

Route::group(['prefix' => 'translations'], function () {
    Route::get('/verses', [TranslationController::class, 'verses']);
});
Route::resource('translations', TranslationController::class);