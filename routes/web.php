<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\ChapterController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MemoryController;
use App\Http\Controllers\PrayerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TranslationController;
use App\Http\Controllers\CommentaryController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\TopicController;
use Illuminate\Support\Facades\Route;

// Auth routes (guest only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// All application routes require authentication
Route::middleware('auth')->group(function () {

    // Redirect / to /home
    Route::redirect('/', '/home');

    Route::get('/home', [HomeController::class, 'index'])->name('home.index');
    Route::get('/search', [SearchController::class, 'index'])->name('search.index');
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::patch('/profile/default-translation', [ProfileController::class, 'updateDefaultTranslation'])->name('profile.default-translation');

    // Book routes
    Route::get('/books/{book}', [BookController::class, 'show'])->name('books.show');
    Route::put('/books/{book}', [BookController::class, 'update'])->name('books.update');

    // Chapter routes
    Route::group(['prefix' => 'chapters'], function () {
        Route::get('/lookup', [ChapterController::class, 'lookup']);
        Route::get('/comments', [ChapterController::class, 'getComments']);
        Route::get('/read-status', [ChapterController::class, 'readStatus']);
        Route::post('/mark-read', [ChapterController::class, 'markRead']);
        Route::get('/last-read', [ChapterController::class, 'lastRead']);
        Route::post('/{chapter}/comment', [ChapterController::class, 'storeComment']);
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

    Route::delete('prayers/date', [PrayerController::class, 'destroyByDate'])->name('prayers.destroyByDate');
    Route::resource('prayers', PrayerController::class);
    Route::get('/topics/verse-search', [TopicController::class, 'verseSearch'])->name('topics.verse-search');
    Route::resource('topics', TopicController::class);
    Route::post('/topics/{topic}/notes', [TopicController::class, 'storeNote'])->name('topics.notes.store');
    Route::delete('/topics/notes/{note}', [TopicController::class, 'destroyNote'])->name('topics.notes.destroy');

    // Memory routes
    Route::resource('memory', MemoryController::class)->except(['create', 'show', 'edit']);
    Route::post('/memory/{memory}/complete', [MemoryController::class, 'complete'])->name('memory.complete');
    Route::post('/memory/{memory}/uncomplete', [MemoryController::class, 'uncomplete'])->name('memory.uncomplete');
    Route::get('/memory/verses', [MemoryController::class, 'getVerses'])->name('memory.verses');

    Route::group(['prefix' => 'translations'], function () {
        Route::get('/verses', [TranslationController::class, 'verses']);
        Route::get('/verse/{verse}', [TranslationController::class, 'getVerse']);
        Route::get('/verse-by-location', [TranslationController::class, 'getVerseByLocation']);
        Route::put('/verse/{verse}', [TranslationController::class, 'updateVerse']);
    });
    Route::resource('translations', TranslationController::class);

});
