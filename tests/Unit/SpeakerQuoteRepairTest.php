<?php

namespace Tests\Unit;

use App\Support\SpeakerQuoteRepair;
use PHPUnit\Framework\TestCase;

class SpeakerQuoteRepairTest extends TestCase
{
    /**
     * @dataProvider brokenVerses
     */
    public function test_it_restores_the_quotes_and_space_between_speakers(string $broken, string $expected): void
    {
        $this->assertSame($expected, SpeakerQuoteRepair::repair($broken));
    }

    public static function brokenVerses(): array
    {
        return [
            'Luke 22:38 NLT' => [
                '"Lord," they replied, "we have two swords among us.That\'s enough," he said.',
                '"Lord," they replied, "we have two swords among us." "That\'s enough," he said.',
            ],
            'Luke 9:13 NLT' => [
                'But Jesus said, "You feed them.Impossible!" they protested. "We have only five loaves."',
                'But Jesus said, "You feed them." "Impossible!" they protested. "We have only five loaves."',
            ],
            'question mark' => [
                'The man asked him, "What is your name?Jacob," he answered.',
                'The man asked him, "What is your name?" "Jacob," he answered.',
            ],
            'exclamation' => [
                'He said, "Abraham!Here I am," he replied.',
                'He said, "Abraham!" "Here I am," he replied.',
            ],
            'several breaks in one verse' => [
                'Peter said, "Do you love me?Yes, Lord," Peter replied, "you know.Then feed my lambs," Jesus said.',
                'Peter said, "Do you love me?" "Yes, Lord," Peter replied, "you know." "Then feed my lambs," Jesus said.',
            ],
        ];
    }

    /**
     * @dataProvider healthyVerses
     */
    public function test_it_leaves_healthy_text_alone(string $text): void
    {
        $this->assertSame($text, SpeakerQuoteRepair::repair($text));
    }

    public static function healthyVerses(): array
    {
        return [
            'already correct' => ['"Lord," they said, "we have two swords." "That\'s enough," he said.'],
            'no quotes at all' => ['In the beginning God created the heavens and the earth.'],
            'ellipsis after a quote' => ['That is why the Book says: "...Waheb in Suphah and the ravines, the Arnon'],
            'dotted leader line' => ['Reuben...............Eliezer son of Zicri'],
            'sentences with a space' => ['He said, "Go." Then he left.'],
        ];
    }
}
