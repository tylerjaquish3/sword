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
            Verse::whereNull('key_words')->chunk(500, function ($verses) use ($commonWords) {
                foreach ($verses as $verse) {
                    $verseText = preg_replace('/[^a-zA-Z0-9\s]/', '', $verse->text);
                    $verseText = strtolower($verseText);
                    $verseText = array_diff(explode(' ', $verseText), $commonWords);
                    $verse->key_words = implode(' ', $verseText);
                    $verse->save();
                }
            });
       
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