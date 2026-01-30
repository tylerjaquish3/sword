<?php

namespace Database\Seeders;

use App\Models\ChapterComment;
use App\Models\VerseComment;
use App\Models\Chapter;
use App\Models\Verse;
use Illuminate\Database\Seeder;

class CommentarySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some chapters and verses to comment on
        $chapters = Chapter::take(10)->get();
        $verses = Verse::take(20)->get();

        // Sample chapter comments
        $chapterComments = [
            [
                'comment' => 'This chapter establishes the foundation of creation and God\'s sovereign power over all things. The repeated phrase "and God saw that it was good" emphasizes the intentionality and goodness of God\'s creative work.',
            ],
            [
                'comment' => 'The fall of man is depicted here with profound theological implications. Notice how the serpent twists God\'s words, and how Adam and Eve both shift blame rather than taking responsibility.',
            ],
            [
                'comment' => 'The covenant with Noah represents God\'s commitment to preserve creation despite human sinfulness. The rainbow serves as a perpetual reminder of divine mercy.',
            ],
            [
                'comment' => 'Abraham\'s call demonstrates the principle of faith - leaving the familiar to follow God\'s promise. This sets the stage for the entire narrative of redemption history.',
            ],
            [
                'comment' => 'The Ten Commandments are divided into two tablets: the first dealing with our relationship to God, the second with our relationship to others. Jesus summarized these as loving God and loving neighbor.',
            ],
            [
                'comment' => 'The Sermon on the Mount presents the ethics of the Kingdom of God. Jesus does not abolish the law but fulfills it, revealing its deeper spiritual intent.',
            ],
            [
                'comment' => 'Paul\'s letter to the Romans provides the most systematic presentation of the gospel. This chapter specifically addresses justification by faith apart from works of the law.',
            ],
            [
                'comment' => 'The love chapter (1 Corinthians 13) defines agape love not as an emotion but as a choice of action. Without love, even the greatest spiritual gifts are meaningless.',
            ],
        ];

        // Create chapter comments
        foreach ($chapters as $index => $chapter) {
            if (isset($chapterComments[$index])) {
                ChapterComment::create([
                    'chapter_id' => $chapter->id,
                    'user_id' => 1,
                    'comment' => $chapterComments[$index]['comment'],
                ]);
            }
        }

        // Sample verse comments
        $verseComments = [
            [
                'comment' => '"In the beginning" - This phrase establishes that there was a definite starting point to creation. God exists outside of time and brought time itself into existence.',
            ],
            [
                'comment' => 'The Hebrew word "bara" (created) is used exclusively of divine activity, emphasizing that creation is uniquely God\'s work.',
            ],
            [
                'comment' => 'Light being created before the sun suggests that God Himself was the source of light initially, pointing to the theme of divine light throughout Scripture.',
            ],
            [
                'comment' => '"Let us make man" - The plural "us" has been traditionally understood as an early hint of the Trinity in the Hebrew scriptures.',
            ],
            [
                'comment' => 'Being made in God\'s image (imago Dei) gives humanity inherent dignity and worth, distinguishing us from the rest of creation.',
            ],
            [
                'comment' => 'The Sabbath rest is not because God was tired, but to establish a pattern of work and rest for human flourishing.',
            ],
            [
                'comment' => '"For God so loved the world" - The Greek word "kosmos" indicates God\'s love extends to all of creation, not just a select few.',
            ],
            [
                'comment' => '"Believe" (pisteuō) in Greek means more than intellectual assent - it implies trust, reliance, and commitment.',
            ],
            [
                'comment' => 'This verse is often called the "gospel in miniature" because it encapsulates the core message of Christianity in one sentence.',
            ],
            [
                'comment' => '"The Word was God" - John makes an unambiguous claim about the deity of Christ from the very beginning of his Gospel.',
            ],
            [
                'comment' => '"Logos" (Word) would have been familiar to both Jewish and Greek readers, connecting Hebrew wisdom tradition with Greek philosophy.',
            ],
            [
                'comment' => 'Paul\'s statement "all have sinned" is universal in scope - there are no exceptions to humanity\'s need for salvation.',
            ],
            [
                'comment' => '"Fall short of the glory of God" suggests that sin is not just breaking rules but failing to reflect God\'s character as we were designed to do.',
            ],
            [
                'comment' => 'Justification is a legal term meaning to be declared righteous, not to be made righteous - it\'s about our standing before God.',
            ],
            [
                'comment' => '"By grace through faith" - Grace is the source, faith is the means. Neither originates with us; both are gifts from God.',
            ],
        ];

        // Create verse comments
        foreach ($verses as $index => $verse) {
            if (isset($verseComments[$index])) {
                VerseComment::create([
                    'verse_id' => $verse->id,
                    'user_id' => 1,
                    'comment' => $verseComments[$index]['comment'],
                ]);
            }
        }

        $this->command->info('Commentary seeder completed: ' . count($chapterComments) . ' chapter comments and ' . count($verseComments) . ' verse comments created.');
    }
}
