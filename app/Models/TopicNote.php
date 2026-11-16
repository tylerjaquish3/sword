<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TopicNote extends Model
{
    protected $guarded = [];

    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }

    public function verses()
    {
        return $this->belongsToMany(Verse::class, 'topic_note_verses')
                    ->with('chapter.book');
    }

    /**
     * Linked verses in Bible order, with consecutive verses of the same chapter and
     * translation merged into one passage ("Hebrews 9:26-28", text as one paragraph).
     * Requires verses.chapter.book to be loaded.
     */
    public function verseGroups(): array
    {
        $groups = [];

        foreach ($this->verses->groupBy('translation_id') as $translationVerses) {
            $run = [];
            foreach ($translationVerses->sortBy([['chapter_id', 'asc'], ['number', 'asc']]) as $v) {
                $last = end($run);
                if ($last && $last->chapter_id === $v->chapter_id && $v->number === $last->number + 1) {
                    $run[] = $v;
                } else {
                    if ($run) $groups[] = $run;
                    $run = [$v];
                }
            }
            if ($run) $groups[] = $run;
        }

        usort($groups, fn($a, $b) => [$a[0]->chapter_id, $a[0]->number, $a[0]->translation_id]
            <=> [$b[0]->chapter_id, $b[0]->number, $b[0]->translation_id]);

        return array_map(function ($run) {
            $first = $run[0];
            $last = end($run);
            $reference = count($run) > 1
                ? preg_replace('/:\d+$/', '', $first->reference) . ':' . $first->number . '-' . $last->number
                : $first->reference;

            return [
                'reference' => $reference,
                'references' => array_map(fn($v) => $v->reference, $run),
                'text'      => implode(' ', array_map(fn($v) => trim($v->text), $run)),
                'url'       => route('translations.index') . '?translation=' . $first->translation_id
                             . '&book=' . $first->chapter->book->id
                             . '&chapter=' . $first->chapter->number,
            ];
        }, $groups);
    }
}
