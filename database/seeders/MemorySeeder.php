<?php

namespace Database\Seeders;

use App\Models\Memory;
use App\Models\User;
use App\Models\Verse;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class MemorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a test user if one doesn't exist
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Tyler',
                'password' => bcrypt('password'),
            ]
        );

        // Get some verses for testing - assuming KJV translation (id=1) exists
        // We'll pick verses from common memory passages
        
        // Memory Goal 1: John 3:16-17 (Active, recently started)
        $memory1 = Memory::create([
            'user_id' => $user->id,
            'title' => 'John 3:16-17 - For God So Loved',
            'start_date' => Carbon::now()->subDays(3),
            'end_date' => Carbon::now()->addDays(14),
            'notes' => 'Classic salvation passage. Focus on memorizing word-for-word.',
        ]);
        
        // Get John (book 43) chapter 3 verses 16-17
        $john3Verses = Verse::whereHas('chapter', function ($query) {
            $query->where('book_id', 43)->where('number', 3);
        })->whereIn('number', [16, 17])->where('translation_id', 1)->pluck('id');
        
        if ($john3Verses->isNotEmpty()) {
            $memory1->verses()->attach($john3Verses);
        }

        // Memory Goal 2: Romans 8:28 (Active, started a while ago)
        $memory2 = Memory::create([
            'user_id' => $user->id,
            'title' => 'Romans 8:28',
            'start_date' => Carbon::now()->subDays(10),
            'end_date' => Carbon::now()->addDays(4),
            'notes' => 'All things work together for good.',
        ]);
        
        // Get Romans (book 45) chapter 8 verse 28
        $romans8Verses = Verse::whereHas('chapter', function ($query) {
            $query->where('book_id', 45)->where('number', 8);
        })->whereIn('number', [28])->where('translation_id', 1)->pluck('id');
        
        if ($romans8Verses->isNotEmpty()) {
            $memory2->verses()->attach($romans8Verses);
        }

        // Memory Goal 3: Psalm 23:1-6 (Active, longer passage)
        $memory3 = Memory::create([
            'user_id' => $user->id,
            'title' => 'Psalm 23 - The Lord is My Shepherd',
            'start_date' => Carbon::now()->subDays(7),
            'end_date' => Carbon::now()->addMonth(),
            'notes' => 'Complete psalm. Take it verse by verse.',
        ]);
        
        // Get Psalms (book 19) chapter 23 all verses
        $psalm23Verses = Verse::whereHas('chapter', function ($query) {
            $query->where('book_id', 19)->where('number', 23);
        })->where('translation_id', 1)->pluck('id');
        
        if ($psalm23Verses->isNotEmpty()) {
            $memory3->verses()->attach($psalm23Verses);
        }

        // Memory Goal 4: Philippians 4:6-7 (Completed)
        $memory4 = Memory::create([
            'user_id' => $user->id,
            'title' => 'Philippians 4:6-7 - Do Not Be Anxious',
            'start_date' => Carbon::now()->subDays(30),
            'end_date' => Carbon::now()->subDays(10),
            'completed_at' => Carbon::now()->subDays(12),
            'notes' => 'Great verses for dealing with anxiety.',
        ]);
        
        // Get Philippians (book 50) chapter 4 verses 6-7
        $phil4Verses = Verse::whereHas('chapter', function ($query) {
            $query->where('book_id', 50)->where('number', 4);
        })->whereIn('number', [6, 7])->where('translation_id', 1)->pluck('id');
        
        if ($phil4Verses->isNotEmpty()) {
            $memory4->verses()->attach($phil4Verses);
        }

        // Memory Goal 5: Proverbs 3:5-6 (Completed)
        $memory5 = Memory::create([
            'user_id' => $user->id,
            'title' => 'Proverbs 3:5-6 - Trust in the Lord',
            'start_date' => Carbon::now()->subDays(45),
            'end_date' => Carbon::now()->subDays(25),
            'completed_at' => Carbon::now()->subDays(28),
            'notes' => 'Foundational wisdom verses.',
        ]);
        
        // Get Proverbs (book 20) chapter 3 verses 5-6
        $prov3Verses = Verse::whereHas('chapter', function ($query) {
            $query->where('book_id', 20)->where('number', 3);
        })->whereIn('number', [5, 6])->where('translation_id', 1)->pluck('id');
        
        if ($prov3Verses->isNotEmpty()) {
            $memory5->verses()->attach($prov3Verses);
        }

        // Memory Goal 6: Matthew 6:33 (Completed quickly)
        $memory6 = Memory::create([
            'user_id' => $user->id,
            'title' => 'Matthew 6:33 - Seek First the Kingdom',
            'start_date' => Carbon::now()->subDays(20),
            'end_date' => Carbon::now()->subDays(15),
            'completed_at' => Carbon::now()->subDays(17),
            'notes' => 'Short but powerful verse.',
        ]);
        
        // Get Matthew (book 40) chapter 6 verse 33
        $matt6Verses = Verse::whereHas('chapter', function ($query) {
            $query->where('book_id', 40)->where('number', 6);
        })->whereIn('number', [33])->where('translation_id', 1)->pluck('id');
        
        if ($matt6Verses->isNotEmpty()) {
            $memory6->verses()->attach($matt6Verses);
        }

        $this->command->info('Memory seeder completed! Created 6 memory goals (3 active, 3 completed).');
    }
}
