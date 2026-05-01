<?php

namespace App\Console\Commands;

use App\Models\Translation;
use App\Models\Verse;
use Illuminate\Console\Command;

class ExportMsgMigration extends Command
{
    protected $signature = 'msg:export-migration';

    protected $description = 'Generate a migration file from locally imported MSG verses so they can be deployed to production';

    public function handle(): void
    {
        $translation = Translation::where('name', 'MSG')->first();

        if (!$translation) {
            $this->error('No MSG translation found. Run php artisan msg:import first.');
            return;
        }

        $verseCount = Verse::where('translation_id', $translation->id)->count();

        if ($verseCount === 0) {
            $this->error('No MSG verses found. Run php artisan msg:import first.');
            return;
        }

        $this->info("Found {$verseCount} MSG verses. Generating migration...");

        $insertBlocks = [];

        Verse::where('translation_id', $translation->id)
            ->select('chapter_id', 'number', 'reference', 'text')
            ->orderBy('chapter_id')
            ->orderBy('number')
            ->chunk(250, function ($chunk) use (&$insertBlocks) {
                $rows = $chunk->map(fn ($v) =>
                    '            [' .
                    "'chapter_id' => {$v->chapter_id}, " .
                    "'translation_id' => \$translationId, " .
                    "'number' => {$v->number}, " .
                    "'reference' => " . var_export($v->reference, true) . ', ' .
                    "'text' => " . var_export($v->text, true) .
                    '],'
                )->implode("\n");

                $insertBlocks[] = "        DB::table('verses')->insertOrIgnore([\n{$rows}\n        ]);";
            });

        $description  = var_export($translation->description ?? 'The Message', true);
        $insertsBlock = implode("\n\n", $insertBlocks);

        $template = <<<'TEMPLATE'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $existing = DB::table('translations')->where('name', 'MSG')->first();
        $translationId = $existing
            ? $existing->id
            : DB::table('translations')->insertGetId(['name' => 'MSG', 'description' => __DESCRIPTION__]);

__INSERTS__
    }

    public function down(): void
    {
        $translation = DB::table('translations')->where('name', 'MSG')->first();
        if ($translation) {
            DB::table('verses')->where('translation_id', $translation->id)->delete();
            DB::table('translations')->where('id', $translation->id)->delete();
        }
    }
};
TEMPLATE;

        $content = str_replace(
            ['__DESCRIPTION__', '__INSERTS__'],
            [$description, $insertsBlock],
            $template
        );

        $filename = date('Y_m_d_His') . '_seed_msg_verses.php';
        $path     = database_path('migrations/' . $filename);

        file_put_contents($path, $content);

        $this->info("Written to database/migrations/{$filename}");
        $this->info("Commit that file and run 'php artisan migrate' on production.");
    }
}
