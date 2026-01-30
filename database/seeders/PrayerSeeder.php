<?php

namespace Database\Seeders;

use App\Models\Prayer;
use Illuminate\Database\Seeder;

class PrayerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Each day has all 4 prayer types (ACTS format)
        $days = [
            // Day 1 - 7 days ago
            [
                'date' => now()->subDays(7)->toDateString(),
                'prayers' => [
                    ['content' => 'Lord, You are holy and worthy of all praise. Your love is everlasting, and Your mercy is new every morning. I stand in awe of Your greatness and majesty.', 'prayer_type_id' => 1],
                    ['content' => 'Lord, I confess that I have fallen short of Your glory. I have let worry and anxiety take hold when I should have trusted in You. Forgive me and renew a right spirit within me.', 'prayer_type_id' => 2],
                    ['content' => 'Thank You, Lord, for the gift of family and friends. Thank You for provision, for health, and for the breath in my lungs. Every good gift comes from You.', 'prayer_type_id' => 3],
                    ['content' => 'Lord, I lift up my family to You. Protect them, guide them, and draw them closer to You. Give us wisdom and unity as we walk through life together.', 'prayer_type_id' => 4],
                ],
            ],
            // Day 2 - 3 days ago
            [
                'date' => now()->subDays(3)->toDateString(),
                'prayers' => [
                    ['content' => 'Father, I praise You for Your faithfulness. You are the Alpha and Omega, the beginning and the end. There is none like You in all the earth.', 'prayer_type_id' => 1],
                    ['content' => 'Father, I confess my impatience with others. Help me to extend the same grace You have shown me. Create in me a clean heart, O God.', 'prayer_type_id' => 2],
                    ['content' => 'Father, I thank You for answered prayers. You have been so faithful even when I could not see Your hand at work. Your timing is always perfect.', 'prayer_type_id' => 3],
                    ['content' => 'Father, I ask for Your guidance in the decisions ahead. Open doors that should be opened and close those that should remain shut. Lead me in the path of righteousness for Your name\'s sake.', 'prayer_type_id' => 4],
                ],
            ],
        ];

        foreach ($days as $day) {
            foreach ($day['prayers'] as $prayer) {
                Prayer::create([
                    'date' => $day['date'],
                    'content' => $prayer['content'],
                    'prayer_type_id' => $prayer['prayer_type_id'],
                ]);
            }
        }
    }
}
