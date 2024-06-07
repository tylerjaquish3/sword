<?php

namespace App\Jobs;

use App\Enum\CommonWords;
use App\Models\Verse;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class DetermineKeyWords implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $success        = true;
        $message        = "";

        $commonWords = CommonWords::COMMON_WORDS;

        try {
            // Loop through each verse and remove any words that are in the common words list, then update the verse->key_words field with what is left
            $verses = Verse::all();
            foreach ($verses as $verse) {

                if ($verse->key_words) {
                    continue;
                }

                $verseText = $verse->text;
                $verseText = preg_replace('/[^a-zA-Z0-9\s]/', '', $verseText);
                $verseText = strtolower($verseText);
                $verseText = explode(' ', $verseText);
                $verseText = array_diff($verseText, $commonWords);
                $verseText = implode(' ', $verseText);
                $verse->key_words = $verseText;

                $verse->save();
            }
       
        } catch (\Exception $e) {
            $success = false;
            $message = $e->getMessage();
            dd($e);
        }

        if ($success) {
            echo 'Finished!'.PHP_EOL;
        }

        echo $message;
    }

}