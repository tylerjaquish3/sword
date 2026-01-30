<?php

namespace Database\Seeders;

use App\Models\Topic;
use Illuminate\Database\Seeder;

class TopicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $topics = [
            [
                'name' => 'Salvation',
                'description' => 'The gift of eternal life through faith in Jesus Christ',
                'keywords' => 'saved, born again, redemption, eternal life, forgiveness, grace, believe',
            ],
            [
                'name' => 'Faith',
                'description' => 'Trusting in God and His promises',
                'keywords' => 'trust, believe, hope, confidence, assurance, faithfulness',
            ],
            [
                'name' => 'Love',
                'description' => 'God\'s love for us and how we should love others',
                'keywords' => 'charity, compassion, kindness, agape, beloved, lovingkindness',
            ],
            [
                'name' => 'Prayer',
                'description' => 'Communicating with God',
                'keywords' => 'pray, petition, intercession, supplication, ask, seek, knock',
            ],
            [
                'name' => 'Forgiveness',
                'description' => 'God\'s forgiveness and forgiving others',
                'keywords' => 'pardon, mercy, forgive, reconciliation, sins, trespass',
            ],
            [
                'name' => 'Hope',
                'description' => 'Confident expectation in God\'s promises',
                'keywords' => 'trust, expectation, future, promise, anchor, wait',
            ],
            [
                'name' => 'Wisdom',
                'description' => 'Godly wisdom and understanding',
                'keywords' => 'knowledge, understanding, discernment, prudence, wise, foolish',
            ],
            [
                'name' => 'Strength',
                'description' => 'Finding strength in the Lord',
                'keywords' => 'power, might, courage, endurance, strong, weakness, able',
            ],
            [
                'name' => 'Peace',
                'description' => 'The peace of God that surpasses understanding',
                'keywords' => 'rest, calm, tranquility, shalom, comfort, anxious, worry',
            ],
            [
                'name' => 'Fear',
                'description' => 'Overcoming fear through faith in God',
                'keywords' => 'afraid, terror, anxiety, courage, bold, dread, trembling',
            ],
            [
                'name' => 'Healing',
                'description' => 'Physical and spiritual healing from God',
                'keywords' => 'restore, health, sick, disease, whole, physician, cure',
            ],
            [
                'name' => 'Joy',
                'description' => 'The joy that comes from the Lord',
                'keywords' => 'rejoice, gladness, happiness, delight, blessed, cheerful',
            ],
            [
                'name' => 'Marriage',
                'description' => 'God\'s design for marriage and family',
                'keywords' => 'husband, wife, wedding, covenant, spouse, union, family',
            ],
            [
                'name' => 'Anger',
                'description' => 'Managing anger and wrath',
                'keywords' => 'wrath, fury, temper, rage, slow to anger, patience',
            ],
            [
                'name' => 'Patience',
                'description' => 'Developing patience and longsuffering',
                'keywords' => 'endurance, perseverance, wait, longsuffering, steadfast',
            ],
        ];

        foreach ($topics as $topic) {
            Topic::create($topic);
        }
    }
}
