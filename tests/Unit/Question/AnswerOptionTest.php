<?php

declare(strict_types=1);

namespace Elros\Testing\Tests\Unit\Question;

use Elros\Testing\Question\AnswerOption;
use PHPUnit\Framework\TestCase;

final class AnswerOptionTest extends TestCase
{
    public function testCorrectAnswer(): void
    {
        $answer = new AnswerOption(1, '4', true);

        self::assertSame(1, $answer->getId());
        self::assertSame('4', $answer->getText());
        self::assertTrue($answer->isCorrect());
    }

    public function testIncorrectAnswer(): void
    {
        $answer = new AnswerOption(2, '5', false);

        self::assertSame(2, $answer->getId());
        self::assertSame('5', $answer->getText());
        self::assertFalse($answer->isCorrect());
    }
}
