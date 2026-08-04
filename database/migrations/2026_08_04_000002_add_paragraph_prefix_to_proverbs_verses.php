<?php

use App\Models\Book;
use App\Models\Chapter;
use App\Models\Verse;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    private string $paragraphPrefix = '</p><p>';

    public function up(): void
    {
        $proverbs = Book::where('name', 'Proverbs')->first();

        if (! $proverbs) {
            return;
        }

        $chapterIds = Chapter::where('book_id', $proverbs->id)->pluck('id');

        // Verse 1 of each chapter already starts its own paragraph, so only
        // verses 2+ need the break — this covers all translations since
        // prefix is keyed on chapter_id + number, not translation_id.
        Verse::whereIn('chapter_id', $chapterIds)
            ->where('number', '>', 1)
            ->where(function ($query) {
                $query->whereNull('prefix')->orWhere('prefix', '');
            })
            ->update(['prefix' => $this->paragraphPrefix]);
    }

    public function down(): void
    {
        $proverbs = Book::where('name', 'Proverbs')->first();

        if (! $proverbs) {
            return;
        }

        $chapterIds = Chapter::where('book_id', $proverbs->id)->pluck('id');

        Verse::whereIn('chapter_id', $chapterIds)
            ->where('prefix', $this->paragraphPrefix)
            ->update(['prefix' => null]);
    }
};
